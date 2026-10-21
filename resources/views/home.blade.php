@extends('layouts.app')

@push('estilos')
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
        <script>
            const porRegionLabels = @json($porRegion->pluck('nombre'));
            const porRegionDatos = @json($porRegion->pluck('total_equipos'));
            const porEstadoLabels = @json($porEstado->pluck('nombre'));
            const porEstadoDatos = @json($porEstado->pluck('total'));
            const porEstadoColores = @json($porEstado->pluck('hex_color'));
            const funcActivos = {{ $funcionariosActivos }};
            const funcInactivos = {{ $funcionariosInactivos }};
            const equipAsignados = {{ $asignados }};
            const equipSinAsignar = {{ $sinAsignar }};
        </script>
        <script src="{{ asset('js/dashboard.js') }}"></script>
    @endpush
    <script src="{{ asset('js/dashboard.js') }}"></script>
@endpush

@section('content')
    <div class="container-fluid">

        <h3 class="mb-4">
            <i class="fas fa-gauge text-primary me-2"></i>Dashboard
        </h3>

        {{-- Banner contratos por vencer --}}
        @if (isset($contratosPorVencer) && $contratosPorVencer > 0)
            <div class="alert alert-warning d-flex justify-content-between align-items-center mb-4">
                <div>
                    <i class="fas fa-triangle-exclamation me-2"></i>
                    <strong>{{ $contratosPorVencer }}</strong> funcionario(s) tienen contratos por vencer en los próximos 7
                    días.
                </div>
                <a href="{{ route('notificacion.contratos') }}" class="btn btn-sm btn-warning">
                    Ver detalle
                </a>
            </div>
        @endif


        {{-- Tarjetas resumen --}}
        <div class="row g-3 mb-4">
       
            <div class="col-6 col-md-3">
                <div class="card shadow-sm h-100 border-0">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="icon-box bg-primary-subtle text-primary">
                            <i class="fas fa-laptop fa-lg"></i>
                        </div>
                        <div>
                            <div class="fs-2 fw-bold">{{ $totalCatalogo }}</div>
                            <div class="text-secondary small">Modelos en catálogo</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card shadow-sm h-100 border-0">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="icon-box bg-success-subtle text-success">
                            <i class="fas fa-desktop fa-lg"></i>
                        </div>
                        <div>
                            <div class="fs-2 fw-bold">{{ $totalInventario }}</div>
                            <div class="text-secondary small">Unidades en inventario</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card shadow-sm h-100 border-0">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="icon-box bg-warning-subtle text-warning">
                            <i class="fas fa-link fa-lg"></i>
                        </div>
                        <div>
                            <div class="fs-2 fw-bold">{{ $asignados }}</div>
                            <div class="text-secondary small">Equipos asignados</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card shadow-sm h-100 border-0">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="icon-box bg-secondary-subtle text-secondary">
                            <i class="fas fa-box fa-lg"></i>
                        </div>
                        <div>
                            <div class="fs-2 fw-bold">{{ $sinAsignar }}</div>
                            <div class="text-secondary small">Sin asignar</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Gráficos fila 1 --}}
        <div class="row g-3 mb-4">
            <div class="col-12 col-md-6">
                <div class="card shadow-sm h-100">
               <div class="card-header bg-body-tertiary fw-bold">
    <i class="fas fa-map-marker-alt me-1 text-primary"></i>Equipos por región
</div>
<div class="card-body">
    <canvas id="chart-tipo" height="220"></canvas>
</div>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-body-tertiary fw-bold">
                        <i class="fas fa-chart-pie me-1 text-primary"></i>Equipos por estado
                    </div>
                    <div class="card-body d-flex align-items-center justify-content-center">
                        <canvas id="chart-estado" height="220"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- Gráficos fila 2 --}}
        <div class="row g-3 mb-4">
            <div class="col-12 col-md-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-body-tertiary fw-bold">
                        <i class="fas fa-users me-1 text-primary"></i>Funcionarios
                    </div>
                    <div class="card-body d-flex align-items-center justify-content-center">
                        <canvas id="chart-funcionarios" height="200"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-body-tertiary fw-bold">
                        <i class="fas fa-chart-pie me-1 text-primary"></i>Asignación
                    </div>
                    <div class="card-body d-flex align-items-center justify-content-center">
                        <canvas id="chart-asignados" height="200"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-body-tertiary fw-bold">
                        <i class="fas fa-clock-rotate-left me-1 text-primary"></i>Últimos movimientos
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush">
                            @forelse ($ultimasAsignaciones as $h)
                                <li class="list-group-item px-3 py-2">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <div class="small fw-medium">
                                                {{ $h->funcionario?->nombre ?? 'Funcionario eliminado' }}
                                            </div>
                                            <div class="small text-secondary">
                                                {{ $h->detalleEquipo?->equipo?->marca ?? '—' }}
                                                {{ $h->detalleEquipo?->equipo?->modelo ?? '' }}
                                                — {{ $h->detalleEquipo?->inventario ?? '—' }}
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            @php
                                                $color = match ($h->accion) {
                                                    'asignado' => 'success',
                                                    'reasignado' => 'primary',
                                                    'desasignado' => 'warning',
                                                    default => 'secondary',
                                                };
                                            @endphp
                                            <span
                                                class="badge bg-{{ $color }}-subtle text-{{ $color }}-emphasis border border-{{ $color }}-subtle">
                                                {{ ucfirst($h->accion) }}
                                            </span>
                                            <div class="small text-secondary mt-1">
                                                {{ $h->fecha->format('d-m-Y') }}
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            @empty
                                <li class="list-group-item text-center text-secondary py-4">
                                    Sin movimientos recientes.
                                </li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection
