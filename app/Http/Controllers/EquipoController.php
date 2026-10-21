<?php

namespace App\Http\Controllers;

use App\Http\Requests\Equipo\EquipoRequest;
use App\Models\Equipo;
use App\Models\TipoEquipo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Spatie\SimpleExcel\SimpleExcelWriter;
use Spatie\SimpleExcel\SimpleExcelReader;


class EquipoController extends Controller
{
    public function index(Request $request)
    {
        $buscar = $request->input('buscar');

        $equipos = Equipo::with('tipoEquipo')
            ->when($buscar, function ($query, $buscar) {
                $query->where(function ($q) use ($buscar) {
                    $q->where('marca', 'like', "%{$buscar}%")
                      ->orWhere('modelo', 'like', "%{$buscar}%")
                      ->orWhereHas('tipoEquipo', fn ($q2) => $q2->where('nombre', 'like', "%{$buscar}%"));
                });
            })
            ->orderBy('marca')
            ->orderBy('modelo')
            ->paginate(12)
            ->withQueryString();

        return view('equipos.index', compact('equipos', 'buscar'));
    }

    public function create()
    {
        return view('equipos.create', [
            'equipo' => new Equipo(),
            'tipoEquipos' => TipoEquipo::orderBy('nombre')->get(),
        ]);
    }

    public function store(EquipoRequest $request)
    {
        $data = $request->validated();
        $data['imagen'] = $request->file('imagen')->store('equipos', 'public');

        Equipo::create($data);

        return redirect()->route('equipo.index')->with('info', 'Equipo registrado correctamente.');
    }

    public function edit(Equipo $equipo)
    {
        return view('equipos.edit', [
            'equipo' => $equipo,
            'tipoEquipos' => TipoEquipo::orderBy('nombre')->get(),
        ]);
    }

   public function update(EquipoRequest $request, Equipo $equipo)
{
    $data = $request->validated();

    if ($request->hasFile('imagen')) {
        if ($equipo->imagen) {
            Storage::disk('public')->delete($equipo->imagen);
        }
        $data['imagen'] = $request->file('imagen')->store('equipos', 'public');
    } else {
        unset($data['imagen']);
    }

    $equipo->update($data);

    return redirect()->route('equipo.index')->with('info', 'Equipo actualizado correctamente.');
}

    public function destroy(Equipo $equipo)
    {
        $equipo->delete();

        return redirect()->route('equipo.index')->with('info', 'Equipo movido a la papelera.');
    }

    public function papelera()
    {
        $equipos = Equipo::onlyTrashed()->with('tipoEquipo')->orderBy('marca')->paginate(15);

        return view('equipos.papelera', compact('equipos'));
    }

    public function restore(int $id)
    {
        $equipo = Equipo::onlyTrashed()->findOrFail($id);
        $equipo->restore();

        return redirect()->route('equipo.papelera')->with('info', 'Equipo restaurado correctamente.');
    }

