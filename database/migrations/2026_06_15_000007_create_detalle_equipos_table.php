<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detalle_equipos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipo_id')->nullable()->constrained('equipos')->nullOnDelete();
            $table->foreignId('estado_id')->constrained('estados_equipo');
            $table->foreignId('tipo_disco_id')->nullable()->constrained('tipo_discos')->nullOnDelete();

            $table->string('inventario')->unique();
            $table->string('serial')->unique();
            $table->unsignedSmallInteger('ram_gb')->nullable();
            $table->unsignedInteger('capacidad_disco_gb')->nullable();
            $table->string('ip')->nullable();
            $table->date('fecha_adquisicion')->nullable();
            $table->unsignedSmallInteger('puerto_switch')->nullable();
            $table->boolean('teletrabajo')->default(false);
            $table->boolean('es_prestamo')->default(false);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detalle_equipos');
    }
};