@extends('layouts.app')

@push('estilos')
    <link rel="stylesheet" href="{{ asset('css/detalle_equipos.css') }}">
@endpush

@push('scripts')
    <script src="{{ asset('js/detalle_equipos.js') }}"></script>
@endpush

@section('content')
<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <h3 class="mb-0">
            <i class="fas fa-desktop text-primary me-2"></i>Equipos sin Asignar
            <span class="badge bg-secondary fs-6 align-middle" id="badge-total">{{ $equipos->total() }}</span>
        </h3>
        <div class="d-flex gap-2 flex-wrap">
            @can('detalle_equipos.crear')
                <a href="{{ route('detalle-equipo.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i>Registrar equipo
                </a>
            @endcan
            <a href="{{ route('detalle-equipo.exportar', request()->query()) }}"
               id="btn-exportar" data-url="{{ route('detalle-equipo.exportar') }}"
               class="btn btn-outline-success">
                <i class="fas fa-file-excel me-1"></i>Exportar
            </a>
            @can('detalle_equipos.crear')
    <a href="{{ route('detalle-equipo.importar') }}" class="btn btn-outline-success">
        <i class="fas fa-file-import me-1"></i>Carga masiva
    </a>
@endcan

<button type="button" class="btn btn-outline-info btn-sm"
        data-bs-toggle="modal" data-bs-target="#modal-validar-masivo">
    <i class="fas fa-magnifying-glass me-1"></i>Validar masivo
</button>
            @can('detalle_equipos.eliminar')
                <a href="{{ route('detalle-equipo.papelera') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-trash me-1"></i>Papelera
                </a>
            @endcan
        </div>
    </div>
{{-- Equipos encontrados en asignados --}}
@if (isset($equiposAsignados) && $equiposAsignados->isNotEmpty())
    <div class="alert alert-info mb-3">
        <h6 class="alert-heading">
            <i class="fas fa-info-circle me-1"></i>
            No encontrado en inventario sin asignar — encontrado en equipos asignados:
        </h6>
        <ul class="mb-0">
            @foreach ($equiposAsignados as $eq)
                <li>
                    <strong>{{ $eq->equipo->marca ?? '—' }} {{ $eq->equipo->modelo ?? '—' }}</strong>
                    — Inventario: <strong>{{ $eq->inventario }}</strong>
                    — Asignado a: <strong>{{ $eq->asignacionActual->funcionario->nombre ?? '—' }}</strong>
                    <a href="{{ route('equipo-asignado.show', $eq) }}" class="btn btn-sm btn-outline-info ms-2" target="_blank">
                        <i class="fas fa-eye me-1"></i>Ver equipo
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
@endif
    {{-- Filtros --}}
    <div class="card shadow-sm mb-3">
        <div class="card-body py-2">
            <form method="GET" action="{{ route('detalle-equipo.index') }}" id="form-filtros" class="row g-2 align-items-end">
                <div class="col-12 col-sm-6 col-md-4">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-magnifying-glass"></i></span>
                        <input type="search" name="buscar" id="buscar" value="{{ $buscar }}"
                            placeholder="Inventario, serial, marca, modelo..."
                            class="form-control">
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <select name="tipo_id" id="tipo_id" class="form-select">
                        <option value="">Todo tipo de equipo</option>
                        @foreach ($tipoEquipos as $tipo)
                            <option value="{{ $tipo->id }}" @selected(request('tipo_id') == $tipo->id)>
                                {{ $tipo->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-3">
                    <select name="estado_id" id="estado_id" class="form-select">
                        <option value="">Todos los estados</option>
                        @foreach ($estadoEquipos as $estado)
                            <option value="{{ $estado->id }}" @selected(request('estado_id') == $estado->id)>
                                {{ $estado->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-1">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter"></i>
                    </button>
                    <a href="{{ route('detalle-equipo.index') }}" class="btn btn-outline-secondary w-100">
                        <i class="fas fa-xmark"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Grid de tarjetas --}}

    <div class="row g-3" id="contenedor-inventario">
        @forelse ($equipos as $equipo)
            <div class="col-12 col-sm-6 col-md-4 col-lg-3" style="z-index:1;" >
                <div class="card h-100 shadow-sm" style="overflow:visible;" id="contenedor-inventario">
                    @if ($equipo->equipo?->imagen)
                     <div class="card-img-wrapper">
    <img src="{{ asset('storage/' . $equipo->equipo->imagen) }}"
         class="equipo-img-zoom"
         alt="{{ $equipo->equipo->modelo }}">
</div>
                    @else
                        <div class="card-img-wrapper d-flex align-items-center justify-content-center bg-secondary-subtle">
                            <i class="fas fa-desktop fa-2x text-secondary"></i>
                        </div>
                    @endif

                    <div class="card-body pb-1">
                        <div class="d-flex justify-content-between align-items-start mb-1">
                            <span class="badge bg-primary-subtle text-primary-emphasis border border-primary-subtle">
                                {{ $equipo->equipo->tipoEquipo->nombre ?? '—' }}
                            </span>
                            <span class="badge bg-{{ $equipo->estado->color }}-subtle text-{{ $equipo->estado->color }}-emphasis border border-{{ $equipo->estado->color }}-subtle">
                                {{ $equipo->estado->nombre ?? '—' }}
                            </span>
                        </div>

                        <h6 class="card-title mb-0 text-uppercase">
                            {{ $equipo->equipo->marca ?? '—' }}
                        </h6>
                        <p class="card-text text-secondary small mb-1">{{ $equipo->equipo->modelo ?? '—' }}</p>

                        <ul class="list-unstyled small text-secondary mb-0">
                            <li><i class="fas fa-barcode me-1"></i>{{ $equipo->inventario }}</li>
                            <li><i class="fas fa-fingerprint me-1"></i>{{ $equipo->serial }}</li>
                            @if ($equipo->equipo?->procesador)
                                <li><i class="fas fa-microchip me-1"></i>{{ $equipo->equipo->procesador }}</li>
                            @endif
                            @if ($equipo->ram_gb)
                                <li><i class="fas fa-memory me-1"></i>{{ $equipo->ram_gb }} GB RAM</li>
                            @endif
                            @if ($equipo->capacidad_disco_gb)
                                <li>
                                    <i class="fas fa-hard-drive me-1"></i>
                                    {{ $equipo->capacidad_disco_gb >= 1000
                                        ? number_format($equipo->capacidad_disco_gb / 1000, 1) . ' TB'
                                        : $equipo->capacidad_disco_gb . ' GB' }}
                                    {{ $equipo->tipoDisco->nombre ?? '' }}
                                </li>
                            @endif
                        </ul>
                    </div>

                    <div class="card-footer bg-transparent small text-secondary border-top-0 pt-0">
                        Actualizado: {{ $equipo->updated_at->format('d-m-Y') }}
                    </div>
<div class="card-footer bg-transparent d-flex justify-content-end gap-2">
    <a href="{{ route('asignacion.index', ['buscar' => $equipo->inventario]) }}"
       class="btn btn-sm btn-outline-success" title="Asignar">
        <i class="fas fa-link"></i>
    </a>
    @can('detalle_equipos.editar')
        <a href="{{ route('detalle-equipo.edit', $equipo) }}"
           class="btn btn-sm btn-outline-primary" title="Editar">
            <i class="fas fa-pen"></i>
        </a>
    @endcan
    @can('detalle_equipos.eliminar')
        <button type="submit" form="del-{{ $equipo->id }}"
                class="btn btn-sm btn-outline-danger" title="Eliminar">
            <i class="fas fa-trash"></i>
        </button>
    @endcan
</div>
                </div>

                @can('detalle_equipos.eliminar')
                    <form id="del-{{ $equipo->id }}" method="POST"
                          action="{{ route('detalle-equipo.destroy', $equipo) }}" class="d-none"
                          onsubmit="return confirm('¿Mover a la papelera el inventario {{ $equipo->inventario }}?')">
                        @csrf @method('DELETE')
                    </form>
                @endcan
            </div>
        @empty
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-body text-center text-secondary py-5">
                        <i class="fas fa-folder-open fa-2x mb-2 d-block"></i>
                        No hay equipos sin asignar.
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    @if ($equipos->hasPages())
        <div class="py-3" id="paginacion">{{ $equipos->links() }}</div>
    @endif
</div>




{{-- Modal validar masivo --}}
<div class="modal fade" id="modal-validar-masivo" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-magnifying-glass me-2"></i>Validación Masiva
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('detalle-equipo.validar-masivo') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <p class="text-secondary small mb-3">
                        Carga un Excel con columnas <strong>inventario</strong> y/o <strong>serial</strong>.
                        Se descargará un Excel con el estado de cada equipo.
                    </p>
                    <div class="mb-3">
                        <input type="file" name="archivo" accept=".xlsx,.xls,.csv"
                               class="form-control" required>
                    </div>
                    <a href="{{ route('detalle-equipo.plantilla-validar-masivo') }}"
                       class="btn btn-outline-success btn-sm">
                        <i class="fas fa-file-excel me-1"></i>Descargar plantilla
                    </a>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-magnifying-glass me-1"></i>Validar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection