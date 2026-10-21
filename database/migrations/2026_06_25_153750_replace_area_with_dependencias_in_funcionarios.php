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
        $table->foreignId('division_id')->nullable()->after('region_id')->constrained('divisiones')->nullOnDelete();
        $table->foreignId('departamento_id')->nullable()->after('division_id')->constrained('departamentos')->nullOnDelete();
        $table->foreignId('unidad_id')->nullable()->after('departamento_id')->constrained('unidades')->nullOnDelete();
        $table->dropColumn('area');
    });
}

public function down(): void
{
    Schema::table('funcionarios', function (Blueprint $table) {
        $table->string('area')->nullable();
        $table->dropForeign(['division_id']);
        $table->dropForeign(['departamento_id']);
        $table->dropForeign(['unidad_id']);
        $table->dropColumn(['division_id', 'departamento_id', 'unidad_id']);
    });
}
};
