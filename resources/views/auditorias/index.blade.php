@extends('layouts.app')

@push('estilos')
    <link rel="stylesheet" href="{{ asset('css/auditorias.css') }}">
@endpush

@section('content')
<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <h3 class="mb-0">
        <i class="fas fa-clock-rotate-left text-primary me-2"></i>Auditoría
        <span class="badge bg-secondary fs-6 align-middle" id="badge-total">{{ $auditorias->total() }}</span>
    </h3>

<a href="{{ route('auditoria.exportar') }}"
   id="btn-exportar"
   data-url="{{ route('auditoria.exportar') }}"
   class="btn btn-outline-success">
    <i class="fas fa-file-excel me-1"></i>Exportar
</a>
</div>

    {{-- Filtros --}}
    <div class="card shadow-sm mb-3">
        <div class="card-body py-2">
            <form method="GET" action="{{ route('auditoria.index') }}" id="form-filtros" class="row g-2 align-items-end">

                <div class="col-12 col-sm-6 col-md-3">
                    <label class="form-label small mb-1">Buscar registro</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text"><i class="fas fa-magnifying-glass"></i></span>
                        <input type="search" name="buscar" id="buscar" value="{{ request('buscar') }}"
                            placeholder="Nombre del registro..."
                            class="form-control">
                    </div>
                </div>

                <div class="col-6 col-sm-4 col-md-2">
                    <label class="form-label small mb-1">Módulo</label>
                    <select name="modulo" class="form-select form-select-sm">
                        <option value="">Todos</option>
                        @foreach ($modulos as $clase => $nombre)
                            <option value="{{ $clase }}" @selected(request('modulo') === $clase)>{{ $nombre }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-6 col-sm-4 col-md-2">
                    <label class="form-label small mb-1">Acción</label>
                    <select name="accion" class="form-select form-select-sm">
                        <option value="">Todas</option>
                        @foreach ($acciones as $accion)
                            <option value="{{ $accion }}" @selected(request('accion') === $accion)>{{ ucfirst($accion) }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-6 col-sm-4 col-md-2">
                    <label class="form-label small mb-1">Desde</label>
                    <input type="date" name="desde" value="{{ request('desde') }}" class="form-control form-control-sm">
                </div>

                <div class="col-6 col-sm-4 col-md-2">
                    <label class="form-label small mb-1">Hasta</label>
                    <input type="date" name="hasta" value="{{ request('hasta') }}" class="form-control form-control-sm">
                </div>

                <div class="col-12 col-md-1 d-flex gap-1">
                    <button type="submit" class="btn btn-primary btn-sm w-100">
                        <i class="fas fa-filter"></i>
                    </button>
                    <a href="{{ route('auditoria.index') }}" class="btn btn-outline-secondary btn-sm w-100" title="Limpiar filtros">
                        <i class="fas fa-xmark"></i>
                    </a>
                </div>

            </form>
        </div>
    </div>

    {{-- Tabla --}}
<div id="contenedor-tabla">
    @include('auditorias._tabla')
</div>
</div>
@push('scripts')
    <script src="{{ asset('js/auditorias.js') }}"></script>
@endpush
@endsection