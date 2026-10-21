<?php

namespace App\Imports;

use App\Models\Equipo;
use App\Models\TipoEquipo;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class EquiposImport implements ToCollection, WithHeadingRow, SkipsEmptyRows
{
    public array $errores = [];
    public int $importados = 0;

    public function collection(Collection $filas): void
    {
        foreach ($filas as $indice => $fila) {
            $fila = $fila->toArray();
            $numero = $indice + 2; // +2 porque la fila 1 es el encabezado

            // Validaciones básicas
            if (empty($fila['marca']) || empty($fila['modelo']) || empty($fila['tipo'])) {
                $this->errores[] = "Fila {$numero}: marca, modelo y tipo son obligatorios.";
                continue;
            }

            $tipo = TipoEquipo::whereRaw('LOWER(nombre) = ?', [strtolower(trim($fila['tipo']))])->first();

            if (! $tipo) {
                $this->errores[] = "Fila {$numero}: tipo '{$fila['tipo']}' no existe en el sistema.";
                continue;
            }

            $existe = Equipo::where('marca', trim($fila['marca']))
                ->where('modelo', trim($fila['modelo']))
                ->exists();

            if ($existe) {
                $this->errores[] = "Fila {$numero}: {$fila['marca']} {$fila['modelo']} ya está registrado.";
                continue;
            }

            Equipo::create([
                'tipo_equipo_id' => $tipo->id,
                'marca' => trim($fila['marca']),
                'modelo' => trim($fila['modelo']),
                'procesador' => trim($fila['procesador'] ?? '') ?: null,
                'entradas' => trim($fila['entradas'] ?? '') ?: null,
                'observacion' => trim($fila['observacion'] ?? '') ?: null,
            ]);

            $this->importados++;
        }
    }
}