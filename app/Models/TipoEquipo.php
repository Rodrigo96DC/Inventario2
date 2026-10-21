<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoEquipo extends Model
{
    protected $guarded = [];

    public function equipos()
    {
        return $this->hasMany(Equipo::class);
    }
}