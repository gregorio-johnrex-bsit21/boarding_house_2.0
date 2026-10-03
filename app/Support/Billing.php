<?php
namespace App\Support;

use App\Models\User;
use App\Models\Setting;
use Carbon\Carbon;

class Billing
{
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

        $amount = $u->bed ? $u->bed->rate : 0;
        if ($u->bed && filter_var(Setting::get('rent.prorate', '0'), FILTER_VALIDATE_BOOLEAN) && $u->move_in_date) {
            $moveIn = $u->move_in_date->copy()->startOfDay();
            if ($moveIn->year === $period->year && $moveIn->month === $period->month) {
                $daysRemaining = $period->daysInMonth - $moveIn->day + 1;
                $amount = round($amount * $daysRemaining / $period->daysInMonth, 2);
            }
        }

        $q = $u->payments()->where('period', $period->toDateString());
        $paidAmount = (float) (clone $q)->where('status', 'verified')->sum('amount');
        $paid = $amount > 0 && $paidAmount >= $amount;
        $pending = ! $paid && (clone $q)->where('status', 'pending')->exists();

        $graceDays = max(0, (int) Setting::get('due.grace_period_days', 3));
        $dueSoonDays = max(0, (int) Setting::get('due.due_soon_days', 5));

        if ($paid) {
            $status = 'paid';
            $next = $period->copy()->addMonthNoOverflow();
            $due = $next->copy()->day(min($dueDay, $next->daysInMonth)); // next cycle's due date
        } elseif ($pending) {
            $status = 'pending';
        } elseif ($paidAmount > 0) {
            $status = 'partial';
        } elseif ($now->gt($due->copy()->addDays($graceDays))) {
            $status = 'overdue';
        } elseif ($now->lte($due) && $now->diffInDays($due) <= $dueSoonDays) {
            $status = 'due_soon';
        } else {
            $status = 'unpaid';
        }

        return [
            'status' => $status,
            'due' => $due,
            'period' => $period,
            'amount' => $amount,
            'paid_amount' => min($paidAmount, $amount),
            'remaining' => max(0, round($amount - $paidAmount, 2)),
        ];
    }
}
