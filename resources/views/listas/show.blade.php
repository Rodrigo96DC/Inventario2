@extends('layouts.app')

@push('estilos')
    <meta name="lista-tipo" content="{{ $lista->tipo }}">
@endpush
@push('scripts')
    <script src="{{ asset('js/listas_show.js') }}"></script>
@endpush

@section('content')
    <div class="container-fluid">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="mb-0">
                <i class="fas fa-list-check text-primary me-2"></i>
                {{ $lista->nombre }}
            </h3>
            <div class="d-flex gap-2">
                <a href="{{ route('lista.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left me-1"></i>Volver
                </a>
                @if ($lista->tipo === 'equipos' && $lista->auto_baja)
                    <button class="btn btn-outline-warning btn-sm" data-bs-toggle="modal"
                        data-bs-target="#modal-importar-baja">
                        <i class="fas fa-file-import me-1"></i>Importar más equipos
                    </button>
                @endif
                @if ($lista->tipo === 'equipos' && $lista->user_id === auth()->id())
    <button type="button" class="btn btn-outline-info btn-sm"
            data-bs-toggle="modal" data-bs-target="#modal-importar-items">
        <i class="fas fa-file-import me-1"></i>Importar equipos
    </button>
@endif
                <a href="{{ route('lista.exportar', $lista) }}" class="btn btn-outline-success btn-sm">
    <i class="fas fa-file-excel me-1"></i>Exportar
</a>
                @if ($lista->user_id === auth()->id())
                    <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal"
                        data-bs-target="#modal-agregar-item">
                        <i class="fas fa-plus me-1"></i>Agregar elemento
                    </button>
                @endif
                @if ($lista->auto_baja)
                    <button type="button" class="btn btn-sm btn-warning" id="btn-abrir-baja">
                        <i class="fas fa-arrow-down me-1"></i>Dar de baja
                    </button>
                @endif
            </div>
        </div>


        {{-- Info lista --}}
        <div class="card shadow-sm mb-3">
            <div class="card-body py-2">
                <div class="row g-2 align-items-center">
                    <div class="col-auto">
                        @php
                            $colorEstado = match ($lista->estado) {
                                'en_proceso' => 'warning',
                                'realizada' => 'success',
                                'cancelada' => 'danger',
                            };
                            $labelEstado = match ($lista->estado) {
                                'en_proceso' => 'En proceso',
                                'realizada' => 'Realizada',
                                'cancelada' => 'Cancelada',
                            };
                        @endphp
                        <span
                            class="badge bg-{{ $colorEstado }}-subtle text-{{ $colorEstado }}-emphasis border border-{{ $colorEstado }}-subtle">
                            {{ $labelEstado }}
                        </span>
                    </div>
                    <div class="col-auto">
                        @if ($lista->tipo === 'equipos')
                            <span class="badge bg-primary-subtle text-primary-emphasis border border-primary-subtle">
                                <i class="fas fa-desktop me-1"></i>Equipos
                            </span>
                        @else
                            <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle">
                                <i class="fas fa-users me-1"></i>Funcionarios
                            </span>
                        @endif
                    </div>
                    <div class="col-auto">
                        @if ($lista->publica)
                            <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle">
                                <i class="fas fa-globe me-1"></i>Pública
                            </span>
                        @else
                            <span class="badge bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle">
                                <i class="fas fa-lock me-1"></i>Privada
                            </span>
                        @endif
                    </div>
                    <div class="col-auto text-secondary small">
                        <i class="fas fa-user me-1"></i>{{ $lista->user->name ?? '—' }}
                        · {{ $lista->created_at->format('d-m-Y H:i') }}
                    </div>
                    @if ($lista->descripcion)
                        <div class="col-12 text-secondary small">
                            <i class="fas fa-info-circle me-1"></i>{{ $lista->descripcion }}
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Barra acciones (visible al seleccionar items) --}}
        <div id="barra-acciones" class="d-none position-fixed bottom-0 start-50 translate-middle-x mb-4"
            style="z-index:1050;min-width:500px;">
            <div class="card shadow-lg border-warning">
                <div class="card-body py-2 px-3">
                    <div class="d-flex align-items-center gap-3 flex-wrap">
                        <span class="fw-bold">
                            <i class="fas fa-check-square me-1 text-warning"></i>
                            <span id="contador-items">0</span> seleccionado(s)
                        </span>
                        @if ($lista->tipo === 'equipos' && $lista->auto_baja)
                            
                                                <button type="button" class="btn btn-sm btn-outline-danger" id="btn-papelera-masivo">
                                <i class="fas fa-trash me-1"></i>Enviar a papelera
                            </button>
                        @endif
                        <button type="button" class="btn btn-sm btn-outline-secondary ms-auto" id="btn-limpiar-sel">
                            <i class="fas fa-xmark"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

