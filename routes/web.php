<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\FuncionarioController;
use App\Http\Controllers\EquipoController;
use App\Http\Controllers\AuditoriaController;
use App\Http\Controllers\DetalleEquipoController;
use App\Http\Controllers\AsignacionController;
use App\Http\Controllers\EquipoAsignadoController;
use App\Http\Controllers\EdificioController;
use App\Http\Controllers\PisoController;
use App\Http\Controllers\PuestoController;
use App\Http\Controllers\DocumentoEquipoController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\DependenciaController;
use App\Http\Controllers\NotificacionController;
use App\Http\Controllers\ListaController;

Route::get('/', function () {
    return redirect()->route('login');
});
Auth::routes();
Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

// ── Rutas internas API para JS (requieren auth web) ───────────────────────────
Route::middleware('auth')->group(function () {
    // ... rutas existentes ...

    // Rutas internas para JS del navegador
    Route::get('interno/funcionarios/buscar', [AsignacionController::class, 'buscarFuncionario'])->name('api.funcionario.buscar');
    Route::get('interno/funcionarios/{funcionario}/equipos', [AsignacionController::class, 'equiposFuncionario'])->name('api.funcionario.equipos');
    Route::get('interno/equipos/{detalle}/historial', [AsignacionController::class, 'historialEquipo'])->name('api.equipo.historial');
    Route::get('interno/puestos/funcionarios-sin-puesto', [PuestoController::class, 'funcionariosSinPuesto'])->name('api.puesto.funcionarios');

Route::get('interno/departamentos-por-division', [DependenciaController::class, 'departamentosPorDivision']);
Route::get('interno/departamento/{departamento}', [DependenciaController::class, 'getDepartamento']);
 Route::get('interno/unidades-por-departamento', [DependenciaController::class, 'unidadesPorDepartamento']);

 Route::post('inventario/ajax-store', [DetalleEquipoController::class, 'ajaxStore'])->name('detalle-equipo.ajax-store');
    });



Route::middleware('auth')->group(function () {
    Route::get('listas', [ListaController::class, 'index'])->name('lista.index');
    Route::post('listas', [ListaController::class, 'store'])->name('lista.store');
    Route::get('listas/{lista}', [ListaController::class, 'show'])->name('lista.show');
    Route::put('listas/{lista}', [ListaController::class, 'update'])->name('lista.update');
    Route::delete('listas/{lista}', [ListaController::class, 'destroy'])->name('lista.destroy');
    Route::post('listas/{lista}/items', [ListaController::class, 'agregarItem'])->name('lista.item.store');
    Route::put('lista-items/{item}', [ListaController::class, 'updateItem'])->name('lista.item.update');
    Route::delete('lista-items/{item}', [ListaController::class, 'destroyItem'])->name('lista.item.destroy');
    Route::post('listas/{lista}/papelera', [ListaController::class, 'enviarPapelera'])->name('lista.papelera');
    Route::post('listas/importar-baja', [ListaController::class, 'importarBaja'])->name('lista.importar-baja');
});

// ── Rutas protegidas ──────────────────────────────────────────────────────────
Route::middleware('web')->group(function () {

    // Funcionarios
    
    Route::delete('funcionarios-masivo', [FuncionarioController::class, 'destroyMasivo'])->name('funcionario.destroyMasivo');

    Route::get('funcionarios/plantilla-rut', [FuncionarioController::class, 'plantillaRut'])->name('funcionario.plantilla-rut');
Route::get('funcionarios/importar-rut', [FuncionarioController::class, 'importarRutVista'])->name('funcionario.importar-rut');
Route::post('funcionarios/importar-rut', [FuncionarioController::class, 'importarRut'])->name('funcionario.importar-rut.post');
Route::get('funcionarios/importar-correos', [FuncionarioController::class, 'importarCorreosVista'])->name('funcionario.importar-correos');
Route::post('funcionarios/importar-correos', [FuncionarioController::class, 'importarCorreos'])->name('funcionario.importar-correos.post');
    Route::get('funcionarios/papelera', [FuncionarioController::class, 'papelera'])->name('funcionario.papelera');
    Route::patch('funcionarios/{id}/restore', [FuncionarioController::class, 'restore'])->name('funcionario.restore');
    Route::delete('funcionarios/{id}/forceDelete', [FuncionarioController::class, 'forceDelete'])->name('funcionario.forceDelete');
    Route::get('funcionarios/exportar', [FuncionarioController::class, 'exportar'])->name('funcionario.exportar');
    Route::get('funcionarios/plantilla', [FuncionarioController::class, 'plantilla'])->name('funcionario.plantilla');
    Route::get('funcionarios/importar', [FuncionarioController::class, 'importarVista'])->name('funcionario.importar');
    Route::post('funcionarios/importar', [FuncionarioController::class, 'importar'])->name('funcionario.importar.post');
    Route::resource('funcionarios', FuncionarioController::class)
           ->names('funcionario');
Route::get('funcionarios/plantilla-rut', [FuncionarioController::class, 'plantillaRut'])->name('funcionario.plantilla-rut');
    // Equipos
    Route::get('equipos/exportar', [EquipoController::class, 'exportar'])->name('equipo.exportar');
    Route::get('equipos/plantilla', [EquipoController::class, 'plantilla'])->name('equipo.plantilla');
    Route::get('equipos/importar', [EquipoController::class, 'importarVista'])->name('equipo.importar');
    Route::post('equipos/importar', [EquipoController::class, 'importar'])->name('equipo.importar.post');
    Route::get('equipos/papelera', [EquipoController::class, 'papelera'])->name('equipo.papelera');
    Route::patch('equipos/{id}/restore', [EquipoController::class, 'restore'])->name('equipo.restore');
    Route::delete('equipos/{id}/forceDelete', [EquipoController::class, 'forceDelete'])->name('equipo.forceDelete');
    Route::resource('equipos', EquipoController::class)
        ->except('show')
        ->names('equipo');

    // Auditoría
    Route::get('auditorias/exportar', [AuditoriaController::class, 'exportar'])->name('auditoria.exportar');
    Route::get('auditorias', [AuditoriaController::class, 'index'])->name('auditoria.index');

    // Inventario
    Route::get('inventario/exportar', [DetalleEquipoController::class, 'exportar'])->name('detalle-equipo.exportar');
    Route::get('inventario/papelera', [DetalleEquipoController::class, 'papelera'])->name('detalle-equipo.papelera');
    Route::patch('inventario/{id}/restore', [DetalleEquipoController::class, 'restore'])->name('detalle-equipo.restore');
    Route::delete('inventario/{id}/forceDelete', [DetalleEquipoController::class, 'forceDelete'])->name('detalle-equipo.forceDelete');
    Route::get('inventario/equipos-por-tipo', [DetalleEquipoController::class, 'equiposPorTipo'])->name('detalle-equipo.equiposPorTipo');
    Route::get('inventario/importar', [DetalleEquipoController::class, 'importarVista'])->name('detalle-equipo.importar');
    Route::post('inventario/importar', [DetalleEquipoController::class, 'importar'])->name('detalle-equipo.importar.post');
    Route::get('inventario/plantilla', [DetalleEquipoController::class, 'plantilla'])->name('detalle-equipo.plantilla');
    Route::resource('inventario', DetalleEquipoController::class)
        ->except('show')
        ->parameters(['inventario' => 'detalle_equipo'])
        ->names('detalle-equipo');

    // Asignaciones
    Route::get('asignaciones', [AsignacionController::class, 'index'])->name('asignacion.index');
    Route::post('asignaciones/asignar', [AsignacionController::class, 'asignar'])->name('asignacion.asignar');
    Route::delete('asignaciones/{id}/desasignar', [AsignacionController::class, 'desasignar'])->name('asignacion.desasignar');

    // Equipos asignados
    
    Route::get('equipos-asignados', [EquipoAsignadoController::class, 'index'])->name('equipo-asignado.index');
    Route::get('equipos-asignados/exportar', [EquipoAsignadoController::class, 'exportar'])->name('equipo-asignado.exportar');
    Route::get('equipos-asignados/{detalle_equipo}', [EquipoAsignadoController::class, 'show'])->name('equipo-asignado.show');
    Route::post('equipos-asignados/{detalle_equipo}/observaciones', [EquipoAsignadoController::class, 'storeObservacion'])->name('equipo-asignado.observacion.store');
    Route::delete('observaciones/{observacion}', [EquipoAsignadoController::class, 'destroyObservacion'])->name('observacion.destroy');
    Route::get('funcionarios/{funcionario}/transferir', [EquipoAsignadoController::class, 'transferirVista'])->name('equipo-asignado.transferir');
    Route::post('funcionarios/{funcionario}/transferir', [EquipoAsignadoController::class, 'transferir'])->name('equipo-asignado.transferir.post');


    // Edificios
    Route::get('edificios', [EdificioController::class, 'index'])->name('edificio.index');
    Route::post('edificios', [EdificioController::class, 'store'])->name('edificio.store');
    Route::put('edificios/{edificio}', [EdificioController::class, 'update'])->name('edificio.update');
    Route::delete('edificios/{edificio}', [EdificioController::class, 'destroy'])->name('edificio.destroy');

    // Pisos
    Route::get('pisos/{piso}', [PisoController::class, 'show'])->name('piso.show');
    Route::post('edificios/{edificio}/pisos', [PisoController::class, 'store'])->name('piso.store');
    Route::put('pisos/{piso}', [PisoController::class, 'update'])->name('piso.update');
    Route::delete('pisos/{piso}', [PisoController::class, 'destroy'])->name('piso.destroy');
    Route::get('pisos/{piso}/exportar', [PisoController::class, 'exportar'])->name('piso.exportar');
    // Puestos
    Route::post('pisos/{piso}/puestos', [PuestoController::class, 'store'])->name('puesto.store');
    Route::put('puestos/{puesto}', [PuestoController::class, 'update'])->name('puesto.update');
    Route::delete('puestos/{puesto}', [PuestoController::class, 'destroy'])->name('puesto.destroy');
    Route::patch('puestos/{puesto}/posicion', [PuestoController::class, 'guardarPosicion'])->name('puesto.posicion');
    Route::post('puestos/{puesto}/asignar', [PuestoController::class, 'asignar'])->name('puesto.asignar');
    Route::delete('puestos/{puesto}/desasignar', [PuestoController::class, 'desasignar'])->name('puesto.desasignar');
    Route::get('puestos/{puesto}/historial', [PuestoController::class, 'historial'])->name('puesto.historial');
    Route::patch('puestos/{puesto}/toggle', [PuestoController::class, 'toggle'])->name('puesto.toggle');

    // Documentos
    Route::get('equipos-asignados/{detalle_equipo}/acta', [DocumentoEquipoController::class, 'acta'])->name('documento.acta');
    Route::get('equipos-asignados/{detalle_equipo}/baja', [DocumentoEquipoController::class, 'baja'])->name('documento.baja');

    // Usuarios
    Route::get('usuarios', [UsuarioController::class, 'index'])->name('usuario.index');
    Route::post('usuarios', [UsuarioController::class, 'store'])->name('usuario.store');
    Route::put('usuarios/{usuario}', [UsuarioController::class, 'update'])->name('usuario.update');
    Route::patch('usuarios/{usuario}/password', [UsuarioController::class, 'cambiarPassword'])->name('usuario.password');
    Route::patch('usuarios/{usuario}/toggle', [UsuarioController::class, 'toggleActivo'])->name('usuario.toggle');
    Route::delete('usuarios/{usuario}', [UsuarioController::class, 'destroy'])->name('usuario.destroy');
    Route::post('usuarios/copiar-roles', [UsuarioController::class, 'copiarRoles'])->name('usuario.copiarRoles');
    Route::post('usuarios/masivo', [UsuarioController::class, 'asignarRolesMasivo'])->name('usuario.masivo');



    Route::get('dependencias', [DependenciaController::class, 'index'])->name('dependencia.index');
    Route::get('dependencias/exportar', [DependenciaController::class, 'exportar'])->name('dependencia.exportar');
    Route::post('dependencias/importar', [DependenciaController::class, 'importar'])->name('dependencia.importar');
    Route::get('dependencias/reporte', [DependenciaController::class, 'descargarReporte'])->name('dependencia.reporte');

    // Divisiones
    Route::post('divisiones', [DependenciaController::class, 'storeDivision'])->name('division.store');
    Route::put('divisiones/{division}', [DependenciaController::class, 'updateDivision'])->name('division.update');
    Route::delete('divisiones/{division}', [DependenciaController::class, 'destroyDivision'])->name('division.destroy');
    Route::patch('divisiones/{division}/toggle', [DependenciaController::class, 'toggleDivision'])->name('division.toggle');

    // Departamentos
    Route::post('departamentos', [DependenciaController::class, 'storeDepartamento'])->name('departamento.store');
    Route::put('departamentos/{departamento}', [DependenciaController::class, 'updateDepartamento'])->name('departamento.update');
    Route::delete('departamentos/{departamento}', [DependenciaController::class, 'destroyDepartamento'])->name('departamento.destroy');
    Route::patch('departamentos/{departamento}/toggle', [DependenciaController::class, 'toggleDepartamento'])->name('departamento.toggle');

    // Unidades
    Route::post('unidades', [DependenciaController::class, 'storeUnidad'])->name('unidad.store');
    Route::put('unidades/{unidad}', [DependenciaController::class, 'updateUnidad'])->name('unidad.update');
    Route::delete('unidades/{unidad}', [DependenciaController::class, 'destroyUnidad'])->name('unidad.destroy');
    Route::patch('unidades/{unidad}/toggle', [DependenciaController::class, 'toggleUnidad'])->name('unidad.toggle');

//Notifivaciones 

Route::get('notificaciones/contratos', [NotificacionController::class, 'contratosPorVencer'])->name('notificacion.contratos');
Route::get('interno/contador-contratos', [NotificacionController::class, 'contadorContratos'])->name('interno.contador-contratos');
    
Route::get('interno/equipos/buscar', [\App\Http\Controllers\ListaController::class, 'buscarEquipos']);

Route::patch('listas/{id}/restore', [ListaController::class, 'restore'])->name('lista.restore');
Route::post('documentos/baja-unida', [DocumentoEquipoController::class, 'bajaUnida'])->name('documento.baja-unida');

Route::get('equipos-asignados/{id}/baja', [DocumentoEquipoController::class, 'baja'])->name('documento.baja');

Route::get('equipos-asignados/{detalle_equipo}/acta', [DocumentoEquipoController::class, 'acta'])
    ->name('documento.acta')
    ->withTrashed();

    Route::get('listas/{lista}/exportar', [ListaController::class, 'exportar'])->name('lista.exportar');

    Route::get('inventario/papelera/exportar', [DetalleEquipoController::class, 'exportarPapelera'])->name('detalle-equipo.papelera.exportar');

Route::patch('equipos-asignados/{detalle_equipo}/hostname', [EquipoAsignadoController::class, 'updateHostname'])->name('equipo-asignado.hostname.update');


Route::get('interno/equipos-por-tipo', [DetalleEquipoController::class, 'equiposPorTipo'])->name('detalle-equipo.equiposPorTipo');

Route::post('asignaciones/importar-masivo', [AsignacionController::class, 'importarMasivo'])->name('asignacion.importar-masivo');
Route::get('asignaciones/plantilla-masivo', [AsignacionController::class, 'plantillaAsignacionMasiva'])->name('asignacion.plantilla-masivo');


Route::post('inventario/validar-masivo', [DetalleEquipoController::class, 'validarMasivo'])->name('detalle-equipo.validar-masivo');
Route::get('inventario/plantilla-validar-masivo', [DetalleEquipoController::class, 'plantillaValidarMasivo'])->name('detalle-equipo.plantilla-validar-masivo');

Route::post('listas/{lista}/importar-items', [ListaController::class, 'importarItems'])->name('lista.importar-items');

Route::delete('asignaciones/{id}/desasignar-web', [AsignacionController::class, 'desasignarWeb'])->name('asignacion.desasignar-web');
});