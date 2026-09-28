<?php
namespace App\Support;

use App\Models\User;
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

        $q = $u->payments()->where('period', $period->toDateString());
        $paid = (clone $q)->where('status', 'verified')->exists();
        $pending = ! $paid && (clone $q)->where('status', 'pending')->exists();

        if ($paid) {
            $status = 'paid';
            $next = $period->copy()->addMonthNoOverflow();
            $due = $next->copy()->day(min($dueDay, $next->daysInMonth)); // next cycle's due date
        } elseif ($pending) {
            $status = 'pending';
        } elseif ($now->gt($due)) {
            $status = 'overdue';
        } elseif ($now->diffInDays($due) <= 5) {
            $status = 'due_soon';
        } else {
            $status = 'unpaid';
        }

        return [
            'status' => $status,
            'due' => $due,
            'period' => $period,
            'amount' => $u->bed ? $u->bed->rate : 0,
        ];
    }
}
