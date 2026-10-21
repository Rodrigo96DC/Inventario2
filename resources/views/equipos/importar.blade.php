@extends('layouts.app')

@push('estilos')
    <link rel="stylesheet" href="{{ asset('css/equipos.css') }}">
@endpush

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12 col-md-8 col-lg-6 mx-auto">

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3 class="mb-0"><i class="fas fa-file-import text-primary me-2"></i>Carga masiva de equipos</h3>
                <a href="{{ route('equipo.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left me-1"></i>Volver
                </a>
            </div>

            {{-- Errores de importación --}}
            @if (session('errores_import'))
                <div class="alert alert-warning alert-dismissible fade show">
                    <strong><i class="fas fa-triangle-exclamation me-1"></i>Algunas filas no se importaron:</strong>
                    <ul class="mb-0 mt-2">
                        @foreach (session('errores_import') as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            {{-- Instrucciones --}}
            <div class="card shadow-sm mb-3">
                <div class="card-body">
                    <h6 class="card-title"><i class="fas fa-circle-info text-primary me-1"></i>Instrucciones</h6>
                    <ol class="mb-2">
                        <li>Descarga la plantilla Excel.</li>
                        <li>Llena las filas respetando los encabezados (no los cambies).</li>
                        <li>El campo <strong>tipo</strong> debe coincidir exactamente con uno de los tipos registrados: <code>Computador</code>, <code>Notebook</code>, <code>Monitor</code>, <code>Proyector</code>, <code>Impresora</code>, <code>Tablet</code>, <code>Plotter</code>, <code>Otro</code>.</li>
                        <li>Los campos <strong>marca</strong>, <strong>modelo</strong> y <strong>tipo</strong> son obligatorios.</li>
                        <li>Si un equipo con la misma marca y modelo ya existe, esa fila se omite.</li>
                    </ol>
                    <a href="{{ route('equipo.plantilla') }}" class="btn btn-sm btn-outline-success">
                        <i class="fas fa-file-excel me-1"></i>Descargar plantilla
                    </a>
                </div>
            </div>

            {{-- Formulario carga --}}
            <div class="card shadow-sm">
                <div class="card-body">
                    <form method="POST" action="{{ route('equipo.importar.post') }}" enctype="multipart/form-data">
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
                                <i class="fas fa-upload me-1"></i>Importar equipos
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection