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
    Schema::create('puestos', function (Blueprint $table) {
        $table->id();
        $table->foreignId('piso_id')->constrained('pisos')->cascadeOnDelete();
        $table->foreignId('funcionario_id')->nullable()->constrained('funcionarios')->nullOnDelete();
        $table->string('numero'); // "23", "A-14", etc.
        $table->string('seccion')->nullable(); // "Finanzas", "DTGI", etc.
        $table->text('observacion')->nullable();
        $table->string('tipo')->default('punto'); // punto | area
        $table->float('pos_x')->nullable(); // coordenada X en % sobre la imagen
        $table->float('pos_y')->nullable(); // coordenada Y en % sobre la imagen
        $table->json('area_puntos')->nullable(); // polígono: [{x, y}, {x, y}, ...]
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('puestos');
    }
};
