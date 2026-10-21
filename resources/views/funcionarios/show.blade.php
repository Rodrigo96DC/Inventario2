@extends('layouts.app')
@if (session('abrir_show'))
    @push('scripts')
        <script>
            // Ya estamos en el show, no necesitamos abrir otra ventana
            // pero sí notificar al index que se actualizó
        </script>
    @endpush
@endif
@section('content')
    <div class="container-fluid">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="mb-0">
                <i class="fas fa-user text-primary me-2"></i>
                {{ $funcionario->nombre }}
            </h3>
            <div class="d-flex gap-2">
                <a href="{{ route('funcionario.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left me-1"></i>Volver
                </a>
                <a href="{{ route('funcionario.edit', $funcionario) }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-pen me-1"></i>Editar
                </a>
            </div>
        </div>



        <div class="row g-3">

            {{-- Columna izquierda --}}
            <div class="col-12 col-md-3">
                <div class="card shadow-sm text-center p-3 mb-3">

                    {{-- Avatar --}}
                    @if ($funcionario->avatar)
                        <img src="{{ asset('storage/' . $funcionario->avatar) }}" alt="{{ $funcionario->nombre }}"
                            class="rounded-circle mx-auto mb-3" style="width:100px;height:100px;object-fit:cover;">
                    @else
                        <div class="rounded-circle bg-secondary-subtle d-flex align-items-center justify-content-center mx-auto mb-3"
                            style="width:100px;height:100px;">
                            <i class="fas fa-user fa-3x text-secondary"></i>
                        </div>
                    @endif

                    <h5 class="fw-bold mb-0">{{ $funcionario->nombre }}</h5>
                    <small class="text-secondary">{{ $funcionario->rut ?? 'Sin RUT' }}</small>

                    <div class="mt-2">
                        @if ($funcionario->activo)
                            <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle">
                                <i class="fas fa-circle-check me-1"></i>Activo
                            </span>
                        @else
                            <span class="badge bg-danger-subtle text-danger-emphasis border border-danger-subtle">
                                <i class="fas fa-circle-xmark me-1"></i>Inactivo
                            </span>
                        @endif
                    </div>

                    <hr>

                    <ul class="list-unstyled text-start small">
                        <li class="mb-2">
                            <span class="text-secondary"><i class="fas fa-map-marker-alt me-1"></i>Región:</span>
                            <span class="fw-medium">{{ $funcionario->region?->nombre ?? '—' }}</span>
                        </li>
                        <li class="mb-2">
                            <span class="text-secondary"><i class="fas fa-building me-1"></i>División:</span>
                            <span class="fw-medium">{{ $funcionario->division?->nombre ?? '—' }}</span>
                        </li>
                        <li class="mb-2">
                            <span class="text-secondary"><i class="fas fa-building-user me-1"></i>Depto:</span>
                            <span class="fw-medium">{{ $funcionario->departamento?->nombre ?? '—' }}</span>
                        </li>
                        <li class="mb-2">
                            <span class="text-secondary"><i class="fas fa-users me-1"></i>Unidad:</span>
                            <span class="fw-medium">{{ $funcionario->unidad?->nombre ?? '—' }}</span>
                        </li>
                        <li class="mb-2">
                            <span class="text-secondary"><i class="fas fa-briefcase me-1"></i>Cargo:</span>
                            <span class="fw-medium">{{ $funcionario->cargo ?? '—' }}</span>
                        </li>
                        <li class="mb-2">
                            <span class="text-secondary"><i class="fas fa-envelope me-1"></i>Correo:</span>
                            <span class="fw-medium">{{ $funcionario->correo ?? '—' }}</span>
                        </li>
                    </ul>

                    <hr>

                    {{-- Fechas contrato --}}
                    <ul class="list-unstyled text-start small">
                        <li class="mb-2">
                            <span class="text-secondary"><i class="fas fa-calendar-plus me-1"></i>Ingreso:</span>
                            <span class="fw-medium">{{ $funcionario->fecha_ingreso?->format('d-m-Y') ?? '—' }}</span>
                        </li>
                        <li>
                            <span class="text-secondary"><i class="fas fa-calendar-xmark me-1"></i>Fin contrato:</span>
                            @if ($funcionario->fecha_fin_contrato)
                                @php
                                    $diasRestantes = (int) now()->diffInDays($funcionario->fecha_fin_contrato, false);
                                @endphp
                                @if ($diasRestantes < 0)
                                    <span class="badge bg-danger-subtle text-danger-emphasis border border-danger-subtle">
                                        Vencido
                                    </span>
                                @elseif ($diasRestantes <= 30)
                                    <span
                                        class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">
                                        <i class="fas fa-triangle-exclamation me-1"></i>
                                        {{ $funcionario->fecha_fin_contrato->format('d-m-Y') }}
                                        ({{ $diasRestantes }} días)
                                    </span>
                                @else
                                    <span class="fw-medium">{{ $funcionario->fecha_fin_contrato->format('d-m-Y') }}</span>
                                @endif
                            @else
                                <span class="fw-medium">—</span>
                            @endif
                        </li>
                    </ul>

                    {{-- Puesto --}}
                    @if ($funcionario->puesto)
                        <hr>
                        <div class="text-start small">
                            <span class="text-secondary"><i class="fas fa-map-pin me-1"></i>Puesto:</span>
                            <span class="badge bg-primary-subtle text-primary-emphasis border border-primary-subtle">
                                {{ $funcionario->puesto->numero }}
                            </span>
                            <small class="text-secondary d-block mt-1">
                                {{ $funcionario->puesto->piso?->nombre ?? '' }}
                                — {{ $funcionario->puesto->piso?->edificio?->nombre ?? '' }}
                            </small>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Columna derecha --}}
            <div class="col-12 col-md-9">
                <div class="card shadow-sm">
                    <div class="card-header bg-body-tertiary">
                        <ul class="nav nav-tabs card-header-tabs">
                            <li class="nav-item">
                                <button class="nav-link active" data-tab="equipos">
                                    <i class="fas fa-desktop me-1"></i>Equipos
                                    <span class="badge bg-secondary ms-1">{{ $funcionario->asignaciones->count() }}</span>
                                </button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link" data-tab="historial">
                                    <i class="fas fa-history me-1"></i>Historial
                                    <span
                                        class="badge bg-secondary ms-1">{{ $funcionario->historialAsignaciones->count() }}</span>
                                </button>
                            </li>
                        </ul>
                    </div>

                    <div class="card-body">

                        {{-- Tab Equipos --}}
                        <div id="tab-equipos">
                            @forelse ($funcionario->asignaciones as $asignacion)
                                @php $eq = $asignacion->detalleEquipo; @endphp
                                <div class="card mb-2 shadow-sm">
                                    <div class="card-body py-2">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="d-flex align-items-center gap-3">
                                                @if ($eq->equipo?->imagen)
                                                    <img src="{{ asset('storage/' . $eq->equipo->imagen) }}"
                                                        style="width:50px;height:40px;object-fit:contain;">
                                                @else
                                                    <div class="bg-secondary-subtle rounded d-flex align-items-center justify-content-center"
                                                        style="width:50px;height:40px;">
                                                        <i class="fas fa-desktop text-secondary"></i>
                                                    </div>
                                                @endif
                                                <div>
                                                    <div class="fw-medium">
                                                        {{ $eq->equipo?->marca ?? '—' }}
                                                        {{ $eq->equipo?->modelo ?? '' }}
                                                    </div>
                                                    <small class="text-secondary">
                                                        {{ $eq->inventario }}
                                                        @if ($asignacion->hostname)
                                                            · {{ $asignacion->hostname }}
                                                        @endif
                                                    </small>
                                                </div>
                                            </div>
                                            <div class="text-end">
                                                @php $estado = $eq->estado; @endphp
                                                <span
                                                    class="badge bg-{{ $estado?->color }}-subtle text-{{ $estado?->color }}-emphasis border border-{{ $estado?->color }}-subtle">
                                                    {{ $estado?->nombre ?? '—' }}
                                                </span>
                                                <div class="d-flex align-items-center gap-2 mt-1">
                                                    <a href="{{ route('equipo-asignado.show', $eq) }}"
                                                        class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('equipo-asignado.transferir', $funcionario) }}"
                                                        class="btn btn-sm btn-outline-warning" title="Transferir equipos">
                                                        <i class="fas fa-right-left"></i>
                                                    </a>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            @empty
                                <p class="text-secondary text-center py-4">
                                    <i class="fas fa-desktop fa-2x mb-2 d-block"></i>
                                    Sin equipos asignados.
                                </p>
                            @endforelse
                        </div>

                        {{-- Tab Historial --}}
                        <div id="tab-historial" class="d-none">
                            <div class="table-responsive">
                                <table class="table table-sm table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Fecha</th>
                                            <th>Acción</th>
                                            <th>Equipo</th>
                                            <th>Hostname</th>
                                            <th>Observación</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($funcionario->historialAsignaciones as $h)
                                            <tr>
                                                <td class="small text-nowrap">{{ $h->fecha->format('d-m-Y H:i') }}</td>
                                                <td>
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
                                                </td>
                                                <td class="small">
                                                    {{ $h->detalleEquipo?->equipo?->marca ?? '—' }}
                                                    {{ $h->detalleEquipo?->equipo?->modelo ?? '' }}
                                                    <small
                                                        class="text-secondary d-block">{{ $h->detalleEquipo?->inventario }}</small>
                                                </td>
                                                <td class="small text-secondary">{{ $h->hostname ?? '—' }}</td>
                                                <td class="small text-secondary">{{ $h->observacion ?? '—' }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center text-secondary py-4">
                                                    Sin historial.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.querySelectorAll('[data-tab]').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    document.querySelectorAll('[data-tab]').forEach(function(b) {
                        b.classList.remove('active');
                    });
                    this.classList.add('active');
                    document.getElementById('tab-equipos').classList.toggle('d-none', this.dataset.tab !==
                        'equipos');
                    document.getElementById('tab-historial').classList.toggle('d-none', this.dataset.tab !==
                        'historial');
                });
            });
        </script>
    @endpush

@endsection
