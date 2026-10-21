<?php

namespace App\Http\Controllers;

use App\Models\Lista;
use App\Models\ListaItem;
use App\Models\DetalleEquipo;
use App\Models\Funcionario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\SimpleExcel\SimpleExcelReader;
use Spatie\SimpleExcel\SimpleExcelWriter;

class ListaController extends Controller
{
    // ── Index ─────────────────────────────────────────────────────────────────
    public function index()
    {
        $listas = Lista::with(['user', 'items'])
            ->where(function ($q) {
                $q->where('user_id', Auth::id())
                    ->orWhere('publica', true);
            })
            ->whereIn('estado', ['en_proceso'])
            ->orderByDesc('created_at')
            ->paginate(15, ['*'], 'activas');

        $listasCompletadas = Lista::with(['user', 'items'])
            ->where(function ($q) {
                $q->where('user_id', Auth::id())
                    ->orWhere('publica', true);
            })
            ->whereIn('estado', ['realizada', 'cancelada'])
            ->orderByDesc('created_at')
            ->paginate(15, ['*'], 'completadas');

        $listasEliminadas = Lista::onlyTrashed()
            ->with(['user', 'items'])
            ->where('user_id', Auth::id())
            ->orderByDesc('deleted_at')
            ->paginate(15, ['*'], 'eliminadas');

        return view('listas.index', compact('listas', 'listasCompletadas', 'listasEliminadas'));
    }

