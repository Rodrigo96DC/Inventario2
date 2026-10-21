<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tipo_equipo_id')->constrained('tipo_equipos');
            $table->string('marca');
            $table->string('modelo');
            $table->string('imagen')->nullable();
            $table->string('entradas')->nullable();
            $table->string('procesador')->nullable();
            $table->text('observacion')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['marca', 'modelo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipos');
    }
};