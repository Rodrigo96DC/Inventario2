@extends('layouts.app')

@push('scripts')
    <script src="{{ asset('js/listas.js') }}"></script>
@endpush

@section('content')
<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0">
            <i class="fas fa-list-check text-primary me-2"></i>Listas
        </h3>
        <div class="d-flex gap-2">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal-nueva-lista">
                <i class="fas fa-plus me-1"></i>Nueva lista
            </button>
            <button class="btn btn-outline-warning" data-bs-toggle="modal" data-bs-target="#modal-importar-baja">
                <i class="fas fa-file-import me-1"></i>Importar baja
            </button>
        </div>
    </div>

    @if (session('info'))
        <div class="alert alert-info alert-dismissible fade show">
            {{ session('info') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="table-responsive">
            {{-- Tabs --}}
<ul class="nav nav-tabs mb-3">
    <li class="nav-item">
        <button class="nav-link active" data-tab="activas">
            <i class="fas fa-clock me-1"></i>En proceso
            <span class="badge bg-secondary ms-1">{{ $listas->total() }}</span>
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-tab="completadas">
            <i class="fas fa-check-circle me-1"></i>Completadas/Canceladas
            <span class="badge bg-success ms-1">{{ $listasCompletadas->total() }}</span>
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-tab="eliminadas">
            <i class="fas fa-trash me-1"></i>Eliminadas
            <span class="badge bg-danger ms-1">{{ $listasEliminadas->total() }}</span>
        </button>
    </li>
</ul>

{{-- Tab Activas --}}
<div id="tab-activas">
    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                @include('listas._tabla', ['coleccion' => $listas, 'mostrarRestore' => false])
            </table>
        </div>
        @if ($listas->hasPages())
            <div class="card-footer bg-body-tertiary">{{ $listas->links() }}</div>
        @endif
    </div>
</div>

{{-- Tab Completadas --}}
<div id="tab-completadas" class="d-none">
    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                @include('listas._tabla', ['coleccion' => $listasCompletadas, 'mostrarRestore' => false])
            </table>
        </div>
        @if ($listasCompletadas->hasPages())
            <div class="card-footer bg-body-tertiary">{{ $listasCompletadas->appends(['completadas' => request('completadas')])->links() }}</div>
        @endif
    </div>
</div>

{{-- Tab Eliminadas --}}
<div id="tab-eliminadas" class="d-none">
    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                @include('listas._tabla', ['coleccion' => $listasEliminadas, 'mostrarRestore' => true])
            </table>
        </div>
        @if ($listasEliminadas->hasPages())
            <div class="card-footer bg-body-tertiary">{{ $listasEliminadas->appends(['eliminadas' => request('eliminadas')])->links() }}</div>
        @endif
    </div>
</div>
            <table class="table table-hover align-middle mb-0">
         
              
            </table>
        </div>
        @if ($listas->hasPages())
            <div class="card-footer bg-body-tertiary">
                {{ $listas->links() }}
            </div>
        @endif
    </div>
</div>


{{-- Modal nueva lista --}}
<div class="modal fade" id="modal-nueva-lista" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="titulo-modal-lista">
                    <i class="fas fa-plus me-2"></i>Nueva Lista
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('lista.store') }}" id="form-lista" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="_method" id="lista-method" value="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nombre</label>
                        <input type="text" name="nombre" id="lista-nombre" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tipo</label>
                        <select name="tipo" id="lista-tipo" class="form-select" required>
                            <option value="equipos">Equipos</option>
                            <option value="funcionarios">Funcionarios</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Estado</label>
                        <select name="estado" id="lista-estado" class="form-select" required>
                            <option value="en_proceso">En proceso</option>
                            <option value="realizada">Realizada</option>
                            <option value="cancelada">Cancelada</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Descripción <small class="text-muted">(opcional)</small></label>
                        <textarea name="descripcion" id="lista-descripcion" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="publica" id="lista-publica" value="1">
                        <label class="form-check-label" for="lista-publica">Lista pública</label>
                    </div>
                    
                    {{-- Opción baja --}}
                    <div class="form-check form-switch mb-3" id="div-auto-baja">
                        <input class="form-check-input" type="checkbox" name="auto_baja" id="lista-auto-baja" value="1">
                        <label class="form-check-label" for="lista-auto-baja">
                            <i class="fas fa-arrow-down text-warning me-1"></i>Es lista de baja
                        </label>
                    </div>

                    {{-- Excel de equipos (solo si es baja) --}}
                    <div id="div-excel-baja" class="d-none">
                        <div class="alert alert-warning small mb-2">
                            <i class="fas fa-info-circle me-1"></i>
                            El archivo debe tener una columna <strong>inventario</strong> con los números de inventario.
                            Se creará la lista con nombre <strong>"Equipos de baja [fecha]"</strong> automáticamente.
                        </div>
                        <label class="form-label">Cargar Excel de equipos <small class="text-muted">(opcional)</small></label>
                        <input type="file" name="archivo" id="lista-archivo" accept=".xlsx,.xls,.csv" class="form-control">
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

{{-- Modal importar baja --}}
<div class="modal fade" id="modal-importar-baja" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-file-import me-2"></i>Importar equipos de baja
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('lista.importar-baja') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <p class="text-secondary small mb-3">
                        El archivo debe tener una columna llamada <strong>inventario</strong> con los números de inventario de los equipos a dar de baja.
                        Se creará automáticamente una lista llamada <strong>"Equipos de baja {{ now()->format('d-m-Y') }}"</strong>.
                    </p>
                    <input type="file" name="archivo" accept=".xlsx,.xls,.csv" class="form-control" required>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-upload me-1"></i>Importar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection