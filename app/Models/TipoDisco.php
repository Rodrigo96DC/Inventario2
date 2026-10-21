<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoDisco extends Model
{
    protected $guarded = [];

    public function detalleEquipos()
    {
        return $this->hasMany(DetalleEquipo::class);
    }
}