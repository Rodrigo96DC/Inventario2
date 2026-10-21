<?php

namespace App\Http\Controllers;

use App\Models\Asignacion;
use App\Models\DetalleEquipo;
use App\Models\EstadoEquipo;
use App\Models\Funcionario;
use App\Models\Observacion;
use App\Models\TipoEquipo;
use App\Services\AsignacionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Spatie\SimpleExcel\SimpleExcelWriter;

class EquipoAsignadoController extends Controller
{
    public function __construct(protected AsignacionService $service) {}

    // ── Listado de equipos asignados ─────────────────────────────────────────
    public function index(Request $request)
    {
        $buscar   = $request->input('buscar');
        $tipoId   = $request->input('tipo_id');
        $estadoId = $request->input('estado_id');
        $regionId = $request->input('region_id');

        $asignaciones = Asignacion::with([
                'detalleEquipo.equipo.tipoEquipo',
                'detalleEquipo.estado',
                'detalleEquipo.macs',
                'funcionario.region',
            ])
        ->when($buscar, fn ($q) =>
    $q->where(function($q2) use ($buscar) {
        $q2->whereHas('detalleEquipo', fn ($q3) =>
            $q3->where('inventario', 'like', "%{$buscar}%")
               ->orWhere('serial', 'like', "%{$buscar}%")
               ->orWhereHas('equipo', fn ($q4) =>
                   $q4->where('marca', 'like', "%{$buscar}%")
                      ->orWhere('modelo', 'like', "%{$buscar}%")
               )
        )
        ->orWhereHas('funcionario', fn ($q3) =>
            $q3->where('nombre', 'like', "%{$buscar}%")
               ->orWhere('rut', 'like', "%{$buscar}%")
        );
    })
)
            ->when($tipoId, fn ($q) =>
                $q->whereHas('detalleEquipo.equipo', fn ($q2) =>
                    $q2->where('tipo_equipo_id', $tipoId)
                )
            )
            ->when($estadoId, fn ($q) =>
                $q->whereHas('detalleEquipo', fn ($q2) =>
                    $q2->where('estado_id', $estadoId)
                )
            )
            ->when($regionId, fn ($q) =>
                $q->whereHas('funcionario', fn ($q2) =>
                    $q2->where('region_id', $regionId)
                )
            )
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $tipoEquipos   = TipoEquipo::orderBy('nombre')->get();
        $estadoEquipos = EstadoEquipo::orderBy('nombre')->get();
        $regiones      = \App\Models\Region::orderBy('nombre')->get();

        return view('equipos_asignados.index', compact(
            'asignaciones', 'buscar', 'tipoEquipos', 'estadoEquipos', 'regiones'
        ));
    }

    // ── Detalle de un equipo asignado ─────────────────────────────────────────
    public function show(DetalleEquipo $detalle_equipo)
    {
        $detalle_equipo->load([
            'equipo.tipoEquipo',
            'estado',
            'tipoDisco',
            'macs',
            'asignacionActual.funcionario.region',
            'historialAsignaciones.funcionario',
            'historialAsignaciones.user',
            'observaciones.user',
        ]);

        return view('equipos_asignados.show', compact('detalle_equipo'));
    }

    // ── Observaciones ─────────────────────────────────────────────────────────
    public function storeObservacion(Request $request, DetalleEquipo $detalle_equipo)
    {
        $request->validate([
            'tipo'    => 'required|string|in:mantenimiento,cambio_pieza,limpieza,revision,otro',
            'nota'    => 'required|string|max:2000',
            'archivo' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ], [
            'tipo.required' => 'Debes seleccionar un tipo de intervención.',
            'nota.required' => 'La observación es obligatoria.',
            'archivo.mimes' => 'El archivo debe ser PDF, JPG o PNG.',
            'archivo.max'   => 'El archivo no puede superar los 5MB.',
        ]);

        $ruta = null;
        if ($request->hasFile('archivo')) {
            $ruta = $request->file('archivo')->store("observaciones/{$detalle_equipo->id}", 'public');
        }

        $detalle_equipo->observaciones()->create([
            'user_id' => auth()->id(),
            'tipo'    => $request->tipo,
            'nota'    => $request->nota,
            'archivo' => $ruta,
        ]);

    return redirect(route('equipo-asignado.show', $detalle_equipo) . '#observaciones')
    ->with('info', 'Observación guardada.');
    }

    public function destroyObservacion(Observacion $observacion)
    {
        if ($observacion->archivo) {
            Storage::disk('public')->delete($observacion->archivo);
        }
        $observacion->delete();

        return back()->with('info', 'Observación eliminada.');
    }

    // ── Transferencia ─────────────────────────────────────────────────────────
public function transferirVista(Funcionario $funcionario, Request $request)
{
    $funcionario->load(['region']);
    $buscar = $request->input('buscar');

    $asignaciones = $funcionario->asignaciones()
        ->with(['detalleEquipo.equipo.tipoEquipo', 'detalleEquipo.estado'])
        ->when($buscar, fn($q) => $q->whereHas('detalleEquipo', fn($q2) =>
            $q2->where('inventario', 'like', "%{$buscar}%")
               ->orWhereHas('equipo', fn($q3) =>
                   $q3->where('marca', 'like', "%{$buscar}%")
                      ->orWhere('modelo', 'like', "%{$buscar}%")
               )
        ))
        ->paginate(15)
        ->withQueryString();

    $funcionarios = Funcionario::where('id', '!=', $funcionario->id)
        ->orderBy('nombre')
        ->get(['id', 'nombre', 'rut']);

    return view('equipos_asignados.transferir', compact('funcionario', 'asignaciones', 'funcionarios', 'buscar'));
}

