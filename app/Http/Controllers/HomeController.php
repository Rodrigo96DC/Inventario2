<?php

namespace App\Http\Controllers;

use App\Models\DetalleEquipo;
use App\Models\Equipo;
use App\Models\EstadoEquipo;
use App\Models\Funcionario;
use App\Models\Region;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        // Tarjetas
        $totalCatalogo   = Equipo::count();
        $totalInventario = DetalleEquipo::count();
        $asignados       = DetalleEquipo::whereHas('asignacionActual')->count();
        $sinAsignar      = $totalInventario - $asignados;

        $funcionariosActivos   = Funcionario::where('activo', true)->count();
        $funcionariosInactivos = Funcionario::where('activo', false)->count();

        // Equipos por región (a través del funcionario asignado)
        $porRegion = Region::withCount(['funcionarios as total_equipos' => function ($q) {
                $q->join('asignaciones', 'asignaciones.funcionario_id', '=', 'funcionarios.id');
            }])
            ->orderByDesc('total_equipos')
            ->get()
            ->filter(fn($r) => $r->total_equipos > 0)
            ->values();

        // Equipos por estado
        $porEstado = EstadoEquipo::all()->map(function ($estado) {
            $estado->total = DetalleEquipo::where('estado_id', $estado->id)->count();
            $estado->hex_color = match($estado->color) {
                'success' => '#198754',
                'warning' => '#ffc107',
                'danger'  => '#dc3545',
                'info'    => '#0dcaf0',
                default   => '#6c757d',
            };
            return $estado;
        });

        // Últimas asignaciones
        $ultimasAsignaciones = \App\Models\HistorialAsignacion::with(['detalleEquipo.equipo', 'funcionario'])
            ->latest('fecha')
            ->limit(8)
            ->get();

        // Contratos por vencer
        $contratosPorVencer = Funcionario::whereNotNull('fecha_fin_contrato')
            ->where('activo', true)
            ->where('fecha_fin_contrato', '<=', now()->addDays(7))
            ->count();

        return view('home', compact(
            'totalCatalogo', 'totalInventario', 'asignados', 'sinAsignar',
            'funcionariosActivos', 'funcionariosInactivos',
            'porRegion', 'porEstado', 'ultimasAsignaciones', 'contratosPorVencer'
        ));
    }
}