<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DetalleEquipo;
use App\Models\Observacion;
use Illuminate\Http\Request;

class EquipoApiController extends Controller
{
    public function buscar(Request $request)
{
    $q = $request->input('q');

    $equipos = DetalleEquipo::with([
        'equipo.tipoEquipo',  // ← importante
        'estado',
        'tipoDisco',
        'macs',
        'asignacionActual.funcionario'
    ])
    ->where('inventario', 'like', "%{$q}%")
    ->orWhere('serial', 'like', "%{$q}%")
    ->limit(10)
    ->get()
    ->map(fn ($e) => $this->formatearEquipo($e));

    return response()->json($equipos);
}

    public function show(DetalleEquipo $detalle)
    {
        $detalle->load(['equipo.tipoEquipo', 'estado', 'tipoDisco', 'macs', 'asignacionActual.funcionario']);
        return response()->json($this->formatearEquipo($detalle));
    }

    public function storeObservacion(Request $request, DetalleEquipo $detalle)
    {
        $request->validate([
            'tipo' => 'required|in:mantenimiento,cambio_pieza,limpieza,revision,otro',
            'nota' => 'required|string|max:2000',
        ]);

        $obs = $detalle->observaciones()->create([
            'user_id' => $request->user()->id,
            'tipo'    => $request->tipo,
            'nota'    => $request->nota,
        ]);

        return response()->json(['ok' => true, 'observacion' => $obs]);
    }

  protected function formatearEquipo(DetalleEquipo $e): array
{
    return [
        'id'           => $e->id,
        'inventario'   => $e->inventario,
        'serial'       => $e->serial,           // ← verifica que esté
        'tipo'         => $e->equipo?->tipoEquipo?->nombre,
        'marca'        => $e->equipo?->marca,
        'modelo'       => $e->equipo?->modelo,
        'procesador'   => $e->equipo?->procesador, // ← viene del equipo
        'ram_gb'       => $e->ram_gb,           // ← viene del detalle
        'disco_gb'     => $e->capacidad_disco_gb,
        'tipo_disco'   => $e->tipoDisco?->nombre,
        'ip'           => $e->ip,
        'mac_ethernet' => $e->macs->firstWhere('interfaz', 'Ethernet')?->mac,
        'mac_wifi'     => $e->macs->firstWhere('interfaz', 'WiFi')?->mac,
        'estado'       => $e->estado?->nombre,
        'estado_color' => $e->estado?->color,
        'teletrabajo'  => $e->teletrabajo,
        'es_prestamo'  => $e->es_prestamo,
        'imagen'       => $e->equipo?->imagen ? asset('storage/' . $e->equipo->imagen) : null,
        'asignado_a'   => $e->asignacionActual ? [
            'id'       => $e->asignacionActual->funcionario?->id,
            'nombre'   => $e->asignacionActual->funcionario?->nombre,
            'hostname' => $e->asignacionActual->hostname,
            'fecha'    => $e->asignacionActual->fecha_asignacion?->format('d-m-Y'),
        ] : null,
    ];
}

public function observaciones(DetalleEquipo $detalle)
{
    $obs = $detalle->observaciones()
        ->with('user')
        ->latest()
        ->get()
        ->map(fn ($o) => [
            'id'     => $o->id,
            'tipo'   => $o->tipo,
            'nota'   => $o->nota,
            'fecha'  => $o->created_at->format('d-m-Y H:i'),
            'usuario'=> $o->user?->name ?? '—',
            'archivo' => $o->archivo ? asset('storage/' . $o->archivo) : null,
        ]);

    return response()->json($obs);
}
}