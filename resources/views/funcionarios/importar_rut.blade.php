@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12 col-md-8 mx-auto">
            <div class="card shadow-sm">
                <div class="card-header bg-body-tertiary d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-id-card me-2"></i>Actualizar datos desde Excel
                    </h5>
                    <a href="{{ route('funcionario.plantilla-rut') }}" class="btn btn-outline-success btn-sm">
                        <i class="fas fa-file-excel me-1"></i>Descargar plantilla
                    </a>
                </div>
                <div class="card-body">
                    <p class="text-secondary small mb-3">
                        El archivo debe tener las columnas:
                        <strong>RUT, APELLIDO PATERNO, APELLIDO MATERNO, NOMBRES, DIVISION, DEPARTAMENTO, UNIDAD, MAIL</strong>
                    </p>
                    <p class="text-secondary small mb-3">
                        El sistema buscará cada funcionario por nombre y actualizará: RUT, División, Departamento, Unidad y Correo.
                        Se generará un Excel con el resultado de cada fila.
                    </p>

                    <form method="POST" action="{{ route('funcionario.importar-rut.post') }}"
                          enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Archivo Excel</label>
                            <input type="file" name="archivo" accept=".xlsx,.xls,.csv"
                                   class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-upload me-1"></i>Importar y actualizar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection