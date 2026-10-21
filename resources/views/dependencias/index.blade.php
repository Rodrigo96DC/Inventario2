@extends('layouts.app')

@push('scripts')
    <script src="{{ asset('js/dependencias.js') }}"></script>
@endpush

@section('content')
    <div class="container-fluid">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="mb-0">
                <i class="fas fa-sitemap text-primary me-2"></i>Dependencias
            </h3>
            <div class="d-flex gap-2">
                <a href="{{ route('dependencia.exportar') }}" class="btn btn-outline-success btn-sm">
                    <i class="fas fa-file-excel me-1"></i>Exportar
                </a>
                <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modal-importar">
                    <i class="fas fa-file-import me-1"></i>Importar
                </button>
            </div>
        </div>



        {{-- Tabs --}}
        <ul class="nav nav-tabs mb-3" id="tabs-dependencias">
            <li class="nav-item">
                <button class="nav-link active" data-tab="divisiones">
                    <i class="fas fa-building me-1"></i>Divisiones
                    <span class="badge bg-secondary ms-1">{{ $divisiones->count() }}</span>
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-tab="departamentos">
                    <i class="fas fa-building-user me-1"></i>Departamentos
                    <span class="badge bg-secondary ms-1">{{ $departamentos->count() }}</span>
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-tab="unidades">
                    <i class="fas fa-users me-1"></i>Unidades
                    <span class="badge bg-secondary ms-1">{{ $unidades->count() }}</span>
                </button>
            </li>
        </ul>

        {{-- Tab Divisiones --}}
        <div id="tab-divisiones">
            <div class="card shadow-sm">
                <div class="card-header bg-body-tertiary d-flex justify-content-between align-items-center">
                    <span class="fw-bold small">Divisiones</span>
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modal-nueva-division">
                        <i class="fas fa-plus me-1"></i>Nueva
                    </button>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Nombre</th>
                                <th class="text-center">Departamentos</th>
                                <th class="text-center">Estado</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($divisiones as $div)
                                <tr>
                                    <td class="fw-medium">{{ $div->nombre }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-secondary">{{ $div->departamentos_count }}</span>
                                    </td>
                                    <td class="text-center">
                                        @if ($div->activo)
                                            <span
                                                class="badge bg-success-subtle text-success-emphasis border border-success-subtle">Activo</span>
                                        @else
                                            <span
                                                class="badge bg-danger-subtle text-danger-emphasis border border-danger-subtle">Deshabilitado</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-primary btn-editar-division"
                                                data-id="{{ $div->id }}" data-nombre="{{ $div->nombre }}">
                                                <i class="fas fa-pen"></i>
                                            </button>
                                            <form method="POST" action="{{ route('division.toggle', $div) }}"
                                                class="d-inline">
                                                @csrf @method('PATCH')
                                                <button type="submit"
                                                    class="btn btn-outline-{{ $div->activo ? 'warning' : 'success' }}">
                                                    <i class="fas fa-{{ $div->activo ? 'ban' : 'check' }}"></i>
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('division.destroy', $div) }}"
                                                class="d-inline"
                                                onsubmit="return confirm('¿Eliminar {{ addslashes($div->nombre) }}?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-secondary py-4">Sin divisiones registradas.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    @if ($divisiones->hasPages())
                        <div class="card-footer bg-body-tertiary" id="paginacion-divisiones">
                            {{ $divisiones->appends(request()->query())->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Tab Departamentos --}}
        <div id="tab-departamentos" class="d-none">
            <div class="card shadow-sm">
                <div class="card-header bg-body-tertiary d-flex justify-content-between align-items-center">
                    <span class="fw-bold small">Departamentos</span>
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal"
                        data-bs-target="#modal-nuevo-departamento">
                        <i class="fas fa-plus me-1"></i>Nuevo
                    </button>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Nombre</th>
                                <th>División</th>
                                <th class="text-center">Unidades</th>
                                <th class="text-center">Estado</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($departamentos as $dep)
                                <tr>
                                    <td class="fw-medium">{{ $dep->nombre }}</td>
                                    <td class="text-secondary small">{{ $dep->division->nombre ?? '—' }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-secondary">{{ $dep->unidades_count }}</span>
                                    </td>
                                    <td class="text-center">
                                        @if ($dep->activo)
                                            <span
                                                class="badge bg-success-subtle text-success-emphasis border border-success-subtle">Activo</span>
                                        @else
                                            <span
                                                class="badge bg-danger-subtle text-danger-emphasis border border-danger-subtle">Deshabilitado</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-primary btn-editar-departamento"
                                                data-id="{{ $dep->id }}" data-nombre="{{ $dep->nombre }}"
                                                data-division="{{ $dep->division_id }}">
                                                <i class="fas fa-pen"></i>
                                            </button>
                                            <form method="POST" action="{{ route('departamento.toggle', $dep) }}"
                                                class="d-inline">
                                                @csrf @method('PATCH')
                                                <button type="submit"
                                                    class="btn btn-outline-{{ $dep->activo ? 'warning' : 'success' }}">
                                                    <i class="fas fa-{{ $dep->activo ? 'ban' : 'check' }}"></i>
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('departamento.destroy', $dep) }}"
                                                class="d-inline"
                                                onsubmit="return confirm('¿Eliminar {{ addslashes($dep->nombre) }}?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-secondary py-4">Sin departamentos
                                        registrados.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    @if ($departamentos->hasPages())
                        <div class="card-footer bg-body-tertiary" id="paginacion-departamentos">
                            {{ $departamentos->appends(request()->query())->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Tab Unidades --}}
        <div id="tab-unidades" class="d-none">
            <div class="card shadow-sm">
                <div class="card-header bg-body-tertiary d-flex justify-content-between align-items-center">
                    <span class="fw-bold small">Unidades</span>
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modal-nueva-unidad">
                        <i class="fas fa-plus me-1"></i>Nueva
                    </button>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Nombre</th>
                                <th>Departamento</th>
                                <th>División</th>
                                <th class="text-center">Estado</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($unidades as $uni)
                                <tr>
                                    <td class="fw-medium">{{ $uni->nombre }}</td>
                                    <td class="text-secondary small">{{ $uni->departamento->nombre ?? '—' }}</td>
                                    <td class="text-secondary small">{{ $uni->departamento->division->nombre ?? '—' }}
                                    </td>
                                    <td class="text-center">
                                        @if ($uni->activo)
                                            <span
                                                class="badge bg-success-subtle text-success-emphasis border border-success-subtle">Activo</span>
                                        @else
                                            <span
                                                class="badge bg-danger-subtle text-danger-emphasis border border-danger-subtle">Deshabilitado</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-primary btn-editar-unidad"
                                                data-id="{{ $uni->id }}" data-nombre="{{ $uni->nombre }}"
                                                data-departamento="{{ $uni->departamento_id }}">
                                                <i class="fas fa-pen"></i>
                                            </button>
                                            <form method="POST" action="{{ route('unidad.toggle', $uni) }}"
                                                class="d-inline">
                                                @csrf @method('PATCH')
                                                <button type="submit"
                                                    class="btn btn-outline-{{ $uni->activo ? 'warning' : 'success' }}">
                                                    <i class="fas fa-{{ $uni->activo ? 'ban' : 'check' }}"></i>
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('unidad.destroy', $uni) }}"
                                                class="d-inline"
                                                onsubmit="return confirm('¿Eliminar {{ addslashes($uni->nombre) }}?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-secondary py-4">Sin unidades registradas.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    @if ($unidades->hasPages())
                        <div class="card-footer bg-body-tertiary" id="paginacion-unidades">
                            {{ $unidades->appends(request()->query())->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Modal nueva división --}}
    <div class="modal fade" id="modal-nueva-division" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-building me-2"></i>Nueva División</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="{{ route('division.store') }}" id="form-division">
                    @csrf
                    <input type="hidden" name="_method" id="division-method" value="POST">
                    <div class="modal-body">
                        <label class="form-label">Nombre</label>
                        <input type="text" name="nombre" id="division-nombre" class="form-control" required>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary"
                            data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal nuevo departamento --}}
    <div class="modal fade" id="modal-nuevo-departamento" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-building-user me-2"></i>Nuevo Departamento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="{{ route('departamento.store') }}" id="form-departamento">
                    @csrf
                    <input type="hidden" name="_method" id="departamento-method" value="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">División</label>
                            <select name="division_id" id="departamento-division" class="form-select" required>
                                <option value="">Seleccione división</option>
                                @foreach ($divisionesSelect as $div)
                                    <option value="{{ $div->id }}">{{ $div->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Nombre</label>
                            <input type="text" name="nombre" id="departamento-nombre" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary"
                            data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal nueva unidad --}}
    <div class="modal fade" id="modal-nueva-unidad" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-users me-2"></i>Nueva Unidad</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="{{ route('unidad.store') }}" id="form-unidad">
                    @csrf
                    <input type="hidden" name="_method" id="unidad-method" value="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">División</label>
                            <select id="unidad-division" class="form-select">
                                <option value="">Seleccione división</option>
                                @foreach ($divisionesSelect as $div)
                                    <option value="{{ $div->id }}">{{ $div->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Departamento</label>
                            <select name="departamento_id" id="unidad-departamento" class="form-select" required>
                                <option value="">Primero seleccione división</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Nombre</label>
                            <input type="text" name="nombre" id="unidad-nombre" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary"
                            data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal importar --}}
    <div class="modal fade" id="modal-importar" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-file-import me-2"></i>Importar Dependencias</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="{{ route('dependencia.importar') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <p class="text-secondary small">
                            El archivo debe tener las columnas: <strong>DIVISION, DEPARTAMENTO, UNIDAD</strong>
                        </p>
                        <input type="file" name="archivo" accept=".xlsx,.xls,.csv" class="form-control" required>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary"
                            data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-upload me-1"></i>Importar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
