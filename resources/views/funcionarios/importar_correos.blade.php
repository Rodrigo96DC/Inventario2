@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12 col-md-8 mx-auto">
            <div class="card shadow-sm">
                <div class="card-header bg-body-tertiary">
                    <h5 class="mb-0">
                        <i class="fas fa-envelope me-2"></i>Importar correos desde Excel
                    </h5>
                </div>
                <div class="card-body">
                    <p class="text-secondary small mb-3">
                        El archivo debe tener las columnas: 
                        <strong>Nombre Usuario, Email usuario</strong>
                    </p>
                    <p class="text-secondary small mb-3">
                        El sistema buscará cada funcionario por nombre y actualizará su correo.
                        Se generará un Excel con el resultado de cada fila.
                    </p>
                    <form method="POST" action="{{ route('funcionario.importar-correos.post') }}"
                          enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Archivo Excel</label>
                            <input type="file" name="archivo" accept=".xlsx,.xls,.csv"
                                   class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-upload me-1"></i>Importar correos
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection