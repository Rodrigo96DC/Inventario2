<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Piso extends Model
{
    protected $guarded = [];

    public function edificio()
    {
        return $this->belongsTo(Edificio::class);
    }

    public function puestos()
    {
        return $this->hasMany(Puesto::class);
    }
}