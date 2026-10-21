@extends('layouts.app')
@push('scripts')
    <script>
        const routeBuscarFuncionario = "{{ route('api.funcionario.buscar') }}";
    </script>
    <script src="{{ asset('js/transferir.js') }}"></script>
@endpush

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-12 col-lg-10 mx-auto">

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h3 class="mb-0">
                        <i class="fas fa-right-left text-warning me-2"></i>Transferir Equipos
                    </h3>
                    <a href="{{ route('equipo-asignado.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-arrow-left me-1"></i>Volver
                    </a>
                </div>

                <div class="card shadow-sm mb-3">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3">
                            @if ($funcionario->avatar)
                                <img src="{{ asset('storage/' . $funcionario->avatar) }}" class="rounded-circle"
                                    width="56" height="56" style="object-fit:cover;">
                            @else
                                <div class="rounded-circle bg-secondary-subtle d-flex align-items-center justify-content-center"
                                    style="width:56px;height:56px;">
                                    <i class="fas fa-user fa-xl text-secondary"></i>
                                </div>
                            @endif
                            <div>
                                <h5 class="mb-0">{{ $funcionario->nombre }}</h5>
                                <small class="text-secondary">
                                    {{ $funcionario->rut_formateado ?? '' }}
                                    {{ $funcionario->cargo ? '· ' . $funcionario->cargo : '' }}
                                   {{ $funcionario->region?->nombre ? '· ' . $funcionario->region->nombre : '' }}
                                </small>
                            </div>
                            <span class="badge bg-primary ms-auto fs-6">
                                {{ $funcionario->asignaciones->count() }} equipo(s)
                            </span>
                        </div>
                    </div>
                </div>

                <form method="POST" id="form-transferir"
                    action="{{ route('equipo-asignado.transferir.post', $funcionario) }}">
                    @csrf

                    <div class="card shadow-sm mb-3">
                        <div class="card-header bg-body-tertiary">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="fw-bold"><i class="fas fa-desktop me-1"></i>Equipos a transferir</span>
                                <button type="button" class="btn btn-sm btn-outline-primary" id="btn-todos">
                                    Seleccionar todos
                                </button>
                            </div>
                            <input type="text" id="buscar-equipo" class="form-control form-control-sm"
                                placeholder="Buscar por inventario, marca, modelo...">
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width:40px;">
                                            <input type="checkbox" id="check-todos" class="form-check-input">
                                        </th>
                                        <th>Tipo</th>
                                        <th>Marca / Modelo</th>
                                        <th>Inventario</th>
                                        <th>Hostname</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($asignaciones as $asignacion)
                                        <tr data-tipo="{{ $asignacion->detalleEquipo->equipo->tipoEquipo->nombre ?? '' }}">
                                            <td>
                                                <input type="checkbox" name="equipos[]"
                                                    value="{{ $asignacion->detalle_equipo_id }}"
                                                    class="form-check-input check-equipo">
                                            </td>

                                            <td>
                                                <span
                                                    class="badge bg-primary-subtle text-primary-emphasis border border-primary-subtle">
                                                    {{ $asignacion->detalleEquipo->equipo->tipoEquipo->nombre ?? '—' }}
                                                </span>
                                            </td>
                                            <td>
                                                <div>{{ $asignacion->detalleEquipo->equipo->marca ?? '—' }}</div>
                                                <small
                                                    class="text-secondary">{{ $asignacion->detalleEquipo->equipo->modelo ?? '—' }}</small>
                                            </td>
                                            <td class="fw-medium">{{ $asignacion->detalleEquipo->inventario }}</td>
                                            <td class="text-secondary small">{{ $asignacion->hostname ?? '—' }}</td>
                                            <td>
                                                @php $estado = $asignacion->detalleEquipo->estado; @endphp
                                                <span
                                                    class="badge bg-{{ $estado->color }}-subtle text-{{ $estado->color }}-emphasis border border-{{ $estado->color }}-subtle">
                                                    {{ $estado->nombre ?? '—' }}
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-secondary py-4">
                                                Este funcionario no tiene equipos asignados.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                            @if ($asignaciones->hasPages())
    <div class="card-footer bg-body-tertiary" id="paginacion-transferir">
        {{ $asignaciones->links() }}
    </div>
@endif
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">
                            <i class="fas fa-user-check me-1"></i>Transferir a
                        </label>

                        <div style="position: relative;">
                            <input type="search" id="buscar-destino"
                                class="form-control @error('funcionario_destino_id') is-invalid @enderror"
                                placeholder="Buscar funcionario por nombre o RUT..." autocomplete="off">

                            <div id="resultados-destino" class="list-group mt-1 d-none"
                                style="position:absolute; z-index:1000; width:100%; left:0; top:100%;">
                            </div>
                        </div>

                        <input type="hidden" name="funcionario_destino_id" id="funcionario_destino_id"
                            value="{{ old('funcionario_destino_id') }}">

                        <div id="destino-seleccionado" class="mt-2 d-none">
                            <div class="alert alert-primary py-2 mb-0 d-flex justify-content-between align-items-center">
                                <span><i class="fas fa-user-check me-1"></i><strong id="destino-nombre"></strong></span>
                                <button type="button" class="btn btn-sm btn-outline-primary" id="btn-cambiar-destino">
                                    <i class="fas fa-xmark"></i>
                                </button>
                            </div>
                        </div>

                        @error('funcionario_destino_id')
                            <span class="invalid-feedback d-block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div id="inputs-equipos"></div>
                    {{-- Reemplaza el botón de submit del formulario --}}
                    <div class="d-flex gap-2 justify-content-end">
                        <a href="{{ route('equipo-asignado.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                        <button type="button" class="btn btn-warning" id="btn-abrir-modal-hostnames">
                            <i class="fas fa-right-left me-1"></i>Transferir equipos seleccionados
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>

    <div class="modal fade" id="modal-hostnames" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-computer me-2"></i>Confirmar hostnames
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-secondary small mb-3">
                        Revisa y ajusta el hostname de cada equipo transferido. Si no corresponde cambiarlo, déjalo igual.
                    </p>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Equipo</th>
                                    <th>Inventario</th>
                                    <th>Hostname</th>
                                </tr>
                            </thead>
                            <tbody id="modal-hostnames-body"></tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-arrow-left me-1"></i>Volver
                    </button>
                    <button type="button" class="btn btn-warning" id="btn-confirmar-transferir">
                        <i class="fas fa-right-left me-1"></i>Confirmar transferencia
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection
