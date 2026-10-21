<?php

namespace App\Http\Controllers;

use App\Models\DetalleEquipo;
use Illuminate\Http\Request;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Style\Table as TableStyle;
use PhpOffice\PhpWord\SimpleType\TblWidth;

class DocumentoEquipoController extends Controller
{
    protected string $logoPath;

    public function __construct()
    {
        $this->logoPath = public_path('images/logo_sea.png');
    }

    // ── Acta de Entrega ───────────────────────────────────────────────────────
    public function acta(DetalleEquipo $detalle_equipo)
    {
        $detalle_equipo->load([
            'equipo.tipoEquipo',
            'tipoDisco',
            'macs',
            'asignacionActual.funcionario.region',
            'asignacionActual.funcionario.division',
        ]);
        $detalle_equipo->load([
            'equipo.tipoEquipo',
            'tipoDisco',
            'macs',
            'asignacionActual.funcionario.region',
        ]);

        $asignacion  = $detalle_equipo->asignacionActual;
        $funcionario = $asignacion?->funcionario;

        // Todos los equipos asignados al funcionario
        $todosEquipos = $funcionario
            ? $funcionario->asignaciones()->with(['detalleEquipo.equipo.tipoEquipo', 'detalleEquipo.tipoDisco'])->get()
            ->map(fn($a) => $a->detalleEquipo)
            : collect([$detalle_equipo]);

        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('Arial');
        $phpWord->setDefaultFontSize(10);

        $section = $phpWord->addSection([
            'pageSize'    => ['width' => 11906, 'height' => 16838],
            'marginTop'   => 720,
            'marginBottom' => 720,
            'marginLeft'  => 720,
            'marginRight' => 720,
        ]);

        // Footer
        $footer = $section->addFooter();
        $footer->addText('Huérfanos 670, Santiago de Chile', ['size' => 8], ['alignment' => 'center', 'borderTopSize' => 4, 'borderTopColor' => '000000']);
        $footer->addText('Teléfono: (56 2) 616 40 00', ['size' => 8], ['alignment' => 'center']);

        // ── Tabla encabezado ──
        $tblHeader = $section->addTable(['borderSize' => 4, 'borderColor' => '000000', 'width' => 9026, 'unit' => TblWidth::TWIP]);
        $tblHeader->addRow();
        $cell = $tblHeader->addCell(1500, ['vMerge' => 'restart', 'valign' => 'center']);
        if (file_exists($this->logoPath)) {
            $cell->addImage($this->logoPath, ['width' => 55, 'height' => 50, 'alignment' => 'center']);
        }
        $cell2 = $tblHeader->addCell(5026, ['vMerge' => 'restart', 'valign' => 'center']);
        $cell2->addText('ACTA DE ENTREGA DE BIENES', ['bold' => true, 'size' => 12], ['alignment' => 'center']);
        $cell2->addText('ASIGNADO(S) A SU CARGO Y/O LABORES', ['bold' => true, 'size' => 12], ['alignment' => 'center']);
        $cell3 = $tblHeader->addCell(2500);
        $cell3->addText('Código     F3-PR-DAF-09', ['size' => 8]);

        $tblHeader->addRow();
        $tblHeader->addCell(1500, ['vMerge' => 'continue']);
        $tblHeader->addCell(5026, ['vMerge' => 'continue']);
        $tblHeader->addCell(2500)->addText('Revisión   0', ['size' => 8]);

        $tblHeader->addRow();
        $tblHeader->addCell(1500, ['vMerge' => 'continue']);
        $tblHeader->addCell(5026, ['vMerge' => 'continue']);
        $tblHeader->addCell(2500)->addText('Fecha       05/2024', ['size' => 8]);

        // ── Tabla meta ──
        $tblMeta = $section->addTable(['borderSize' => 4, 'borderColor' => '000000', 'width' => 9026, 'unit' => TblWidth::TWIP]);
        $tblMeta->addRow();
        $tblMeta->addCell(2256)->addText('Preparado por:', ['bold' => true, 'size' => 8]);
        $tblMeta->addCell(2257)->addText('Dueño del Proceso:', ['bold' => true, 'size' => 8]);
        $tblMeta->addCell(2256)->addText('Revisado por:', ['bold' => true, 'size' => 8]);
        $tblMeta->addCell(2257)->addText('Aprobado por:', ['bold' => true, 'size' => 8]);
        $tblMeta->addRow();
        $tblMeta->addCell(2256)->addText('USG', ['size' => 8]);
        $tblMeta->addCell(2257)->addText('USG', ['size' => 8]);
        $tblMeta->addCell(2256)->addText('DPCG', ['size' => 8]);
        $tblMeta->addCell(2257)->addText('DAF', ['size' => 8]);

        // ── Texto intro ──
        $section->addTextBreak(1);
        $section->addText('Santiago, ' . now()->format('d-m-Y') . '.', ['size' => 10]);
        $section->addTextBreak(1);

        $run = $section->addTextRun(['size' => 10]);
        $run->addText('Por intermedio del presente, se hace entrega a el/la funcionario/a ');
        $run->addText($funcionario?->nombre ?? '—', ['bold' => true]);
        $run->addText(' perteneciente a la ');
        $run->addText($funcionario?->division?->nombre ?? '—', ['bold' => true]);
        $run->addText(', el/los bienes(s) que se detalla(n) a continuación:');

        $section->addTextBreak(1);

        // ── Tabla equipos ──
        $tblEquipos = $section->addTable(['borderSize' => 4, 'borderColor' => '000000', 'width' => 9026, 'unit' => TblWidth::TWIP]);
        $tblEquipos->addRow();
        foreach (['Equipo', 'Marca', 'Modelo', 'Inventario', 'Serie', 'CPU', 'RAM', 'Disco'] as $h) {
            $tblEquipos->addCell(null, ['bgColor' => 'D9D9D9'])
                ->addText($h, ['bold' => true, 'size' => 9], ['alignment' => 'center']);
        }

        foreach ($todosEquipos as $eq) {
            $disco = $eq->capacidad_disco_gb
                ? ($eq->capacidad_disco_gb >= 1000
                    ? number_format($eq->capacidad_disco_gb / 1000, 0) . 'TB'
                    : $eq->capacidad_disco_gb . 'GB')
                . ' ' . ($eq->tipoDisco?->nombre ?? '')
                : '—';

            $tblEquipos->addRow();
            $tblEquipos->addCell(null)->addText($eq->equipo?->tipoEquipo?->nombre ?? '—', ['size' => 9]);
            $tblEquipos->addCell(null)->addText($eq->equipo?->marca ?? '—', ['size' => 9]);
            $tblEquipos->addCell(null)->addText($eq->equipo?->modelo ?? '—', ['size' => 9]);
            $tblEquipos->addCell(null)->addText($eq->inventario, ['size' => 9]);
            $tblEquipos->addCell(null)->addText($eq->serial, ['size' => 9]);
            $tblEquipos->addCell(null)->addText($eq->equipo?->procesador ?? '—', ['size' => 9]);
            $tblEquipos->addCell(null)->addText($eq->ram_gb ? $eq->ram_gb . ',0 GB' : '—', ['size' => 9]);
            $tblEquipos->addCell(null)->addText($disco, ['size' => 9]);
        }

        // Observaciones
        $section->addTextBreak(1);
        $obsRun = $section->addTextRun();
        $obsRun->addText('Observaciones: ', ['bold' => true, 'size' => 10]);
        $obsRun->addText('Sin observaciones.', ['size' => 10]);

        $section->addTextBreak(1);
        $section->addText('En señal de conformidad se firma la presente acta:', ['size' => 10]);

        // ── Firmas ──
        $section->addTextBreak(3);
        $tblFirmas = $section->addTable(['width' => 9026, 'unit' => TblWidth::TWIP, 'borderSize' => 0, 'borderColor' => 'FFFFFF']);
        $tblFirmas->addRow();
        $tblFirmas->addCell(3008, ['borderSize' => 0])->addText('Lorena Marín Silva', ['bold' => true, 'size' => 10], ['alignment' => 'center']);
        $tblFirmas->addCell(3010, ['borderSize' => 0])->addText($funcionario?->nombre ?? '—', ['bold' => true, 'size' => 10], ['alignment' => 'center']);
        $tblFirmas->addCell(3008, ['borderSize' => 0])->addText('Rodrigo López Villagra', ['bold' => true, 'size' => 10], ['alignment' => 'center']);

        $tblFirmas->addRow();
        $tblFirmas->addCell(3008, ['borderSize' => 0])->addText('Encargado/a de Inventario', ['size' => 9], ['alignment' => 'center']);
        $tblFirmas->addCell(3010, ['borderSize' => 0])->addText($funcionario?->division?->nombre ?? '—', ['size' => 9], ['alignment' => 'center']);
        $tblFirmas->addCell(3008, ['borderSize' => 0])->addText('Unidad de Soporte Informático', ['size' => 9], ['alignment' => 'center']);

        $tblFirmas->addRow();
        $tblFirmas->addCell(3008, ['borderSize' => 0])->addText('División de Administración y Finanzas', ['size' => 9], ['alignment' => 'center']);
        $tblFirmas->addCell(3010, ['borderSize' => 0])->addText('', ['size' => 9]);
        $tblFirmas->addCell(3008, ['borderSize' => 0])->addText('División de Tecnologías y Gestión de la Información', ['size' => 9], ['alignment' => 'center']);

        $nombre = "Acta_Entrega_{$detalle_equipo->inventario}.docx";
        $ruta   = storage_path("app/temp_{$nombre}");

        IOFactory::createWriter($phpWord, 'Word2007')->save($ruta);

        return response()->download($ruta, $nombre)->deleteFileAfterSend(true);
    }

