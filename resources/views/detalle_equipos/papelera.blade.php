@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0"><i class="fas fa-trash text-secondary me-2"></i>Papelera de Inventario</h3>
        <a href="{{ route('detalle-equipo.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>Volver
        </a>
    </div>

    {{-- Buscador --}}
<div class="card shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('detalle-equipo.papelera') }}" class="row g-2 align-items-end" id="form-filtros-papelera">
            <div class="col-12 col-md-4">
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-magnifying-glass"></i></span>
                    <input type="search" name="buscar" value="{{ $buscar ?? '' }}"
                           placeholder="Inventario, serial, marca, modelo..."
                           class="form-control" id="buscar-papelera">
                </div>
            </div>
            <div class="col-6 col-md-2">
                <input type="date" name="fecha_desde" value="{{ $fechaDesde ?? '' }}"
                       class="form-control" placeholder="Desde">
            </div>
            <div class="col-6 col-md-2">
                <input type="date" name="fecha_hasta" value="{{ $fechaHasta ?? '' }}"
                       class="form-control" placeholder="Hasta">
            </div>
            <div class="col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-filter"></i>
                </button>
                <a href="{{ route('detalle-equipo.papelera') }}" class="btn btn-outline-secondary w-100">
                    <i class="fas fa-xmark"></i>
                </a>
            </div>
            <div class="col-md-2">
                <a href="{{ route('detalle-equipo.papelera.exportar', request()->query()) }}"
                   class="btn btn-outline-success w-100">
                    <i class="fas fa-file-excel me-1"></i>Exportar
                </a>
            </div>
        </form>
    </div>
</div>

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>N°</th>
                        <th>Inventario</th>
                        <th>Serial</th>
                        <th>Tipo</th>
                        <th>Marca / Modelo</th>
                        <th>Estado</th>
                        <th>Eliminado el</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>{{ $num=1 }}
                    @forelse ($equipos as $equipo)
                   
                        <tr>
                            <td>{{$num++}}</td>
                            <td class="fw-medium">{{ $equipo->inventario }}</td>
                            <td class="text-secondary">{{ $equipo->serial }}</td>
                            <td>{{ $equipo->equipo->tipoEquipo->nombre ?? '—' }}</td>
                            <td>{{ $equipo->equipo->marca ?? '—' }} {{ $equipo->equipo->modelo ?? '—' }}</td>
                            <td>
                                <span class="badge bg-{{ $equipo->estado->color }}-subtle text-{{ $equipo->estado->color }}-emphasis border border-{{ $equipo->estado->color }}-subtle">
                                    {{ $equipo->estado->nombre }}
                                </span>
                            </td>
                            <td>{{ $equipo->deleted_at->format('d-m-Y H:i') }}</td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    {{-- Acta de baja --}}
                                   <button type="button" class="btn btn-outline-warning btn-acta-baja"
        data-id="{{ $equipo->id }}"
        data-inventario="{{ $equipo->inventario }}"
        data-detalle="{{ $equipo->detalle_baja ?? '' }}"
        title="Acta de baja">
    <i class="fas fa-file-word"></i>
</button>
                                    @can('detalle_equipos.restaurar')
                                        <button type="submit" form="rest-{{ $equipo->id }}"
                                                class="btn btn-outline-success" title="Restaurar">
                                            <i class="fas fa-rotate-left"></i>
                                        </button>
                                    @endcan
                                    @can('detalle_equipos.eliminar')
                                        <button type="submit" form="perm-{{ $equipo->id }}"
                                                class="btn btn-outline-danger" title="Eliminar permanente">
                                            <i class="fas fa-trash-can"></i>
                                        </button>
                                    @endcan
                                </div>
                                @can('detalle_equipos.restaurar')
                                    <form id="rest-{{ $equipo->id }}" method="POST"
                                          action="{{ route('detalle-equipo.restore', $equipo->id) }}" class="d-none">
                                        @csrf @method('PATCH')
                                    </form>
                                @endcan
                                @can('detalle_equipos.eliminar')
                                    <form id="perm-{{ $equipo->id }}" method="POST"
                                          action="{{ route('detalle-equipo.forceDelete', $equipo->id) }}" class="d-none"
                                          onsubmit="return confirm('¿Eliminar permanentemente {{ $equipo->inventario }}?')">
                                        @csrf @method('DELETE')
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-secondary py-5">
                                <i class="fas fa-box-open fa-2x mb-2 d-block"></i>
                                La papelera está vacía.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($equipos->hasPages())
           <div class="card-footer bg-body-tertiary" id="paginacion">
    {{ $equipos->appends(request()->query())->links() }}
</div>
        @endif
    </div>
</div>

{{-- Modal acta de baja --}}
<div class="modal fade" id="modal-acta-baja" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-file-word text-warning me-2"></i>Acta de Baja
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-secondary small mb-3">
                    Equipo: <strong id="modal-baja-inventario"></strong>
                </p>
                <div class="mb-3">
                    <label class="form-label">Detalle de la baja <small class="text-muted">(opcional)</small></label>
                    <textarea id="modal-baja-detalle" class="form-control" rows="3"
                              placeholder="Motivo de baja, observaciones..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-warning" id="btn-descargar-acta">
                    <i class="fas fa-download me-1"></i>Descargar acta
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script src="{{ asset('js/inventario_papelera.js') }}"></script>
@endpush

@endsection