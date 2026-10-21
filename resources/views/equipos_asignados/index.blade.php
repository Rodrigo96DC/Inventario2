@extends('layouts.app')

@push('scripts')
    <script src="{{ asset('js/equipos_asignados.js') }}"></script>
@endpush

@section('content')
<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <h3 class="mb-0">
            <i class="fas fa-laptop-user text-primary me-2"></i>Equipos Asignados
            <span class="badge bg-secondary fs-6 align-middle" id="badge-total">{{ $asignaciones->total() }}</span>
        </h3>
 <a href="{{ route('equipo-asignado.exportar', request()->query()) }}" class="btn btn-success btn-sm">
    <i class="fas fa-file-excel me-1"></i>Exportar
</a>
    </div>

    <div class="card shadow-sm mb-3">
        <div class="card-body py-2">
            <form method="GET" action="{{ route('equipo-asignado.index') }}"
                  id="form-filtros" class="row g-2 align-items-end">
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-magnifying-glass"></i></span>
                        <input type="search" name="buscar" id="buscar" value="{{ $buscar }}"
                            placeholder="Funcionario, inventario, serial..."
                            class="form-control">
                    </div>
                </div>
                <div class="col-6 col-md-2">
                    <select name="tipo_id" class="form-select">
                        <option value="">Todo tipo</option>
                        @foreach ($tipoEquipos as $tipo)
                            <option value="{{ $tipo->id }}" @selected(request('tipo_id') == $tipo->id)>
                                {{ $tipo->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <select name="estado_id" class="form-select">
                        <option value="">Todos los estados</option>
                        @foreach ($estadoEquipos as $estado)
                            <option value="{{ $estado->id }}" @selected(request('estado_id') == $estado->id)>
                                {{ $estado->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-3">
                    <select name="region_id" class="form-select">
                        <option value="">Todas las regiones</option>
                        @foreach ($regiones as $region)
                            <option value="{{ $region->id }}" @selected(request('region_id') == $region->id)>
                                {{ $region->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2 d-flex gap-1">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter"></i>
                    </button>
                    <a href="{{ route('equipo-asignado.index') }}" class="btn btn-outline-secondary w-100">
                        <i class="fas fa-xmark"></i>
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
                        <th>Imagen</th>
                        <th>Funcionario</th>
                        <th>Tipo</th>
                        <th>Marca / Modelo</th>
                        <th>Inventario</th>
                        <th>Hostname</th>
                        <th>Estado</th>
                        <th>Región</th>
                        <th>Fecha Asig.</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($asignaciones as $asignacion)
                        <tr>
                            <td>
                                @if ($asignacion->detalleEquipo->equipo?->imagen)
                                    <img src="{{ asset('storage/' . $asignacion->detalleEquipo->equipo->imagen) }}"
                                         class="rounded" width="40" height="40" style="object-fit:cover;">
                                @else
                                    <div class="rounded bg-secondary-subtle d-flex align-items-center justify-content-center"
                                         style="width:40px;height:40px;">
                                        <i class="fas fa-desktop text-secondary small"></i>
                                    </div>
                                @endif
                            </td>
                            <td>
                                <div class="fw-medium">{{ $asignacion->funcionario->nombre ?? '—' }}</div>
                                <small class="text-secondary">{{ $asignacion->funcionario->rut_formateado ?? '' }}</small>
                            </td>
                            <td>
                                <span class="badge bg-primary-subtle text-primary-emphasis border border-primary-subtle">
                                    {{ $asignacion->detalleEquipo->equipo->tipoEquipo->nombre ?? '—' }}
                                </span>
                            </td>
                            <td>
                                <div>{{ $asignacion->detalleEquipo->equipo->marca ?? '—' }}</div>
                                <small class="text-secondary">{{ $asignacion->detalleEquipo->equipo->modelo ?? '—' }}</small>
                            </td>
                            <td class="fw-medium">{{ $asignacion->detalleEquipo->inventario }}</td>
                            <td class="text-secondary small">{{ $asignacion->hostname ?? '—' }}</td>
                            <td>
                                @php $estado = $asignacion->detalleEquipo->estado; @endphp
                                <span class="badge bg-{{ $estado->color }}-subtle text-{{ $estado->color }}-emphasis border border-{{ $estado->color }}-subtle">
                                    {{ $estado->nombre ?? '—' }}
                                </span>
                            </td>
                            <td class="text-secondary small">{{ $asignacion->funcionario->region->nombre ?? '—' }}</td>
                            <td class="text-secondary small text-nowrap">{{ $asignacion->fecha_asignacion?->format('d-m-Y') ?? '—' }}</td>
                 <td class="text-end">
    <div class="btn-group btn-group-sm">
        <a href="{{ route('equipo-asignado.show', $asignacion->detalleEquipo) }}"
           class="btn btn-outline-primary" title="Ver detalle">
            <i class="fas fa-eye"></i>
        </a>
        <a href="{{ route('equipo-asignado.transferir', $asignacion->funcionario) }}"
           class="btn btn-outline-warning" title="Transferir equipos">
            <i class="fas fa-right-left"></i>
        </a>
   <form method="POST" action="{{ route('asignacion.desasignar-web', $asignacion->detalle_equipo_id) }}"
      class="d-inline"
      onsubmit="return confirm('¿Desasignar {{ addslashes($asignacion->detalleEquipo->inventario) }} de {{ addslashes($asignacion->funcionario->nombre) }}?')">
    @csrf @method('DELETE')
    <button type="submit" class="btn btn-outline-danger" title="Desasignar">
        <i class="fas fa-link-slash"></i>
    </button>
</form>
    </div>
</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center text-secondary py-5">
                                <i class="fas fa-folder-open fa-2x mb-2 d-block"></i>
                                No hay equipos asignados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($asignaciones->hasPages())
            <div id="paginacion" class="card-footer bg-body-tertiary">
                {{ $asignaciones->links() }}
            </div>
        @endif
    </div>
</div>
@endsection