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
    Schema::create('pisos', function (Blueprint $table) {
        $table->id();
        $table->foreignId('edificio_id')->constrained('edificios')->cascadeOnDelete();
        $table->string('nombre'); // "Piso 1", "Piso 15", "Subterráneo", etc.
        $table->integer('numero')->nullable();
        $table->string('imagen')->nullable(); // mapa del piso
        $table->text('observacion')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pisos');
    }
};
