<?php

namespace App\Http\Controllers;

use App\Models\Edificio;
use App\Models\Piso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PisoController extends Controller
{
   public function show(Piso $piso)
{
    $piso->load(['edificio', 'puestos.funcionario']);

    $puestosJson = $piso->puestos->map(function ($p) {
        return [
            'id'          => $p->id,
            'numero'      => $p->numero,
            'seccion'     => $p->seccion,
            'observacion' => $p->observacion,
            'x1'          => $p->x1,
            'y1'          => $p->y1,
            'x2'          => $p->x2,
            'y2'          => $p->y2,
            'ocupado'     => $p->estaOcupado(),
            'activo'      => $p->activo,
            'funcionario' => $p->funcionario?->nombre,
        ];
    })->values();

    return view('puestos.pisos.show', compact('piso', 'puestosJson'));
}

    public function store(Request $request, Edificio $edificio)
    {
        $request->validate([
            'nombre'  => 'required|string|max:255',
            'numero'  => 'nullable|integer',
            'imagen'  => 'required|image|mimes:jpeg,png,jpg|max:5120',
        ]);

        $ruta = $request->file('imagen')->store("pisos/{$edificio->id}", 'public');

        $edificio->pisos()->create([
            'nombre'  => $request->nombre,
            'numero'  => $request->numero,
            'imagen'  => $ruta,
        ]);

        return redirect()->route('edificio.index')->with('info', 'Piso registrado correctamente.');
    }

    public function update(Request $request, Piso $piso)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'numero' => 'nullable|integer',
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
        ]);

        $data = $request->only(['nombre', 'numero']);

        if ($request->hasFile('imagen')) {
            Storage::disk('public')->delete($piso->imagen);
            $data['imagen'] = $request->file('imagen')->store("pisos/{$piso->edificio_id}", 'public');
        }

        $piso->update($data);

        return redirect()->route('piso.show', $piso)->with('info', 'Piso actualizado.');
    }

    public function destroy(Piso $piso)
    {
        Storage::disk('public')->delete($piso->imagen);
        $edificioId = $piso->edificio_id;
        $piso->delete();

        return redirect()->route('edificio.index')->with('info', 'Piso eliminado.');
    }

    public function exportar(Piso $piso)
{
    $piso->load(['edificio', 'puestos.funcionario']);

    $filas = $piso->puestos->map(fn ($p) => [
        'Edificio'    => $piso->edificio->nombre,
        'Piso'        => $piso->nombre,
        'Puesto'      => $p->numero,
        'Sección'     => $p->seccion ?? '—',
        'Estado'      => $p->activo ? 'Activo' : 'Deshabilitado',
        'Funcionario' => $p->funcionario?->nombre ?? '—',
        'RUT'         => $p->funcionario?->rut_formateado ?? '—',
        'Cargo'       => $p->funcionario?->cargo ?? '—',
        'area' => $puesto->funcionario->division?->nombre ?? '—',
'area' => $f->division?->nombre ?? '—',
    ]);

    $nombre = 'Piso_' . str_replace(' ', '_', $piso->nombre) . '_' . now()->format('Ymd') . '.xlsx';

    return \Spatie\SimpleExcel\SimpleExcelWriter::streamDownload($nombre)
        ->addRows($filas)
        ->toBrowser();
}
}