<?php

namespace App\Http\Controllers;

use App\Http\Requests\DetalleEquipo\DetalleEquipoRequest;
use App\Models\DetalleEquipo;
use App\Models\Equipo;
use App\Models\EstadoEquipo;
use App\Models\TipoDisco;
use App\Models\TipoEquipo;
use App\Services\DetalleEquipoService;
use Illuminate\Http\Request;
use Spatie\SimpleExcel\SimpleExcelReader;
use Spatie\SimpleExcel\SimpleExcelWriter;



class DetalleEquipoController extends Controller
{
    public function __construct(protected DetalleEquipoService $service) {}

   public function index(Request $request)
{
    $buscar   = $request->input('buscar');
    $tipoId   = $request->input('tipo_id');
    $estadoId = $request->input('estado_id');

    $equipos = DetalleEquipo::with(['equipo.tipoEquipo', 'estado', 'tipoDisco'])
        ->whereDoesntHave('asignacionActual')
        ->when($buscar, fn($q) =>
            $q->where('inventario', 'like', "%{$buscar}%")
              ->orWhere('serial', 'like', "%{$buscar}%")
              ->orWhereHas('equipo', fn($q2) =>
                  $q2->where('marca', 'like', "%{$buscar}%")
                     ->orWhere('modelo', 'like', "%{$buscar}%")
              )
        )
        ->when($tipoId, fn($q) =>
            $q->whereHas('equipo', fn($q2) => $q2->where('tipo_equipo_id', $tipoId))
        )
        ->when($estadoId, fn($q) => $q->where('estado_id', $estadoId))
        ->orderByDesc('updated_at')
        ->paginate(12)
        ->withQueryString();

    // Si no hay resultados y hay búsqueda, buscar en equipos asignados
    $equiposAsignados = collect();
    if ($buscar && $equipos->isEmpty()) {
        $equiposAsignados = DetalleEquipo::with(['equipo.tipoEquipo', 'estado', 'asignacionActual.funcionario'])
            ->whereHas('asignacionActual')
            ->where(fn($q) =>
                $q->where('inventario', 'like', "%{$buscar}%")
                  ->orWhere('serial', 'like', "%{$buscar}%")
                  ->orWhereHas('equipo', fn($q2) =>
                      $q2->where('marca', 'like', "%{$buscar}%")
                         ->orWhere('modelo', 'like', "%{$buscar}%")
                  )
            )
            ->limit(5)
            ->get();
    }

    $tipoEquipos   = TipoEquipo::orderBy('nombre')->get();
    $estadoEquipos = EstadoEquipo::orderBy('nombre')->get();

    return view('detalle_equipos.index', compact(
        'equipos', 'buscar', 'tipoEquipos', 'estadoEquipos', 'equiposAsignados'
    ));
}

   public function create()
{
    return view('detalle_equipos.create', $this->datosFormulario());
}

