<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Auditoria extends Model
{
    protected $guarded = [];

    protected $casts = [
        'cambios' => 'array',
    ];

    public function nombreModulo(): Attribute
    {
        return Attribute::make(
            get: fn () => match ($this->auditable_type) {
                'App\Models\Equipo'        => 'Equipos',
                'App\Models\Funcionario'   => 'Funcionarios',
                'App\Models\DetalleEquipo' => 'Inventario',
                'App\Models\Insumo'        => 'Insumos',
                default                    => class_basename($this->auditable_type ?? ''),
            }
        );
    }

    public function colorAccion(): Attribute
    {
        return Attribute::make(
            get: fn () => match ($this->accion) {
                'creado'               => 'success',
                'actualizado'          => 'primary',
                'eliminado'            => 'warning',
                'eliminado_permanente' => 'danger',
                'restaurado'           => 'info',
                default                => 'secondary',
            }
        );
    }

    public function auditable()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}