<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('device_catalog')) {
            Schema::create('device_catalog', function (Blueprint $table) {
                $table->id();
                $table->string('name', 100);
                $table->string('note', 255)->nullable();
                $table->boolean('available')->default(true);
                $table->string('reason', 255)->nullable();
                $table->unsignedSmallInteger('sort_order')->default(0);
                $table->timestamps();
            });
        } else {
            foreach (['id', 'name', 'note', 'available', 'reason', 'sort_order', 'created_at', 'updated_at'] as $column) {
                if (! Schema::hasColumn('device_catalog', $column)) {
                    throw new RuntimeException("Existing device_catalog table is missing required column [{$column}].");
                }
            }
        }

        if (DB::table('device_catalog')->count() === 0) {
            DB::table('device_catalog')->insert([
                [
                    'name' => 'Electric fan',
                    'note' => null,
                    'available' => true,
                    'reason' => null,
                    'sort_order' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Rice cooker',
                    'note' => 'Shared with roommates',
                    'available' => true,
                    'reason' => null,
                    'sort_order' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
        }

        Schema::create('device_declaration_catalog', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_declaration_id')
                ->unique()
                ->constrained('device_declarations')
                ->cascadeOnDelete();
            $table->foreignId('device_catalog_id')
                ->constrained('device_catalog')
                ->restrictOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        // Intentionally retain catalog and declaration data on rollback.
    }
};
