<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detalle_equipo_macs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('detalle_equipo_id')->constrained()->cascadeOnDelete();
            $table->string('mac', 17); // formato AA:BB:CC:DD:EE:FF
            $table->string('interfaz')->nullable(); // 'Ethernet', 'WiFi', etc. (opcional)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detalle_equipo_macs');
    }
};