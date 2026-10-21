<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DetalleEquipo extends Model
{
    use SoftDeletes, Auditable;

    protected $guarded = [];

    protected $casts = [
        'teletrabajo' => 'boolean',
        'es_prestamo' => 'boolean',
        'fecha_adquisicion' => 'date',
    ];

    public function equipo()
    {
        return $this->belongsTo(Equipo::class);
    }

    public function estado()
    {
        return $this->belongsTo(EstadoEquipo::class, 'estado_id');
    }

    public function tipoDisco()
    {
        return $this->belongsTo(TipoDisco::class);
    }

    public function macs()
    {
        return $this->hasMany(DetalleEquipoMac::class);
    }

    public function asignacionActual()
    {
        return $this->hasOne(Asignacion::class);
    }

    public function historialAsignaciones()
    {
        return $this->hasMany(HistorialAsignacion::class)->latest('fecha');
    }

    public function archivos()
    {
        return $this->morphMany(Archivo::class, 'archivable');
    }

    public function observaciones()
{
    return $this->hasMany(Observacion::class)->latest();
}

public function detalleEquipos()
{
    return $this->hasMany(DetalleEquipo::class, 'estado_id');
}

public function listaItems()
{
    return $this->morphMany(ListaItem::class, 'itemable');
}
}