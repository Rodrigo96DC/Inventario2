<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'DTG Inventario') }}</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">

    @stack('estilos')
</head>
<body>
    <script>
        // Aplica el tema guardado ANTES de pintar la página, para evitar el "flash" del tema incorrecto
        (function () {
            const tema = localStorage.getItem('tema') || 'light';
            document.documentElement.setAttribute('data-bs-theme', tema);
        })();
    </script>

    <nav class="navbar navbar-expand-md bg-body-tertiary border-bottom shadow-sm">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold" href="{{ route('home') }}">
            <i class="fas fa-boxes-stacked text-primary me-2"></i>{{ config('app.name', 'DTG Inventario') }}
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarContent">
            <ul class="navbar-nav ms-auto align-items-md-center gap-2">

                @auth
                    <li class="nav-item dropdown">
                        <a class="nav-link position-relative" href="#" role="button" data-bs-toggle="dropdown" id="campana-notificaciones">
                            <i class="fas fa-bell"></i>
                            <span id="badge-notificaciones" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger d-none">
                                0
                            </span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" style="min-width:320px;" id="dropdown-notificaciones">
                            <li><h6 class="dropdown-header"><i class="fas fa-calendar-xmark me-1"></i>Contratos por vencer</h6></li>
                            <li id="lista-notificaciones">
                                <span class="dropdown-item-text text-secondary small">Cargando...</span>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item text-center text-primary small" href="{{ route('notificacion.contratos') }}">
                                    Ver todos
                                </a>
                            </li>
                        </ul>
                    </li>
                @endauth

                <li class="nav-item">
                    <button id="toggle-tema" type="button" class="btn btn-sm btn-outline-secondary" title="Cambiar tema">
                        <i class="fas fa-moon"></i>
                    </button>
                </li>
                @auth
                    <li class="nav-item dropdown">
                        <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-circle-user me-1"></i>{{ Auth::user()->name }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a class="dropdown-item" href="{{ route('logout') }}"
                                   onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                    Cerrar sesión
                                </a>
                                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                    @csrf
                                </form>
                            </li>
                        </ul>
                    </li>
                @else
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('login') }}">Iniciar sesión</a>
                    </li>
                @endauth
            </ul>
        </div>
    </div>
</nav>

    <div class="d-flex">
        @auth
            <aside class="sidebar d-none d-md-flex flex-column flex-shrink-0 p-3 bg-body-tertiary">
                <ul class="nav nav-pills flex-column gap-1">
                    <li class="nav-item">
                        <a href="{{ route('home') }}" class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}">
                            <i class="fas fa-gauge me-2"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('funcionario.index') }}" class="nav-link {{ request()->routeIs('funcionario.*') ? 'active' : '' }}">
                            <i class="fas fa-users me-2"></i>Funcionarios
                        </a>
                    </li>
<li class="nav-item">
    <a href="{{ route('equipo.index') }}" class="nav-link {{ request()->routeIs('equipo.*') ? 'active' : '' }}">
        <i class="fas fa-laptop me-2"></i>Equipos
    </a>
</li>
<li class="nav-item">
    <a href="{{ route('detalle-equipo.index') }}" class="nav-link {{ request()->routeIs('detalle-equipo.*') ? 'active' : '' }}">
        <i class="fas fa-desktop me-2"></i>Inventario
    </a>
</li>
<li class="nav-item">
    <a href="{{ route('asignacion.index') }}" class="nav-link {{ request()->routeIs('asignacion.*') ? 'active' : '' }}">
        <i class="fas fa-link me-2"></i>Asignaciones
    </a>
</li>
<li class="nav-item">
    <a href="{{ route('equipo-asignado.index') }}" class="nav-link {{ request()->routeIs('equipo-asignado.*') ? 'active' : '' }}">
        <i class="fas fa-laptop-user me-2"></i>Asignados
    </a>
</li>
<li class="nav-item">
    <a href="{{ route('lista.index') }}" class="nav-link {{ request()->routeIs('lista.*') ? 'active' : '' }}">
    <i class="fas fa-list-check me-2"></i>Listas
</a>
</li>

<li class="nav-item">
    <a href="{{ route('edificio.index') }}" class="nav-link {{ request()->routeIs('edificio.*') || request()->routeIs('piso.*') || request()->routeIs('puesto.*') ? 'active' : '' }}">
        <i class="fas fa-building me-2"></i>Puestos
    </a>
</li>
<a href="{{ route('dependencia.index') }}" class="nav-link {{ request()->routeIs('dependencia.*') ? 'active' : '' }}">
    <i class="fas fa-sitemap me-2"></i>Dependencias
</a>
<li class="nav-item">
    <a href="{{ route('usuario.index') }}" class="nav-link {{ request()->routeIs('usuario.*') ? 'active' : '' }}">
        <i class="fas fa-users-gear me-2"></i>Usuarios
    </a>
</li>
<li class="nav-item">
    <a href="{{ route('auditoria.index') }}" class="nav-link {{ request()->routeIs('auditoria.*') ? 'active' : '' }}">
        <i class="fas fa-clock-rotate-left me-2"></i>Auditoría
    </a>
</li>
                    {{-- Próximos módulos: Equipos, Asignaciones, Insumos, etc. se agregan aquí --}}
                </ul>
            </aside>
        @endauth

        <main class="main-content p-3 p-md-4">
            @if (session('info'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('info') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

   <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/app.js') }}"></script>
<script src="{{ asset('js/notificaciones.js') }}"></script>
    @stack('scripts')
</body>
</html>