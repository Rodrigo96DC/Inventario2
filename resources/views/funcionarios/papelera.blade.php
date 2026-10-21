@extends('layouts.app')

@section('content')
<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0"><i class="fas fa-trash text-secondary me-2"></i>Papelera de Funcionarios</h3>
        <a href="{{ route('funcionario.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>Volver
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Nombre</th>
                        <th>RUT</th>
                        <th>Eliminado el</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($funcionarios as $funcionario)
                        <tr>
                            <td>{{ $funcionario->nombre }}</td>
                            <td class="text-secondary">{{ $funcionario->rut_formateado ?? '—' }}</td>
                            <td>{{ $funcionario->deleted_at->format('d-m-Y H:i') }}</td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    @can('funcionarios.restaurar')
                                        <button type="submit" form="restaurar-{{ $funcionario->id }}" class="btn btn-outline-success" title="Restaurar">
                                            <i class="fas fa-rotate-left"></i>
                                        </button>
                                    @endcan
                                    @can('funcionarios.eliminar')
                                        <button type="submit" form="eliminar-perm-{{ $funcionario->id }}" class="btn btn-outline-danger" title="Eliminar permanente">
                                            <i class="fas fa-trash-can"></i>
                                        </button>
                                    @endcan
                                </div>

                                @can('funcionarios.restaurar')
                                    <form id="restaurar-{{ $funcionario->id }}" method="POST" action="{{ route('funcionario.restore', $funcionario->id) }}" class="d-none">
                                        @csrf
                                        @method('PATCH')
                                    </form>
                                @endcan
                                @can('funcionarios.eliminar')
                                    <form id="eliminar-perm-{{ $funcionario->id }}" method="POST" action="{{ route('funcionario.forceDelete', $funcionario->id) }}" class="d-none"
                                          onsubmit="return confirm('¿Eliminar permanentemente a {{ addslashes($funcionario->nombre) }}? Esta acción no se puede deshacer.')">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-secondary py-5">
                                <i class="fas fa-box-open fa-2x mb-2 d-block"></i>
                                La papelera está vacía.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($funcionarios->hasPages())
            <div class="card-footer bg-body-tertiary">
                {{ $funcionarios->links() }}
            </div>
        @endif
    </div>
</div>
@endsection