    public function store(DetalleEquipoRequest $request)
    {
        $this->service->crear($request->validated());

        return redirect()->route('detalle-equipo.index')->with('info', 'Equipo registrado correctamente.');
    }

public function edit(DetalleEquipo $detalle_equipo)
{
    $detalle_equipo->load(['equipo.tipoEquipo', 'estado', 'tipoDisco', 'macs']);

    $macEthernet = $detalle_equipo->macs->firstWhere('interfaz', 'Ethernet');
    $macWifi     = $detalle_equipo->macs->firstWhere('interfaz', 'WiFi');

    return view('detalle_equipos.edit', array_merge(
        $this->datosFormulario($detalle_equipo->equipo->tipo_equipo_id, $detalle_equipo),
        compact('detalle_equipo', 'macEthernet', 'macWifi')
    ));
}
public function update(DetalleEquipoRequest $request, DetalleEquipo $detalle_equipo)
{
    $this->service->actualizar($detalle_equipo, $request->validated());

    if ($request->input('redirect_to') === 'show') {
        return redirect()->route('equipo-asignado.show', $detalle_equipo)
            ->with('info', 'Equipo actualizado correctamente.');
    }

    return redirect()->route('detalle-equipo.index')
        ->with('info', 'Equipo actualizado correctamente.');
}
    public function destroy(DetalleEquipo $detalle_equipo)
    {
        $detalle_equipo->delete();

        return redirect()->route('detalle-equipo.index')->with('info', 'Equipo movido a la papelera.');
    }


public function papelera(Request $request)
{
    $buscar    = $request->input('buscar');
    $fechaDesde = $request->input('fecha_desde');
    $fechaHasta = $request->input('fecha_hasta');

    $equipos = DetalleEquipo::onlyTrashed()
        ->with(['equipo.tipoEquipo', 'estado'])
        ->when($buscar, fn($q) =>
            $q->where('inventario', 'like', "%{$buscar}%")
              ->orWhere('serial', 'like', "%{$buscar}%")
              ->orWhereHas('equipo', fn($q2) =>
                  $q2->where('marca', 'like', "%{$buscar}%")
                     ->orWhere('modelo', 'like', "%{$buscar}%")
              )
        )
        ->when($fechaDesde, fn($q) => $q->where('deleted_at', '>=', $fechaDesde . ' 00:00:00'))
        ->when($fechaHasta, fn($q) => $q->where('deleted_at', '<=', $fechaHasta . ' 23:59:59'))
        ->orderByDesc('deleted_at')
        ->paginate(15)
        ->withQueryString();

    return view('detalle_equipos.papelera', compact('equipos', 'buscar', 'fechaDesde', 'fechaHasta'));
}

public function exportarPapelera(Request $request)
{
    $buscar     = $request->input('buscar');
    $fechaDesde = $request->input('fecha_desde');
    $fechaHasta = $request->input('fecha_hasta');

    $equipos = DetalleEquipo::onlyTrashed()
        ->with([
            'equipo.tipoEquipo',
            'estado',
            'observaciones.user',
            'historialAsignaciones.funcionario',
            'historialAsignaciones.user',
        ])
        ->when($buscar, fn($q) =>
            $q->where('inventario', 'like', "%{$buscar}%")
              ->orWhere('serial', 'like', "%{$buscar}%")
              ->orWhereHas('equipo', fn($q2) =>
                  $q2->where('marca', 'like', "%{$buscar}%")
                     ->orWhere('modelo', 'like', "%{$buscar}%")
              )
        )
        ->when($fechaDesde, fn($q) => $q->where('deleted_at', '>=', $fechaDesde . ' 00:00:00'))
        ->when($fechaHasta, fn($q) => $q->where('deleted_at', '<=', $fechaHasta . ' 23:59:59'))
        ->orderByDesc('deleted_at')
        ->get();

    $ruta   = storage_path('app/papelera_inventario_' . now()->format('Ymd_His') . '.xlsx');
    $writer = \Spatie\SimpleExcel\SimpleExcelWriter::create($ruta)
        ->addHeader([
            'Inventario', 'Serial', 'Tipo', 'Marca', 'Modelo',
            'Estado', 'Detalle Baja', 'Fecha Baja',
            'Usuarios asignados', 'Historial asignaciones',
            'Observaciones',
        ]);

    $equipos->each(function($eq) use ($writer) {

        // Usuarios que tuvo
        $usuarios = $eq->historialAsignaciones
            ->where('accion', 'asignado')
            ->map(fn($h) => $h->funcionario?->nombre ?? '—')
            ->unique()
            ->implode(' | ');

        // Historial completo
        $historial = $eq->historialAsignaciones
            ->map(fn($h) =>
                $h->fecha->format('d-m-Y') . ' - ' .
                ucfirst($h->accion) . ' - ' .
                ($h->funcionario?->nombre ?? '—') . 
                ($h->user ? ' (por ' . $h->user->name . ')' : '')
            )
            ->implode(' | ');

        // Observaciones
        $observaciones = $eq->observaciones
            ->map(fn($o) =>
                $o->created_at->format('d-m-Y') . ' - ' .
                ucfirst(str_replace('_', ' ', $o->tipo)) . ': ' .
                $o->nota .
                ($o->user ? ' (por ' . $o->user->name . ')' : '')
            )
            ->implode(' | ');

        $writer->addRow([
            $eq->inventario,
            $eq->serial ?? '—',
            $eq->equipo?->tipoEquipo?->nombre ?? '—',
            $eq->equipo?->marca ?? '—',
            $eq->equipo?->modelo ?? '—',
            $eq->estado?->nombre ?? '—',
            $eq->detalle_baja ?? '—',
            $eq->deleted_at?->format('d-m-Y H:i') ?? '—',
            $usuarios ?: '—',
            $historial ?: '—',
            $observaciones ?: '—',
        ]);
    });

    $writer->close();
    return response()->download($ruta, 'papelera_inventario_' . now()->format('Ymd') . '.xlsx')
        ->deleteFileAfterSend();
}

    public function restore(int $id)
    {
        DetalleEquipo::onlyTrashed()->findOrFail($id)->restore();

        return redirect()->route('detalle-equipo.papelera')->with('info', 'Equipo restaurado correctamente.');
    }

    public function forceDelete(int $id)
    {
        $equipo = DetalleEquipo::onlyTrashed()->findOrFail($id);
        $equipo->macs()->delete();
        $equipo->forceDelete();

        return redirect()->route('detalle-equipo.papelera')->with('info', 'Equipo eliminado permanentemente.');
    }

    public function exportar(Request $request)
    {
        $ruta = storage_path('app/inventario_' . now()->format('Ymd_His') . '.xlsx');

        $writer = SimpleExcelWriter::create($ruta)
            ->addHeader(['Inventario', 'Serial', 'Tipo', 'Marca', 'Modelo', 'Estado', 'RAM (GB)', 'Disco (GB)', 'Tipo Disco', 'IP', 'MAC Ethernet', 'MAC WiFi', 'Teletrabajo', 'Préstamo', 'Fecha Adquisición']);

        DetalleEquipo::with(['equipo.tipoEquipo', 'estado', 'tipoDisco', 'macs'])
            ->whereDoesntHave('asignacionActual')
            ->when($request->tipo_id, fn ($q) =>
                $q->whereHas('equipo', fn ($q2) => $q2->where('tipo_equipo_id', $request->tipo_id))
            )
            ->when($request->estado_id, fn ($q) => $q->where('estado_id', $request->estado_id))
            ->when($request->buscar, function ($q) use ($request) {
                $q->where(function ($q2) use ($request) {
                    $q2->where('inventario', 'like', "%{$request->buscar}%")
                       ->orWhere('serial', 'like', "%{$request->buscar}%");
                });
            })
            ->orderBy('inventario')
            ->each(function ($e) use ($writer) {
                $writer->addRow([
                    $e->inventario,
                    $e->serial,
                    $e->equipo->tipoEquipo->nombre ?? '—',
                    $e->equipo->marca ?? '—',
                    $e->equipo->modelo ?? '—',
                    $e->estado->nombre ?? '—',
                    $e->ram_gb ?? '—',
                    $e->capacidad_disco_gb ?? '—',
                    $e->tipoDisco->nombre ?? '—',
                    $e->ip ?? '—',
                    $e->macs->firstWhere('interfaz', 'Ethernet')?->mac ?? '—',
                    $e->macs->firstWhere('interfaz', 'WiFi')?->mac ?? '—',
                    $e->teletrabajo ? 'Sí' : 'No',
                    $e->es_prestamo ? 'Sí' : 'No',
                    $e->fecha_adquisicion?->format('d-m-Y') ?? '—',
                ]);
            });

        $writer->close();

        return response()->download($ruta, 'inventario_' . now()->format('Ymd_His') . '.xlsx')->deleteFileAfterSend();
    }

    // Carga los equipos por tipo para el select dinámico
    public function equiposPorTipo(Request $request)
    {
        $equipos = Equipo::where('tipo_equipo_id', $request->tipo_id)
            ->orderBy('marca')->orderBy('modelo')
            ->get(['id', 'marca', 'modelo']);

        return response()->json($equipos);
    }

protected function datosFormulario(?int $tipoSeleccionado = null, ?DetalleEquipo $detalleEquipo = null): array
{
    return [
        'detalleEquipo'  => $detalleEquipo ?? new DetalleEquipo(),
        'tipoEquipos'    => TipoEquipo::orderBy('nombre')->get(),
        'equipos'        => $tipoSeleccionado
                                ? Equipo::where('tipo_equipo_id', $tipoSeleccionado)->orderBy('marca')->get()
                                : collect(),
        'estados'        => EstadoEquipo::orderBy('nombre')->get(),
        'tiposDiscos'    => TipoDisco::orderBy('nombre')->get(),
    ];
}

    public function importarVista()
{
    return view('detalle_equipos.importar', [
        'estados'     => EstadoEquipo::orderBy('nombre')->get(),
        'tiposDiscos' => TipoDisco::orderBy('nombre')->get(),
        'equipos'     => Equipo::with('tipoEquipo')->orderBy('marca')->orderBy('modelo')->get(),
    ]);
}

public function importar(Request $request)
{
    $request->validate([
        'archivo' => 'required|file|mimes:xlsx,xls,csv|max:5120',
    ]);

    $extension    = $request->file('archivo')->getClientOriginalExtension();
    $rutaTemporal = storage_path('app/temp_import_inventario.' . $extension);
    copy($request->file('archivo')->getRealPath(), $rutaTemporal);

    $resultados = [];
    $numero     = 1;

    SimpleExcelReader::create($rutaTemporal)
        ->getRows()
        ->each(function (array $fila) use (&$resultados, &$numero) {
            $numero++;
            $inventario = trim($fila['inventario'] ?? '');

            if (empty($inventario)) {
                $resultados[] = [
                    'fila'       => $numero,
                    'inventario' => '—',
                    'resultado'  => 'Omitido',
                    'detalle'    => 'Inventario vacío.',
                ];
                return;
            }

            // Buscar equipo por modelo
            $equipo = null;
            if (!empty($fila['modelo'])) {
                $equipo = \App\Models\Equipo::where('modelo', 'like', '%' . trim($fila['modelo']) . '%')->first();
            }

            if (!$equipo && !empty($fila['marca'])) {
                $equipo = \App\Models\Equipo::where('marca', 'like', '%' . trim($fila['marca']) . '%')->first();
            }

            // Estado
            $estado = \App\Models\EstadoEquipo::where('nombre', trim($fila['estado'] ?? ''))->first()
                ?? \App\Models\EstadoEquipo::first();

            // Tipo disco
            $tipoDisco = null;
            if (!empty($fila['tipo_disco'])) {
                $tipoDisco = \App\Models\TipoDisco::where('nombre', 'like', '%' . trim($fila['tipo_disco']) . '%')->first();
            }

            // RAM
            $ram = null;
            if (!empty($fila['ram_gb'])) {
                preg_match('/(\d+)/', $fila['ram_gb'], $m);
                $ram = $m[1] ?? null;
            }

            // Disco
            $disco = null;
            if (!empty($fila['capacidad_disco_gb'])) {
                $val = strtoupper($fila['capacidad_disco_gb']);
                if (str_contains($val, 'TB')) {
                    preg_match('/(\d+[,.]?\d*)/', $val, $m);
                    $disco = isset($m[1]) ? (int)(str_replace(',', '.', $m[1]) * 1000) : null;
                } else {
                    preg_match('/(\d+)/', $val, $m);
                    $disco = $m[1] ?? null;
                }
            }

            try {
                \App\Models\DetalleEquipo::create([
                    'equipo_id'          => $equipo?->id,
                    'estado_id'          => $estado->id,
                    'tipo_disco_id'      => $tipoDisco?->id,
                    'inventario'         => $inventario,
                    'serial'             => trim($fila['serial'] ?? '') ?: null,
                    'ram_gb'             => $ram,
                    'capacidad_disco_gb' => $disco,
                    'ip'                 => trim($fila['ip'] ?? '') ?: null,
                    'fecha_adquisicion'  => !empty($fila['fecha_adquisicion']) ? $fila['fecha_adquisicion'] : null,
                   'puerto_switch' => !empty($fila['puerto_switch']) ? (int)$fila['puerto_switch'] : null,
                    'teletrabajo'        => in_array(strtolower($fila['teletrabajo'] ?? ''), ['si', 'sí', '1', 'true']),
                    'es_prestamo'        => in_array(strtolower($fila['es_prestamo'] ?? ''), ['si', 'sí', '1', 'true']),
                ]);

                $resultados[] = [
                    'fila'       => $numero,
                    'inventario' => $inventario,
                    'resultado'  => 'Importado',
                    'detalle'    => 'Registrado correctamente.',
                ];

            } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
                $resultados[] = [
                    'fila'       => $numero,
                    'inventario' => $inventario,
                    'resultado'  => 'Duplicado',
                    'detalle'    => "El inventario {$inventario} ya existe en el sistema.",
                ];
            } catch (\Exception $e) {
                $resultados[] = [
                    'fila'       => $numero,
                    'inventario' => $inventario,
                    'resultado'  => 'Error',
                    'detalle'    => $e->getMessage(),
                ];
            }
        });