    // ── Store ─────────────────────────────────────────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'nombre'      => 'required|string|max:255',
            'tipo'        => 'required|in:equipos,funcionarios',
            'estado'      => 'required|in:en_proceso,cancelada,realizada',
            'publica'     => 'boolean',
            'auto_baja'   => 'boolean',
            'descripcion' => 'nullable|string|max:1000',
            'archivo'     => 'nullable|file|mimes:xlsx,xls,csv|max:5120',
        ]);

        $nombre = $request->boolean('auto_baja')
            ? 'Equipos de baja ' . now()->format('d-m-Y')
            : $request->nombre;

        // Verificar si ya existe una lista con ese nombre y agregar número
        $nombreBase = $nombre;
        $contador   = 2;
        while (Lista::where('user_id', Auth::id())->where('nombre', $nombre)->exists()) {
            $nombre = $nombreBase . ' ' . $contador;
            $contador++;
        }

        $lista = Lista::create([
            'user_id'     => Auth::id(),
            'nombre'      => $nombre,
            'tipo'        => $request->tipo,
            'estado'      => $request->estado,
            'publica'     => $request->boolean('publica'),
            'auto_baja'   => $request->boolean('auto_baja'),
            'descripcion' => $request->descripcion,
        ]);

        // Procesar Excel si viene
        if ($request->hasFile('archivo') && $request->boolean('auto_baja')) {
            $extension    = $request->file('archivo')->getClientOriginalExtension();
            $rutaTemporal = storage_path('app/temp_baja_store.' . $extension);
            copy($request->file('archivo')->getRealPath(), $rutaTemporal);

            $encontrados   = 0;
            $noEncontrados = [];

            \Spatie\SimpleExcel\SimpleExcelReader::create($rutaTemporal)
                ->getRows()
                ->each(function (array $fila) use ($lista, &$encontrados, &$noEncontrados) {
                    $inventario = trim($fila['inventario'] ?? $fila['INVENTARIO'] ?? array_values($fila)[0] ?? '');
                    if (empty($inventario)) return;

                    $equipo = DetalleEquipo::where('inventario', $inventario)->first();
                    if (!$equipo) {
                        $noEncontrados[] = $inventario;
                        return;
                    }

                    $lista->items()->create([
                        'itemable_type' => DetalleEquipo::class,
                        'itemable_id'   => $equipo->id,
                        'estado'        => 'pendiente',
                        'detalle'       => null,
                    ]);
                    $encontrados++;
                });

            @unlink($rutaTemporal);

            $msg = "{$encontrados} equipo(s) agregados a la lista.";
            if (count($noEncontrados) > 0) {
                $msg .= ' No encontrados: ' . implode(', ', $noEncontrados);
            }

            return redirect()->route('lista.show', $lista)->with('info', $msg);
        }

        return redirect()->route('lista.show', $lista)->with('info', 'Lista creada correctamente.');
    }

    // ── Show ──────────────────────────────────────────────────────────────────
   public function show(Lista $lista)
{
    if ($lista->user_id !== Auth::id() && !$lista->publica) {
        abort(403, 'No tienes permiso para ver esta lista.');
    }

    $lista->load('user');

 $items = $lista->items()
    ->with([
        'itemable' => fn($q) => $q->withTrashed(),
        'itemable.equipo.tipoEquipo',
        'itemable.estado',
        'itemable.asignacionActual.funcionario',
    ])
    ->where('estado', '!=', 'completado')
    ->paginate(15)
    ->withQueryString();

    $itemsCompletados = $lista->items()
        ->with([
            'itemable' => fn($q) => $q->withTrashed(),
            'itemable.equipo.tipoEquipo',
            'itemable.estado',
        ])
        ->where('estado', 'completado')
        ->paginate(15, ['*'], 'completados')
        ->withQueryString();

    return view('listas.show', compact('lista', 'items', 'itemsCompletados'));
}

    // ── Update ────────────────────────────────────────────────────────────────
    public function update(Request $request, Lista $lista)
    {
        $this->autorizar($lista);

        $request->validate([
            'nombre'      => 'required|string|max:255',
            'estado'      => 'required|in:en_proceso,cancelada,realizada',
            'publica'     => 'boolean',
            'descripcion' => 'nullable|string|max:1000',
        ]);

        $lista->update([
            'nombre'      => $request->nombre,
            'estado'      => $request->estado,
            'publica'     => $request->boolean('publica'),
            'descripcion' => $request->descripcion,
        ]);

        return redirect()->route('lista.show', $lista)->with('info', 'Lista actualizada.');
    }

    // ── Destroy ───────────────────────────────────────────────────────────────
    public function destroy(Lista $lista)
    {
        $this->autorizar($lista);
        $lista->delete();
        return redirect()->route('lista.index')->with('info', 'Lista eliminada.');
    }

    // ── Agregar item ──────────────────────────────────────────────────────────
    public function agregarItem(Request $request, Lista $lista)
    {
        $this->autorizar($lista);

        $request->validate([
            'itemable_id'   => 'required|integer',
            'estado'        => 'required|in:pendiente,en_proceso,completado',
            'detalle'       => 'nullable|string|max:1000',
        ]);

        $modelo = $lista->tipo === 'equipos'
            ? DetalleEquipo::findOrFail($request->itemable_id)
            : Funcionario::findOrFail($request->itemable_id);

        $lista->items()->create([
            'itemable_type' => get_class($modelo),
            'itemable_id'   => $modelo->id,
            'estado'        => $request->estado,
            'detalle'       => $request->detalle,
        ]);

        return redirect()->route('lista.show', $lista)->with('info', 'Elemento agregado.');
    }

    // ── Actualizar item ───────────────────────────────────────────────────────
    public function updateItem(Request $request, ListaItem $item)
    {
        $request->validate([
            'estado'  => 'required|in:pendiente,en_proceso,completado',
            'detalle' => 'nullable|string|max:1000',
        ]);

        $item->update([
            'estado'  => $request->estado,
            'detalle' => $request->detalle,
        ]);

        return redirect()->route('lista.show', $item->lista_id)->with('info', 'Elemento actualizado.');
    }

    // ── Eliminar item ─────────────────────────────────────────────────────────
    public function destroyItem(ListaItem $item)
    {
        $listaId = $item->lista_id;
        $item->delete();
        return redirect()->route('lista.show', $listaId)->with('info', 'Elemento eliminado.');
    }

    // ── Importar baja desde Excel ─────────────────────────────────────────────
    public function importarBaja(Request $request)
    {
        $request->validate([
            'archivo' => 'required|file|mimes:xlsx,xls,csv|max:5120',
        ]);

        $extension    = $request->file('archivo')->getClientOriginalExtension();
        $rutaTemporal = storage_path('app/temp_baja.' . $extension);
        copy($request->file('archivo')->getRealPath(), $rutaTemporal);

        // Crear lista automática
        $lista = Lista::create([
            'user_id'     => Auth::id(),
            'nombre'      => 'Equipos de baja ' . now()->format('d-m-Y'),
            'tipo'        => 'equipos',
            'estado'      => 'en_proceso',
            'publica'     => false,
            'auto_baja'   => true,
            'descripcion' => 'Lista generada automáticamente desde importación de baja.',
        ]);

        $encontrados   = 0;
        $noEncontrados = [];

        SimpleExcelReader::create($rutaTemporal)
            ->getRows()
            ->each(function (array $fila) use ($lista, &$encontrados, &$noEncontrados) {
                $inventario = trim($fila['inventario'] ?? $fila['INVENTARIO'] ?? array_values($fila)[0] ?? '');
                if (empty($inventario)) return;

                $equipo = DetalleEquipo::where('inventario', $inventario)->first();

                if (!$equipo) {
                    $noEncontrados[] = $inventario;
                    return;
                }

                $lista->items()->create([
                    'itemable_type' => DetalleEquipo::class,
                    'itemable_id'   => $equipo->id,
                    'estado'        => 'pendiente',
                    'detalle'       => null,
                ]);
                $encontrados++;
            });

        @unlink($rutaTemporal);

        return redirect()->route('lista.show', $lista)
            ->with('info', "{$encontrados} equipo(s) agregados a la lista. " .
                (count($noEncontrados) > 0
                    ? count($noEncontrados) . ' no encontrados: ' . implode(', ', $noEncontrados)
                    : ''));
    }

    // ── Enviar a papelera ─────────────────────────────────────────────────────
    public function enviarPapelera(Request $request, Lista $lista)
    {
        $this->autorizar($lista);

        $ids             = $request->input('items', []);
        $detalle         = $request->input('detalle');
        $opcionDocumento = $request->input('opcion_documento', 'sin_documento');
        $enviados        = 0;
        $equiposIds      = [];

        ListaItem::whereIn('id', $ids)
            ->where('lista_id', $lista->id)
            ->each(function ($item) use ($detalle, &$enviados, &$equiposIds, $lista) {
                \Log::info('procesando item', ['id' => $item->id, 'itemable_id' => $item->itemable_id]);
                $itemable = $item->itemable;
                if (!$itemable) return;

                if ($lista->tipo === 'equipos') {
                    // Cambiar estado a "De Baja" antes de enviar a papelera
                    $itemable->update(['estado_id' => 4]);

                    // Desasignar si tiene asignación activa
                    if ($itemable->asignacionActual) {
                        $itemable->asignacionActual->delete();
                    }

                    // Mover a papelera
                    $itemable->delete();
                    $equiposIds[] = $itemable->id;
                } elseif ($lista->tipo === 'funcionarios') {
                    // Desactivar funcionario
                    $itemable->update(['activo' => false]);
                }
$itemable->forceFill(['detalle_baja' => $detalle])->save();
                // Actualizar item
                $item->update([
                    'estado'  => 'completado',
                    'detalle' => $detalle ?: $item->detalle,
                ]);

                $enviados++;
            });
            

        if ($opcionDocumento === 'unida' && count($equiposIds) > 0) {
            return redirect()->route('lista.show', $lista)
                ->with('info', "{$enviados} equipo(s) dados de baja.")
                ->with('descargar_baja_unida', $equiposIds);
        }

        return redirect()->route('lista.show', $lista)
            ->with('info', "{$enviados} elemento(s) procesados correctamente.");
    }

    // ── Helper autorización ───────────────────────────────────────────────────
    private function autorizar(Lista $lista)
    {
        if ($lista->user_id !== Auth::id()) {
            abort(403, 'No tienes permiso para modificar esta lista.');
        }
    }

    public function buscarEquipos(Request $request)
    {
        return response()->json(
            \App\Models\DetalleEquipo::with(['equipo'])
                ->where('inventario', 'like', "%{$request->q}%")
                ->orWhereHas(
                    'equipo',
                    fn($q) =>
                    $q->where('marca', 'like', "%{$request->q}%")
                        ->orWhere('modelo', 'like', "%{$request->q}%")
                )
                ->limit(8)
                ->get()
                ->map(fn($e) => [
                    'id'        => $e->id,
                    'inventario' => $e->inventario,
                    'marca'     => $e->equipo?->marca,
                    'modelo'    => $e->equipo?->modelo,
                ])
        );
    }


    public function restore($id)
    {
        $lista = Lista::onlyTrashed()->findOrFail($id);
        if ($lista->user_id !== Auth::id()) abort(403);
        $lista->restore();
        return redirect()->route('lista.index')->with('info', 'Lista restaurada correctamente.');
    }