{{-- Tabla items --}}
<div class="card shadow-sm">
    <div class="card-header bg-body-tertiary">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <ul class="nav nav-tabs card-header-tabs">
                <li class="nav-item">
                    <button class="nav-link active" data-tab="pendientes">
                        <i class="fas fa-clock me-1"></i>Pendientes
                        <span class="badge bg-secondary ms-1">{{ $items->total() }}</span>
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-tab="completados">
                        <i class="fas fa-check-circle me-1"></i>Completados
                        <span class="badge bg-success ms-1">{{ $itemsCompletados->total() }}</span>
                    </button>
                </li>
            </ul>
            @if ($lista->tipo === 'equipos' && $lista->auto_baja)
                <div class="form-check mb-0">
                    <input type="checkbox" class="form-check-input" id="check-todos">
                    <label class="form-check-label small" for="check-todos">Seleccionar todos</label>
                </div>
            @endif
        </div>
    </div>

    {{-- Tab Pendientes --}}
    <div id="tab-pendientes">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        @if ($lista->tipo === 'equipos' && $lista->auto_baja)
                            <th style="width:40px;"></th>
                        @endif
                        @if ($lista->tipo === 'equipos')
                           <th>Equipo</th>
    <th>Inventario</th>
    <th>Asignado a</th>
    <th>Hostname</th>
    <th>Estado equipo</th>
                        @else
                            <th>Funcionario</th>
                            <th>División</th>
                        @endif
                        <th class="text-center">Estado item</th>
                        <th>Detalle</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($items as $item)
                        @php
                            $colorItem = match($item->estado) {
                                'pendiente'  => 'secondary',
                                'en_proceso' => 'warning',
                                'completado' => 'success',
                            };
                            $labelItem = match($item->estado) {
                                'pendiente'  => 'Pendiente',
                                'en_proceso' => 'En proceso',
                                'completado' => 'Completado',
                            };
                        @endphp
                        <tr>
                            @if ($lista->tipo === 'equipos' && $lista->auto_baja)
                                <td>
                                    <input type="checkbox" class="form-check-input check-item"
                                           value="{{ $item->id }}"
                                           data-equipo-id="{{ $item->itemable_id }}">
                                </td>
                            @endif
                            @if ($lista->tipo === 'equipos')
                                 <td>
        <div class="fw-medium">
            {{ $item->itemable?->equipo?->marca ?? '—' }}
            {{ $item->itemable?->equipo?->modelo ?? '' }}
        </div>
        <small class="text-secondary">
            {{ $item->itemable?->equipo?->tipoEquipo?->nombre ?? '—' }}
        </small>
    </td>
    <td class="fw-medium">{{ $item->itemable?->inventario ?? '—' }}</td>
    <td class="small">
        {{ $item->itemable?->asignacionActual?->funcionario?->nombre ?? 'Sin asignar' }}
    </td>
    <td class="small text-secondary">
        {{ $item->itemable?->asignacionActual?->hostname ?? '—' }}
    </td>
    <td>
        @if ($item->itemable?->estado)
            <span class="badge bg-{{ $item->itemable->estado->color }}-subtle 
                         text-{{ $item->itemable->estado->color }}-emphasis 
                         border border-{{ $item->itemable->estado->color }}-subtle">
                {{ $item->itemable->estado->nombre }}
            </span>
        @else
            <span class="text-secondary small">—</span>
        @endif
    </td>
                            @else
                                <td class="fw-medium">{{ $item->itemable?->nombre ?? '—' }}</td>
                                <td class="small text-secondary">{{ $item->itemable?->division?->nombre ?? '—' }}</td>
                            @endif
                            <td class="text-center">
                                <span class="badge bg-{{ $colorItem }}-subtle text-{{ $colorItem }}-emphasis border border-{{ $colorItem }}-subtle">
                                    {{ $labelItem }}
                                </span>
                            </td>
                            <td class="small text-secondary" style="max-width:200px;">
                                <span class="text-truncate d-block" title="{{ $item->detalle ?? '' }}">
                                    {{ $item->detalle ?? '—' }}
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary btn-editar-item"
                                            data-id="{{ $item->id }}"
                                            data-estado="{{ $item->estado }}"
                                            data-detalle="{{ $item->detalle }}">
                                        <i class="fas fa-pen"></i>
                                    </button>
                                    @if ($lista->tipo === 'equipos' && $lista->auto_baja)
                                      <a href="{{ route('documento.baja', $item->itemable_id) }}"
   class="btn btn-outline-warning" title="Acta de baja">
    <i class="fas fa-file-word"></i>
