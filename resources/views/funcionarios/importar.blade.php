@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12 col-md-8 col-lg-6 mx-auto">

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3 class="mb-0"><i class="fas fa-file-import text-primary me-2"></i>Carga masiva de funcionarios</h3>
                <a href="{{ route('funcionario.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left me-1"></i>Volver
                </a>
            </div>

            <div class="card shadow-sm mb-3">
                <div class="card-body">
                    <h6 class="card-title"><i class="fas fa-circle-info text-primary me-1"></i>Instrucciones</h6>
                    <ol class="mb-2">
                        <li>Descarga la plantilla Excel.</li>
                        <li>El campo <strong>nombre</strong> es obligatorio.</li>
                        <li>El campo <strong>region</strong> debe coincidir exactamente con una región registrada.</li>
                        <li>El campo <strong>activo</strong> acepta: <code>1</code>, <code>si</code>, <code>activo</code>, <code>true</code> para activo — cualquier otro valor se trata como inactivo.</li>
                        <li>Si un funcionario con el mismo RUT ya existe, esa fila se omite.</li>
                        <li>Al finalizar se descarga un Excel con el resultado de cada fila.</li>
                    </ol>

                    <div class="mt-2">
                        <strong class="d-block mb-1 small">Regiones disponibles:</strong>
                        <div class="d-flex flex-wrap gap-1">
                            @foreach ($regiones as $region)
                                <code class="small">{{ $region }} - </code>
                            @endforeach
                        </div>
                    </div>

                    <a href="{{ route('funcionario.plantilla') }}" class="btn btn-sm btn-outline-success mt-3">
                        <i class="fas fa-file-excel me-1"></i>Descargar plantilla
                    </a>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-body">
                    <form method="POST" action="{{ route('funcionario.importar.post') }}" enctype="multipart/form-data">
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
                                <i class="fas fa-upload me-1"></i>Importar funcionarios
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection