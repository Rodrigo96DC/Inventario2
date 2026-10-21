@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12 col-md-8 col-lg-7 mx-auto">

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3 class="mb-0">
                    <i class="fas fa-file-import text-primary me-2"></i>Carga masiva de inventario
                </h3>
                <a href="{{ route('detalle-equipo.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left me-1"></i>Volver
                </a>
            </div>

            <div class="card shadow-sm mb-3">
                <div class="card-body">
                    <h6 class="card-title"><i class="fas fa-circle-info text-primary me-1"></i>Instrucciones</h6>
                    <ol class="mb-3">
                        <li>Descarga la plantilla Excel.</li>
                        <li><strong>marca</strong> y <strong>modelo</strong> deben coincidir exactamente con un equipo del catálogo.</li>
                        <li><strong>inventario</strong> y <strong>serial</strong> son obligatorios y deben ser únicos.</li>
                        <li><strong>estado</strong> acepta: <code>Correcto</code>, <code>Observación</code>, <code>Proceso de Baja</code>, <code>De Baja</code>. Si no se indica, queda como <code>Correcto</code>.</li>
                        <li><strong>tipo_disco</strong> acepta: <code>HDD</code>, <code>SSD</code>, <code>SSD NVMe</code>, <code>Sin Disco</code>.</li>
                        <li><strong>teletrabajo</strong> y <strong>es_prestamo</strong> aceptan: <code>1</code>, <code>si</code>, <code>true</code>.</li>
                        <li>Al finalizar se descarga un Excel con el resultado fila por fila.</li>
                    </ol>

                    {{-- Referencias rápidas --}}
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <p class="small fw-bold mb-1">Estados disponibles:</p>
                            @foreach ($estados as $estado)
                                <span class="badge bg-{{ $estado->color }}-subtle text-{{ $estado->color }}-emphasis border border-{{ $estado->color }}-subtle me-1">
                                    {{ $estado->nombre }}
                                </span>
                            @endforeach
                        </div>
                        <div class="col-md-6">
                            <p class="small fw-bold mb-1">Tipos de disco:</p>
                            @foreach ($tiposDiscos as $disco)
                                <code class="me-1 small">{{ $disco->nombre }}</code>
                            @endforeach
                        </div>
                    </div>

                    <a href="{{ route('detalle-equipo.plantilla') }}" class="btn btn-sm btn-outline-success">
                        <i class="fas fa-file-excel me-1"></i>Descargar plantilla
                    </a>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-body">
                    <form method="POST" action="{{ route('detalle-equipo.importar.post') }}"
                          enctype="multipart/form-data">
                        @csrf
                        <div class="form-group mb-3">
                            <label for="archivo" class="form-label">Archivo Excel (.xlsx / .xls)</label>
                            <input type="file" name="archivo" id="archivo" accept=".xlsx,.xls"
                                class="form-control @error('archivo') is-invalid @enderror">
                            @error('archivo')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-upload me-1"></i>Importar inventario
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection