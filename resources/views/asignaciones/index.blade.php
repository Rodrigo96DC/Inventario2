@extends('layouts.app')

@push('estilos')
    <link rel="stylesheet" href="{{ asset('css/asignaciones.css') }}">
@endpush

@push('scripts')
    <script>
 const routes = {
    buscarFuncionario:  "{{ url('interno/funcionarios/buscar') }}",
    equiposFuncionario: "{{ url('interno/funcionarios') }}",
    historialEquipo:    "{{ url('interno/equipos') }}",
    asignar:            "{{ route('asignacion.asignar') }}",
    desasignar:         "{{ url('asignaciones') }}",
    csrf:               "{{ csrf_token() }}",
};

    </script>
    <script src="{{ asset('js/asignaciones.js') }}"></script>
@endpush

@section('content')
<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0">
            <i class="fas fa-link text-primary me-2"></i>Asignaciones
        </h3>

        <button type="button" class="btn btn-outline-success btn-sm" 
        data-bs-toggle="modal" data-bs-target="#modal-registrar-equipo">
    <i class="fas fa-plus me-1"></i>Registrar equipo
    </button>
<button type="button" class="btn btn-outline-info btn-sm align-items-end"
        data-bs-toggle="modal" data-bs-target="#modal-importar-masivo">
    <i class="fas fa-file-import me-1"></i>Asignación masiva
</button>

    </div>

    <div class="row g-3">

        {{-- Panel izquierdo: Funcionario --}}
        <div class="col-12 col-lg-3">
            <div class="panel-funcionario">

                {{-- Buscador funcionario --}}
                <div class="card shadow-sm mb-3">
                    <div class="card-body">
                        <label class="form-label fw-bold">
                            <i class="fas fa-magnifying-glass me-1"></i>Buscar funcionario
                        </label>
                        <input type="search" id="buscar-funcionario"
                            class="form-control" placeholder="Nombre o RUT...">

                        <div id="resultados-funcionario" class="list-group mt-2 d-none"></div>
                    </div>
                </div>

                {{-- Perfil funcionario --}}
                <div class="card shadow-sm d-none" id="card-funcionario">
                    <div class="card-body">

                        {{-- Avatar + nombre --}}
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div id="funcionario-avatar-wrapper">
                                <div class="rounded-circle bg-secondary-subtle d-flex align-items-center justify-content-center avatar-lg">
                                    <i class="fas fa-user fa-2x text-secondary"></i>
                                </div>
                            </div>
<div class="d-flex align-items-start gap-2 flex-wrap">
    <div>
        <h6 class="fw-bold mb-0" id="funcionario-nombre">—</h6>
        <small class="text-secondary" id="funcionario-rut">—</small>
    </div>
    <a href="#" id="btn-editar-funcionario" target="_blank"
       class="btn btn-outline-primary d-none mt-1"
       style="font-size:.65rem;padding:2px 6px;"
       title="Editar funcionario">
        <i class="fas fa-pen"></i>
    </a>
</div>
                   
                        </div>

                        {{-- Tabs --}}
                        <ul class="nav nav-tabs nav-fill mb-3" id="tabs-funcionario">
                            <li class="nav-item">
                                <button class="nav-link active" data-tab="perfil">
                                    <i class="fas fa-id-card me-1"></i>Perfil
                                </button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link" data-tab="equipos-asignados">
                                    <i class="fas fa-desktop me-1"></i>Equipos
                                    <span class="badge bg-primary ms-1" id="badge-equipos-asignados">0</span>
                                </button>
                            </li>
                        </ul>

                        {{-- Tab Perfil --}}
                        <div id="tab-perfil">
                            <ul class="list-unstyled small">
                                <li class="mb-2">
                                    <span class="text-secondary">Región</span>
                                    <div class="fw-medium" id="funcionario-region">—</div>
                                </li>
                                <li class="mb-2">
                                    <span class="text-secondary">Área</span>
                                    <div class="fw-medium" id="funcionario-area">—</div>
                                </li>
                                <li class="mb-2">
                                    <span class="text-secondary">Cargo</span>
                                    <div class="fw-medium" id="funcionario-cargo">—</div>
                                </li>
                                <li>
                                    <span class="text-secondary">Correo</span>
                                    <div class="fw-medium" id="funcionario-correo">—</div>
                                </li>
                            </ul>
                        </div>

                        {{-- Tab Equipos asignados --}}
                        <div id="tab-equipos-asignados" class="d-none">
                            <div id="lista-equipos-asignados">
                                <p class="text-secondary text-center small py-3">
                                    Sin equipos asignados.
                                </p>
                            </div>
                        </div>

                    </div>
                </div>
{{-- Equipos seleccionados --}}
<div id="panel-seleccionados" class="d-none mt-2">
    <div class="card shadow-sm">
        <div class="card-header bg-body-tertiary py-2">
            <small class="fw-bold">
                <i class="fas fa-check-square me-1 text-primary"></i>
                Equipos seleccionados (<span id="contador-panel">0</span>)
            </small>
        </div>
        <div id="lista-seleccionados" class="card-body p-2" style="max-height:300px;overflow-y:auto;">
        </div>
    </div>
</div>
            </div>
        </div>

        {{-- Panel derecho: Equipos disponibles --}}
        <div class="col-12 col-lg-9">

            {{-- Filtros --}}
            <div class="card shadow-sm mb-3">
                <div class="card-body py-2">
                    <form method="GET" action="{{ route('asignacion.index') }}"
                          id="form-filtros" class="row g-2 align-items-end">
                        <div class="col-12 col-sm-6 col-md-4">
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-magnifying-glass"></i></span>
                                <input type="search" name="buscar" id="buscar" value="{{ $buscar }}"
                                    placeholder="Inventario, serial, marca..."
                                    class="form-control">
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <select name="tipo_id" class="form-select">
                                <option value="">Todo tipo</option>
                                @foreach ($tiposEquipo as $tipo)
                                    <option value="{{ $tipo->id }}" @selected(request('tipo_id') == $tipo->id)>
                                        {{ $tipo->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6 col-md-3">
                            <select name="estado_id" class="form-select">
                                <option value="">Todos los estados</option>
                                @foreach ($estadosEquipo as $estado)
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
                            <a href="{{ route('asignacion.index') }}" class="btn btn-outline-secondary w-100">
                                <i class="fas fa-xmark"></i>
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Grid equipos --}}
            <div class="row g-3" id="grid-equipos">
                @forelse ($equiposDisponibles as $equipo)
                    <div class="col-12 col-sm-6 col-md-4 col-xl-3">
 <div class="equipo-card card h-100 {{ $equipo->asignacionActual ? 'border-warning' : '' }}"
     data-id="{{ $equipo->id }}"
     data-tipo="{{ $equipo->equipo?->tipoEquipo?->nombre ?? '' }}"
     data-asignado="{{ $equipo->asignacionActual ? '1' : '0' }}"
     data-funcionario="{{ $equipo->asignacionActual?->funcionario?->nombre ?? '' }}"
     data-marca="{{ $equipo->equipo?->marca ?? '' }}"
     data-modelo="{{ $equipo->equipo?->modelo ?? '' }}"
     data-inventario="{{ $equipo->inventario }}">

                            <div class="check-overlay">
                                <i class="fas fa-check"></i>
                            </div>

                            @if ($equipo->equipo?->imagen)
                                <div class="card-img-wrapper">
                                    <img src="{{ asset('storage/' . $equipo->equipo->imagen) }}"
                                         alt="{{ $equipo->equipo->modelo }}">
                                </div>
                            @else
                                <div class="card-img-wrapper d-flex align-items-center justify-content-center bg-secondary-subtle">
                                    <i class="fas fa-desktop fa-2x text-secondary"></i>
                                </div>
                            @endif

                            <div class="card-body pb-1">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="badge bg-primary-subtle text-primary-emphasis border border-primary-subtle small">
                                        {{ $equipo->equipo->tipoEquipo->nombre ?? '—' }}
                                    </span>
                                    <span class="badge bg-{{ $equipo->estado->color }}-subtle text-{{ $equipo->estado->color }}-emphasis border border-{{ $equipo->estado->color }}-subtle small">
                                        {{ $equipo->estado->nombre ?? '—' }}
                                    </span>
                                </div>
                                <h6 class="card-title mb-0 text-uppercase small">
                                    {{ $equipo->equipo->marca ?? '—' }}
                                </h6>
                                <p class="card-text text-secondary small mb-1">{{ $equipo->equipo->modelo ?? '—' }}</p>
                                <ul class="list-unstyled small text-secondary mb-0">
                                    <li><i class="fas fa-barcode me-1"></i>{{ $equipo->inventario }}</li>
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

                                    <li>                                {{-- Agregar después del inventario --}}
@if ($equipo->asignacionActual)
    <div class="mt-1">
        <small class="text-warning">
            <i class="fas fa-user me-1"></i>
            Asignado a: {{ $equipo->asignacionActual->funcionario?->nombre ?? '—' }}
        </small>
    </div>
@endif</li>
                                </ul>
                            </div>

                            <div class="card-footer bg-transparent border-0 pt-0">

                                <button type="button"
                                        class="btn btn-sm btn-outline-secondary w-100 btn-historial"
                                        data-id="{{ $equipo->id }}">
                                    <i class="fas fa-clock-rotate-left me-1"></i>Historial
                                </button>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="card shadow-sm">
                            <div class="card-body text-center text-secondary py-5">
                                <i class="fas fa-folder-open fa-2x mb-2 d-block"></i>
                                No hay equipos disponibles para asignar.
                            </div>
                        </div>
                    </div>
                @endforelse
            </div>

            @if ($equiposDisponibles->hasPages())
                <div class="py-3">{{ $equiposDisponibles->links() }}</div>
            @endif
        </div>
    </div>
</div>

{{-- Barra flotante de asignación --}}
<div id="barra-accion" class="card shadow-lg border-primary">
    <div class="card-body py-2 px-3">
        <div class="d-flex align-items-center gap-3 flex-wrap">
            <span class="fw-bold text-primary">
                <i class="fas fa-check-square me-1"></i>
                <span id="contador-seleccionados">0</span> equipo(s) seleccionado(s)
            </span>
            <button type="button" class="btn btn-sm btn-outline-secondary ms-auto" id="btn-limpiar">
                <i class="fas fa-xmark me-1"></i>Limpiar
            </button>
            <button type="button" class="btn btn-sm btn-primary" id="btn-abrir-modal-asignar"
                    disabled>
                <i class="fas fa-link me-1"></i>Asignar a funcionario
            </button>
        </div>
    </div>
</div>

{{-- Modal asignar --}}
<div class="modal fade" id="modal-asignar" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-link me-2"></i>Confirmar Asignación
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-3">
                    Asignando <strong id="modal-cantidad">0</strong> equipo(s) a:
                    <strong id="modal-funcionario-nombre">—</strong>
                </p>

                <div class="table-responsive mb-3">
                    <table class="table table-sm table-bordered align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Equipo</th>
                                <th>Inventario</th>
                                <th>Hostname</th>
                            </tr>
                        </thead>
                        <tbody id="modal-equipos-body"></tbody>
                    </table>
                </div>

                <div class="mb-3">
                    <label for="modal-observacion" class="form-label">
                        Observación <small class="text-muted">(opcional, aplica a todos)</small>
                    </label>
                    <textarea id="modal-observacion" class="form-control" rows="2"
                              placeholder="Ej: Cambio de equipo por falla..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btn-confirmar-asignar">
                    <i class="fas fa-link me-1"></i>Confirmar asignación
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Modal historial --}}
<div class="modal fade" id="modal-historial" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-clock-rotate-left me-2"></i>
                    <span id="modal-historial-titulo">Detalle del Equipo</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <ul class="nav nav-tabs mb-3" id="tabs-historial">
                    <li class="nav-item">
                        <button class="nav-link active" data-tab-historial="detalle">
                            <i class="fas fa-info-circle me-1"></i>Detalle
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-tab-historial="historial">
                            <i class="fas fa-history me-1"></i>Historial
                        </button>
                    </li>
                </ul>

                {{-- Tab detalle --}}
                <div id="tab-historial-detalle">
                    <div class="row g-2" id="detalle-equipo-content">
                        <div class="text-center py-4">
                            <div class="spinner-border text-primary"></div>
                        </div>
                    </div>
                </div>

                {{-- Tab historial --}}
                <div id="tab-historial-historial" class="d-none">
                    <div id="historial-content">
                        <div class="text-center py-4">
                            <div class="spinner-border text-primary"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal registrar equipo --}}
<div class="modal fade" id="modal-registrar-equipo" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-plus me-2"></i>Registrar Equipo en Inventario
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                {{-- Alertas --}}
                <div id="alerta-equipo" class="d-none"></div>

                <div class="row g-3">
                    {{-- Tipo equipo --}}
                    <div class="col-md-6">
                        <label class="form-label">Tipo de equipo</label>
                        <select id="reg-tipo-equipo" class="form-select">
                            <option value="">Seleccione tipo</option>
                            @foreach ($tiposEquipo as $tipo)
                                <option value="{{ $tipo->id }}">{{ $tipo->nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Modelo --}}
                    <div class="col-md-6">
                        <label class="form-label">Marca / Modelo</label>
                        <select id="reg-equipo-id" class="form-select">
                            <option value="">Seleccione tipo primero</option>
                        </select>
                    </div>

                    {{-- Inventario --}}
                    <div class="col-md-4">
                        <label class="form-label">N° Inventario</label>
                        <input type="text" id="reg-inventario" class="form-control" placeholder="1600000000">
                    </div>

                    {{-- Serial --}}
                    <div class="col-md-4">
                        <label class="form-label">Serial <small class="text-muted">(opcional)</small></label>
                        <input type="text" id="reg-serial" class="form-control">
                    </div>

                    {{-- Estado --}}
                    <div class="col-md-4">
                        <label class="form-label">Estado</label>
                        <select id="reg-estado" class="form-select">
                            @foreach ($estadosEquipo as $estado)
                                <option value="{{ $estado->id }}">{{ $estado->nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- RAM --}}
                    <div class="col-md-3">
                        <label class="form-label">RAM (GB) <small class="text-muted">(opcional)</small></label>
                        <input type="number" id="reg-ram" class="form-control" placeholder="16">
                    </div>

                    {{-- Tipo disco --}}
                    <div class="col-md-3">
                        <label class="form-label">Tipo disco <small class="text-muted">(opcional)</small></label>
                        <select id="reg-tipo-disco" class="form-select">
                            <option value="">— Sin disco —</option>
                            @foreach ($tiposDiscos as $disco)
                                <option value="{{ $disco->id }}">{{ $disco->nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Capacidad disco --}}
                    <div class="col-md-3">
                        <label class="form-label">Capacidad disco (GB) <small class="text-muted">(opcional)</small></label>
                        <input type="number" id="reg-disco-gb" class="form-control" placeholder="480">
                    </div>

                    {{-- Fecha adquisición --}}
                    <div class="col-md-3">
                        <label class="form-label">Fecha adquisición <small class="text-muted">(opcional)</small></label>
                        <input type="date" id="reg-fecha" class="form-control">
                    </div>

                    {{-- IP --}}
                    <div class="col-md-4">
                        <label class="form-label">IP <small class="text-muted">(opcional)</small></label>
                        <input type="text" id="reg-ip" class="form-control" placeholder="192.168.1.1">
                    </div>

                    {{-- Puerto switch --}}
                    <div class="col-md-4">
                        <label class="form-label">Puerto switch <small class="text-muted">(opcional)</small></label>
                        <input type="number" id="reg-puerto" class="form-control">
                    </div>

                    {{-- MAC Ethernet --}}
                    <div class="col-md-4">
                        <label class="form-label">MAC Ethernet <small class="text-muted">(opcional)</small></label>
                        <input type="text" id="reg-mac-eth" class="form-control" placeholder="AA-BB-CC-DD-EE-FF">
                    </div>

                    {{-- MAC WiFi --}}
                    <div class="col-md-4">
                        <label class="form-label">MAC WiFi <small class="text-muted">(opcional)</small></label>
                        <input type="text" id="reg-mac-wifi" class="form-control" placeholder="AA-BB-CC-DD-EE-FF">
                    </div>

                    {{-- Teletrabajo --}}
                    <div class="col-md-4">
                        <div class="form-check form-switch mt-4">
                            <input class="form-check-input" type="checkbox" id="reg-teletrabajo">
                            <label class="form-check-label" for="reg-teletrabajo">Teletrabajo</label>
                        </div>
                    </div>

                    {{-- Préstamo --}}
                    <div class="col-md-4">
                        <div class="form-check form-switch mt-4">
                            <input class="form-check-input" type="checkbox" id="reg-prestamo">
                            <label class="form-check-label" for="reg-prestamo">Préstamo</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btn-guardar-equipo">
                    <i class="fas fa-save me-1"></i>Guardar equipo
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Modal importar masivo --}}
<div class="modal fade" id="modal-importar-masivo" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-file-import me-2"></i>Asignación Masiva
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('asignacion.importar-masivo') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <p class="text-secondary small mb-3">
                        El archivo debe tener las columnas:
                        <strong>inventario, serial, funcionario, hostname</strong><br>
                        El campo <strong>funcionario</strong> acepta: nombre, RUT o inicio del correo
                        (ej: <code>rodrigo.lopez</code>)
                    </p>
                    <div class="mb-3">
                        <input type="file" name="archivo" accept=".xlsx,.xls,.csv"
                               class="form-control" required>
                    </div>
                    <a href="{{ route('asignacion.plantilla-masivo') }}" class="btn btn-outline-success btn-sm">
                        <i class="fas fa-file-excel me-1"></i>Descargar plantilla
                    </a>
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