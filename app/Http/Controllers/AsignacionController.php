<?php

namespace App\Http\Controllers;

use App\Models\DetalleEquipo;
use App\Models\EstadoEquipo;
use App\Models\Funcionario;
use App\Models\TipoEquipo;
use App\Services\AsignacionService;
use Illuminate\Http\Request;

class AsignacionController extends Controller
{
    public function __construct(protected AsignacionService $service) {}

public function index(Request $request)
{
    $buscar   = $request->input('buscar');
    $tipoId   = $request->input('tipo_id');
    $estadoId = $request->input('estado_id');

    $equiposDisponibles = DetalleEquipo::with([
            'equipo.tipoEquipo', 'estado', 'macs', 'tipoDisco',
            'asignacionActual.funcionario' // ← agregar
        ])
        ->when(
            $buscar,
            fn($q) =>
            $q->where('inventario', 'like', "%{$buscar}%")
                ->orWhere('serial', 'like', "%{$buscar}%")
                ->orWhereHas(
                    'equipo',
                    fn($q2) =>
                    $q2->where('marca', 'like', "%{$buscar}%")
                        ->orWhere('modelo', 'like', "%{$buscar}%")
                )
        )
        ->when(
            $tipoId,
            fn($q) =>
            $q->whereHas('equipo', fn($q2) => $q2->where('tipo_equipo_id', $tipoId))
        )
        ->when($estadoId, fn($q) => $q->where('estado_id', $estadoId))
        ->orderByDesc('updated_at')
        ->paginate(12)
        ->withQueryString();

    $tiposEquipo   = TipoEquipo::orderBy('nombre')->get();
    $estadosEquipo = EstadoEquipo::orderBy('nombre')->get();
    $tiposDiscos   = \App\Models\TipoDisco::orderBy('nombre')->get();

    return view('asignaciones.index', compact(
        'equiposDisponibles', 'buscar',
        'tiposEquipo', 'estadosEquipo', 'tiposDiscos'
    ));
}

    public function asignar(Request $request)
    {
        $request->validate([
            'funcionario_id' => 'required|exists:funcionarios,id',
            'equipos'        => 'required|array|min:1',
            'equipos.*'      => 'exists:detalle_equipos,id',
            'hostnames'      => 'nullable|array',
            'observacion'    => 'nullable|string|max:500',
        ]);

        $funcionario = Funcionario::findOrFail($request->funcionario_id);
        $hostnames   = $request->input('hostnames', []);

        foreach ($request->equipos as $equipoId) {
            $detalleEquipo = DetalleEquipo::findOrFail($equipoId);
            $this->service->asignar($detalleEquipo, $funcionario, [
                'hostname'         => $hostnames[$equipoId] ?? null,
                'observacion'      => $request->observacion,
                'fecha_asignacion' => now()->toDateString(),
            ]);
        }

        $cantidad = count($request->equipos);
        $mensaje  = $cantidad === 1
            ? "Equipo asignado correctamente a {$funcionario->nombre}."
            : "{$cantidad} equipos asignados correctamente a {$funcionario->nombre}.";

        return response()->json(['ok' => true, 'mensaje' => $mensaje]);
    }

    public function desasignar(int $id, Request $request)
    {
        $detalleEquipo = DetalleEquipo::findOrFail($id);
        $this->service->desasignar($detalleEquipo, $request->input('observacion'));

        return response()->json(['ok' => true, 'mensaje' => 'Equipo desasignado correctamente.']);
    }

    public function buscarFuncionario(Request $request)
    {
        $funcionarios = Funcionario::where('nombre', 'like', "%{$request->q}%")
            ->orWhere('rut', 'like', "%{$request->q}%")
            ->limit(8)
            ->get()
            ->map(fn($f) => [
                'id'     => $f->id,
                'nombre' => $f->nombre,
                'rut'    => $f->rut_formateado,
                'area'   => $f->area,
                'cargo'  => $f->cargo,
                'correo' => $f->correo,
                'region' => $f->region->nombre ?? '—',
                'activo' => $f->activo,
                'avatar' => $f->avatar ? asset('storage/' . $f->avatar) : null,
            ]);

        return response()->json($funcionarios);
    }

    public function equiposFuncionario(Funcionario $funcionario)
    {
        $equipos = $funcionario->asignaciones()
            ->with(['detalleEquipo.equipo.tipoEquipo', 'detalleEquipo.estado'])
            ->get()
            ->map(fn($a) => [
                'asignacion_id' => $a->id,
                'detalle_id'    => $a->detalle_equipo_id,
                'inventario'    => $a->detalleEquipo->inventario,
                'serial'        => $a->detalleEquipo->serial,
                'tipo'          => $a->detalleEquipo->equipo->tipoEquipo->nombre ?? '—',
                'marca'         => $a->detalleEquipo->equipo->marca ?? '—',
                'modelo'        => $a->detalleEquipo->equipo->modelo ?? '—',
                'estado'        => $a->detalleEquipo->estado->nombre ?? '—',
                'color'         => $a->detalleEquipo->estado->color ?? 'secondary',
                'hostname'      => $a->hostname,
                'fecha'         => $a->fecha_asignacion?->format('d-m-Y'),
                'imagen'        => $a->detalleEquipo->equipo->imagen
                    ? asset('storage/' . $a->detalleEquipo->equipo->imagen)
                    : null,
            ]);

        return response()->json($equipos);
    }

    public function historialEquipo(DetalleEquipo $detalle)
    {
        $historial = $detalle->historialAsignaciones()
            ->with(['funcionario', 'user'])
            ->get()
            ->map(fn($h) => [
                'accion'      => $h->accion,
                'funcionario' => $h->funcionario->nombre ?? 'Funcionario eliminado',
                'hostname'    => $h->hostname,
                'observacion' => $h->observacion,
                'usuario'     => $h->user->name ?? 'Sistema',
                'fecha'       => $h->fecha->format('d-m-Y H:i'),
            ]);

        $detalle->load(['equipo.tipoEquipo', 'estado', 'tipoDisco', 'macs']);

        return response()->json([
            'detalle' => [
                'inventario'   => $detalle->inventario,
                'serial'       => $detalle->serial,
                'tipo'         => $detalle->equipo->tipoEquipo->nombre ?? '—',
                'marca'        => $detalle->equipo->marca ?? '—',
                'modelo'       => $detalle->equipo->modelo ?? '—',
                'estado'       => $detalle->estado->nombre ?? '—',
                'color'        => $detalle->estado->color ?? 'secondary',
                'ram'          => $detalle->ram_gb ? $detalle->ram_gb . ' GB' : '—',
                'disco'        => $detalle->capacidad_disco_gb
                    ? $detalle->capacidad_disco_gb . ' GB ' . ($detalle->tipoDisco->nombre ?? '')
                    : '—',
                'ip'           => $detalle->ip ?? '—',
                'mac_ethernet' => $detalle->macs->firstWhere('interfaz', 'Ethernet')?->mac ?? '—',
                'mac_wifi'     => $detalle->macs->firstWhere('interfaz', 'WiFi')?->mac ?? '—',
                'teletrabajo'  => $detalle->teletrabajo ? 'Sí' : 'No',
                'es_prestamo'  => $detalle->es_prestamo ? 'Sí' : 'No',
                'fecha_adq'    => $detalle->fecha_adquisicion?->format('d-m-Y') ?? '—',
                'imagen'       => $detalle->equipo->imagen
                    ? asset('storage/' . $detalle->equipo->imagen)
                    : null,
            ],
            'historial' => $historial,
        ]);
    }