</a>
                                    @endif
                                    @if ($lista->user_id === auth()->id())
                                        <form method="POST" action="{{ route('lista.item.destroy', $item) }}"
                                              class="d-inline"
                                              onsubmit="return confirm('¿Eliminar este elemento de la lista?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-secondary py-5">
                                <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                Sin elementos pendientes.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($items->hasPages())
            <div class="card-footer bg-body-tertiary">
                {{ $items->links() }}
            </div>
        @endif
    </div>

    {{-- Tab Completados --}}
    <div id="tab-completados" class="d-none">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        @if ($lista->tipo === 'equipos')
                            <th>Equipo</th>
                            <th>Inventario</th>
                            <th>Estado equipo</th>
                        @else
                            <th>Funcionario</th>
                            <th>División</th>
                        @endif
                        <th>Detalle</th>
                        <th class="text-center">Completado</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($itemsCompletados as $item)
                        <tr class="text-secondary">
                            @if ($lista->tipo === 'equipos')
                                <td>
                                    <div>{{ $item->itemable?->equipo?->marca ?? '—' }} {{ $item->itemable?->equipo?->modelo ?? '' }}</div>
                                    <small>{{ $item->itemable?->equipo?->tipoEquipo?->nombre ?? '—' }}</small>
                                </td>
                                <td>{{ $item->itemable?->inventario ?? 'En papelera' }}</td>
                                <td>
                                    @if ($item->itemable?->estado)
                                        <span class="badge bg-{{ $item->itemable->estado->color }}-subtle text-{{ $item->itemable->estado->color }}-emphasis border border-{{ $item->itemable->estado->color }}-subtle">
                                            {{ $item->itemable->estado->nombre }}
                                        </span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle">
                                            En papelera
                                        </span>
                                    @endif
                                </td>
                            @else
                                <td>{{ $item->itemable?->nombre ?? '—' }}</td>
                                <td>{{ $item->itemable?->division?->nombre ?? '—' }}</td>
                            @endif
                            <td class="small" style="max-width:200px;">
                                <span class="text-truncate d-block" title="{{ $item->detalle ?? '' }}">
                                    {{ $item->detalle ?? '—' }}
                                </span>
                            </td>
                            <td class="text-center">
                                <i class="fas fa-check-circle text-success"></i>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-secondary py-4">
                                Sin elementos completados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
   @if ($itemsCompletados->hasPages())
    <div class="card-footer bg-body-tertiary">
        {{ $itemsCompletados->appends(array_merge(request()->query(), ['tab' => 'completados']))->links() }}
    </div>
