<?php

namespace App\Http\Controllers;

use App\Models\Funcionario;
use App\Models\Piso;
use App\Models\Puesto;
use App\Services\PuestoService;
use Illuminate\Http\Request;

class PuestoController extends Controller
{
    public function __construct(protected PuestoService $service) {}

public function store(Request $request, Piso $piso)
{
    $request->validate([
        'numero'      => 'required|string|max:20',
        'seccion'     => 'nullable|string|max:255',
        'observacion' => 'nullable|string',
        'x1'          => 'required|integer',
        'y1'          => 'required|integer',
        'x2'          => 'required|integer',
        'y2'          => 'required|integer',
    ]);

    $puesto = $piso->puestos()->create($request->only([
        'numero', 'seccion', 'observacion', 'x1', 'y1', 'x2', 'y2'
    ]));

    return response()->json([
        'ok'      => true,
        'id'      => $puesto->id,
        'mensaje' => "Puesto {$puesto->numero} creado.",
    ]);
}

public function update(Request $request, Puesto $puesto)
{
    $request->validate([
        'numero'      => 'required|string|max:20',
        'seccion'     => 'nullable|string|max:255',
        'observacion' => 'nullable|string',
        'x1'          => 'nullable|integer',
        'y1'          => 'nullable|integer',
        'x2'          => 'nullable|integer',
        'y2'          => 'nullable|integer',
    ]);

    $puesto->update($request->only([
        'numero', 'seccion', 'observacion', 'x1', 'y1', 'x2', 'y2'
    ]));

    return response()->json(['ok' => true, 'mensaje' => 'Puesto actualizado.']);
}



public function destroy(Puesto $puesto)
{
    $puesto->delete();

    return response()->json(['ok' => true, 'mensaje' => 'Puesto eliminado.']);
}

//     public function guardarPosicion(Request $request, Puesto $puesto)
//     {
//         $request->validate([
//             'tipo'        => 'required|in:punto,area',
//             'pos_x'       => 'nullable|numeric',
//             'pos_y'       => 'nullable|numeric',
//             'area_puntos' => 'nullable|array',
//         ]);

//         $this->service->guardarPosicion($puesto, $request->all());

//         return response()->json(['ok' => true]);
//     }

    public function asignar(Request $request, Puesto $puesto)
    {
        $request->validate([
            'funcionario_id' => 'required|exists:funcionarios,id',
            'observacion'    => 'nullable|string|max:500',
        ]);

        $funcionario = Funcionario::findOrFail($request->funcionario_id);
        $this->service->asignar($puesto, $funcionario, $request->observacion);

        return response()->json([
            'ok'      => true,
            'mensaje' => "Puesto {$puesto->numero} asignado a {$funcionario->nombre}.",
        ]);
    }

    public function desasignar(Puesto $puesto)
    {
        $this->service->desasignar($puesto);

        return response()->json(['ok' => true, 'mensaje' => 'Puesto desasignado correctamente.']);
    }

    public function historial(Puesto $puesto)
    {
        $historial = $puesto->historial()
            ->with(['funcionario', 'user'])
            ->get()
            ->map(fn ($h) => [
                'accion'      => $h->accion,
                'funcionario' => $h->funcionario->nombre ?? 'Eliminado',
                'usuario'     => $h->user->name ?? 'Sistema',
                'observacion' => $h->observacion,
                'fecha'       => $h->fecha->format('d-m-Y H:i'),
            ]);

        return response()->json([
            'puesto'   => [
                'numero'      => $puesto->numero,
                'seccion'     => $puesto->seccion ?? '—',
                'observacion' => $puesto->observacion ?? '—',
                'ocupado'     => $puesto->estaOcupado(),
                 'activo'      => $puesto->activo, 
                'funcionario' => $puesto->funcionario ? [
                    'id'     => $puesto->funcionario->id,
                    'nombre' => $puesto->funcionario->nombre,
                    'rut'    => $puesto->funcionario->rut_formateado,
                    'cargo'  => $puesto->funcionario->cargo,
                    'area'   => $puesto->funcionario->area,
                    'avatar' => $puesto->funcionario->avatar
                                    ? asset('storage/' . $puesto->funcionario->avatar)
                                    : null,
                ] : null,
            ],
            'historial' => $historial,
        ]);
    }

//     // API para buscar funcionarios sin puesto asignado
  public function funcionariosSinPuesto(Request $request)
{
    $funcionarios = Funcionario::with('puesto.piso')
        ->where(function ($q) use ($request) {
            $q->where('nombre', 'like', "%{$request->q}%")
              ->orWhere('rut', 'like', "%{$request->q}%");
        })
        ->limit(8)
        ->get()
        ->map(fn ($f) => [
            'id'           => $f->id,
            'nombre'       => $f->nombre,
            'rut'          => $f->rut_formateado,
            'cargo'        => $f->cargo,
            'area'         => $f->area,
            'tiene_puesto' => $f->puesto !== null,
            'puesto_actual'=> $f->puesto
                ? "Puesto {$f->puesto->numero} — {$f->puesto->piso->nombre}"
                : null,
        ]);

    return response()->json($funcionarios);
}

public function toggle(Puesto $puesto)
{
    $puesto->update(['activo' => ! $puesto->activo]);

    return response()->json([
        'ok'      => true,
        'activo'  => $puesto->activo,
        'mensaje' => $puesto->activo ? 'Puesto habilitado.' : 'Puesto deshabilitado.',
    ]);
}
}