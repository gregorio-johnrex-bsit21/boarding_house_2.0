<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            
            // Core Relational Tracking
            // Constrained assumes you have a 'tenants' table
            $table->foreignId('tenant_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('room_number'); 
            
            // Financial Data
            $table->dateTime('date'); 
            $table->decimal('amount', 10, 2);
            $table->string('status')->default('pending'); // Default to pending
            
            // Online/Digital Payment Auditing
            $table->string('reference_number')->nullable(); 
            $table->string('receipt_path')->nullable(); 
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};