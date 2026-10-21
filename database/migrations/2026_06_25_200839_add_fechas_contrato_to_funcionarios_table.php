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
    Schema::table('funcionarios', function (Blueprint $table) {
        $table->date('fecha_ingreso')->nullable()->after('correo');
        $table->date('fecha_fin_contrato')->nullable()->after('fecha_ingreso');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('funcionarios', function (Blueprint $table) {
            //
        });
    }
};