public function exportar(Lista $lista)
{
    $this->autorizar($lista);

    $items = $lista->items()
        ->with([
            'itemable' => fn($q) => $q->withTrashed(),
            'itemable.equipo.tipoEquipo',
            'itemable.estado',
        ])
        ->get();

    $ruta   = storage_path('app/lista_' . $lista->id . '_' . now()->format('Ymd_His') . '.xlsx');
    $writer = SimpleExcelWriter::create($ruta);

    if ($lista->tipo === 'equipos') {
        $writer->addHeader([
            'Inventario', 'Serial', 'Tipo', 'Marca', 'Modelo',
            'Estado Equipo', 'Estado Lista', 'Detalle', 'Fecha baja'
        ]);
        $items->each(function($item) use ($writer) {
            $eq = $item->itemable;
            $writer->addRow([
                $eq?->inventario ?? '—',
                $eq?->serial ?? '—',
                $eq?->equipo?->tipoEquipo?->nombre ?? '—',
                $eq?->equipo?->marca ?? '—',
                $eq?->equipo?->modelo ?? '—',
                $eq?->estado?->nombre ?? 'En papelera',
                ucfirst(str_replace('_', ' ', $item->estado)),
                $item->detalle ?? '—',
                $eq?->deleted_at?->format('d-m-Y H:i') ?? '—',
            ]);
        });
    } else {
        $writer->addHeader([
            'Nombre', 'RUT', 'División', 'Departamento', 'Cargo',
            'Estado Funcionario', 'Estado Lista', 'Detalle'
        ]);
        $items->each(function($item) use ($writer) {
            $f = $item->itemable;
            $writer->addRow([
                $f?->nombre ?? '—',
                $f?->rut_formateado ?? '—',
                $f?->division?->nombre ?? '—',
                $f?->departamento?->nombre ?? '—',
                $f?->cargo ?? '—',
                $f?->activo ? 'Activo' : 'Inactivo',
                ucfirst(str_replace('_', ' ', $item->estado)),
                $item->detalle ?? '—',
            ]);
        });
    }

    $writer->close();
    $nombre = 'Lista_' . str_replace(' ', '_', $lista->nombre) . '_' . now()->format('Ymd') . '.xlsx';
    return response()->download($ruta, $nombre)->deleteFileAfterSend();
}


