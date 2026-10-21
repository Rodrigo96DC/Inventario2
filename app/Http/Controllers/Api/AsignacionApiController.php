<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DetalleEquipo;
use App\Models\Funcionario;
use App\Services\AsignacionService;
use Illuminate\Http\Request;

class AsignacionApiController extends Controller
{
    public function __construct(protected AsignacionService $service) {}

    public function asignar(Request $request)
    {
        $request->validate([
            'detalle_equipo_id' => 'required|exists:detalle_equipos,id',
            'funcionario_id'    => 'required|exists:funcionarios,id',
            'hostname'          => 'nullable|string|max:100',
            'observacion'       => 'nullable|string|max:500',
        ]);

        $detalle     = DetalleEquipo::findOrFail($request->detalle_equipo_id);
        $funcionario = Funcionario::findOrFail($request->funcionario_id);

        $asignacion = $this->service->asignar($detalle, $funcionario, [
            'hostname'         => $request->hostname,
            'observacion'      => $request->observacion,
            'fecha_asignacion' => now()->toDateString(),
        ]);

        return response()->json([
            'ok'      => true,
            'mensaje' => "Equipo {$detalle->inventario} asignado a {$funcionario->nombre}.",
        ]);
    }

    public function desasignar(Request $request, DetalleEquipo $detalle)
    {
        $this->service->desasignar($detalle, $request->input('observacion'));

        return response()->json([
            'ok'      => true,
            'mensaje' => "Equipo {$detalle->inventario} desasignado correctamente.",
        ]);
    }
}