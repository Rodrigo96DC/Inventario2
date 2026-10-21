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
    Schema::create('lista_items', function (Blueprint $table) {
        $table->id();
        $table->foreignId('lista_id')->constrained('listas')->cascadeOnDelete();
        $table->nullableMorphs('itemable'); // polimórfico: equipo o funcionario
        $table->enum('estado', ['pendiente', 'en_proceso', 'completado'])->default('pendiente');
        $table->text('detalle')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lista_items');
    }
};
