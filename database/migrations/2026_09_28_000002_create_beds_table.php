<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // One row = one bunk (98 rows). mesh_id matches the object name in sally_bh.glb.
        Schema::create('beds', function (Blueprint $t) {
            $t->id();
            $t->foreignId('room_id')->constrained()->cascadeOnDelete();
            $t->unsignedTinyInteger('bed_no');
            $t->char('bunk', 1);                       // A = top, B = bottom
            $t->string('label');                       // "Bed 1 · Top"
            $t->string('mesh_id')->unique();           // "R15_Bed1_A"
            $t->string('status')->default('available'); // available|occupied|reserved|ready
            $t->decimal('monthly_rate', 10, 2)->nullable(); // overrides the room rate
            $t->timestamps();
            $t->unique(['room_id', 'bed_no', 'bunk']);
        });
    }
    public function down(): void { Schema::dropIfExists('beds'); }
};
