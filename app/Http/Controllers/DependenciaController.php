<?php

namespace App\Http\Controllers;

use App\Models\Division;
use App\Models\Departamento;
use App\Models\Unidad;
use Illuminate\Http\Request;
use Spatie\SimpleExcel\SimpleExcelWriter;
use Spatie\SimpleExcel\SimpleExcelReader;

class DependenciaController extends Controller
{
   public function index()
{
    $divisiones    = Division::withCount('departamentos')->orderBy('nombre')->paginate(15, ['*'], 'pag_div');
    $divisionesSelect   = Division::where('activo', true)->orderBy('nombre')->get();
$departamentosSelect = Departamento::where('activo', true)->orderBy('nombre')->get();
    $departamentos = Departamento::with('division')->withCount('unidades')->orderBy('nombre')->paginate(15, ['*'], 'pag_dep');
    $unidades      = Unidad::with('departamento.division')->orderBy('nombre')->paginate(15, ['*'], 'pag_uni');

    return view('dependencias.index', compact('divisiones', 'departamentos', 'unidades','divisionesSelect','departamentosSelect'));
}

    // ── Divisiones ────────────────────────────────────────────────────────────
    public function storeDivision(Request $request)
    {
        $request->validate(['nombre' => 'required|string|max:255|unique:divisiones,nombre']);
        Division::create(['nombre' => $request->nombre]);
        return redirect(route('dependencia.index') . '#divisiones')->with('info', 'División creada.');
    }

    public function updateDivision(Request $request, Division $division)
    {
        $request->validate(['nombre' => 'required|string|max:255|unique:divisiones,nombre,' . $division->id]);
        $division->update(['nombre' => $request->nombre]);
        return redirect()->route('dependencia.index')->with('info', 'División actualizada.');
    }

    public function destroyDivision(Division $division)
    {
        $division->delete();
        return redirect()->route('dependencia.index')->with('info', 'División eliminada.');
    }

    public function toggleDivision(Division $division)
    {
        $division->update(['activo' => !$division->activo]);
        return redirect()->route('dependencia.index')
            ->with('info', $division->activo ? 'División habilitada.' : 'División deshabilitada.');
    }

    // ── Departamentos ─────────────────────────────────────────────────────────
    public function storeDepartamento(Request $request)
    {
        $request->validate([
            'nombre'      => 'required|string|max:255',
            'division_id' => 'required|exists:divisiones,id',
        ]);
        Departamento::create($request->only(['nombre', 'division_id']));
        return redirect(route('dependencia.index') . '#departamentos')->with('info', 'Departamento creado.');
    }

    public function updateDepartamento(Request $request, Departamento $departamento)
    {
        $request->validate([
            'nombre'      => 'required|string|max:255',
            'division_id' => 'required|exists:divisiones,id',
        ]);
        $departamento->update($request->only(['nombre', 'division_id']));
        return redirect()->route('dependencia.index')->with('info', 'Departamento actualizado.');
    }

    public function destroyDepartamento(Departamento $departamento)
    {
        $departamento->delete();
        return redirect()->route('dependencia.index')->with('info', 'Departamento eliminado.');
    }

    public function toggleDepartamento(Departamento $departamento)
    {
        $departamento->update(['activo' => !$departamento->activo]);
        return redirect()->route('dependencia.index')
            ->with('info', $departamento->activo ? 'Departamento habilitado.' : 'Departamento deshabilitado.');
    }

    // ── Unidades ──────────────────────────────────────────────────────────────
    public function storeUnidad(Request $request)
    {
        $request->validate([
            'nombre'          => 'required|string|max:255',
            'departamento_id' => 'required|exists:departamentos,id',
        ]);
        Unidad::create($request->only(['nombre', 'departamento_id']));
        return redirect(route('dependencia.index') . '#unidades')->with('info', 'Unidad creada.');

    }

    public function updateUnidad(Request $request, Unidad $unidad)
    {
        $request->validate([
            'nombre'          => 'required|string|max:255',
            'departamento_id' => 'required|exists:departamentos,id',
        ]);
        $unidad->update($request->only(['nombre', 'departamento_id']));
        return redirect()->route('dependencia.index')->with('info', 'Unidad actualizada.');
    }

    public function destroyUnidad(Unidad $unidad)
    {
        $unidad->delete();
        return redirect()->route('dependencia.index')->with('info', 'Unidad eliminada.');
    }

