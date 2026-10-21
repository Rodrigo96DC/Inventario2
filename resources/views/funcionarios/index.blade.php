@extends('layouts.app')
@push('estilos')
    <link rel="stylesheet" href="{{ asset('css/funcionarios.css') }}">
@endpush

@push('scripts')
    <script src="{{ asset('js/funcionarios.js') }}"></script>
@endpush
@section('content')
    <div class="container-fluid">

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <h3 class="mb-0">
                <i class="fas fa-users text-primary me-2"></i>Funcionarios
                <span class="badge bg-secondary fs-6 align-middle">{{ $funcionarios->total() }}</span>
            </h3>

            <div class="d-flex gap-2">
                @can('funcionarios.crear')
                    <a href="{{ route('funcionario.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i>Nuevo funcionario
                    </a>
                    <a href="{{ route('funcionario.importar-rut') }}" class="btn btn-outline-info btn-sm">
                        <i class="fas fa-id-card me-1"></i>Actualizar RUT/Datos
                    </a>
                    <a href="{{ route('funcionario.importar-correos') }}" class="btn btn-outline-info btn-sm">
                        <i class="fas fa-envelope me-1"></i>Importar correos
                    </a>
                @endcan

                @can('funcionarios.crear')
                    <a href="{{ route('funcionario.importar') }}" class="btn btn-outline-success">
                        <i class="fas fa-file-import me-1"></i>Carga masiva
                    </a>
   
                @endcan
               <a href="{{ route('funcionario.exportar', request()->query()) }}" class="btn btn-outline-success">
    <i class="fas fa-file-excel me-1"></i>Exportar
</a>

                @can('funcionarios.eliminar')
                    <a href="{{ route('funcionario.papelera') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-trash me-1"></i>Papelera
                    </a>
                @endcan
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header bg-body-tertiary">
                <form method="GET" action="{{ route('funcionario.index') }}" id="form-filtros" class="row g-2">
                    <div class="col-sm-6 col-md-3">
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-magnifying-glass"></i></span>
                            <input type="search" name="buscar" value="{{ $buscar }}"
                                placeholder="Nombre, RUT, cargo..." class="form-control" id="buscar">
                        </div>
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <select name="division_id" class="form-select">
                            <option value="">Todas las divisiones</option>
                            @foreach ($divisiones as $div)
                                <option value="{{ $div->id }}" @selected($divisionId == $div->id)>
                                    {{ $div->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-sm-6 col-md-2">
                        <select name="region_id" class="form-select">
                            <option value="">Todas las regiones</option>
                            @foreach ($regiones as $reg)
                                <option value="{{ $reg->id }}" @selected($regionId == $reg->id)>
                                    {{ $reg->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-sm-6 col-md-2">
                        <select name="estado" class="form-select">
                            <option value="">Todos los estados</option>
                            <option value="1" @selected($estadoId === '1')>Activo</option>
                            <option value="0" @selected($estadoId === '0')>Inactivo</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex gap-1">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-filter"></i>
                        </button>
                        <a href="{{ route('funcionario.index') }}" class="btn btn-outline-secondary w-100">
                            <i class="fas fa-xmark"></i>
                        </a>
                    </div>
                </form>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width:40px;">
    <input type="checkbox" id="check-todos" class="form-check-input">
</th>
                            <th>Nombre</th>
                            <th>RUT</th>
                            <th style="max-width:150px;">Región</th>
                            <th style="max-width:200px;">División</th>
                            <th>Cargo</th>
                            <th>Puesto</th>
                            <th class="text-center">Estado</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($funcionarios as $funcionario)
                            <tr>
                                <td>
    <input type="checkbox" class="form-check-input check-funcionario" 
           value="{{ $funcionario->id }}">
</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                     @if ($funcionario->avatar)
    <img src="{{ asset('storage/' . $funcionario->avatar) }}"
        alt="{{ $funcionario->nombre }}" class="rounded-circle" width="36"
        height="36" style="object-fit: cover;">
@else
    @php
        $partes    = explode(' ', trim($funcionario->nombre));
        $inicial1  = strtoupper(substr($partes[0] ?? '', 0, 1));
        $inicial2  = strtoupper(substr($partes[2] ?? $partes[1] ?? '', 0, 1));
        $iniciales = $inicial1 . $inicial2;
        $colores   = ['4F46E5','0891B2','059669','D97706','DC2626','7C3AED','DB2777'];
        $color     = $colores[crc32($funcionario->nombre) % count($colores)];
    @endphp
    <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold text-white"
         style="width:36px;height:36px;background-color:#{{ $color }};font-size:13px;">
        {{ $iniciales }}
    </div>
@endif
                                        <span class="fw-medium">{{ $funcionario->nombre }}</span>
                                    </div>
                                </td>
                                <td class="text-secondary">{{ $funcionario->rut_formateado ?? '—' }}</td>
                                <td style="max-width:150px;">
    <span class="text-truncate d-block" title="{{ $funcionario->region?->nombre ?? '—' }}">
        {{ $funcionario->region?->nombre ?? '—' }}
    </span>
</td>
                                <td style="max-width:200px;">
                                    <span class="text-truncate d-block"
                                        title="{{ $funcionario->division?->nombre ?? '—' }}">
                                        {{ $funcionario->division?->nombre ?? '—' }}
                                    </span>
                                </td>
                                <td>{{ $funcionario->cargo ?? '—' }}</td>
                                <td>
                                    @if ($funcionario->puesto)
                                        <span
                                            class="badge bg-primary-subtle text-primary-emphasis border border-primary-subtle">
                                            <i class="fas fa-map-pin me-1"></i>
                                            {{ $funcionario->puesto->numero }}
                                            <small
                                                class="ms-1 opacity-75">{{ $funcionario->puesto->piso->nombre ?? '' }}</small>
                                        </span>
                                    @else
                                        <a href="{{ route('edificio.index') }}" class="btn btn-xs btn-outline-secondary"
                                            style="font-size:.7rem;padding:2px 8px;" title="Asignar puesto">
                                            <i class="fas fa-map-pin me-1"></i>Sin puesto
                                        </a>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if ($funcionario->activo)
                                        <span
                                            class="badge bg-success-subtle text-success-emphasis border border-success-subtle">
                                            <i class="fas fa-circle-check me-1"></i>Activo
                                        </span>
                                    @else
                                        <span
                                            class="badge bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle">
                                            <i class="fas fa-circle-minus me-1"></i>Inactivo
                                        </span>
                                    @endif
                                </td>
                                <td class="text-end">

                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('funcionario.show', $funcionario) }}"
                                            class="btn btn-outline-info" title="Ver detalle" target="_blank">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @can('funcionarios.editar')
                                            <a href="{{ route('funcionario.edit', $funcionario) }}"
                                                class="btn btn-outline-primary" title="Editar" target="_blank">
                                                <i class="fas fa-pen"></i>
                                            </a>
                                        @endcan
                                        <a href="{{ route('equipo-asignado.transferir', $funcionario) }}"
                                            class="btn btn-outline-warning" title="Transferir equipos">
                                            <i class="fas fa-right-left"></i>
                                        </a>
                                        @can('funcionarios.eliminar')
                                            <button type="submit" form="eliminar-{{ $funcionario->id }}"
                                                class="btn btn-outline-danger" title="Eliminar">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            <form id="eliminar-{{ $funcionario->id }}" method="POST"
                                                action="{{ route('funcionario.destroy', $funcionario) }}" class="d-none"
                                                onsubmit="return confirm('¿Mover a la papelera a {{ addslashes($funcionario->nombre) }}?')">
                                                @csrf
                                                @method('DELETE')
                                            </form>
                                        @endcan
                                    </div>

                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-secondary py-5">
                                    <i class="fas fa-folder-open fa-2x mb-2 d-block"></i>
                                    No hay funcionarios que mostrar.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($funcionarios->hasPages())
                <div id="paginacion" class="card-footer bg-body-tertiary">
                    {{ $funcionarios->links() }}
                </div>
            @endif
        </div>
    </div>


    <div id="barra-masiva" class="d-none position-fixed bottom-0 start-50 translate-middle-x mb-4"
     style="z-index:1050;min-width:350px;">
    <div class="card shadow-lg border-danger">
        <div class="card-body py-2 px-3 d-flex align-items-center gap-3">
            <span class="fw-bold text-danger">
                <i class="fas fa-check-square me-1"></i>
                <span id="contador-sel">0</span> seleccionado(s)
            </span>
            <form method="POST" action="{{ route('funcionario.destroyMasivo') }}" 
                  id="form-masivo" class="ms-auto"
                  onsubmit="return confirm('¿Mover a la papelera los funcionarios seleccionados?')">
                @csrf
                @method('DELETE')
                <div id="inputs-masivo"></div>
                <button type="submit" class="btn btn-danger btn-sm">
                    <i class="fas fa-trash me-1"></i>Eliminar seleccionados
                </button>
            </form>
            <button type="button" class="btn btn-outline-secondary btn-sm" id="btn-limpiar-sel">
                <i class="fas fa-xmark"></i>
            </button>
        </div>
    </div>
</div>
@endsection
