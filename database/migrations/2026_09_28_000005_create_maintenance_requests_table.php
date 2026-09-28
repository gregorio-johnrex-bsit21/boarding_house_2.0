<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('maintenance_requests', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained('users')->cascadeOnDelete();
            $t->foreignId('bed_id')->nullable()->constrained()->nullOnDelete();
            $t->string('issue_category');                 // pipes | light | lock | furniture | wifi | other
            $t->string('urgency')->default('normal');     // low | normal | urgent
            $t->text('description');
            $t->string('image')->nullable();
            $t->string('status')->default('pending');     // pending | in_progress | resolved
            $t->timestamp('resolved_at')->nullable();
            $t->decimal('cost', 10, 2)->nullable();       // optional repair cost log
            $t->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('maintenance_requests'); }
};
