<?php

namespace App\Http\Controllers;

use App\Http\Requests\Funcionario\FuncionarioRequest;
use App\Models\Funcionario;
use App\Models\Region;
use App\Models\Division;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Spatie\SimpleExcel\SimpleExcelWriter;
use Spatie\SimpleExcel\SimpleExcelReader;


class FuncionarioController extends Controller
{
  public function index(Request $request)
{
    $buscar      = $request->input('buscar');
    $divisionId  = $request->input('division_id');
    $estadoId    = $request->input('estado'); // activo/inactivo
    $regionId    = $request->input('region_id');

    $funcionarios = Funcionario::with(['region', 'division', 'puesto.piso'])
    ->when($buscar, function ($query, $buscar) {
    $palabras = array_filter(explode(' ', $buscar), fn($p) => strlen($p) > 1);
    $query->where(function ($q) use ($buscar, $palabras) {
        // Búsqueda cadena completa
        $q->where('nombre', 'like', "%{$buscar}%")
          ->orWhere('rut', 'like', "%{$buscar}%")
          ->orWhere('cargo', 'like', "%{$buscar}%");
        // Búsqueda por palabras separadas
        if (count($palabras) > 1) {
            $q->orWhere(function($q2) use ($palabras) {
                foreach ($palabras as $palabra) {
                    $q2->where('nombre', 'like', "%{$palabra}%");
                }
            });
        }
    });
})
        ->when($divisionId, fn($q) => $q->where('division_id', $divisionId))
        ->when($regionId, fn($q) => $q->where('region_id', $regionId))
        ->when($estadoId !== null && $estadoId !== '', fn($q) =>
            $q->where('activo', $estadoId === '1'))
        ->orderByDesc('updated_at')
        ->paginate(15)
        ->withQueryString();

    $divisiones = \App\Models\Division::where('activo', true)->orderBy('nombre')->get();
    $regiones   = \App\Models\Region::orderBy('nombre')->get();

    return view('funcionarios.index', compact(
        'funcionarios', 'buscar', 'divisiones', 'regiones',
        'divisionId', 'regionId', 'estadoId'
    ));
}

   public function create()
{
    return view('funcionarios.create', [
        'funcionario' => new Funcionario(),
        'regiones'    => Region::orderBy('nombre')->get(),
        'divisiones'  => Division::where('activo', true)->orderBy('nombre')->get(),
    ]);
}

    public function store(FuncionarioRequest $request)
    {
        $data = $request->validated();
        $data['activo'] = $request->boolean('activo');

        if ($request->hasFile('avatar')) {
            $data['avatar'] = $request->file('avatar')->store('avatares', 'public');
        }

        Funcionario::create($data);

        return redirect()->route('funcionario.index')->with('info', 'Funcionario registrado correctamente.');
    }

  public function edit(Funcionario $funcionario)
{
    return view('funcionarios.edit', [
        'funcionario' => $funcionario,
        'regiones'    => Region::orderBy('nombre')->get(),
        'divisiones'  => Division::where('activo', true)->orderBy('nombre')->get(),
    ]);
}

    public function update(FuncionarioRequest $request, Funcionario $funcionario)
    {
        $data = $request->validated();
        $data['activo'] = $request->boolean('activo');

      if ($request->boolean('eliminar_avatar') && $funcionario->avatar) {
    Storage::disk('public')->delete($funcionario->avatar);
    $data['avatar'] = null;
} elseif ($request->hasFile('avatar')) {
    if ($funcionario->avatar) {
        Storage::disk('public')->delete($funcionario->avatar);
    }
    $data['avatar'] = $request->file('avatar')->store('avatares', 'public');
} else {
    unset($data['avatar']);
}

        $funcionario->update($data);

     return redirect()->route('funcionario.show', $funcionario)
    ->with('info', 'Funcionario actualizado correctamente.')
    ->with('abrir_show', true);
    }

   public function destroy(Funcionario $funcionario)
{
    $funcionario->delete();
    return redirect()->back()->with('info', 'Funcionario movido a la papelera.');
}
public function destroyMasivo(Request $request)
{
    $ids = $request->input('ids', []);
    Funcionario::whereIn('id', $ids)->each(fn($f) => $f->delete());
    return redirect()->back()->with('info', count($ids) . ' funcionario(s) movidos a la papelera.');
}

    public function papelera()
    {
        $funcionarios = Funcionario::onlyTrashed()
            ->with('region')
            ->orderByDesc('updated_at')
            ->paginate(15);

        return view('funcionarios.papelera', compact('funcionarios'));
    }

    public function restore(int $id)
    {
        $funcionario = Funcionario::onlyTrashed()->findOrFail($id);
        $funcionario->restore();

        return redirect()->route('funcionario.papelera')->with('info', 'Funcionario restaurado correctamente.');
    }

    public function forceDelete(int $id)
    {
        $funcionario = Funcionario::onlyTrashed()->findOrFail($id);

        if ($funcionario->avatar) {
            Storage::disk('public')->delete($funcionario->avatar);
        }

        $funcionario->forceDelete();

        return redirect()->route('funcionario.papelera')->with('info', 'Funcionario eliminado permanentemente.');
    }

public function exportar(Request $request)
{
    $buscar     = $request->input('buscar');
    $divisionId = $request->input('division_id');
    $regionId   = $request->input('region_id');
    $estadoId   = $request->input('estado');

    $ruta   = storage_path('app/funcionarios_' . now()->format('Ymd_His') . '.xlsx');
    $writer = SimpleExcelWriter::create($ruta)
        ->addHeader([
            'ID', 'Nombre', 'RUT', 'Región', 'División', 'Departamento', 'Unidad',
            'Cargo', 'Correo', 'Fecha Ingreso', 'Fin Contrato', 'Estado', 'Registrado el'
        ]);

    Funcionario::with(['region', 'division', 'departamento', 'unidad'])
        ->when($buscar, fn($q) => $q->where(function($q2) use ($buscar) {
            $q2->where('nombre', 'like', "%{$buscar}%")
               ->orWhere('rut', 'like', "%{$buscar}%")
               ->orWhere('cargo', 'like', "%{$buscar}%");
        }))
        ->when($divisionId, fn($q) => $q->where('division_id', $divisionId))
        ->when($regionId, fn($q) => $q->where('region_id', $regionId))
        ->when($estadoId !== null && $estadoId !== '', fn($q) =>
            $q->where('activo', $estadoId === '1'))
        ->orderByDesc('updated_at')
        ->each(function ($funcionario) use ($writer) {
            $writer->addRow([
                $funcionario->id,
                $funcionario->nombre,
                $funcionario->rut_formateado ?? '—',
                $funcionario->region?->nombre ?? '—',
                $funcionario->division?->nombre ?? '—',
                $funcionario->departamento?->nombre ?? '—',
                $funcionario->unidad?->nombre ?? '—',
                $funcionario->cargo ?? '—',
                $funcionario->correo ?? '—',
                $funcionario->fecha_ingreso?->format('d-m-Y') ?? '—',
                $funcionario->fecha_fin_contrato?->format('d-m-Y') ?? '—',
                $funcionario->activo ? 'Activo' : 'Inactivo',
                $funcionario->created_at->format('d-m-Y'),
            ]);
        });

    $writer->close();
    return response()->download($ruta, 'funcionarios_' . now()->format('Ymd_His') . '.xlsx')
        ->deleteFileAfterSend();
}

public function plantilla()
{
    $ruta = storage_path('app/plantilla_funcionarios.xlsx');
    SimpleExcelWriter::create($ruta)
        ->addHeader([
            'nombre', 'rut', 'region', 'division', 'departamento', 'unidad',
            'cargo', 'correo', 'fecha_ingreso', 'fecha_fin_contrato', 'activo'
        ])
        ->close();
    return response()->download($ruta, 'plantilla_funcionarios.xlsx')->deleteFileAfterSend();
}

public function importarVista()
{
    $regiones = Region::orderBy('nombre')->pluck('nombre')->toArray();

    return view('funcionarios.importar', compact('regiones'));
}

public function importar(Request $request)
{
    $request->validate([
        'archivo' => 'required|file|mimes:xlsx,xls,csv|max:5120',
    ], [
        'archivo.required' => 'Debes seleccionar un archivo.',
        'archivo.mimes'    => 'El archivo debe ser .xlsx, .xls o .csv.',
        'archivo.max'      => 'El archivo no puede superar los 5MB.',
    ]);

    $resultados = [];
    $numero = 1;

    SimpleExcelReader::create($request->file('archivo')->getRealPath(), 'xlsx')
        ->getRows()
        ->each(function (array $fila) use (&$resultados, &$numero) {
            $numero++;

            if (empty($fila['nombre'])) {
                $resultados[] = [
                    'fila'      => $numero,
                    'nombre'    => $fila['nombre'] ?? '—',
                    'rut'       => $fila['rut'] ?? '—',
                    'region'    => $fila['region'] ?? '—',
                    'resultado' => 'Error',
                    'detalle'   => 'El nombre es obligatorio.',
                ];
                return;
            }

            // Validar región
            $region = null;
            if (!empty($fila['region'])) {
                $region = Region::whereRaw('LOWER(nombre) = ?', [strtolower(trim($fila['region']))])->first();
                if (!$region) {
                    $resultados[] = [
                        'fila'      => $numero,
                        'nombre'    => $fila['nombre'],
                        'rut'       => $fila['rut'] ?? '—',
                        'region'    => $fila['region'],
                        'resultado' => 'Error',
                        'detalle'   => "La región '{$fila['region']}' no existe en el sistema.",
                    ];
                    return;
                }
            }

            // Validar RUT duplicado
            if (!empty($fila['rut'])) {
                $rutNormalizado = strtoupper(str_replace(['.', ' '], '', trim($fila['rut'])));
                if (Funcionario::where('rut', $rutNormalizado)->exists()) {
                    $resultados[] = [
                        'fila'      => $numero,
                        'nombre'    => $fila['nombre'],
                        'rut'       => $fila['rut'],
                        'region'    => $fila['region'] ?? '—',
                        'resultado' => 'Omitido',
                        'detalle'   => "Ya existe un funcionario con el RUT {$fila['rut']}.",
                    ];
                    return;
                }
            }

            // Buscar división
            $division = null;
            if (!empty($fila['division'])) {
                $division = \App\Models\Division::whereRaw('LOWER(nombre) = ?', [strtolower(trim($fila['division']))])->first();
            }

            // Buscar departamento
            $departamento = null;
            if (!empty($fila['departamento']) && $division) {
                $departamento = \App\Models\Departamento::whereRaw('LOWER(nombre) = ?', [strtolower(trim($fila['departamento']))])
                    ->where('division_id', $division->id)
                    ->first();
            }

            // Buscar unidad
            $unidad = null;
            if (!empty($fila['unidad']) && $departamento) {
                $unidad = \App\Models\Unidad::whereRaw('LOWER(nombre) = ?', [strtolower(trim($fila['unidad']))])
                    ->where('departamento_id', $departamento->id)
                    ->first();
            }

            $activo = !empty($fila['activo'])
                ? in_array(strtolower(trim($fila['activo'])), ['1', 'si', 'sí', 'true', 'activo'])
                : true;

            Funcionario::create([
                'nombre'             => trim($fila['nombre']),
                'rut'                => !empty($fila['rut']) ? strtoupper(str_replace(['.', ' '], '', trim($fila['rut']))) : null,
                'region_id'          => $region?->id,
                'division_id'        => $division?->id,
                'departamento_id'    => $departamento?->id,
                'unidad_id'          => $unidad?->id,
                'cargo'              => trim($fila['cargo'] ?? '') ?: null,
                'correo'             => trim($fila['correo'] ?? '') ?: null,
                'fecha_ingreso'      => !empty($fila['fecha_ingreso']) ? $fila['fecha_ingreso'] : null,
                'fecha_fin_contrato' => !empty($fila['fecha_fin_contrato']) ? $fila['fecha_fin_contrato'] : null,
                'activo'             => $activo,
            ]);

            $resultados[] = [
                'fila'      => $numero,
                'nombre'    => $fila['nombre'],
                'rut'       => $fila['rut'] ?? '—',
                'region'    => $fila['region'] ?? '—',
                'resultado' => 'Importado',
                'detalle'   => 'Registrado correctamente.',
            ];
        });

    // Generar Excel de resultados
    $ruta = storage_path('app/resultado_funcionarios_' . now()->format('Ymd_His') . '.xlsx');

    $writer = SimpleExcelWriter::create($ruta)
        ->addHeader(['Fila', 'Nombre', 'RUT', 'Región', 'Resultado', 'Detalle']);

    foreach ($resultados as $r) {
        $writer->addRow([
            $r['fila'],
            $r['nombre'],
            $r['rut'],
            $r['region'],
            $r['resultado'],
            $r['detalle'],
        ]);
    }

    $writer->close();

    $importados = collect($resultados)->where('resultado', 'Importado')->count();
    $errores    = collect($resultados)->where('resultado', 'Error')->count();
    $omitidos   = collect($resultados)->where('resultado', 'Omitido')->count();

    $nombre = "resultado_funcionarios_{$importados}ok_{$errores}err_{$omitidos}omit.xlsx";

    return response()->download($ruta, $nombre)->deleteFileAfterSend();
}

public function show(Funcionario $funcionario)
{
    $funcionario->load([
        'region',
        'division',
        'departamento',
        'unidad',
        'puesto.piso.edificio',
        'asignaciones.detalleEquipo.equipo.tipoEquipo',
        'asignaciones.detalleEquipo.estado',
        'historialAsignaciones.detalleEquipo.equipo',
    ]);

    return view('funcionarios.show', compact('funcionario'));
}

public function plantillaRut()
{
    $ruta = storage_path('app/plantilla_actualizacion_funcionarios.xlsx');
    SimpleExcelWriter::create($ruta)
        ->addHeader([
            'RUT', 'APELLIDO PATERNO', 'APELLIDO MATERNO', 'NOMBRES',
            'DIVISION', 'DEPARTAMENTO', 'UNIDAD', 'MAIL'
        ])
        ->close();
    return response()->download($ruta, 'plantilla_actualizacion_funcionarios.xlsx')
        ->deleteFileAfterSend();
}

public function importarRutVista()
{
    return view('funcionarios.importar_rut');
}

public function importarRut(Request $request)
{
    $request->validate(['archivo' => 'required|file|mimes:xlsx,xls,csv|max:5120']);

    $extension    = $request->file('archivo')->getClientOriginalExtension();
    $rutaTemporal = storage_path('app/temp_import_rut.' . $extension);
    copy($request->file('archivo')->getRealPath(), $rutaTemporal);

    $actualizados   = [];
    $noEncontrados  = [];
    $errores        = [];

    SimpleExcelReader::create($rutaTemporal)
        ->getRows()
        ->each(function (array $fila) use (&$actualizados, &$noEncontrados, &$errores) {
            try {
                $nombres = trim($fila['NOMBRES'] ?? '');
                $apPat   = trim($fila['APELLIDO PATERNO'] ?? '');
                $apMat   = trim($fila['APELLIDO MATERNO'] ?? '');

                if (empty($nombres) && empty($apPat)) return;

                // Buscar funcionario por apellidos y nombre
                $funcionario = Funcionario::where('nombre', 'like', "%{$apPat}%")
                    ->where('nombre', 'like', "%{$apMat}%")
                    ->where('nombre', 'like', "%{$nombres}%")
                    ->first();

                if (!$funcionario) {
                    $noEncontrados[] = [
                        'nombre'    => "$nombres $apPat $apMat",
                        'rut'       => $fila['RUT'] ?? '—',
                        'resultado' => 'No encontrado',
                        'detalle'   => 'No se encontró coincidencia en el sistema.',
                    ];
                    return;
                }

                // Buscar división
                $division = null;
                if (!empty($fila['DIVISION'])) {
                    $division = \App\Models\Division::whereRaw(
                        'LOWER(nombre) LIKE ?', ['%' . strtolower(trim($fila['DIVISION'])) . '%']
                    )->first();
                }

                // Buscar departamento
                $departamento = null;
                if (!empty($fila['DEPARTAMENTO']) && $division) {
                    $departamento = \App\Models\Departamento::whereRaw(
                        'LOWER(nombre) LIKE ?', ['%' . strtolower(trim($fila['DEPARTAMENTO'])) . '%']
                    )->where('division_id', $division->id)->first();
                }

                // Buscar unidad
                $unidad = null;
                if (!empty($fila['UNIDAD']) && $departamento) {
                    $unidad = \App\Models\Unidad::whereRaw(
                        'LOWER(nombre) LIKE ?', ['%' . strtolower(trim($fila['UNIDAD'])) . '%']
                    )->where('departamento_id', $departamento->id)->first();
                }

                $datosActualizar = [];

                if (!empty($fila['RUT'])) {
                    $datosActualizar['rut'] = strtoupper(str_replace(['.', ' '], '', trim($fila['RUT'])));
                }
                if (!empty($fila['MAIL'])) {
                    $datosActualizar['correo'] = trim($fila['MAIL']);
                }
                if ($division) {
                    $datosActualizar['division_id'] = $division->id;
                }
                if ($departamento) {
                    $datosActualizar['departamento_id'] = $departamento->id;
                }
                if ($unidad) {
                    $datosActualizar['unidad_id'] = $unidad->id;
                }

                $funcionario->update($datosActualizar);

                $actualizados[] = [
                    'nombre'    => $funcionario->nombre,
                    'rut'       => $fila['RUT'] ?? '—',
                    'resultado' => 'Actualizado',
                    'detalle'   => 'Datos actualizados correctamente.',
                ];

            } catch (\Exception $e) {
                $errores[] = [
                    'nombre'    => ($fila['NOMBRES'] ?? '') . ' ' . ($fila['APELLIDO PATERNO'] ?? ''),
                    'rut'       => $fila['RUT'] ?? '—',
                    'resultado' => 'Error',
                    'detalle'   => $e->getMessage(),
                ];
            }
        });

    @unlink($rutaTemporal);

    // Generar Excel resultado
    $ruta = storage_path('app/resultado_actualizacion_' . now()->format('Ymd_His') . '.xlsx');
    $writer = SimpleExcelWriter::create($ruta)
        ->addHeader(['Nombre', 'RUT', 'Resultado', 'Detalle']);

    foreach (array_merge($actualizados, $noEncontrados, $errores) as $r) {
        $writer->addRow([
            $r['nombre'],
            $r['rut'],
            $r['resultado'],
            $r['detalle'],
        ]);
    }
    $writer->close();

    $totalAct = count($actualizados);
    $totalNoE = count($noEncontrados);
    $totalErr = count($errores);

    $nombre = "resultado_actualizacion_{$totalAct}ok_{$totalNoE}noencontrados_{$totalErr}err.xlsx";

    return response()->download($ruta, $nombre)->deleteFileAfterSend();
}

public function importarCorreos(Request $request)
{
    $request->validate(['archivo' => 'required|file|mimes:xlsx,xls,csv|max:5120']);

    $extension    = $request->file('archivo')->getClientOriginalExtension();
    $rutaTemporal = storage_path('app/temp_import_correos.' . $extension);
    copy($request->file('archivo')->getRealPath(), $rutaTemporal);

    $actualizados  = [];
    $noEncontrados = [];
    $errores       = [];

    \Spatie\SimpleExcel\SimpleExcelReader::create($rutaTemporal)
        ->getRows()
        ->each(function (array $fila) use (&$actualizados, &$noEncontrados, &$errores) {
            try {
                $nombreExcel = trim($fila['Nombre Usuario'] ?? '');
                $correo      = trim($fila['Email usuario'] ?? '');

                if (empty($nombreExcel) || empty($correo)) return;

                // Dividir el nombre en palabras y buscar funcionario que contenga todas
                $palabras = array_filter(explode(' ', $nombreExcel));

                $query = Funcionario::query();
                foreach ($palabras as $palabra) {
                    if (strlen($palabra) > 2) { // ignorar palabras muy cortas
                        $query->where('nombre', 'like', "%{$palabra}%");
                    }
                }

                $funcionario = $query->first();

                if (!$funcionario) {
                    $noEncontrados[] = [
                        'nombre'    => $nombreExcel,
                        'correo'    => $correo,
                        'resultado' => 'No encontrado',
                        'detalle'   => 'Sin coincidencia en el sistema.',
                    ];
                    return;
                }

                $funcionario->update(['correo' => $correo]);

                $actualizados[] = [
                    'nombre'    => $funcionario->nombre,
                    'correo'    => $correo,
                    'resultado' => 'Actualizado',
                    'detalle'   => 'Correo actualizado correctamente.',
                ];

            } catch (\Exception $e) {
                $errores[] = [
                    'nombre'    => $fila['Nombre Usuario'] ?? '—',
                    'correo'    => $fila['Email usuario'] ?? '—',
                    'resultado' => 'Error',
                    'detalle'   => $e->getMessage(),
                ];
            }
        });

    @unlink($rutaTemporal);

    // Generar Excel resultado
    $ruta = storage_path('app/resultado_correos_' . now()->format('Ymd_His') . '.xlsx');
    $writer = \Spatie\SimpleExcel\SimpleExcelWriter::create($ruta)
        ->addHeader(['Nombre Excel', 'Correo', 'Resultado', 'Detalle']);

    foreach (array_merge($actualizados, $noEncontrados, $errores) as $r) {
        $writer->addRow([
            $r['nombre'],
            $r['correo'],
            $r['resultado'],
            $r['detalle'],
        ]);
    }
    $writer->close();

    $totalAct = count($actualizados);
    $totalNoE = count($noEncontrados);
    $totalErr = count($errores);

    $nombre = "resultado_correos_{$totalAct}ok_{$totalNoE}noencontrados_{$totalErr}err.xlsx";

    return response()->download($ruta, $nombre)->deleteFileAfterSend();
}

public function importarCorreosVista()
{
    return view('funcionarios.importar_correos');
}
}