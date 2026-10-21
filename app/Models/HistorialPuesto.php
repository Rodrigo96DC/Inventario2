<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HistorialPuesto extends Model
{
    protected $table = 'historial_puestos';
    protected $guarded = [];

    protected $casts = [
        'fecha' => 'datetime',
    ];

    public function puesto()
    {
        return $this->belongsTo(Puesto::class);
    }

    public function funcionario()
    {
        return $this->belongsTo(Funcionario::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}