  public function transferir(Request $request, Funcionario $funcionario)
{
    $request->validate([
        'funcionario_destino_id' => 'required|exists:funcionarios,id',
        'equipos'                => 'required|array|min:1',
        'equipos.*'              => 'exists:detalle_equipos,id',
        'observacion'            => 'nullable|string|max:500',
        'hostnames'              => 'nullable|array',
        'hostnames.*'            => 'nullable|string|max:100',
    ], [
        'funcionario_destino_id.required' => 'Debes seleccionar el funcionario de destino.',
        'equipos.required'                => 'Debes seleccionar al menos un equipo.',
    ]);

    $destino = Funcionario::findOrFail($request->funcionario_destino_id);

    foreach ($request->equipos as $equipoId) {
        $detalleEquipo = DetalleEquipo::findOrFail($equipoId);

        // Usa el hostname del modal si se proporcionó, si no mantiene el actual
        $hostname = $request->hostnames[$equipoId]
            ?? $detalleEquipo->asignacionActual?->hostname;

        $this->service->asignar($detalleEquipo, $destino, [
            'hostname'         => $hostname ?: null,
            'observacion'      => $request->observacion ?? "Transferido desde {$funcionario->nombre}.",
            'fecha_asignacion' => now()->toDateString(),
        ]);
    }

    $cantidad = count($request->equipos);
    return redirect()->route('equipo-asignado.index')
        ->with('info', "{$cantidad} equipo(s) transferido(s) correctamente a {$destino->nombre}.");
}

    // ── Exportar ──────────────────────────────────────────────────────────────
 public function exportar(Request $request)
{
    $ruta   = storage_path('app/equipos_asignados_' . now()->format('Ymd_His') . '.xlsx');
    $writer = SimpleExcelWriter::create($ruta)
        ->addHeader([
            'Funcionario', 'RUT', 'Región', 'División', 'Departamento', 'Unidad',
            'Tipo', 'Marca', 'Modelo', 'Inventario', 'Serial',
            'Estado', 'Hostname', 'Fecha Asignación',
        ]);

    Asignacion::with([
            'detalleEquipo.equipo.tipoEquipo',
            'detalleEquipo.estado',
            'funcionario.region',
            'funcionario.division',
            'funcionario.departamento',
            'funcionario.unidad',
        ])
        ->when($request->buscar, fn($q) =>
            $q->where(function($q2) use ($request) {
                $q2->whereHas('detalleEquipo', fn($q3) =>
                    $q3->where('inventario', 'like', "%{$request->buscar}%")
                       ->orWhere('serial', 'like', "%{$request->buscar}%")
                       ->orWhereHas('equipo', fn($q4) =>
                           $q4->where('marca', 'like', "%{$request->buscar}%")
                              ->orWhere('modelo', 'like', "%{$request->buscar}%")
                       )
                )
                ->orWhereHas('funcionario', fn($q3) =>
                    $q3->where('nombre', 'like', "%{$request->buscar}%")
                       ->orWhere('rut', 'like', "%{$request->buscar}%")
                );
            })
        )
        ->when($request->tipo_id, fn($q) =>
            $q->whereHas('detalleEquipo.equipo', fn($q2) =>
                $q2->where('tipo_equipo_id', $request->tipo_id)
            )
        )
        ->when($request->estado_id, fn($q) =>
            $q->whereHas('detalleEquipo', fn($q2) =>
                $q2->where('estado_id', $request->estado_id)
            )
        )
        ->when($request->region_id, fn($q) =>
            $q->whereHas('funcionario', fn($q2) =>
                $q2->where('region_id', $request->region_id)
            )
        )
        ->each(function ($a) use ($writer) {
            $writer->addRow([
                $a->funcionario->nombre ?? '—',
                $a->funcionario->rut_formateado ?? '—',
                $a->funcionario->region->nombre ?? '—',
                $a->funcionario->division?->nombre ?? '—',
                $a->funcionario->departamento?->nombre ?? '—',
                $a->funcionario->unidad?->nombre ?? '—',
                $a->detalleEquipo->equipo->tipoEquipo->nombre ?? '—',
                $a->detalleEquipo->equipo->marca ?? '—',
                $a->detalleEquipo->equipo->modelo ?? '—',
                $a->detalleEquipo->inventario ?? '—',
                $a->detalleEquipo->serial ?? '—',
                $a->detalleEquipo->estado->nombre ?? '—',
                $a->hostname ?? '—',
                $a->fecha_asignacion?->format('d-m-Y') ?? '—',
            ]);
        });

    $writer->close();
    return response()->download($ruta, 'equipos_asignados_' . now()->format('Ymd_His') . '.xlsx')
        ->deleteFileAfterSend();
}

public function updateHostname(Request $request, DetalleEquipo $detalle_equipo)
{
    $request->validate([
        'hostname'   => 'required|string|max:100',
        'comentario' => 'nullable|string|max:500',
    ]);

    $asignacion = $detalle_equipo->asignacionActual;
    if (!$asignacion) abort(404);

    $hostnameAnterior = $asignacion->hostname;
    $asignacion->update(['hostname' => $request->hostname]);

    // Registrar en historial
    \App\Models\HistorialAsignacion::create([
        'detalle_equipo_id' => $detalle_equipo->id,
        'funcionario_id'    => $asignacion->funcionario_id,
        'user_id'           => auth()->id(),
        'accion'            => 'actualizado',
        'hostname'          => $request->hostname,
        'observacion'       => 'Hostname cambiado de "' . $hostnameAnterior . '" a "' . $request->hostname . '"' .
                               ($request->comentario ? '. ' . $request->comentario : ''),
        'fecha'             => now(),
    ]);

    return redirect()->route('equipo-asignado.show', $detalle_equipo)
        ->with('info', 'Hostname actualizado correctamente.');
}
}