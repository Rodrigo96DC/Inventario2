@extends('layouts.app')

@section('content')
    <div class="container-fluid">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="mb-0">
                <i class="fas fa-desktop text-primary me-2"></i>
                {{ $detalle_equipo->equipo->marca ?? '' }} {{ $detalle_equipo->equipo->modelo ?? '' }}
                <small class="text-secondary fs-6">— {{ $detalle_equipo->inventario }}</small>
            </h3>
            <div class="d-flex gap-2">
                <a href="{{ route('edificio.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left me-1"></i>Volver
                </a>
                @if ($detalle_equipo->asignacionActual?->funcionario)
                    <a href="{{ route('equipo-asignado.transferir', $detalle_equipo->asignacionActual->funcionario) }}"
                        class="btn btn-warning btn-sm">
                        <i class="fas fa-right-left me-1"></i>Transferir
                    </a>
                    <a href="{{ route('documento.acta', $detalle_equipo) }}" class="btn btn-success btn-sm">
                        <i class="fas fa-file-word me-1"></i>Acta
                    </a>
                @endif
                <a href="{{ route('documento.baja', $detalle_equipo) }}" class="btn btn-danger btn-sm">
                    <i class="fas fa-file-word me-1"></i>Baja
                </a>
                <a href="{{ route('detalle-equipo.edit', $detalle_equipo) }}?from=show" class="btn btn-primary btn-sm">
                    <i class="fas fa-pen me-1"></i>Editar
                </a>
            </div>
        </div>


        <div class="row g-3">

            {{-- Imagen + datos funcionario --}}
            <div class="col-12 col-md-3">
                <div class="card shadow-sm text-center p-3 mb-3">
                    @if ($detalle_equipo->equipo?->imagen)
                        <img src="{{ asset('storage/' . $detalle_equipo->equipo->imagen) }}" class="img-fluid rounded mb-2"
                            style="max-height:180px;object-fit:contain;">
                    @else
                        <div class="bg-secondary-subtle rounded d-flex align-items-center justify-content-center mb-2"
                            style="height:140px;">
                            <i class="fas fa-desktop fa-3x text-secondary"></i>
                        </div>
                    @endif

                    @php $asignacion = $detalle_equipo->asignacionActual; @endphp
                    @if ($asignacion)
                        <h6 class="fw-bold mb-0" id="nombre-funcionario">{{ $asignacion->funcionario->nombre ?? '—' }}</h6>
                                          <button type="button" class="btn btn-xs btn-outline-secondary"
                                    style="font-size:.7rem;padding:2px 8px;"
                                    onclick="copiarTexto('{{ $asignacion->funcionario->nombre ?? '' }}', this)">
                                    <i class="fas fa-copy"></i>
                                </button>
                        <small class="text-secondary">{{ $asignacion->funcionario->rut_formateado ?? '' }}</small>
                        <hr class="my-2">
                        <small class="text-secondary d-block">{{ $asignacion->funcionario->cargo ?? '' }}</small>
                        <small class="text-secondary d-block">{{ $asignacion->funcionario->area ?? '' }}</small>
                        <small class="text-secondary d-block">{{ $asignacion->funcionario->region->nombre ?? '' }}</small>
                        @if ($asignacion->hostname)
                            <div class="d-flex align-items-center gap-2" style="margin-left: 83px">
                                <span class="badge bg-primary-subtle text-primary-emphasis border border-primary-subtle">
                                    <i class="fas fa-computer me-1"></i>{{ $asignacion->hostname }}
                                </span>
                                <button type="button" class="btn btn-xs btn-outline-primary"
                                    style="font-size:.7rem;padding:2px 8px;" id="btn-editar-hostname"
                                    data-hostname="{{ $asignacion->hostname }}">
                                    <i class="fas fa-pen"></i>
                                </button>
                                <button type="button" class="btn btn-xs btn-outline-secondary"
                                    style="font-size:.7rem;padding:2px 8px;"
                                    onclick="copiarTexto('{{ $asignacion->hostname }}', this)">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                        @endif
                        <div class="mt-2">
                            <small class="text-secondary">Asignado el
                                {{ $asignacion->fecha_asignacion?->format('d-m-Y') }}</small>
                        </div>
                    @else
                        <span class="badge bg-secondary">Sin asignación actual</span>
                    @endif
                </div>
            </div>

            {{-- Tabs principales --}}
            <div class="col-12 col-md-9">
                <div class="card shadow-sm">
                    <div class="card-header bg-body-tertiary">
                        <ul class="nav nav-tabs card-header-tabs" id="tabs-detalle">
                            <li class="nav-item">
                                <button class="nav-link active" data-tab="info">
                                    <i class="fas fa-info-circle me-1"></i>Técnico
                                </button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link" data-tab="ad">
                                    <i class="fas fa-network-wired me-1"></i>AD / Licencia
                                </button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link" data-tab="observaciones">
                                    <i class="fas fa-clipboard-list me-1"></i>Observaciones
                                    <span
                                        class="badge bg-secondary ms-1">{{ $detalle_equipo->observaciones->count() }}</span>
                                </button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link" data-tab="historial">
                                    <i class="fas fa-history me-1"></i>Historial
                                    <span
                                        class="badge bg-secondary ms-1">{{ $detalle_equipo->historialAsignaciones->count() }}</span>
                                </button>
                            </li>
                        </ul>
                    </div>

                    <div class="card-body">

                        {{-- Tab Técnico --}}
                        <div id="tab-info">
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <table class="table table-sm table-bordered mb-0">
                                        <tr>
                                            <th class="bg-body-tertiary" style="width:40%">Tipo</th>
                                            <td>{{ $detalle_equipo->equipo->tipoEquipo->nombre ?? '—' }}</td>
                                        </tr>
                                        <tr>
                                            <th class="bg-body-tertiary">Marca</th>
                                            <td>{{ $detalle_equipo->equipo->marca ?? '—' }}</td>
                                        </tr>
                                        <tr>
                                            <th class="bg-body-tertiary">Modelo</th>
                                            <td>{{ $detalle_equipo->equipo->modelo ?? '—' }}</td>
                                        </tr>
                                        <tr>
                                            <th class="bg-body-tertiary">Inventario</th>
                                            <td class="fw-bold">{{ $detalle_equipo->inventario }}</td>
                                        </tr>
                                        <tr>
                                            <th class="bg-body-tertiary">Serial</th>
                                            <td>{{ $detalle_equipo->serial }}</td>
                                        </tr>
                                        <tr>
                                            <th class="bg-body-tertiary">Estado</th>
                                            <td>
                                                @php $estado = $detalle_equipo->estado; @endphp
                                                <span
                                                    class="badge bg-{{ $estado->color }}-subtle text-{{ $estado->color }}-emphasis border border-{{ $estado->color }}-subtle">
                                                    {{ $estado->nombre ?? '—' }}
                                                </span>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-sm table-bordered mb-0">
                                        <tr>
                                            <th class="bg-body-tertiary" style="width:40%">RAM</th>
                                            <td>{{ $detalle_equipo->ram_gb ? $detalle_equipo->ram_gb . ' GB' : '—' }}</td>
                                        </tr>
                                        <tr>
                                            <th class="bg-body-tertiary">Disco</th>
                                            <td>
                                                @if ($detalle_equipo->capacidad_disco_gb)
                                                    {{ $detalle_equipo->capacidad_disco_gb >= 1000
                                                        ? number_format($detalle_equipo->capacidad_disco_gb / 1000, 1) . ' TB'
                                                        : $detalle_equipo->capacidad_disco_gb . ' GB' }}
                                                    {{ $detalle_equipo->tipoDisco->nombre ?? '' }}
                                                @else
                                                    —
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th class="bg-body-tertiary">IP</th>
                                            <td>{{ $detalle_equipo->ip ?? '—' }}</td>
                                        </tr>
                                        <tr>
                                            <th class="bg-body-tertiary">MAC Ethernet</th>
                                            <td>{{ $detalle_equipo->macs->firstWhere('interfaz', 'Ethernet')?->mac ?? '—' }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <th class="bg-body-tertiary">MAC WiFi</th>
                                            <td>{{ $detalle_equipo->macs->firstWhere('interfaz', 'WiFi')?->mac ?? '—' }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <th class="bg-body-tertiary">Puerto Switch</th>
                                            <td>{{ $detalle_equipo->puerto_switch ?? '—' }}</td>
                                        </tr>
                                        <tr>
                                            <th class="bg-body-tertiary">Teletrabajo</th>
                                            <td>{{ $detalle_equipo->teletrabajo ? 'Sí' : 'No' }}</td>
                                        </tr>
                                        <tr>
                                            <th class="bg-body-tertiary">Préstamo</th>
                                            <td>{{ $detalle_equipo->es_prestamo ? 'Sí' : 'No' }}</td>
                                        </tr>
                                        <tr>
                                            <th class="bg-body-tertiary">Adquisición</th>
                                            <td>{{ $detalle_equipo->fecha_adquisicion?->format('d-m-Y') ?? '—' }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        {{-- Tab AD / Licencia --}}
                        <div id="tab-ad" class="d-none">

                            {{-- Sección AD --}}
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="text-primary mb-0">
                                    <i class="fas fa-network-wired me-1"></i>Activación y Desactivación del AD y licencia
                                </h6>
                                <button type="button" class="btn btn-sm btn-outline-primary" id="btn-copiar-ad"
                                    title="Copiar datos para correo">
                                    <i class="fas fa-copy me-1"></i>Copiar para correo
                                </button>
                            </div>

                            <div class="table-responsive mb-3">
                                <table class="table table-bordered align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Nombre (Hostname)</th>
                                            <th>MAC Ethernet / WiFi</th>
                                            <th>Ubicación</th>
                                            <th>Área</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="fw-bold" id="ad-hostname">
                                                {{ $detalle_equipo->asignacionActual?->hostname ?? '—' }}</td>
                                            <td id="ad-mac">
                                                {{ $detalle_equipo->macs->firstWhere('interfaz', 'Ethernet')?->mac ?? '—' }}
                                                @if ($detalle_equipo->macs->firstWhere('interfaz', 'WiFi'))
                                                    / {{ $detalle_equipo->macs->firstWhere('interfaz', 'WiFi')->mac }}
                                                @endif
                                            </td>
                                            <td id="ad-region">
                                                {{ $detalle_equipo->asignacionActual?->funcionario?->region?->nombre ?? '—' }}
                                            </td>
                                            <td id="ad-area">
                                                {{ $detalle_equipo->asignacionActual?->funcionario?->unidad?->nombre ??
                                                    ($detalle_equipo->asignacionActual?->funcionario?->departamento?->nombre ??
                                                        ($detalle_equipo->asignacionActual?->funcionario?->division?->nombre ?? '—')) }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            {{-- Sección Acta --}}
                            {{-- Sección Acta --}}
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="text-primary mb-0">
                                    <i class="fas fa-file-contract me-1"></i>Creación de Acta de entrega o retiro
                                </h6>
                                <button type="button" class="btn btn-sm btn-outline-primary" id="btn-copiar-acta">
                                    <i class="fas fa-copy me-1"></i>Copiar datos acta
                                </button>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Tipo</th>
                                            <th>Marca</th>
                                            <th>Modelo</th>
                                            <th>Inventario</th>
                                            <th>Serial</th>
                                            <th>MAC Ethernet</th>
                                            <th>MAC WiFi</th>
                                            <th>CPU</th>
                                            <th>RAM</th>
                                            <th>Disco</th>
                                            <th>Responsable</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td id="acta-tipo">{{ $detalle_equipo->equipo->tipoEquipo->nombre ?? '—' }}
                                            </td>
                                            <td id="acta-marca">{{ $detalle_equipo->equipo->marca ?? '—' }}</td>
                                            <td id="acta-modelo">{{ $detalle_equipo->equipo->modelo ?? '—' }}</td>
                                            <td id="acta-inventario">{{ $detalle_equipo->inventario }}</td>
                                            <td id="acta-serial">{{ $detalle_equipo->serial }}</td>
                                            <td>{{ $detalle_equipo->macs->firstWhere('interfaz', 'Ethernet')?->mac ?? '—' }}
                                            </td>
                                            <td>{{ $detalle_equipo->macs->firstWhere('interfaz', 'WiFi')?->mac ?? '—' }}
                                            </td>
                                            <td id="acta-cpu">{{ $detalle_equipo->equipo->procesador ?? '—' }}</td>
                                            <td id="acta-ram">
                                                {{ $detalle_equipo->ram_gb ? $detalle_equipo->ram_gb . ' GB' : '—' }}</td>
                                            <td id="acta-disco">
                                                @if ($detalle_equipo->capacidad_disco_gb)
                                                    {{ $detalle_equipo->capacidad_disco_gb >= 1000
                                                        ? number_format($detalle_equipo->capacidad_disco_gb / 1000, 1) . ' TB'
                                                        : $detalle_equipo->capacidad_disco_gb . ' GB' }}
                                                    {{ $detalle_equipo->tipoDisco?->nombre ?? '' }}
                                                @else
                                                    —
                                                @endif
                                            </td>
                                            <td>{{ $detalle_equipo->asignacionActual?->funcionario?->nombre ?? '—' }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Tab Observaciones --}}
                        <div id="tab-observaciones" class="d-none">

                            {{-- Formulario nueva observación --}}
                            <div class="card bg-body-tertiary mb-3">
                                <div class="card-body">
                                    <h6 class="card-title mb-3">
                                        <i class="fas fa-plus me-1"></i>Nueva observación
                                    </h6>
                                    <form method="POST"
                                        action="{{ route('equipo-asignado.observacion.store', $detalle_equipo) }}#observaciones"
                                        enctype="multipart/form-data">
                                        @csrf
                                        <div class="row g-2">
                                            <div class="col-md-4">
                                                <label class="form-label small">Tipo de intervención</label>
                                                <select name="tipo"
                                                    class="form-select form-select-sm @error('tipo') is-invalid @enderror">
                                                    <option value="">Seleccione</option>
                                                    <option value="mantenimiento" @selected(old('tipo') === 'mantenimiento')>
                                                        Mantenimiento</option>
                                                    <option value="cambio_pieza" @selected(old('tipo') === 'cambio_pieza')>Cambio de
                                                        pieza</option>
                                                    <option value="limpieza" @selected(old('tipo') === 'limpieza')>Limpieza</option>
                                                    <option value="revision" @selected(old('tipo') === 'revision')>Revisión</option>
                                                    <option value="otro" @selected(old('tipo') === 'otro')>Otro</option>
                                                </select>
                                                @error('tipo')
                                                    <span class="invalid-feedback">{{ $message }}</span>
                                                @enderror
                                            </div>
                                            <div class="col-md-8">
                                                <label class="form-label small">Observación</label>
                                                <textarea name="nota" rows="2" class="form-control form-control-sm @error('nota') is-invalid @enderror"
                                                    placeholder="Describe la intervención realizada...">{{ old('nota') }}</textarea>
                                                @error('nota')
                                                    <span class="invalid-feedback">{{ $message }}</span>
                                                @enderror
                                            </div>
                                            <div class="col-md-8">
                                                <label class="form-label small">Adjunto <small class="text-muted">(PDF,
                                                        JPG, PNG — opcional)</small></label>
                                                <input type="file" name="archivo" accept=".pdf,.jpg,.jpeg,.png"
                                                    class="form-control form-control-sm @error('archivo') is-invalid @enderror">
                                                @error('archivo')
                                                    <span class="invalid-feedback">{{ $message }}</span>
                                                @enderror
                                            </div>
                                            <div class="col-md-4 d-flex align-items-end">
                                                <button type="submit" class="btn btn-primary btn-sm w-100">
                                                    <i class="fas fa-save me-1"></i>Guardar observación
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            {{-- Lista observaciones --}}
                            @forelse ($detalle_equipo->observaciones as $obs)
                                @php
                                    $colorTipo = match ($obs->tipo) {
                                        'mantenimiento' => 'primary',
                                        'cambio_pieza' => 'warning',
                                        'limpieza' => 'info',
                                        'revision' => 'secondary',
                                        default => 'dark',
                                    };
                                @endphp
                                <div class="card mb-2 shadow-sm">
                                    <div class="card-body py-2">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <span
                                                    class="badge bg-{{ $colorTipo }}-subtle text-{{ $colorTipo }}-emphasis border border-{{ $colorTipo }}-subtle me-2">
                                                    {{ ucfirst(str_replace('_', ' ', $obs->tipo)) }}
                                                </span>
                                                <small class="text-secondary">
                                                    {{ $obs->created_at->format('d-m-Y H:i') }} —
                                                    {{ $obs->user->name ?? 'Sistema' }}
                                                </small>
                                                <p class="mb-1 mt-1">{{ $obs->nota }}</p>
                                                @if ($obs->archivo)
                                                    <a href="{{ asset('storage/' . $obs->archivo) }}" target="_blank"
                                                        class="btn btn-sm btn-outline-secondary">
                                                        <i class="fas fa-paperclip me-1"></i>Ver adjunto
                                                    </a>
                                                @endif
                                            </div>
                                            <form method="POST" action="{{ route('observacion.destroy', $obs) }}"
                                                onsubmit="return confirm('¿Eliminar esta observación?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger ms-2">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <p class="text-secondary text-center py-3">
                                    <i class="fas fa-clipboard fa-2x mb-2 d-block"></i>
                                    Sin observaciones registradas.
                                </p>
                            @endforelse
                        </div>

                        {{-- Tab Historial --}}
                        <div id="tab-historial" class="d-none">
                            <div class="table-responsive">
                                <table class="table table-hover table-sm align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Fecha</th>
                                            <th>Acción</th>
                                            <th>Funcionario</th>
                                            <th>Hostname</th>
                                            <th>Observación</th>
                                            <th>Registrado por</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($detalle_equipo->historialAsignaciones as $h)
                                            <tr>
                                                <td class="text-secondary small text-nowrap">
                                                    {{ $h->fecha->format('d-m-Y H:i') }}
                                                </td>
                                                <td>
                                                    @php
                                                        $colorAccion = match ($h->accion) {
                                                            'asignado' => 'success',
                                                            'reasignado' => 'primary',
                                                            'desasignado' => 'warning',
                                                            'actualizado' => 'info',
                                                            default => 'secondary',
                                                        };
                                                    @endphp
                                                    <span
                                                        class="badge bg-{{ $colorAccion }}-subtle text-{{ $colorAccion }}-emphasis border border-{{ $colorAccion }}-subtle">
                                                        {{ ucfirst($h->accion) }}
                                                    </span>
                                                </td>
                                                <td>{{ $h->funcionario->nombre ?? 'Eliminado' }}</td>
                                                <td class="text-secondary small">{{ $h->hostname ?? '—' }}</td>
                                                <td class="text-secondary small">{{ $h->observacion ?? '—' }}</td>
                                                <td class="text-secondary small">{{ $h->user->name ?? 'Sistema' }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center text-secondary py-4">
                                                    Sin historial registrado.
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
    <div class="modal fade" id="modal-editar-hostname" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-computer me-2"></i>Editar Hostname
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="{{ route('equipo-asignado.hostname.update', $detalle_equipo) }}">
                    @csrf @method('PATCH')
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Hostname</label>
                            <input type="text" name="hostname" id="modal-hostname-input" class="form-control"
                                required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Comentario <small class="text-muted">(opcional)</small></label>
                            <textarea name="comentario" class="form-control" rows="2" placeholder="Motivo del cambio..."></textarea>
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
    @push('scripts')
        <script src="{{ asset('js/equipos_asignados.js') }}"></script>
    @endpush
@endsection
