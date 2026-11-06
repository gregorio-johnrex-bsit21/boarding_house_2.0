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
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            // Added ->unique() to ensure no two rooms share the same number
            $table->string('room_number')->unique(); 
            
            // Stores "Normal" or "Premium"
            $table->string('room_type'); 
            
            // Added price field (using decimal for accurate currency storage)
            $table->decimal('price', 10, 2); 
            
            // Status defaults to Available
            $table->string('status')->default('Available'); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};