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
    Schema::create('listas', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->cascadeOnDelete();
        $table->string('nombre');
        $table->enum('tipo', ['equipos', 'funcionarios']);
        $table->enum('estado', ['en_proceso', 'cancelada', 'realizada'])->default('en_proceso');
        $table->boolean('publica')->default(false);
        $table->boolean('auto_baja')->default(false); // para listas generadas automáticamente
        $table->text('descripcion')->nullable();
        $table->timestamps();
        $table->softDeletes();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('listas');
    }
};
