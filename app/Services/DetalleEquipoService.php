<?php

namespace App\Services;

use App\Models\DetalleEquipo;
use App\Models\DetalleEquipoMac;
use Illuminate\Support\Facades\DB;

class DetalleEquipoService
{
    public function crear(array $datos): DetalleEquipo
    {
        return DB::transaction(function () use ($datos) {
            $detalleEquipo = DetalleEquipo::create($this->datosBase($datos));
            $this->sincronizarMacs($detalleEquipo, $datos);
            return $detalleEquipo;
        });
    }

    public function actualizar(DetalleEquipo $detalleEquipo, array $datos): DetalleEquipo
    {
        return DB::transaction(function () use ($detalleEquipo, $datos) {
            $detalleEquipo->update($this->datosBase($datos));
            $this->sincronizarMacs($detalleEquipo, $datos);
            return $detalleEquipo;
        });
    }

    protected function datosBase(array $datos): array
    {
        return [
            'equipo_id'          => $datos['equipo_id'],
            'estado_id'          => $datos['estado_id'],
            'tipo_disco_id'      => $datos['tipo_disco_id'] ?? null,
            'inventario'         => $datos['inventario'],
            'serial'             => strtoupper($datos['serial']),
            'ram_gb'             => $datos['ram_gb'] ?? null,
            'capacidad_disco_gb' => $datos['capacidad_disco_gb'] ?? null,
            'ip'                 => $datos['ip'] ?? null,
            'fecha_adquisicion'  => $datos['fecha_adquisicion'] ?? null,
            'puerto_switch'      => $datos['puerto_switch'] ?? null,
            'teletrabajo'        => $datos['teletrabajo'] ?? false,
            'es_prestamo'        => $datos['es_prestamo'] ?? false,
        ];
    }

    protected function sincronizarMacs(DetalleEquipo $detalleEquipo, array $datos): void
    {
        $detalleEquipo->macs()->delete();

        if (! empty($datos['mac_ethernet'])) {
            $detalleEquipo->macs()->create([
                'mac'      => strtoupper($datos['mac_ethernet']),
                'interfaz' => 'Ethernet',
            ]);
        }

        if (! empty($datos['mac_wifi'])) {
            $detalleEquipo->macs()->create([
                'mac'      => strtoupper($datos['mac_wifi']),
                'interfaz' => 'WiFi',
            ]);
        }
    }
}