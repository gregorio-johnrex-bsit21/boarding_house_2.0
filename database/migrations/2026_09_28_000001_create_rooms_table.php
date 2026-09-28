<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('rooms', function (Blueprint $t) {
            $t->id();
            $t->string('mesh_room')->unique();   // 3D model name, e.g. "R15"
            $t->string('number');                // real room number shown to people, e.g. "9"
            $t->unsignedTinyInteger('capacity'); // bunks in the room
            $t->decimal('monthly_rate', 10, 2)->default(0); // per bunk
            $t->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('rooms'); }
};