@endif
    </div>
</div>


    {{-- Modal editar item --}}
    <div class="modal fade" id="modal-editar-item" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-pen me-2"></i>Editar elemento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="form-editar-item">
                    @csrf @method('PUT')
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Estado</label>
                            <select name="estado" id="item-estado" class="form-select">
                                <option value="pendiente">Pendiente</option>
                                <option value="en_proceso">En proceso</option>
                                <option value="completado">Completado</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Detalle <small class="text-muted">(opcional)</small></label>
                            <textarea name="detalle" id="item-detalle" class="form-control" rows="3"
                                placeholder="Ingresa un comentario o detalle..."></textarea>
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

    {{-- Modal agregar item --}}
    <div class="modal fade" id="modal-agregar-item" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-plus me-2"></i>Agregar elemento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="{{ route('lista.item.store', $lista) }}">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">
                                {{ $lista->tipo === 'equipos' ? 'N° Inventario' : 'Nombre funcionario' }}
                            </label>
                            <input type="text" id="buscar-item" class="form-control"
                                placeholder="{{ $lista->tipo === 'equipos' ? 'Buscar por inventario...' : 'Buscar por nombre...' }}">
                            <div id="resultados-item" class="list-group mt-1 d-none"
                                style="position:relative;z-index:1000;"></div>
                            <input type="hidden" name="itemable_id" id="itemable-id">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Estado</label>
                            <select name="estado" class="form-select">
                                <option value="pendiente">Pendiente</option>
                                <option value="en_proceso">En proceso</option>
                                <option value="completado">Completado</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Detalle <small class="text-muted">(opcional)</small></label>
                            <textarea name="detalle" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary"
                            data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Agregar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal confirmar baja --}}
    <div class="modal fade" id="modal-confirmar-baja" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-arrow-down text-warning me-2"></i>Confirmar Baja
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-3">
                        Vas a dar de baja <strong id="baja-cantidad">0</strong> equipo(s).
                    </p>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Detalle de la baja <small
                                class="text-muted">(opcional)</small></label>
                        <textarea id="baja-detalle" class="form-control" rows="3" placeholder="Motivo de baja, observaciones..."></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">¿Descargar documento de baja?</label>
                        <div class="d-flex flex-column gap-2">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="opcion-baja" id="baja-sin-doc"
                                    value="sin_documento" checked>
                                <label class="form-check-label" for="baja-sin-doc">
                                    Sin documento
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="opcion-baja" id="baja-separadas"
                                    value="separadas">
                                <label class="form-check-label" for="baja-separadas">
                                    <i class="fas fa-file-word text-primary me-1"></i>
                                    Acta por cada equipo (máx. 20)
                                </label>
                            </div>
                                              </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-warning" id="btn-ejecutar-baja">
                        <i class="fas fa-arrow-down me-1"></i>Ejecutar baja
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Form papelera masivo --}}
    <form method="POST" action="{{ route('lista.papelera', $lista) }}" id="form-papelera">
        @csrf
        <div id="inputs-papelera"></div>
    </form>
<form method="POST" action="{{ route('documento.baja-unida') }}" id="form-baja-unida">
    @csrf
    <div id="inputs-baja-unida"></div>
    <input type="hidden" name="detalle" id="baja-unida-detalle">
</form>





{{-- Modal importar items --}}
<div class="modal fade" id="modal-importar-items" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-file-import me-2"></i>Importar equipos a la lista
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('lista.importar-items', $lista) }}"
                  enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <p class="text-secondary small mb-3">
                        El archivo debe tener las columnas <strong>inventario</strong> y/o <strong>serial</strong>.
                        Se descargará un Excel con el resultado de cada fila.
                    </p>
                    <input type="file" name="archivo" accept=".xlsx,.xls,.csv"
                           class="form-control" required>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-upload me-1"></i>Importar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
