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
    Schema::create('historial_puestos', function (Blueprint $table) {
        $table->id();
        $table->foreignId('puesto_id')->constrained('puestos')->cascadeOnDelete();
        $table->foreignId('funcionario_id')->nullable()->constrained('funcionarios')->nullOnDelete();
        $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
        $table->string('accion'); // asignado | desasignado
        $table->text('observacion')->nullable();
        $table->timestamp('fecha')->useCurrent();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('historial_puestos');
    }
};