    @unlink($rutaTemporal);

    // Generar Excel resultado
    $ruta   = storage_path('app/resultado_inventario_' . now()->format('Ymd_His') . '.xlsx');
    $writer = SimpleExcelWriter::create($ruta)
        ->addHeader(['Fila', 'Inventario', 'Resultado', 'Detalle']);

    foreach ($resultados as $r) {
        $writer->addRow([$r['fila'], $r['inventario'], $r['resultado'], $r['detalle']]);
    }
    $writer->close();

    $importados  = collect($resultados)->where('resultado', 'Importado')->count();
    $duplicados  = collect($resultados)->where('resultado', 'Duplicado')->count();
    $errores     = collect($resultados)->where('resultado', 'Error')->count();

    $nombre = "resultado_inventario_{$importados}ok_{$duplicados}dup_{$errores}err.xlsx";

    return response()->download($ruta, $nombre)->deleteFileAfterSend();
}

public function plantilla()
{
    $ruta = storage_path('app/plantilla_inventario.xlsx');

    SimpleExcelWriter::create($ruta)
        ->addHeader([
            'marca', 'modelo', 'inventario', 'serial', 'estado',
            'tipo_disco', 'ram_gb', 'capacidad_disco_gb',
            'mac_ethernet', 'mac_wifi', 'ip', 'puerto_switch',
            'fecha_adquisicion', 'teletrabajo', 'es_prestamo',
        ])
        ->close();

    return response()->download($ruta, 'plantilla_inventario.xlsx')->deleteFileAfterSend();
}


public function ajaxStore(Request $request)
{
    $request->validate([
        'equipo_id'  => 'required|exists:equipos,id',
        'inventario' => 'required|string|max:50|unique:detalle_equipos,inventario',
        'estado_id'  => 'required|exists:estados_equipo,id',
    ]);

    $detalle = DetalleEquipo::create([
        'equipo_id'          => $request->equipo_id,
        'inventario'         => $request->inventario,
        'serial' => !empty($request->serial) ? $request->serial : 'S/N' . now()->format('dmYHi'),
        'estado_id'          => $request->estado_id,
        'ram_gb'             => $request->ram_gb ?: null,
        'tipo_disco_id'      => $request->tipo_disco_id ?: null,
        'capacidad_disco_gb' => $request->capacidad_disco_gb ?: null,
        'fecha_adquisicion'  => $request->fecha_adquisicion ?: null,
        'ip'                 => $request->ip ?: null,
        'puerto_switch'      => $request->puerto_switch ?: null,
        'teletrabajo'        => $request->teletrabajo ?? false,
        'es_prestamo'        => $request->es_prestamo ?? false,
    ]);

    // MACs
    if ($request->mac_ethernet) {
        $detalle->macs()->create(['interfaz' => 'Ethernet', 'mac' => $request->mac_ethernet]);
    }
    if ($request->mac_wifi) {
        $detalle->macs()->create(['interfaz' => 'WiFi', 'mac' => $request->mac_wifi]);
    }

    return response()->json([
        'ok'      => true,
        'mensaje' => "Equipo {$request->inventario} registrado correctamente.",
        'id'      => $detalle->id,
    ]);
}

public function validarMasivo(Request $request)
{
    $request->validate(['archivo' => 'required|file|mimes:xlsx,xls,csv|max:5120']);

    $extension    = $request->file('archivo')->getClientOriginalExtension();
    $rutaTemporal = storage_path('app/temp_validar_masivo.' . $extension);
    copy($request->file('archivo')->getRealPath(), $rutaTemporal);

    $resultados = [];

    \Spatie\SimpleExcel\SimpleExcelReader::create($rutaTemporal)
        ->getRows()
        ->each(function (array $fila) use (&$resultados) {
            $inventario = trim($fila['inventario'] ?? $fila['INVENTARIO'] ?? '');
            $serial     = trim($fila['serial'] ?? $fila['SERIAL'] ?? '');

            if (empty($inventario) && empty($serial)) return;

            $equipo = \App\Models\DetalleEquipo::with([
                    'equipo.tipoEquipo',
                    'estado',
                    'asignacionActual.funcionario',
                ])
                ->when($inventario, fn($q) => $q->where('inventario', $inventario))
                ->when(!$inventario && $serial, fn($q) => $q->where('serial', $serial))
                ->withTrashed()
                ->first();

            if (!$equipo) {
                $resultados[] = [
                    'inventario'  => $inventario ?: $serial,
                    'serial'      => '—',
                    'tipo'        => '—',
                    'marca'       => '—',
                    'modelo'      => '—',
                    'estado'      => '—',
                    'asignado_a'  => '—',
                    'hostname'    => '—',
                    'resultado'   => 'No encontrado',
                ];
                return;
            }

            $resultados[] = [
                'inventario'  => $equipo->inventario,
                'serial'      => $equipo->serial ?? '—',
                'tipo'        => $equipo->equipo?->tipoEquipo?->nombre ?? '—',
                'marca'       => $equipo->equipo?->marca ?? '—',
                'modelo'      => $equipo->equipo?->modelo ?? '—',
                'estado'      => $equipo->deleted_at ? 'En papelera' : ($equipo->estado?->nombre ?? '—'),
                'asignado_a'  => $equipo->asignacionActual?->funcionario?->nombre ?? 'Sin asignar',
                'hostname'    => $equipo->asignacionActual?->hostname ?? '—',
                'resultado'   => $equipo->deleted_at ? 'En papelera' : 'Encontrado',
            ];
        });

    @unlink($rutaTemporal);

    $ruta   = storage_path('app/validacion_masiva_' . now()->format('Ymd_His') . '.xlsx');
    $writer = \Spatie\SimpleExcel\SimpleExcelWriter::create($ruta)
        ->addHeader([
            'Inventario', 'Serial', 'Tipo', 'Marca', 'Modelo',
            'Estado', 'Asignado a', 'Hostname', 'Resultado'
        ]);

    foreach ($resultados as $r) {
        $writer->addRow([
            $r['inventario'],
            $r['serial'],
            $r['tipo'],
            $r['marca'],
            $r['modelo'],
            $r['estado'],
            $r['asignado_a'],
            $r['hostname'],
            $r['resultado'],
        ]);
    }
    $writer->close();

    $encontrados   = collect($resultados)->where('resultado', 'Encontrado')->count();
    $noEncontrados = collect($resultados)->where('resultado', 'No encontrado')->count();
    $papelera      = collect($resultados)->where('resultado', 'En papelera')->count();

    $nombre = "validacion_{$encontrados}ok_{$noEncontrados}noencontrados_{$papelera}papelera.xlsx";

    return response()->download($ruta, $nombre)->deleteFileAfterSend();
}

public function plantillaValidarMasivo()
{
    $ruta = storage_path('app/plantilla_validar_masivo.xlsx');
    \Spatie\SimpleExcel\SimpleExcelWriter::create($ruta)
        ->addHeader(['inventario', 'serial'])
        ->close();
    return response()->download($ruta, 'plantilla_validar_masivo.xlsx')->deleteFileAfterSend();
}

}