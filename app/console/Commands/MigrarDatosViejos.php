<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Region;
use App\Models\TipoEquipo;
use App\Models\Equipo;
use App\Models\DetalleEquipo;
use App\Models\Funcionario;
use App\Models\EstadoEquipo;
use App\Models\TipoDisco;
use App\Models\DetalleEquipoMac;
use App\Models\HistorialAsignacion;


class MigrarDatosViejos extends Command
{
    protected $signature   = 'migrar:datos-viejos';
    protected $description = 'Migra datos desde la BD vieja (inventario) a la nueva';

    public function handle()
    {
        $this->info('Iniciando migración...');

        $this->migrarRegiones();
        $this->migrarTipoEquipos();
        $this->migrarEquipos();
        $this->migrarDetalleEquipos();
        $this->migrarFuncionarios();
        $this->migrarAsignaciones();

        $this->info('✅ Migración completada.');
    }

    // ── Regiones ──────────────────────────────────────────────────────────────
    private function migrarRegiones()
    {
        $this->info('Migrando regiones...');
        $regiones = DB::connection('vieja')->select('SELECT * FROM regiones');

        foreach ($regiones as $r) {
            Region::updateOrCreate(
                ['id' => $r->id],
                [
                    'numero'     => $r->numero,
                    'nombre'     => $r->nombre,
                    'capital'    => $r->capital,
                    'created_at' => $r->created_at,
                    'updated_at' => $r->updated_at,
                ]
            );
        }
        $this->info('  ✓ ' . count($regiones) . ' regiones migradas.');
    }

    // ── Tipos de equipo ───────────────────────────────────────────────────────
    private function migrarTipoEquipos()
    {
        $this->info('Migrando tipos de equipo...');
        $tipos = DB::connection('vieja')->select('SELECT * FROM tipo_equipos');

        foreach ($tipos as $t) {
            TipoEquipo::updateOrCreate(
                ['id' => $t->id],
                [
                    'nombre'     => $t->detalle,
                    'created_at' => $t->created_at,
                    'updated_at' => $t->updated_at,
                ]
            );
        }
        $this->info('  ✓ ' . count($tipos) . ' tipos migrados.');
    }

    // ── Equipos (catálogo) ────────────────────────────────────────────────────
    private function migrarEquipos()
    {
        $this->info('Migrando equipos (catálogo)...');
        $equipos = DB::connection('vieja')->select('SELECT * FROM equipos WHERE deleted_at IS NULL');

        foreach ($equipos as $e) {
            // Buscar o crear el tipo de equipo por nombre
            $tipo = TipoEquipo::firstOrCreate(
                ['nombre' => $e->tipo_equipo],
                ['nombre' => $e->tipo_equipo]
            );

            Equipo::updateOrCreate(
                ['id' => $e->id],
                [
                    'tipo_equipo_id' => $tipo->id,
                    'marca'          => $e->marca,
                    'modelo'         => $e->modelo,
                    'imagen'         => $e->imagen && !str_starts_with($e->imagen, '/img') ? $e->imagen : null,
                    'entradas'       => $e->entradas,
                    'procesador'     => $e->procesador,
                    'observacion'    => $e->observacion,
                    'created_at'     => $e->created_at,
                    'updated_at'     => $e->updated_at,
                ]
            );
        }
        $this->info('  ✓ ' . count($equipos) . ' equipos migrados.');
    }

