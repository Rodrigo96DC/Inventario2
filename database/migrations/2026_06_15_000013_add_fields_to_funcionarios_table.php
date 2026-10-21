<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('funcionarios', function (Blueprint $table) {
            $table->string('rut')->nullable()->unique()->after('nombre');
            $table->string('cargo')->nullable()->after('area');
            $table->string('correo')->nullable()->after('cargo');
        });
    }

    public function down(): void
    {
        Schema::table('funcionarios', function (Blueprint $table) {
            $table->dropColumn(['rut', 'cargo', 'correo']);
        });
    }
};