<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('device_catalog', 'total_units')) {
            Schema::table('device_catalog', function (Blueprint $table) {
                $table->unsignedInteger('total_units')->nullable();
            });
        }
    }

    public function down(): void
    {
        // Intentionally retain catalog inventory data on rollback.
    }
};
