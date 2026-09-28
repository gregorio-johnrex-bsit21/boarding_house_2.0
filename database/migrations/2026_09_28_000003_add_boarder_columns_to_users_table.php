<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $t) {
            $t->string('role')->default('tenant')->index();          // admin | tenant
            $t->string('phone')->nullable();
            $t->foreignId('bed_id')->nullable()->unique()->constrained()->nullOnDelete();
            $t->date('move_in_date')->nullable();
            $t->unsignedTinyInteger('due_day')->default(1);          // day of month rent is due
        });
    }
    public function down(): void
    {
        Schema::table('users', function (Blueprint $t) {
            $t->dropConstrainedForeignId('bed_id');
            $t->dropColumn(['role', 'phone', 'move_in_date', 'due_day']);
        });
    }
};
