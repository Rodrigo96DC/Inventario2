<?php

namespace App\Http\Controllers;

use App\Models\Auditoria;
use Illuminate\Http\Request;
use Spatie\SimpleExcel\SimpleExcelWriter;

class AuditoriaController extends Controller
{
   public function index(Request $request)
{
    $modulos = [
        'App\Models\Equipo'        => 'Equipos',
        'App\Models\Funcionario'   => 'Funcionarios',
        'App\Models\DetalleEquipo' => 'Inventario',
        'App\Models\Insumo'        => 'Insumos',
    ];

    $acciones = ['creado', 'actualizado', 'eliminado', 'eliminado_permanente', 'restaurado'];

    $auditorias = Auditoria::with('user')
        ->when($request->modulo, fn ($q) => $q->where('auditable_type', $request->modulo))
        ->when($request->accion, fn ($q) => $q->where('accion', $request->accion))
        ->when($request->buscar, fn ($q) => $q->where('descripcion', 'like', "%{$request->buscar}%"))
        ->when($request->desde, fn ($q) => $q->whereDate('created_at', '>=', $request->desde))
        ->when($request->hasta, fn ($q) => $q->whereDate('created_at', '<=', $request->hasta))
        ->latest()
        ->paginate(25)
        ->withQueryString();

    // Si es petición AJAX devuelve solo el partial de la tabla
    if ($request->ajax() || $request->has('ajax')) {
        return view('auditorias._tabla', compact('auditorias'));
    }

    return view('auditorias.index', compact('auditorias', 'modulos', 'acciones'));
}




public function exportar(Request $request)
{
    $ruta = storage_path('app/auditoria_' . now()->format('Ymd_His') . '.xlsx');

    $writer = SimpleExcelWriter::create($ruta)
        ->addHeader(['Fecha', 'Módulo', 'Registro', 'Acción', 'Usuario', 'Campo', 'Antes', 'Después']);

    Auditoria::with('user')
        ->when($request->modulo, fn ($q) => $q->where('auditable_type', $request->modulo))
        ->when($request->accion, fn ($q) => $q->where('accion', $request->accion))
        ->when($request->buscar, fn ($q) => $q->where('descripcion', 'like', "%{$request->buscar}%"))
        ->when($request->desde, fn ($q) => $q->whereDate('created_at', '>=', $request->desde))
        ->when($request->hasta, fn ($q) => $q->whereDate('created_at', '<=', $request->hasta))
        ->latest()
        ->each(function ($auditoria) use ($writer) {
            if ($auditoria->cambios) {
                // Una fila por cada campo modificado
                foreach ($auditoria->cambios['despues'] as $campo => $despues) {
                    $writer->addRow([
                        $auditoria->created_at->format('d-m-Y H:i'),
                        $auditoria->nombre_modulo,
                        $auditoria->descripcion ?? '—',
                        ucfirst(str_replace('_', ' ', $auditoria->accion)),
                        $auditoria->user->name ?? 'Sistema',
                        $campo,
                        $auditoria->cambios['antes'][$campo] ?? '—',
                        $despues,
                    ]);
                }
            } else {
                // Sin cambios detallados (creado, eliminado, restaurado)
                $writer->addRow([
                    $auditoria->created_at->format('d-m-Y H:i'),
                    $auditoria->nombre_modulo,
                    $auditoria->descripcion ?? '—',
                    ucfirst(str_replace('_', ' ', $auditoria->accion)),
                    $auditoria->user->name ?? 'Sistema',
                    '—', '—', '—',
                ]);
            }
        });

    $writer->close();

    return response()->download($ruta, 'auditoria_' . now()->format('Ymd_His') . '.xlsx')->deleteFileAfterSend();
}
}