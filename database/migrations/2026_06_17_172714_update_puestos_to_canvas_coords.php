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
    Schema::table('puestos', function (Blueprint $table) {
        $table->dropColumn(['tipo', 'pos_x', 'pos_y', 'area_puntos']);
        $table->integer('x1')->nullable()->after('observacion');
        $table->integer('y1')->nullable()->after('x1');
        $table->integer('x2')->nullable()->after('y1');
        $table->integer('y2')->nullable()->after('x2');
    });
}

public function down(): void
{
    Schema::table('puestos', function (Blueprint $table) {
        $table->dropColumn(['x1', 'y1', 'x2', 'y2']);
        $table->string('tipo')->default('punto');
        $table->float('pos_x')->nullable();
        $table->float('pos_y')->nullable();
        $table->json('area_puntos')->nullable();
    });
}
};
