@extends('layouts.app')

@push('estilos')
    <link rel="stylesheet" href="{{ asset('css/puestos.css') }}">
@endpush

@push('scripts')
    <script>
        const routes = {
            storePuesto:        "{{ route('puesto.store', $piso) }}",
            updatePuesto:       "{{ url('puestos') }}",
            destroyPuesto:      "{{ url('puestos') }}",
            togglePuesto:       "{{ url('puestos') }}",
            asignarPuesto:      "{{ url('puestos') }}",
            desasignarPuesto:   "{{ url('puestos') }}",
            historialPuesto:    "{{ url('puestos') }}",
            buscarFuncionarios: "{{ route('api.puesto.funcionarios') }}",
            csrf:               "{{ csrf_token() }}",
            imagenPiso:         "{{ asset('storage/' . $piso->imagen) }}",
        };
        let puestosData = {!! json_encode($puestosJson) !!};
    </script>
    <script src="{{ asset('js/puestos.js') }}"></script>
@endpush

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <h3 class="mb-0">
            <i class="fas fa-map text-primary me-2"></i>{{ $piso->nombre }}
            <small class="text-secondary fs-6">— {{ $piso->edificio->nombre }}</small>
        </h3>
        <div class="d-flex gap-2">
            <a href="{{ route('edificio.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i>Volver
            </a>
            <button class="btn btn-sm btn-outline-primary" id="btn-modo-edicion">
                <i class="fas fa-pen me-1"></i>Modo edición
            </button>
            <a href="{{ route('piso.exportar', $piso) }}" class="btn btn-success btn-sm">
    <i class="fas fa-file-excel me-1"></i>Exportar
</a>
        </div>
    </div>

    <div class="d-flex gap-3 flex-wrap">

        {{-- Canvas --}}
        <div>
            <div id="planoContainer">
                <img id="imagenPiso" src="{{ asset('storage/' . $piso->imagen) }}">
                <canvas id="canvasPuestos"></canvas>
            </div>
        </div>

        {{-- Panel derecho --}}
        <div style="width:340px;">

            {{-- Form nuevo puesto (solo en modo edición) --}}
            <div id="panel-nuevo-puesto" class="card shadow-sm mb-3 d-none">
                <div class="card-header bg-body-tertiary fw-bold">
                    <i class="fas fa-plus me-1"></i><span id="titulo-form">Nuevo Puesto</span>
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label small">Número</label>
                            <input type="text" id="input-numero" class="form-control form-control-sm"
                                   placeholder="Ej: 23">
                        </div>
                        <div class="col-6">
                            <label class="form-label small">Sección</label>
                            <input type="text" id="input-seccion" class="form-control form-control-sm">
                        </div>
                        <div class="col-12">
                            <label class="form-label small">Observación</label>
                            <input type="text" id="input-observacion" class="form-control form-control-sm">
                        </div>
                        <div class="col-12">
                            <small class="text-secondary">
                                <i class="fas fa-info-circle me-1"></i>
                                Arrastra en el mapa para dibujar el área del puesto.
                            </small>
                        </div>
                    </div>
                </div>
             <div class="card-footer d-flex gap-2">
<button type="button" class="btn btn-primary btn-sm flex-grow-1" id="btn-guardar-puesto" disabled>
    <i class="fas fa-save me-1"></i>Guardar
</button>
<button type="button" class="btn btn-outline-warning btn-sm d-none" id="btn-toggle-inline">
    <i class="fas fa-ban"></i>
</button>
<button type="button" class="btn btn-outline-danger btn-sm d-none" id="btn-eliminar-inline">
    <i class="fas fa-trash"></i>
</button>
<button type="button" class="btn btn-outline-secondary btn-sm" id="btn-cancelar-edicion">
    <i class="fas fa-xmark"></i>