    public function toggleUnidad(Unidad $unidad)
    {
        $unidad->update(['activo' => !$unidad->activo]);
        return redirect()->route('dependencia.index')
            ->with('info', $unidad->activo ? 'Unidad habilitada.' : 'Unidad deshabilitada.');
    }

    // ── Exportar ──────────────────────────────────────────────────────────────
    public function exportar()
    {
        $filas = Unidad::with('departamento.division')
            ->orderBy('nombre')
            ->get()
            ->map(fn($u) => [
                'División'      => $u->departamento->division->nombre ?? '—',
                'Departamento'  => $u->departamento->nombre ?? '—',
                'Unidad'        => $u->nombre,
                'Estado'        => $u->activo ? 'Activo' : 'Deshabilitado',
            ]);

        return SimpleExcelWriter::streamDownload('dependencias.xlsx')
            ->addRows($filas)
            ->toBrowser();
    }

    // ── Importar ──────────────────────────────────────────────────────────────
    public function importar(Request $request)
    {
        $request->validate(['archivo' => 'required|file|mimes:xlsx,xls,csv']);

          // Guardar con extensión correcta
$extension    = $request->file('archivo')->getClientOriginalExtension();
$rutaTemporal = storage_path('app/temp_import_dependencias.' . $extension);
copy($request->file('archivo')->getRealPath(), $rutaTemporal);
$filas = SimpleExcelReader::create($rutaTemporal)->getRows();

        $insertados  = [];
        $duplicados  = [];
        $errores     = [];

        foreach ($filas as $fila) {
            try {
                $nombreDiv = trim($fila['DIVISION'] ?? '');
                $nombreDep = trim($fila['DEPARTAMENTO'] ?? '');
                $nombreUni = trim($fila['UNIDAD'] ?? '');

                if (empty($nombreDiv)) continue;

                // División
                $division = Division::firstOrCreate(
                    ['nombre' => $nombreDiv],
                    ['activo' => true]
                );

                // Departamento
                $departamento = null;
                if ($nombreDep) {
                    $departamento = Departamento::firstOrCreate(
                        ['nombre' => $nombreDep, 'division_id' => $division->id],
                        ['activo' => true]
                    );
                }

                // Unidad
                if ($nombreUni && $departamento) {
                    $existe = Unidad::where('nombre', $nombreUni)
                        ->where('departamento_id', $departamento->id)
                        ->exists();

                    if ($existe) {
                        $duplicados[] = $nombreUni;
                    } else {
                        Unidad::create([
                            'nombre'          => $nombreUni,
                            'departamento_id' => $departamento->id,
                            'activo'          => true,
                        ]);
                        $insertados[] = "$nombreDiv > $nombreDep > $nombreUni";
                    }
                }
            } catch (\Exception $e) {
                $errores[] = $e->getMessage();
            }
        }

        // Generar Excel con resultado
        $resultado = collect($insertados)->map(fn($i) => ['Registro' => $i, 'Estado' => 'Insertado'])
            ->merge(collect($duplicados)->map(fn($d) => ['Registro' => $d, 'Estado' => 'Duplicado']))
            ->merge(collect($errores)->map(fn($e) => ['Registro' => $e, 'Estado' => 'Error']));

        $rutaReporte = storage_path('app/temp_reporte_dependencias.xlsx');
        SimpleExcelWriter::create($rutaReporte)->addRows($resultado)->close();

        session(['reporte_dependencias' => $rutaReporte]);

        return redirect()->route('dependencia.index')
            ->with('info', count($insertados) . ' registros insertados, ' . count($duplicados) . ' duplicados.');
    }

    public function descargarReporte()
    {
        $ruta = session('reporte_dependencias');
        if (!$ruta || !file_exists($ruta)) {
            return redirect()->route('dependencia.index');
        }
        return response()->download($ruta, 'reporte_dependencias.xlsx')->deleteFileAfterSend(true);
    }

    public function departamentosPorDivision(Request $request)
{
    return response()->json(
        Departamento::where('division_id', $request->division_id)
            ->where('activo', true)
            ->orderBy('nombre')
            ->get(['id', 'nombre'])
    );
}

public function getDepartamento(Departamento $departamento)
{
    return response()->json($departamento->only(['id', 'nombre', 'division_id']));
}

public function unidadesPorDepartamento(Request $request)
{
    return response()->json(
        Unidad::where('departamento_id', $request->departamento_id)
            ->where('activo', true)
            ->orderBy('nombre')
            ->get(['id', 'nombre'])
    );
}
}