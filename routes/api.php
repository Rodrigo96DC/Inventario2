<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EquipoApiController;
use App\Http\Controllers\Api\FuncionarioApiController;
use App\Http\Controllers\Api\AsignacionApiController;
use Illuminate\Support\Facades\Route;

// ── Autenticación ─────────────────────────────────────────────────────────────
Route::post('login',  [AuthController::class, 'login']);
Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
 Route::get('funcionarios/buscar',          [FuncionarioApiController::class, 'buscar']);
// ── Rutas protegidas ──────────────────────────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    // Usuario autenticado
    Route::get('me', [AuthController::class, 'me']);

    // Equipos
    Route::get('equipos/buscar',      [EquipoApiController::class, 'buscar']);
    Route::get('equipos/{detalle}',   [EquipoApiController::class, 'show']);
    Route::post('equipos/{detalle}/observaciones', [EquipoApiController::class, 'storeObservacion']);
Route::get('equipos/{detalle}/observaciones', [EquipoApiController::class, 'observaciones']);
    // Funcionarios
   
    Route::get('funcionarios/{funcionario}/equipos', [FuncionarioApiController::class, 'equipos']);

    // Asignaciones
    Route::post('asignaciones/asignar',                    [AsignacionApiController::class, 'asignar']);
    Route::delete('asignaciones/{detalle}/desasignar',     [AsignacionApiController::class, 'desasignar']);
});