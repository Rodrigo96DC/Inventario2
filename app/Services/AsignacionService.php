<?php

namespace App\Services;

use App\Models\Asignacion;
use App\Models\DetalleEquipo;
use App\Models\Funcionario;
use App\Models\HistorialAsignacion;
use Illuminate\Support\Facades\DB;

class AsignacionService
{
    /**
     * Asigna (o reasigna) un equipo a un funcionario.
     */
    public function asignar(DetalleEquipo $detalleEquipo, Funcionario $funcionario, array $datos = []): Asignacion
    {
        return DB::transaction(function () use ($detalleEquipo, $funcionario, $datos) {
            $actual = $detalleEquipo->asignacionActual;

            [$accion, $observacion] = $this->resolverAccion($actual, $funcionario, $datos['observacion'] ?? null);

            $asignacion = Asignacion::updateOrCreate(
                ['detalle_equipo_id' => $detalleEquipo->id],
                [
                    'funcionario_id' => $funcionario->id,
                    'hostname' => $datos['hostname'] ?? $actual?->hostname,
                    'fecha_asignacion' => $datos['fecha_asignacion'] ?? now()->toDateString(),
                    'observacion' => $observacion,
                ]
            );
            $asignacion->load(['detalleEquipo', 'funcionario']);

            $this->registrarHistorial($detalleEquipo, $funcionario, $accion, $asignacion->hostname, $observacion);

            return $asignacion;
        });
    }

    /**
     * Quita la asignación actual de un equipo (vuelve a bodega).
     */
    public function desasignar(DetalleEquipo $detalleEquipo, ?string $observacion = null): void
    {
        DB::transaction(function () use ($detalleEquipo, $observacion) {
            $actual = $detalleEquipo->asignacionActual;

            if (! $actual) {
                return;
            }

            $this->registrarHistorial(
                $detalleEquipo,
                $actual->funcionario,
                'desasignado',
                $actual->hostname,
                $observacion ?? $actual->observacion
            );

            $actual->delete();
        });
    }

    /**
     * Decide si es una asignación nueva, una reasignación, o solo una
     * actualización de datos (mismo funcionario), y arma la observación
     * automática para el historial.
     */
    protected function resolverAccion(?Asignacion $actual, Funcionario $funcionario, ?string $observacion): array
    {
        if (! $actual) {
            return ['asignado', $observacion];
        }

        if ($actual->funcionario_id !== $funcionario->id) {
            $anterior = $actual->funcionario?->nombre ?? 'un funcionario eliminado';
            $observacion ??= "Reasignado desde {$anterior}.";

            return ['reasignado', $observacion];
        }

        return ['actualizado', $observacion ?? $actual->observacion];
    }

    protected function registrarHistorial(DetalleEquipo $detalleEquipo, ?Funcionario $funcionario, string $accion, ?string $hostname, ?string $observacion): HistorialAsignacion
    {
        return HistorialAsignacion::create([
            'detalle_equipo_id' => $detalleEquipo->id,
            'funcionario_id' => $funcionario?->id,
            'user_id' => auth()->id(),
            'accion' => $accion,
            'hostname' => $hostname,
            'observacion' => $observacion,
            'fecha' => now(),
        ]);
    }
}