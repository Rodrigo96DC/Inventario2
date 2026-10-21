<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Funcionario;
use Illuminate\Http\Request;

class FuncionarioApiController extends Controller
{
    public function buscar(Request $request)
{
    $q = $request->input('q');

    $funcionarios = Funcionario::with('region')
        ->where(function($query) use ($q) {
            $query->where('nombre', 'like', "%{$q}%")
                  ->orWhere('rut', 'like', "%{$q}%");
        })
        ->limit(10)
        ->get()
        ->map(fn ($f) => [
            'id'     => $f->id,
            'nombre' => $f->nombre,
            'rut'    => $f->rut_formateado,
            'cargo'  => $f->cargo,
            'area'   => $f->area,
            'region' => $f->region?->nombre,
            'activo' => $f->activo,
            'avatar' => $f->avatar ? asset('storage/' . $f->avatar) : null,
        ])
        ->values(); // ← esto es clave

    return response()->json($funcionarios);
}

    public function equipos(Funcionario $funcionario)
    {
        $equipos = $funcionario->asignaciones()
            ->with(['detalleEquipo.equipo.tipoEquipo', 'detalleEquipo.estado', 'detalleEquipo.macs'])
            ->get()
            ->map(fn ($a) => [
                'id'         => $a->detalleEquipo->id,
                'inventario' => $a->detalleEquipo->inventario,
                'tipo'       => $a->detalleEquipo->equipo?->tipoEquipo?->nombre,
                'marca'      => $a->detalleEquipo->equipo?->marca,
                'modelo'     => $a->detalleEquipo->equipo?->modelo,
                'estado'     => $a->detalleEquipo->estado?->nombre,
                'color'      => $a->detalleEquipo->estado?->color,
                'hostname'   => $a->hostname,
                'fecha'      => $a->fecha_asignacion?->format('d-m-Y'),
                'imagen'     => $a->detalleEquipo->equipo?->imagen
                                    ? asset('storage/' . $a->detalleEquipo->equipo->imagen)
                                    : null,
            ]);

        return response()->json([
            'funcionario' => [
                'id'     => $funcionario->id,
                'nombre' => $funcionario->nombre,
                'rut'    => $funcionario->rut_formateado,
                'cargo'  => $funcionario->cargo,
                'area'   => $funcionario->area,
            ],
            'equipos' => $equipos,
        ]);
    }
}