<?php

namespace App\Http\Controllers;

use App\Models\Edificio;
use Illuminate\Http\Request;

class EdificioController extends Controller
{
    public function index()
    {
        $edificios = Edificio::withCount('pisos')->orderBy('nombre')->get();
        return view('puestos.edificios.index', compact('edificios'));
    }

  public function store(Request $request)
{
    $request->validate([
        'nombre'    => 'required|string|max:255',
        'direccion' => 'nullable|string|max:255',
        'ciudad'    => 'nullable|string|max:255',
    ]);

    Edificio::create($request->only(['nombre', 'direccion', 'ciudad']));

    return redirect()->route('edificio.index')->with('info', 'Edificio registrado correctamente.');
}

public function update(Request $request, Edificio $edificio)
{
    $request->validate([
        'nombre'    => 'required|string|max:255',
        'direccion' => 'nullable|string|max:255',
        'ciudad'    => 'nullable|string|max:255',
    ]);

    $edificio->update($request->only(['nombre', 'direccion', 'ciudad']));

    return redirect()->route('edificio.index')->with('info', 'Edificio actualizado.');
}
    public function destroy(Edificio $edificio)
    {
        $edificio->delete();
        return redirect()->route('edificio.index')->with('info', 'Edificio eliminado.');
    }
}