    // ── Detalle equipos (inventario) ──────────────────────────────────────────
    private function migrarDetalleEquipos()
    {
        $this->info('Migrando inventario...');
        $detalles = DB::connection('vieja')->select('SELECT * FROM detalle_equipos');

        // Asegurar estados y tipos de disco
        $estadoDefecto = EstadoEquipo::firstOrCreate(['nombre' => 'Correcto'], ['color' => 'success']);
        $tiposDiscoMap = [
            'HDD' => TipoDisco::firstOrCreate(['nombre' => 'HDD']),
            'SSD' => TipoDisco::firstOrCreate(['nombre' => 'SSD']),
            'SSD NVMe' => TipoDisco::firstOrCreate(['nombre' => 'SSD NVMe']),
        ];

        $migrados = 0;
        $errores  = 0;

        foreach ($detalles as $d) {
            try {
                // Parsear RAM — "16,0 GB" → 16
                $ram = null;
                if ($d->ram) {
                    preg_match('/(\d+)[,.]?\d*/', str_replace('.', '', $d->ram), $m);
                    $ram = isset($m[1]) ? (int) $m[1] : null;
                }

                // Parsear disco — "512GB", "1TB", "480GB"
                $disco = null;
                if ($d->capacidad_disco) {
                    $val = strtoupper($d->capacidad_disco);
                    if (str_contains($val, 'TB')) {
                        preg_match('/(\d+[,.]?\d*)/', $val, $m);
                        $disco = isset($m[1]) ? (int)(str_replace(',', '.', $m[1]) * 1000) : null;
                    } else {
                        preg_match('/(\d+)/', $val, $m);
                        $disco = isset($m[1]) ? (int) $m[1] : null;
                    }
                }

                // Tipo disco
                $tipoDisco = null;
                if ($d->tipo_disco) {
                    $nombreDisco = strtoupper(trim($d->tipo_disco));
                    $tipoDisco = $tiposDiscoMap[$nombreDisco]
                        ?? TipoDisco::firstOrCreate(['nombre' => ucfirst(strtolower($d->tipo_disco))]);
                }

                // Estado
                $estado = EstadoEquipo::where('nombre', $d->estado)->first() ?? $estadoDefecto;

                // Fecha adquisición
                $fecha = null;
                if ($d->fecha_adqui) {
                    try { $fecha = \Carbon\Carbon::parse($d->fecha_adqui)->toDateString(); } catch (\Exception $e) {}
                }

                $detalle = DetalleEquipo::updateOrCreate(
                    ['inventario' => $d->inventario],
                    [
                        'id'                  => $d->id,
                        'equipo_id'           => $d->equipo_id,
                        'estado_id'           => $estado->id,
                        'tipo_disco_id'       => $tipoDisco?->id,
                        'serial'              => $d->serial,
                        'ram_gb'              => $ram,
                        'capacidad_disco_gb'  => $disco,
                        'ip'                  => $d->ip,
                        'fecha_adquisicion'   => $fecha,
                        'puerto_switch'       => $d->puerto,
                        'teletrabajo'         => strtoupper($d->teletrabajo ?? '') === 'SI',
                        'created_at'          => $d->created_at,
                        'updated_at'          => $d->updated_at,
                    ]
                );

                // MAC address
            // MAC address — la vieja guarda "MAC_ETH / MAC_WIFI" en un solo campo
if ($d->mac) {
    $partes = preg_split('/\s*\/\s*/', trim($d->mac));
    
    $macEth  = isset($partes[0]) ? trim($partes[0]) : null;
    $macWifi = isset($partes[1]) ? trim($partes[1]) : null;

    // Validar formato MAC (XX-XX-XX-XX-XX-XX o XX:XX:XX:XX:XX:XX)
    $esValidaMac = fn($mac) => $mac && 
        strlen($mac) <= 20 && 
        preg_match('/^([0-9A-Fa-f]{2}[:-]){5}[0-9A-Fa-f]{2}$/', $mac);

    if ($esValidaMac($macEth)) {
        DetalleEquipoMac::updateOrCreate(
            ['detalle_equipo_id' => $detalle->id, 'interfaz' => 'Ethernet'],
            ['mac' => $macEth]
        );
    }

    if ($esValidaMac($macWifi)) {
        DetalleEquipoMac::updateOrCreate(
            ['detalle_equipo_id' => $detalle->id, 'interfaz' => 'WiFi'],
            ['mac' => $macWifi]
        );
    }
}

                $migrados++;
          } catch (\Exception $e) {
    $this->warn("  ⚠ Error en detalle {$d->inventario}: " . $e->getMessage());
    $errores++;
}
        }

        $this->info("  ✓ {$migrados} unidades migradas, {$errores} errores.");
    }

    // ── Funcionarios ──────────────────────────────────────────────────────────
    private function migrarFuncionarios()
    {
        $this->info('Migrando funcionarios...');
        $funcionarios = DB::connection('vieja')->select('SELECT * FROM funcionarios');

        $migrados = 0;

        foreach ($funcionarios as $f) {
            // Buscar región por nombre
            $region = Region::where('nombre', 'like', '%' . trim($f->region ?? '') . '%')->first()
                ?? Region::where('nombre', 'like', '%Central%')->first();

            Funcionario::updateOrCreate(
                ['id' => $f->id],
                [
                    'nombre'     => $f->nombre,
                    'area'       => $f->area,
                    'region_id'  => $region?->id,
                    'activo'     => strtolower($f->estado ?? 'activo') === 'activo',
                    'avatar'     => null, // los avatares viejos son SVG genéricos
                    'created_at' => $f->created_at,
                    'updated_at' => $f->updated_at,
                    'deleted_at' => $f->deleted_at,
                ]
            );
            $migrados++;
        }

        $this->info("  ✓ {$migrados} funcionarios migrados.");
    }

    // ── Asignaciones ──────────────────────────────────────────────────────────
   private function migrarAsignaciones()
{
    $this->info('Migrando asignaciones...');
    $asignaciones = DB::connection('vieja')->select(
        'SELECT * FROM funcionario_equipos ORDER BY created_at ASC'
    );

    $migrados = 0;
    $errores  = 0;

    foreach ($asignaciones as $a) {
        try {
            $detalle     = DetalleEquipo::find($a->detalle_equipo_id);
            $funcionario = Funcionario::find($a->funcionario_id);

            if (! $detalle || ! $funcionario) continue;

            $esActual = strtolower($a->estado ?? '') === 'asignado';

            // Si es asignación actual, crear registro en tabla asignaciones
            if ($esActual) {
                \App\Models\Asignacion::updateOrCreate(
                    ['detalle_equipo_id' => $detalle->id],
                    [
                        'funcionario_id'   => $funcionario->id,
                        'hostname'         => $a->hostname,
                        'fecha_asignacion' => $a->created_at ?? now(),
                        'observacion'      => $a->observacion,
                    ]
                );
            }

            // Guardar en historial
            HistorialAsignacion::create([
                'detalle_equipo_id' => $detalle->id,
                'funcionario_id'    => $funcionario->id,
                'hostname'          => $a->hostname,
                'accion'            => $esActual ? 'asignado' : 'desasignado',
                'observacion'       => $a->observacion,
                'fecha'             => $a->created_at ?? now(),
                'created_at'        => $a->created_at,
                'updated_at'        => $a->updated_at,
            ]);

            $migrados++;
        } catch (\Exception $e) {
            $this->warn("  ⚠ Error asignación {$a->id}: " . $e->getMessage());
            $errores++;
        }
    }

    $this->info("  ✓ {$migrados} asignaciones migradas, {$errores} errores.");
}
}