</button>
</div>
            </div>

            {{-- Leyenda --}}
            <div class="card shadow-sm mb-3">
                <div class="card-body py-2">
                    <div class="d-flex gap-3 flex-wrap">
                        <span class="small"><span style="color:#2563eb;">■</span> Ocupado</span>
                        <span class="small"><span style="color:#6c757d;">■</span> Disponible</span>
                        <span class="small"><span style="color:#dc3545;">■</span> Deshabilitado</span>
                    </div>
                </div>
            </div>

            {{-- Lista puestos --}}
            <div class="card shadow-sm">
                <div class="card-header bg-body-tertiary">
                    <input type="text" id="buscador-puestos" class="form-control form-control-sm"
                           placeholder="Buscar puesto o sección...">
                </div>
                <div class="card-body p-0">
                    <div style="max-height:400px;overflow-y:auto;">
                        <table class="table table-sm table-hover mb-0" id="tabla-puestos">
<thead class="table-light">
    <tr>
        <th>#</th>
        <th>Puesto</th>
        <th>Sección</th>
        <th>Estado</th>
    </tr>
</thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer text-center bg-body-tertiary">
                    <button class="btn btn-sm btn-outline-secondary" id="btn-prev">◀</button>
                    <span id="page-info" class="mx-2 small"></span>
                    <button class="btn btn-sm btn-outline-secondary" id="btn-next">▶</button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal info puesto --}}
<div class="modal fade" id="modal-puesto-info" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-map-pin me-2 text-primary"></i>Puesto <span id="info-numero">—</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <ul class="nav nav-tabs mb-3">
                    <li class="nav-item">
                        <button class="nav-link active" data-tab-puesto="info">
                            <i class="fas fa-info-circle me-1"></i>Info
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-tab-puesto="historial">
                            <i class="fas fa-history me-1"></i>Historial
                        </button>
                    </li>
                </ul>

                <div id="tab-puesto-info">
                    <div id="info-funcionario-card" class="d-none">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div id="info-avatar"></div>
                            <div>
                                <div class="fw-bold" id="info-funcionario-nombre">—</div>
                                <small class="text-secondary" id="info-funcionario-rut">—</small>
                            </div>
                        </div>
                        <ul class="list-unstyled small mb-3">
                            <li><span class="text-secondary">Cargo:</span> <span id="info-funcionario-cargo">—</span></li>
                            <li><span class="text-secondary">Área:</span> <span id="info-funcionario-area">—</span></li>
                        </ul>
                        <button class="btn btn-sm btn-outline-danger w-100" id="btn-desasignar-puesto">
                            <i class="fas fa-user-minus me-1"></i>Desasignar
                        </button>
                    </div>

                    <div id="info-disponible" class="d-none">
                        <p class="text-secondary text-center mb-3">
                            <i class="fas fa-user-slash fa-2x mb-2 d-block"></i>Puesto disponible
                        </p>
                        <div id="aviso-deshabilitado" class="alert alert-danger py-2 small d-none">
    <i class="fas fa-ban me-1"></i>Este puesto está deshabilitado. Habilítalo en modo edición antes de asignar.
</div>
                        <div style="position:relative;">
                            <input type="search" id="buscar-funcionario-puesto"
                                   class="form-control form-control-sm mb-2"
                                   placeholder="Buscar funcionario...">
                            <div id="resultados-funcionario-puesto"
                                 class="list-group d-none"
                                 style="position:absolute;z-index:1000;width:100%;top:100%;"></div>
                        </div>
                        <div id="funcionario-seleccionado-puesto" class="d-none mb-2">
                            <div class="alert alert-primary py-2 d-flex justify-content-between align-items-center">
                                <span id="funcionario-seleccionado-nombre"></span>
                                <button type="button" class="btn btn-sm btn-outline-primary" id="btn-cambiar-funcionario">
                                    <i class="fas fa-xmark"></i>
                                </button>
                            </div>
                        </div>
                        <input type="text" id="observacion-asignacion"
                               class="form-control form-control-sm mb-2"
                               placeholder="Observación (opcional)">
                        <button class="btn btn-sm btn-primary w-100" id="btn-asignar-puesto" disabled>
                            <i class="fas fa-user-plus me-1"></i>Asignar funcionario
                        </button>
                    </div>
                </div>

                <div id="tab-puesto-historial" class="d-none">
                    <div id="historial-puesto-content"></div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection