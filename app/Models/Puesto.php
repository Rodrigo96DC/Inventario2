<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Puesto extends Model
{
    protected $guarded = [];

    protected $casts = [
        'x1'     => 'integer',
        'y1'     => 'integer',
        'x2'     => 'integer',
        'y2'     => 'integer',
        'activo' => 'boolean',
    ];

    public function piso()
    {
        return $this->belongsTo(Piso::class);
    }

    public function funcionario()
    {
        return $this->belongsTo(Funcionario::class);
    }

    public function historial()
    {
        return $this->hasMany(HistorialPuesto::class)->latest('fecha');
    }

    public function estaOcupado(): bool
    {
        return $this->funcionario_id !== null;
    }
}