    // ── Informe de Baja ───────────────────────────────────────────────────────
    public function baja($id, Request $request)
    {
         $detalle_equipo = DetalleEquipo::withTrashed()
        ->with(['equipo.tipoEquipo', 'tipoDisco', 'estado'])
        ->findOrFail($id);


    $detalle = $request->input('detalle', '');
    if ($detalle && !$detalle_equipo->detalle_baja) {
        $detalle_equipo->withoutEvents(function() use ($detalle_equipo, $detalle) {
            $detalle_equipo->forceFill(['detalle_baja' => $detalle])->save();
        });
    }

        $plantilla = storage_path('app/templates/plantilla_baja.docx');
        if (!file_exists($plantilla)) {
            abort(404, 'Plantilla no encontrada.');
        }

        $processor = new \PhpOffice\PhpWord\TemplateProcessor($plantilla);

        $disco = $detalle_equipo->capacidad_disco_gb
            ? ($detalle_equipo->capacidad_disco_gb >= 1000
                ? number_format($detalle_equipo->capacidad_disco_gb / 1000, 0) . 'TB'
                : $detalle_equipo->capacidad_disco_gb . 'GB')
            . ' ' . ($detalle_equipo->tipoDisco?->nombre ?? '')
            : 'Sin disco duro.';

        $processor->setValue('tipo_equipo', $detalle_equipo->equipo?->tipoEquipo?->nombre ?? '—');
        $processor->setValue('marca',       $detalle_equipo->equipo?->marca ?? '—');
        $processor->setValue('modelo',      $detalle_equipo->equipo?->modelo ?? '—');
        $processor->setValue('serial',      $detalle_equipo->serial ?? '—');
        $processor->setValue('inventario',  $detalle_equipo->inventario);
        $processor->setValue('procesador',  $detalle_equipo->equipo?->procesador ?? '—');
        $processor->setValue('fecha',       now()->format('d-m-Y')); // ← fecha actual
        $processor->setValue('descripcion_baja', $request->input('detalle', '')); // ← comentario
        $processor->setValue('usuario', auth()->user()->name ?? '—');
        $processor->setValue('descripcion_baja', $detalle_equipo->detalle_baja ?? $detalle);
        $nombre = "Baja_{$detalle_equipo->inventario}.docx";
        $ruta   = storage_path("app/temp_{$nombre}");
        $processor->saveAs($ruta);

        return response()->download($ruta, $nombre)->deleteFileAfterSend(true);
    }
    public function bajaUnida(Request $request)
    {
        $ids = $request->input('equipos', []);
        $detalle_baja = $request->input('detalle', '');

        if (empty($ids) || count($ids) > 20) {
            abort(400, 'Selecciona entre 1 y 20 equipos.');
        }

        $equipos = DetalleEquipo::withTrashed()
            ->with(['equipo.tipoEquipo', 'tipoDisco', 'estado'])
            ->whereIn('id', $ids)
            ->get();

        $plantilla = storage_path('app/templates/plantilla_baja.docx');
        if (!file_exists($plantilla)) {
            abort(404, 'Plantilla no encontrada.');
        }

        $phpWord = new \PhpOffice\PhpWord\PhpWord();
        $phpWord->setDefaultFontName('Arial');
        $phpWord->setDefaultFontSize(10);

        $section = $phpWord->addSection([
            'pageSize'     => ['width' => 11906, 'height' => 16838],
            'marginTop'    => 720,
            'marginBottom' => 720,
            'marginLeft'   => 720,
            'marginRight'  => 720,
        ]);

        // ── Encabezado ────────────────────────────────────────────────────────────
        $section->addText('INFORME DE BAJA DE EQUIPOS', ['bold' => true, 'size' => 14], ['alignment' => 'center']);
        $section->addText('Fecha: ' . now()->format('d-m-Y'), ['size' => 10], ['alignment' => 'center']);
        $section->addTextBreak(1);

        if ($detalle_baja) {
            $section->addText('Motivo de baja:', ['bold' => true, 'size' => 10]);
            $section->addText($detalle_baja, ['size' => 10]);
            $section->addTextBreak(1);
        }

        // ── Tabla equipos ─────────────────────────────────────────────────────────
        $tbl = $section->addTable([
            'borderSize'  => 4,
            'borderColor' => '000000',
            'width'       => 9026,
            'unit'        => \PhpOffice\PhpWord\SimpleType\TblWidth::TWIP,
        ]);

        // Cabecera
        $tbl->addRow();
        foreach (['N°', 'Tipo', 'Marca', 'Modelo', 'Inventario', 'Serie', 'CPU', 'RAM', 'Disco'] as $h) {
            $tbl->addCell(null, ['bgColor' => 'D9D9D9'])
                ->addText($h, ['bold' => true, 'size' => 8], ['alignment' => 'center']);
        }

        // Filas equipos
        foreach ($equipos as $i => $eq) {
            $disco = $eq->capacidad_disco_gb
                ? ($eq->capacidad_disco_gb >= 1000
                    ? number_format($eq->capacidad_disco_gb / 1000, 0) . 'TB'
                    : $eq->capacidad_disco_gb . 'GB')
                . ' ' . ($eq->tipoDisco?->nombre ?? '')
                : '—';

            $tbl->addRow();
            $tbl->addCell(null)->addText($i + 1, ['size' => 8]);
            $tbl->addCell(null)->addText($eq->equipo?->tipoEquipo?->nombre ?? '—', ['size' => 8]);
            $tbl->addCell(null)->addText($eq->equipo?->marca ?? '—', ['size' => 8]);
            $tbl->addCell(null)->addText($eq->equipo?->modelo ?? '—', ['size' => 8]);
            $tbl->addCell(null)->addText($eq->inventario, ['size' => 8]);
            $tbl->addCell(null)->addText($eq->serial ?? '—', ['size' => 8]);
            $tbl->addCell(null)->addText($eq->equipo?->procesador ?? '—', ['size' => 8]);
            $tbl->addCell(null)->addText($eq->ram_gb ? $eq->ram_gb . ' GB' : '—', ['size' => 8]);
            $tbl->addCell(null)->addText(trim($disco), ['size' => 8]);
        }

        $section->addTextBreak(2);
        $section->addText('Total equipos: ' . count($equipos), ['bold' => true, 'size' => 10]);

        $nombre = 'Baja_Masiva_' . now()->format('Ymd_His') . '.docx';
        $ruta   = storage_path('app/temp_' . $nombre);

        \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007')->save($ruta);

        return response()->download($ruta, $nombre)->deleteFileAfterSend(true);
    }




}
