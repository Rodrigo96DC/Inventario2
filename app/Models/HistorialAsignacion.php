<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HistorialAsignacion extends Model
{
    protected $table = 'historial_asignaciones';
    protected $guarded = [];

    protected $casts = [
        'fecha' => 'datetime',
    ];

    public function detalleEquipo()
    {
        return $this->belongsTo(DetalleEquipo::class);
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