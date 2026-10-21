<?php

namespace App\Exports;

use App\Models\Equipo;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EquiposExport implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    public function query()
    {
        return Equipo::with('tipoEquipo')->orderBy('marca')->orderBy('modelo');
    }

    public function headings(): array
    {
        return [
            'ID',
            'Tipo',
            'Marca',
            'Modelo',
            'Procesador',
            'Entradas',
            'Observación',
            'Registrado el',
        ];
    }

    public function map($equipo): array
    {
        return [
            $equipo->id,
            $equipo->tipoEquipo->nombre ?? '—',
            $equipo->marca,
            $equipo->modelo,
            $equipo->procesador ?? '—',
            $equipo->entradas ?? '—',
            $equipo->observacion ?? '—',
            $equipo->created_at->format('d-m-Y'),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FF2563EB']],
            ],
        ];
    }
}