<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EstadoEquipo extends Model
{
    protected $table = 'estados_equipo';
    protected $guarded = [];

    public function detalleEquipos()
    {
        return $this->hasMany(DetalleEquipo::class, 'estado_id');
    }
}