<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // 🌟 FIXED: Commented out to prevent the 'Duplicate column tenant_id' error crash
            // $table->unsignedBigInteger('tenant_id')->after('id');
            
            // Note: If you need to add other fields like 'amount' or 'reference_number' 
            // here in the future, you can safely add them right under this comment.
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Safely bypassed to keep rollback tracking stable
            // $table->dropColumn('tenant_id');
        });
    }
};