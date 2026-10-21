<?php

namespace App\Http\Controllers;

use App\Models\Funcionario;
use Carbon\Carbon;

class NotificacionController extends Controller
{
    public function contratosPorVencer()
    {
        $funcionarios = Funcionario::with(['division', 'departamento', 'unidad'])
            ->whereNotNull('fecha_fin_contrato')
            ->where('activo', true)
            ->where('fecha_fin_contrato', '<=', now()->addDays(7))
            ->orderBy('fecha_fin_contrato')
            ->get();

        return view('notificaciones.contratos', compact('funcionarios'));
    }

    public function contadorContratos()
    {
        $count = Funcionario::whereNotNull('fecha_fin_contrato')
            ->where('activo', true)
            ->where('fecha_fin_contrato', '<=', now()->addDays(7))
            ->count();

        $funcionarios = Funcionario::whereNotNull('fecha_fin_contrato')
            ->where('activo', true)
            ->where('fecha_fin_contrato', '<=', now()->addDays(7))
            ->orderBy('fecha_fin_contrato')
            ->limit(5)
            ->get(['id', 'nombre', 'fecha_fin_contrato'])
            ->map(fn($f) => [
                'id'     => $f->id,
                'nombre' => $f->nombre,
                'dias'   => (int) now()->diffInDays($f->fecha_fin_contrato, false),
                'fecha'  => $f->fecha_fin_contrato->format('d-m-Y'),
            ]);

        return response()->json([
            'count'        => $count,
            'funcionarios' => $funcionarios,
        ]);
    }
}