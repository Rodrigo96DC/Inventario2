<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Funcionario extends Model
{
    use SoftDeletes, Auditable;

    protected $guarded = [];

 protected $casts = [
    'fecha_ingreso'      => 'date',
    'fecha_fin_contrato' => 'date',
    'activo'             => 'boolean',
];

    public function setRutAttribute($value): void
    {
        $this->attributes['rut'] = $value
            ? strtoupper(str_replace(['.', ' '], '', $value))
            : null;
    }

    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    public function asignaciones()
    {
        return $this->hasMany(Asignacion::class);
    }

    public function historialAsignaciones()
    {
        return $this->hasMany(HistorialAsignacion::class);
    }

    public function getRutFormateadoAttribute(): ?string
{
    if (! $this->rut) {
        return null;
    }

    $cuerpo = substr($this->rut, 0, -1);
    $dv = substr($this->rut, -1);

    return number_format((int) $cuerpo, 0, '', '.') . '-' . $dv;
}


public function puesto()
{
    return $this->hasOne(Puesto::class);
}

public function historialPuestos()
{
    return $this->hasMany(HistorialPuesto::class)->latest('fecha');
}

public function division()
{
    return $this->belongsTo(Division::class);
}

public function departamento()
{
    return $this->belongsTo(Departamento::class);
}

public function unidad()
{
    return $this->belongsTo(Unidad::class);
}

public function listaItems()
{
    return $this->morphMany(ListaItem::class, 'itemable');
}
}