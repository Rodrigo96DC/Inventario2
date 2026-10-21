@extends('layouts.app')

@push('estilos')
    <link rel="stylesheet" href="{{ asset('css/equipos.css') }}">
@endpush

@push('scripts')
    <script src="{{ asset('js/equipos.js') }}"></script>
@endpush

@section('content')
    <div class="container-fluid">

        {{-- Encabezado --}}
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <h3 class="mb-0">
                <i class="fas fa-laptop text-primary me-2"></i>Equipos
                <span class="badge bg-secondary fs-6 align-middle">{{ $equipos->total() }}</span>
            </h3>

            <div class="d-flex gap-2 flex-wrap">
                @can('equipos.crear')
                    <a href="{{ route('equipo.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i>Nuevo equipo
                    </a>
                @endcan
                @can('equipos.eliminar')
                    <a href="{{ route('equipo.papelera') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-trash me-1"></i>Papelera
                    </a>
                @endcan
                @can('equipos.crear')
                    <a href="{{ route('equipo.importar') }}" class="btn btn-outline-success">
                        <i class="fas fa-file-import me-1"></i>Carga masiva
                    </a>
                @endcan
                <a href="{{ route('equipo.exportar') }}" class="btn btn-outline-success">
                    <i class="fas fa-file-excel me-1"></i>Exportar
                </a>
            </div>
        </div>

        {{-- Barra de herramientas --}}
        <div class="card shadow-sm mb-3">
            <div class="card-body py-2">
                <div class="d-flex flex-wrap align-items-center gap-2">

                    {{-- Buscador --}}
                    <form method="GET" action="{{ route('equipo.index') }}" class="flex-grow-1" style="max-width: 360px;">
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-magnifying-glass"></i></span>
                            <input type="search" name="buscar" id="buscar" value="{{ $buscar }}"
                                placeholder="Buscar por marca, modelo, tipo..." class="form-control">
                        </div>
                    </form>

                    <div class="ms-auto d-flex gap-2 align-items-center flex-wrap">
                        {{-- Tamaño cards --}}
                        <div class="btn-group btn-group-sm" id="vista-grid-solo" title="Tamaño de tarjetas">
                            <button class="btn btn-outline-secondary" data-card-size="sm" title="Pequeño">
                                <i class="fas fa-grip"></i>
                            </button>
                            <button class="btn btn-outline-secondary" data-card-size="md" title="Mediano">
                                <i class="fas fa-table-cells-large"></i>
                            </button>
                            <button class="btn btn-outline-secondary" data-card-size="lg" title="Grande">
                                <i class="fas fa-square-full"></i>
                            </button>
                        </div>

                        {{-- Toggle vista --}}
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-secondary" id="vista-grid" title="Vista tarjetas">
                                <i class="fas fa-table-cells-large"></i>
                            </button>
                            <button class="btn btn-outline-secondary" id="vista-tabla" title="Vista tabla">
                                <i class="fas fa-table-list"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Vista GRID --}}
        <div id="contenedor-grid">
            <div class="row g-3" id="grid-equipos">
                @forelse ($equipos as $equipo)
                    <div class="equipo-col col-12 col-sm-6 col-md-4 col-lg-3" style="z-index:1;">
                        <div class="card h-100 shadow-sm" style="overflow: visible;">

                            @if ($equipo->imagen)
                           <div class="card-img-wrapper">
    <img src="{{ asset('storage/' . $equipo->imagen) }}"
         class="equipo-img-zoom"
         alt="{{ $equipo->marca }} {{ $equipo->modelo }}">
</div>
                            @else
                                <div
                                    class="card-img-wrapper d-flex align-items-center justify-content-center bg-secondary-subtle">
                                    <i class="fas fa-image fa-2x text-secondary"></i>
                                </div>
                            @endif

                            <div class="card-body">
                                <span
                                    class="badge bg-primary-subtle text-primary-emphasis border border-primary-subtle mb-2">
                                    {{ $equipo->tipoEquipo->nombre ?? '—' }}
                                </span>
                                <h6 class="card-title mb-1">{{ $equipo->marca }}</h6>
                                <p class="card-text text-secondary mb-2">{{ $equipo->modelo }}</p>

                                @if ($equipo->procesador)
                                    <p class="card-text small text-secondary mb-0">
                                        <i class="fas fa-microchip me-1"></i>{{ $equipo->procesador }}
                                    </p>
                                @endif
                                @if ($equipo->entradas)
                                    <p class="card-text small text-secondary mb-0">
                                        <i class="fas fa-plug me-1"></i>{{ $equipo->entradas }}
                                    </p>
                                @endif
                            </div>

                            <div class="card-footer bg-transparent d-flex justify-content-end gap-2">
                                @can('equipos.editar')
                                    <a href="{{ route('equipo.edit', $equipo) }}" class="btn btn-sm btn-outline-primary"
                                        title="Editar">
                                        <i class="fas fa-pen"></i>
                                    </a>
                                @endcan
                                @can('equipos.eliminar')
                                    <button type="submit" form="del-{{ $equipo->id }}" class="btn btn-sm btn-outline-danger"
                                        title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                @endcan
                            </div>
                        </div>

                        @can('equipos.eliminar')
                            <form id="del-{{ $equipo->id }}" method="POST" action="{{ route('equipo.destroy', $equipo) }}"
                                class="d-none"
                                onsubmit="return confirm('¿Mover a la papelera {{ addslashes($equipo->marca . ' ' . $equipo->modelo) }}?')">
                                @csrf @method('DELETE')
                            </form>
                        @endcan
                    </div>
                @empty
                    <div class="col-12">
                        <div class="card shadow-sm">
                            <div class="card-body text-center text-secondary py-5">
                                <i class="fas fa-folder-open fa-2x mb-2 d-block"></i>
                                No hay equipos que mostrar.
                            </div>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Vista TABLA --}}
        <div id="contenedor-tabla" class="d-none">
            <div class="card shadow-sm">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Imagen</th>
                                <th>Tipo</th>
                                <th>Marca</th>
                                <th>Modelo</th>
                                <th>Procesador</th>
                                <th>Entradas</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($equipos as $equipo)
                                <tr>
                                    <td>
                                        @if ($equipo->imagen)
                                            <img src="{{ asset('storage/' . $equipo->imagen) }}"
                                                alt="{{ $equipo->modelo }}" class="rounded" width="48"
                                                height="48" style="object-fit: cover;">
                                        @else
                                            <div class="rounded bg-secondary-subtle d-flex align-items-center justify-content-center"
                                                style="width: 48px; height: 48px;">
                                                <i class="fas fa-image text-secondary"></i>
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <span
                                            class="badge bg-primary-subtle text-primary-emphasis border border-primary-subtle">
                                            {{ $equipo->tipoEquipo->nombre ?? '—' }}
                                        </span>
                                    </td>
                                    <td class="fw-medium">{{ $equipo->marca }}</td>
                                    <td>{{ $equipo->modelo }}</td>
                                    <td class="text-secondary small">{{ $equipo->procesador ?? '—' }}</td>
                                    <td class="text-secondary small">{{ $equipo->entradas ?? '—' }}</td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm">
                                            @can('equipos.editar')
                                                <a href="{{ route('equipo.edit', $equipo) }}" class="btn btn-outline-primary"
                                                    title="Editar">
                                                    <i class="fas fa-pen"></i>
                                                </a>
                                            @endcan
                                            @can('equipos.eliminar')
                                                <button type="submit" form="del-tabla-{{ $equipo->id }}"
                                                    class="btn btn-outline-danger" title="Eliminar">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            @endcan
                                        </div>
                                        @can('equipos.eliminar')
                                            <form id="del-tabla-{{ $equipo->id }}" method="POST"
                                                action="{{ route('equipo.destroy', $equipo) }}" class="d-none"
                                                onsubmit="return confirm('¿Mover a la papelera {{ addslashes($equipo->marca . ' ' . $equipo->modelo) }}?')">
                                                @csrf @method('DELETE')
                                            </form>
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-secondary py-5">
                                        <i class="fas fa-folder-open fa-2x mb-2 d-block"></i>
                                        No hay equipos que mostrar.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Paginación --}}
        @if ($equipos->hasPages())
            <div class="py-3">{{ $equipos->links() }}</div>
        @endif

    </div>
@endsection
