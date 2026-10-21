<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetalleEquipoMac extends Model
{
    protected $guarded = [];

    public function detalleEquipo()
    {
        return $this->belongsTo(DetalleEquipo::class);
    }
}