<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->date('period_start')->nullable();
            $table->date('period_end')->nullable();
        });

        DB::table('payments')->orderBy('id')->chunk(100, function ($payments) {
            foreach ($payments as $payment) {
                $start = Carbon::parse($payment->period)->startOfMonth();
                DB::table('payments')->where('id', $payment->id)->update([
                    'period_start' => $start->toDateString(),
                    'period_end' => $start->copy()->addMonthNoOverflow()->toDateString(),
                ]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['period_start', 'period_end']);
        });
    }
};