public function importarItems(Request $request, Lista $lista)
{
    $this->autorizar($lista);
    $request->validate(['archivo' => 'required|file|mimes:xlsx,xls,csv|max:5120']);

    $extension    = $request->file('archivo')->getClientOriginalExtension();
    $rutaTemporal = storage_path('app/temp_import_lista_items.' . $extension);
    copy($request->file('archivo')->getRealPath(), $rutaTemporal);

    $resultados  = [];
    $insertados  = 0;
    $duplicados  = 0;
    $noEncontrados = 0;

    \Spatie\SimpleExcel\SimpleExcelReader::create($rutaTemporal)
        ->getRows()
        ->each(function (array $fila) use ($lista, &$resultados, &$insertados, &$duplicados, &$noEncontrados) {
            $inventario = trim($fila['inventario'] ?? $fila['INVENTARIO'] ?? '');
            $serial     = trim($fila['serial'] ?? $fila['SERIAL'] ?? '');

            if (empty($inventario) && empty($serial)) return;

            $equipo = DetalleEquipo::when($inventario, fn($q) => $q->where('inventario', $inventario))
                ->when(!$inventario && $serial, fn($q) => $q->where('serial', $serial))
                ->first();

            if (!$equipo) {
                $noEncontrados++;
                $resultados[] = [
                    'inventario' => $inventario ?: $serial,
                    'serial'     => '—',
                    'tipo'       => '—',
                    'marca'      => '—',
                    'modelo'     => '—',
                    'resultado'  => 'No encontrado',
                    'detalle'    => 'Equipo no encontrado en el inventario.',
                ];
                return;
            }

            // Verificar si ya está en la lista
            $existe = $lista->items()
                ->where('itemable_type', DetalleEquipo::class)
                ->where('itemable_id', $equipo->id)
                ->exists();

            if ($existe) {
                $duplicados++;
                $resultados[] = [
                    'inventario' => $equipo->inventario,
                    'serial'     => $equipo->serial ?? '—',
                    'tipo'       => $equipo->equipo?->tipoEquipo?->nombre ?? '—',
                    'marca'      => $equipo->equipo?->marca ?? '—',
                    'modelo'     => $equipo->equipo?->modelo ?? '—',
                    'resultado'  => 'Duplicado',
                    'detalle'    => 'El equipo ya está en esta lista.',
                ];
                return;
            }

            $lista->items()->create([
                'itemable_type' => DetalleEquipo::class,
                'itemable_id'   => $equipo->id,
                'estado'        => 'pendiente',
                'detalle'       => null,
            ]);

            $insertados++;
            $resultados[] = [
                'inventario' => $equipo->inventario,
                'serial'     => $equipo->serial ?? '—',
                'tipo'       => $equipo->equipo?->tipoEquipo?->nombre ?? '—',
                'marca'      => $equipo->equipo?->marca ?? '—',
                'modelo'     => $equipo->equipo?->modelo ?? '—',
                'resultado'  => 'Agregado',
                'detalle'    => 'Equipo agregado a la lista correctamente.',
            ];
        });

    @unlink($rutaTemporal);

    // Generar Excel resultado
    $ruta   = storage_path('app/resultado_lista_items_' . now()->format('Ymd_His') . '.xlsx');
    $writer = \Spatie\SimpleExcel\SimpleExcelWriter::create($ruta)
        ->addHeader(['Inventario', 'Serial', 'Tipo', 'Marca', 'Modelo', 'Resultado', 'Detalle']);

    foreach ($resultados as $r) {
        $writer->addRow([
            $r['inventario'], $r['serial'], $r['tipo'],
            $r['marca'], $r['modelo'], $r['resultado'], $r['detalle'],
        ]);
    }
    $writer->close();

    $nombre = "lista_items_{$insertados}agregados_{$duplicados}dup_{$noEncontrados}noencontrados.xlsx";

    return response()->download($ruta, $nombre)->deleteFileAfterSend();
}
}
