@extends('layouts.app')

@section('content')
<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <h3 class="mb-0">
            <i class="fas fa-building text-primary me-2"></i>Edificios y Pisos
        </h3>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal-edificio">
            <i class="fas fa-plus me-1"></i>Nuevo edificio
        </button>
    </div>

    @forelse ($edificios as $edificio)
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-body-tertiary d-flex justify-content-between align-items-center">
                <div>
                    <i class="fas fa-building me-2 text-primary"></i>
                    <strong>{{ $edificio->nombre }}</strong>
                    @if ($edificio->direccion)
                        <small class="text-secondary ms-2">{{ $edificio->direccion }}{{ $edificio->ciudad ? ', ' . $edificio->ciudad : '' }}</small>
                    @endif
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-outline-secondary btn-editar-edificio"
                            data-id="{{ $edificio->id }}"
                            data-nombre="{{ $edificio->nombre }}"
                            data-direccion="{{ $edificio->direccion }}"
                            data-ciudad="{{ $edificio->ciudad }}">
                        <i class="fas fa-pen"></i>
                    </button>
                    <form method="POST" action="{{ route('edificio.destroy', $edificio) }}"
                          onsubmit="return confirm('¿Eliminar {{ addslashes($edificio->nombre) }} y todos sus pisos?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse"
                            data-bs-target="#pisos-{{ $edificio->id }}">
                        <i class="fas fa-layer-group me-1"></i>{{ $edificio->pisos_count }} piso(s)
                    </button>
                </div>
            </div>

            <div class="collapse" id="pisos-{{ $edificio->id }}">
                <div class="card-body">
                    <div class="row g-3">
                        @foreach ($edificio->pisos as $piso)
                            <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                                <div class="card h-100 shadow-sm">
                                    @if ($piso->imagen)
                                        <img src="{{ asset('storage/' . $piso->imagen) }}"
                                             class="card-img-top" style="height:120px;object-fit:cover;"
                                             alt="{{ $piso->nombre }}">
                                    @else
                                        <div class="d-flex align-items-center justify-content-center bg-secondary-subtle"
                                             style="height:120px;">
                                            <i class="fas fa-map fa-2x text-secondary"></i>
                                        </div>
                                    @endif
                                    <div class="card-body py-2">
                                        <h6 class="card-title mb-0">{{ $piso->nombre }}</h6>
                                        <small class="text-secondary">{{ $piso->puestos->count() }} puesto(s)</small>
                                    </div>
                                 <div class="card-footer bg-transparent d-flex gap-2">
    <a href="{{ route('piso.show', $piso) }}" class="btn btn-sm btn-primary w-100">
        <i class="fas fa-map me-1"></i>Ver mapa
    </a>
    <button class="btn btn-sm btn-outline-secondary btn-editar-piso"
            data-id="{{ $piso->id }}"
            data-nombre="{{ $piso->nombre }}"
            data-numero="{{ $piso->numero }}"
            title="Editar piso">
        <i class="fas fa-pen"></i>
    </button>
</div>
                                </div>
                            </div>
                        @endforeach

                        {{-- Agregar piso --}}
                        <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                            <div class="card h-100 shadow-sm border-dashed" style="border: 2px dashed var(--bs-border-color);">
                                <div class="card-body d-flex align-items-center justify-content-center">
                                    <button class="btn btn-outline-primary btn-agregar-piso"
                                            data-edificio-id="{{ $edificio->id }}"
                                            data-edificio-nombre="{{ $edificio->nombre }}">
                                        <i class="fas fa-plus me-1"></i>Agregar piso
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="card shadow-sm">
            <div class="card-body text-center text-secondary py-5">
                <i class="fas fa-building fa-2x mb-2 d-block"></i>
                No hay edificios registrados.
            </div>
        </div>
    @endforelse
</div>

{{-- Modal nuevo/editar edificio --}}
<div class="modal fade" id="modal-edificio" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-edificio-titulo">
                    <i class="fas fa-building me-2"></i>Nuevo Edificio
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="form-edificio" method="POST" action="{{ route('edificio.store') }}">
                @csrf
                <input type="hidden" name="_method" id="edificio-method" value="POST">
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label class="form-label">Nombre</label>
                        <input type="text" name="nombre" id="edificio-nombre" class="form-control" required>
                    </div>
                    <div class="form-group mb-3">
                        <label class="form-label">Dirección <small class="text-muted">(opcional)</small></label>
                        <input type="text" name="direccion" id="edificio-direccion" class="form-control">
                    </div>
                    <div class="form-group mb-3">
                        <label class="form-label">Ciudad <small class="text-muted">(opcional)</small></label>
                        <input type="text" name="ciudad" id="edificio-ciudad" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="btn-guardar-edificio">
                        <i class="fas fa-save me-1"></i>Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal agregar piso --}}
<div class="modal fade" id="modal-piso" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-layer-group me-2"></i>Agregar Piso
                    <small class="text-secondary" id="modal-piso-edificio"></small>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="form-piso" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label">Nombre del piso</label>
                            <input type="text" name="nombre" class="form-control"
                                   placeholder="Ej: Piso 15, Subterráneo..." required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Número <small class="text-muted">(opcional)</small></label>
                            <input type="number" name="numero" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Imagen del mapa</label>
                            <input type="file" name="imagen" accept="image/png,image/jpeg"
                                   class="form-control" required>
                            <small class="text-muted">JPG o PNG — recomendado al menos 1200px de ancho</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade" id="modal-editar-piso" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-pen me-2"></i>Editar Piso
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="form-editar-piso" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label">Nombre del piso</label>
                            <input type="text" name="nombre" id="editar-piso-nombre"
                                   class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Número <small class="text-muted">(opcional)</small></label>
                            <input type="number" name="numero" id="editar-piso-numero" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label">
                                Nueva imagen del mapa <small class="text-muted">(opcional — solo si quieres reemplazarla)</small>
                            </label>
                            <input type="file" name="imagen" accept="image/png,image/jpeg" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>Guardar cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@push('scripts')
    <script src="{{ asset('js/edificios.js') }}"></script>
@endpush
@endsection