    public function forceDelete(int $id)
    {
        $equipo = Equipo::onlyTrashed()->findOrFail($id);

        Storage::disk('public')->delete($equipo->imagen);
        $equipo->forceDelete();

        return redirect()->route('equipo.papelera')->with('info', 'Equipo eliminado permanentemente.');
    }


public function exportar()
{
    $ruta = storage_path('app/equipos_' . now()->format('Ymd_His') . '.xlsx');

    $writer = SimpleExcelWriter::create($ruta)
        ->addHeader(['ID', 'Tipo', 'Marca', 'Modelo', 'Procesador', 'Entradas', 'Observación', 'Registrado el']);

    Equipo::with('tipoEquipo')->orderBy('marca')->orderBy('modelo')->each(function ($equipo) use ($writer) {
        $writer->addRow([
            $equipo->id,
            $equipo->tipoEquipo->nombre ?? '—',
            $equipo->marca,
            $equipo->modelo,
            $equipo->procesador ?? '—',
            $equipo->entradas ?? '—',
            $equipo->observacion ?? '—',
            $equipo->created_at->format('d-m-Y'),
        ]);
    });

    $writer->close();

    return response()->download($ruta, 'equipos_' . now()->format('Ymd_His') . '.xlsx')->deleteFileAfterSend();
}

public function importarVista()
{
    return view('equipos.importar');
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

            // Validación campos obligatorios
            if (empty($fila['marca']) || empty($fila['modelo']) || empty($fila['tipo'])) {
                $resultados[] = [
                    'fila'      => $numero,
                    'tipo'      => $fila['tipo'] ?? '—',
                    'marca'     => $fila['marca'] ?? '—',
                    'modelo'    => $fila['modelo'] ?? '—',
                    'resultado' => 'Error',
                    'detalle'   => 'Marca, modelo y tipo son obligatorios.',
                ];
                return;
            }

            $tipo = TipoEquipo::whereRaw('LOWER(nombre) = ?', [strtolower(trim($fila['tipo']))])->first();

            if (! $tipo) {
                $resultados[] = [
                    'fila'      => $numero,
                    'tipo'      => $fila['tipo'],
                    'marca'     => $fila['marca'],
                    'modelo'    => $fila['modelo'],
                    'resultado' => 'Error',
                    'detalle'   => "El tipo '{$fila['tipo']}' no existe en el sistema.",
                ];
                return;
            }

            $existe = Equipo::where('marca', trim($fila['marca']))
                ->where('modelo', trim($fila['modelo']))
                ->exists();

            if ($existe) {
                $resultados[] = [
                    'fila'      => $numero,
                    'tipo'      => $fila['tipo'],
                    'marca'     => $fila['marca'],
                    'modelo'    => $fila['modelo'],
                    'resultado' => 'Omitido',
                    'detalle'   => 'Ya existe un equipo con esa marca y modelo.',
                ];
                return;
            }

            Equipo::create([
                'tipo_equipo_id' => $tipo->id,
                'marca'          => trim($fila['marca']),
                'modelo'         => trim($fila['modelo']),
                'procesador'     => trim($fila['procesador'] ?? '') ?: null,
                'entradas'       => trim($fila['entradas'] ?? '') ?: null,
                'observacion'    => trim($fila['observacion'] ?? '') ?: null,
            ]);

            $resultados[] = [
                'fila'      => $numero,
                'tipo'      => $fila['tipo'],
                'marca'     => $fila['marca'],
                'modelo'    => $fila['modelo'],
                'resultado' => 'Importado',
                'detalle'   => 'Registrado correctamente.',
            ];
        });

    // Generar Excel de resultados
    $ruta = storage_path('app/resultado_importacion_' . now()->format('Ymd_His') . '.xlsx');

    $writer = SimpleExcelWriter::create($ruta)
        ->addHeader(['Fila', 'Tipo', 'Marca', 'Modelo', 'Resultado', 'Detalle']);

    foreach ($resultados as $r) {
        $writer->addRow([
            $r['fila'],
            $r['tipo'],
            $r['marca'],
            $r['modelo'],
            $r['resultado'],
            $r['detalle'],
        ]);
    }

    $writer->close();

    $importados = collect($resultados)->where('resultado', 'Importado')->count();
    $errores    = collect($resultados)->where('resultado', 'Error')->count();
    $omitidos   = collect($resultados)->where('resultado', 'Omitido')->count();

    $nombre = "resultado_importacion_{$importados}ok_{$errores}err_{$omitidos}omit.xlsx";

    return response()->download($ruta, $nombre)->deleteFileAfterSend();
}

public function plantilla()
{
    $ruta = storage_path('app/plantilla_equipos.xlsx');

    SimpleExcelWriter::create($ruta)
        ->addHeader(['tipo', 'marca', 'modelo', 'procesador', 'entradas', 'observacion'])
        ->close();

    return response()->download($ruta, 'plantilla_equipos.xlsx')->deleteFileAfterSend();
}
}