    public function importarMasivo(Request $request)
{
    $request->validate(['archivo' => 'required|file|mimes:xlsx,xls,csv|max:5120']);

    $extension    = $request->file('archivo')->getClientOriginalExtension();
    $rutaTemporal = storage_path('app/temp_asignacion_masiva.' . $extension);
    copy($request->file('archivo')->getRealPath(), $rutaTemporal);

    $resultados = [];
    $numero     = 1;

    \Spatie\SimpleExcel\SimpleExcelReader::create($rutaTemporal)
        ->getRows()
        ->each(function (array $fila) use (&$resultados, &$numero) {
            $numero++;
            $inventario  = trim($fila['inventario'] ?? '');
            $serial      = trim($fila['serial'] ?? '');
            $buscarFunc  = trim($fila['funcionario'] ?? '');
            $hostname    = trim($fila['hostname'] ?? '') ?: null;

            if (empty($inventario) && empty($serial)) {
                $resultados[] = [
                    'fila'        => $numero,
                    'inventario'  => '—',
                    'funcionario' => $buscarFunc,
                    'resultado'   => 'Error',
                    'detalle'     => 'Inventario o serial vacío.',
                ];
                return;
            }

            // Buscar equipo
            $equipo = \App\Models\DetalleEquipo::when($inventario, fn($q) =>
                    $q->where('inventario', $inventario)
                )
                ->when(!$inventario && $serial, fn($q) =>
                    $q->where('serial', $serial)
                )
                ->first();

            if (!$equipo) {
                $resultados[] = [
                    'fila'        => $numero,
                    'inventario'  => $inventario ?: $serial,
                    'funcionario' => $buscarFunc,
                    'resultado'   => 'Error',
                    'detalle'     => 'Equipo no encontrado en el inventario.',
                ];
                return;
            }

            // Buscar funcionario por nombre, RUT o inicio de correo
if (empty($buscarFunc)) {
    $resultados[] = [
        'fila'        => $numero,
        'inventario'  => $equipo->inventario,
        'funcionario' => '—',
        'resultado'   => 'Error',
        'detalle'     => 'Nombre de funcionario vacío.',
    ];
    return;
}

// Ahora sí buscar el funcionario
$funcionario = \App\Models\Funcionario::where(function($q) use ($buscarFunc) {
    $q->where('nombre', 'like', "%{$buscarFunc}%")
      ->orWhere('rut', 'like', "%{$buscarFunc}%")
      ->orWhere('correo', 'like', "{$buscarFunc}%");
})->first();

if (!$funcionario) {
    $resultados[] = [
        'fila'        => $numero,
        'inventario'  => $equipo->inventario,
        'funcionario' => $buscarFunc,
        'resultado'   => 'Error',
        'detalle'     => "Funcionario '{$buscarFunc}' no encontrado.",
    ];
    return;
}

$asignacionActual = $equipo->asignacionActual;
if ($asignacionActual && $asignacionActual->funcionario_id === $funcionario->id) {
    $resultados[] = [
        'fila'        => $numero,
        'inventario'  => $equipo->inventario,
        'funcionario' => $funcionario->nombre,
        'resultado'   => 'Omitido',
        'detalle'     => "El equipo ya está asignado a {$funcionario->nombre}.",
    ];
    return;
}

            try {
                app(\App\Services\AsignacionService::class)->asignar($equipo, $funcionario, [
                    'hostname'         => $hostname,
                    'observacion'      => 'Asignación masiva desde Excel.',
                    'fecha_asignacion' => now()->toDateString(),
                ]);

                $resultados[] = [
                    'fila'        => $numero,
                    'inventario'  => $equipo->inventario,
                    'funcionario' => $funcionario->nombre,
                    'resultado'   => 'Asignado',
                    'detalle'     => "Equipo asignado correctamente a {$funcionario->nombre}.",
                ];
            } catch (\Exception $e) {
                $resultados[] = [
                    'fila'        => $numero,
                    'inventario'  => $equipo->inventario,
                    'funcionario' => $funcionario->nombre,
                    'resultado'   => 'Error',
                    'detalle'     => $e->getMessage(),
                ];
            }
        });

    @unlink($rutaTemporal);

    // Generar Excel resultado
    $ruta   = storage_path('app/resultado_asignacion_masiva_' . now()->format('Ymd_His') . '.xlsx');
    $writer = \Spatie\SimpleExcel\SimpleExcelWriter::create($ruta)
        ->addHeader(['Fila', 'Inventario', 'Funcionario encontrado', 'Resultado', 'Detalle']);

    foreach ($resultados as $r) {
        $writer->addRow([
            $r['fila'],
            $r['inventario'],
            $r['funcionario'],
            $r['resultado'],
            $r['detalle'],
        ]);
    }
    $writer->close();

    $asignados = collect($resultados)->where('resultado', 'Asignado')->count();
    $errores   = collect($resultados)->where('resultado', 'Error')->count();
    $nombre    = "asignacion_masiva_{$asignados}ok_{$errores}err.xlsx";

    return response()->download($ruta, $nombre)->deleteFileAfterSend();
}

public function plantillaAsignacionMasiva()
{
    $ruta = storage_path('app/plantilla_asignacion_masiva.xlsx');
    \Spatie\SimpleExcel\SimpleExcelWriter::create($ruta)
        ->addHeader(['inventario', 'serial', 'funcionario', 'hostname'])
        ->close();
    return response()->download($ruta, 'plantilla_asignacion_masiva.xlsx')->deleteFileAfterSend();
}

public function desasignarWeb(int $id)
{
    $detalleEquipo = DetalleEquipo::findOrFail($id);
    $this->service->desasignar($detalleEquipo);
    return redirect()->back()->with('info', 'Equipo desasignado correctamente.');
}
}
