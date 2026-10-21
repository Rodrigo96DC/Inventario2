<?php

namespace App\Services;

use App\Models\Funcionario;
use App\Models\Puesto;
use Illuminate\Support\Facades\DB;

class PuestoService
{
    public function asignar(Puesto $puesto, Funcionario $funcionario, ?string $observacion = null): void
    {
        DB::transaction(function () use ($puesto, $funcionario, $observacion) {
            // Si el funcionario ya tiene un puesto, desasignarlo primero
            $puestoAnterior = Puesto::where('funcionario_id', $funcionario->id)
                ->where('id', '!=', $puesto->id)
                ->first();

            if ($puestoAnterior) {
                $this->desasignar($puestoAnterior, "Reasignado al puesto {$puesto->numero}.");
            }

            // Si el puesto ya tiene un funcionario, desasignarlo
            if ($puesto->funcionario_id && $puesto->funcionario_id !== $funcionario->id) {
                $this->desasignar($puesto, "Puesto reasignado a {$funcionario->nombre}.");
            }

            $puesto->update(['funcionario_id' => $funcionario->id]);

            $puesto->historial()->create([
                'funcionario_id' => $funcionario->id,
                'user_id'        => auth()->id(),
                'accion'         => 'asignado',
                'observacion'    => $observacion,
                'fecha'          => now(),
            ]);
        });
    }

    public function desasignar(Puesto $puesto, ?string $observacion = null): void
    {
        DB::transaction(function () use ($puesto, $observacion) {
            $puesto->historial()->create([
                'funcionario_id' => $puesto->funcionario_id,
                'user_id'        => auth()->id(),
                'accion'         => 'desasignado',
                'observacion'    => $observacion,
                'fecha'          => now(),
            ]);

            $puesto->update(['funcionario_id' => null]);
        });
    }

    public function guardarPosicion(Puesto $puesto, array $datos): void
    {
        $puesto->update([
            'tipo'        => $datos['tipo'],
            'pos_x'       => $datos['pos_x'] ?? null,
            'pos_y'       => $datos['pos_y'] ?? null,
            'area_puntos' => $datos['area_puntos'] ?? null,
        ]);
    }
}