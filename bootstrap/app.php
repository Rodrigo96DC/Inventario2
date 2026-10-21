<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(function () {
        \Illuminate\Support\Facades\Route::middleware('api')
            ->prefix('api')
            ->group(base_path('routes/api.php'));

        \Illuminate\Support\Facades\Route::middleware('web')
            ->group(base_path('routes/web.php'));
    })
 ->withMiddleware(function (Middleware $middleware) {
    // Quita statefulApi() si lo agregaste
    // y agrega esto en su lugar:
    $middleware->alias([
        'role'               => \Spatie\Permission\Middleware\RoleMiddleware::class,
        'permission'         => \Spatie\Permission\Middleware\PermissionMiddleware::class,
        'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        'activo'             => \App\Http\Middleware\VerificarUsuarioActivo::class,
    ]);
    // $middleware->appendToGroup('web', \App\Http\Middleware\VerificarUsuarioActivo::class);
})
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
