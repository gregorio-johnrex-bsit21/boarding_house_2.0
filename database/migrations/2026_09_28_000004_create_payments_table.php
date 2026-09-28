<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->foreignId('bed_id')->nullable()->constrained()->nullOnDelete();
            $t->date('period');                                   // first day of the billing month
            $t->decimal('amount', 10, 2);
            $t->string('payment_method');                         // gcash | cash
            $t->string('gcash_reference')->nullable()->unique();  // blocks reused reference numbers
            $t->string('receipt_image')->nullable();
            $t->string('status')->default('pending');             // pending | verified | rejected
            $t->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('verified_at')->nullable();
            $t->string('note')->nullable();
            $t->timestamps();
            $t->index(['user_id', 'period']);
        });
    }
    public function down(): void { Schema::dropIfExists('payments'); }
};
