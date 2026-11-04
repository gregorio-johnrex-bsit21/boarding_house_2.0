<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('device_declarations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name', 100);
            $table->text('note')->nullable();
            $table->string('status')->default('pending');
            $table->decimal('monthly_fee', 10, 2)->nullable();
            $table->string('rejection_reason', 255)->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'status']);
            $table->index(['status', 'reviewed_at']);
        });

        Schema::create('utility_bills', function (Blueprint $table) {
            $table->id();
            $table->date('period');
            $table->string('type');
            $table->decimal('total_amount', 10, 2);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['period', 'type']);
        });

        if (Schema::hasTable('tenant_device_declarations')) {
            DB::table('tenant_device_declarations')
                ->orderBy('id')
                ->chunk(100, function ($declarations): void {
                    foreach ($declarations as $declaration) {
                        DB::table('device_declarations')->insert([
                            'user_id' => $declaration->user_id,
                            'name' => mb_substr($declaration->name, 0, 100),
                            'note' => $declaration->notes,
                            'status' => in_array($declaration->status, ['pending', 'approved', 'rejected'], true)
                                ? $declaration->status
                                : 'pending',
                            'monthly_fee' => null,
                            'rejection_reason' => null,
                            'reviewed_by' => null,
                            'reviewed_at' => null,
                            'created_at' => $declaration->created_at,
                            'updated_at' => $declaration->updated_at,
                        ]);
                    }
                });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('utility_bills');
        Schema::dropIfExists('device_declarations');
    }
};
