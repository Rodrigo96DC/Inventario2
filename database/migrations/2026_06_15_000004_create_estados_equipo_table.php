<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('estados_equipo', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->unique();
            $table->string('color')->default('secondary'); // success, warning, danger, secondary...
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('estados_equipo');
    }
};