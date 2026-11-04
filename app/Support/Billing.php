<?php
namespace App\Support;

use App\Models\User;
use App\Models\Setting;
use App\Models\UtilityBill;
use App\Models\DeviceDeclaration;
use Carbon\Carbon;

class Billing
{
    public static function amountToCents($amount): int
    {
        $amount = trim((string) $amount);
        if (! preg_match('/^([+-]?)(\d+)(?:\.(\d*))?(?:[eE]([+-]?\d+))?$/', $amount, $parts)) {
            return 0;
        }

        $digits = ltrim($parts[2].($parts[3] ?? ''), '0');
        if ($digits === '') {
            return 0;
        }
        if (strlen($digits) > 18) {
            return ($parts[1] ?? '') === '-' ? PHP_INT_MIN : PHP_INT_MAX;
        }

        $exponent = (int) ($parts[4] ?? 0);
        if ($exponent > 18) {
            return ($parts[1] ?? '') === '-' ? PHP_INT_MIN : PHP_INT_MAX;
        }
        if ($exponent < -18) {
            return 0;
        }
        $shift = 2 - strlen($parts[3] ?? '') + $exponent;
        $digitsValue = (int) $digits;
        if ($shift >= 0) {
            if (strlen($digits) + $shift > 18) {
                return ($parts[1] ?? '') === '-' ? PHP_INT_MIN : PHP_INT_MAX;
            }
            $cents = $digitsValue * (10 ** $shift);
        } else {
            $scale = -$shift;
            if ($scale > 18) {
                return 0;
            }
            $divisor = 10 ** $scale;
            $cents = intdiv($digitsValue, $divisor);
            if (($digitsValue % $divisor) >= intdiv($divisor + 1, 2)) {
                $cents++;
            }
        }

        return ($parts[1] ?? '') === '-' ? -$cents : $cents;
    }

    public static function centsToAmount(int $cents): string
    {
        $sign = $cents < 0 ? '-' : '';
        $absolute = abs($cents);

        return $sign.intdiv($absolute, 100).'.'.str_pad((string) ($absolute % 100), 2, '0', STR_PAD_LEFT);
    }

    /**
     * Split one utility total among currently bed-assigned tenants, assigning
     * remainder cents in ascending user ID order.
     */
    public static function electricityShares(Carbon $period): array
    {
        $period = $period->copy()->startOfMonth();
        $utility = UtilityBill::query()
            ->whereDate('period', $period->toDateString())
            ->where('type', 'electricity')
            ->first();
        $occupantIds = User::query()
            ->where('role', 'tenant')
            ->whereNotNull('bed_id')
            ->orderBy('id')
            ->pluck('id');

        if (! $utility || $occupantIds->isEmpty()) {
            return [];
        }

        $totalCents = max(0, self::amountToCents($utility->total_amount));
        $shareCents = intdiv($totalCents, $occupantIds->count());
        $remainderCents = $totalCents % $occupantIds->count();
        $shares = [];

        foreach ($occupantIds as $index => $userId) {
            $shares[$userId] = self::centsToAmount($shareCents + ($index < $remainderCents ? 1 : 0));
        }

        return $shares;
    }

    /**
     * Where a tenant stands in the current billing cycle.
     * status: paid | pending | overdue | due_soon | unpaid
     */
    public static function cycle(User $u, ?Carbon $now = null): array
    {
        $now = ($now ?? now())->copy()->startOfDay();
        $period = $now->copy()->startOfMonth();
        $dueDay = max(1, (int) $u->due_day);
        $due = $period->copy()->day(min($dueDay, $period->daysInMonth));

        $rentCents = self::effectiveRentCents($u);
        if ($u->bed && filter_var(Setting::get('rent.prorate', '0'), FILTER_VALIDATE_BOOLEAN) && $u->move_in_date) {
            $moveIn = $u->move_in_date->copy()->startOfDay();
            if ($moveIn->year === $period->year && $moveIn->month === $period->month) {
                $daysRemaining = $period->daysInMonth - $moveIn->day + 1;
                $rentCents = intdiv(
                    (2 * $rentCents * $daysRemaining) + $period->daysInMonth,
                    2 * $period->daysInMonth
                );
            }

        }

        $q = $u->payments()->whereDate('period', $period->toDateString());
        $verifiedTotalCents = (clone $q)->where('status', 'verified')->get(['amount'])
            ->sum(fn ($payment) => self::amountToCents($payment->amount));
        $electricityCents = 0;
        $devices = DeviceDeclaration::query()
            ->where('user_id', $u->id)
            ->where('status', 'approved')
            ->whereNotNull('reviewed_at')
            ->where('reviewed_at', '<', $period->copy()->addMonthNoOverflow())
            ->orderBy('id')
            ->get(['id', 'name', 'monthly_fee']);
        $deviceLines = $devices->map(fn ($device) => [
            'label' => $device->name.' surcharge',
            'amount' => self::centsToAmount(self::amountToCents($device->monthly_fee ?? '0.00')),
            'type' => 'device',
        ]);
        $deviceCents = $deviceLines->sum(fn ($line) => self::amountToCents($line['amount']));
        $totalCents = $rentCents + $deviceCents;
        $paidCents = min($verifiedTotalCents, $totalCents);
        $paid = $totalCents > 0 && $paidCents >= $totalCents;
        $pending = ! $paid && (clone $q)->where('status', 'pending')->exists();

        $graceDays = max(0, (int) Setting::get('due.grace_period_days', 3));
        $dueSoonDays = max(0, (int) Setting::get('due.due_soon_days', 5));

        if ($paid) {
            $status = 'paid';
            $next = $period->copy()->addMonthNoOverflow();
            $due = $next->copy()->day(min($dueDay, $next->daysInMonth)); // next cycle's due date
        } elseif ($pending) {
            $status = 'pending';
        } elseif ($paidCents > 0) {
            $status = 'partial';
        } elseif ($now->gt($due->copy()->addDays($graceDays))) {
            $status = 'overdue';
        } elseif ($now->lte($due) && $now->diffInDays($due) <= $dueSoonDays) {
            $status = 'due_soon';
        } else {
            $status = 'unpaid';
        }

        $lines = collect([
            ['label' => 'Base bed-space rent', 'amount' => self::centsToAmount($rentCents), 'type' => 'rent'],
        ])->concat($deviceLines)->values()->all();

        return [
            'status' => $status,
            'due' => $due,
            'period' => $period,
            'amount' => self::centsToAmount($totalCents),
            'paid_amount' => self::centsToAmount($paidCents),
            'verified_total' => self::centsToAmount($verifiedTotalCents),
            'remaining' => self::centsToAmount(max(0, $totalCents - $verifiedTotalCents)),
            'lines' => $lines,
            'electricity_share' => self::centsToAmount($electricityCents),
            'device_total' => self::centsToAmount($deviceCents),
        ];
    }

    private static function effectiveRentCents(User $user): int
    {
        if (! $user->bed) {
            return 0;
        }

        $bedRate = $user->bed->getRawOriginal('monthly_rate');
        if ($bedRate !== null) {
            return self::amountToCents($bedRate);
        }

        $settingKey = $user->bed->bunk === 'A' ? 'rent.top_bunk_rate' : 'rent.bottom_bunk_rate';
        $configuredRate = Setting::get($settingKey);
        if ($configuredRate !== null && $configuredRate !== '') {
            return self::amountToCents($configuredRate);
        }

        return self::amountToCents(Setting::get('rent.default_monthly_rate', '1500.00'));
    }
}
