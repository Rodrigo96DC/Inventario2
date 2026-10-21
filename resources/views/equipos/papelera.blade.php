@extends('layouts.app')

@section('content')
<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0"><i class="fas fa-trash text-secondary me-2"></i>Papelera de Equipos</h3>
        <a href="{{ route('equipo.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>Volver
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Tipo</th>
                        <th>Marca</th>
                        <th>Modelo</th>
                        <th>Eliminado el</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($equipos as $equipo)
                        <tr>
                            <td>{{ $equipo->tipoEquipo->nombre ?? '—' }}</td>
                            <td>{{ $equipo->marca }}</td>
                            <td>{{ $equipo->modelo }}</td>
                            <td>{{ $equipo->deleted_at->format('d-m-Y H:i') }}</td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    @can('equipos.restaurar')
                                        <button type="submit" form="restaurar-{{ $equipo->id }}" class="btn btn-outline-success" title="Restaurar">
                                            <i class="fas fa-rotate-left"></i>
                                        </button>
                                    @endcan
                                    @can('equipos.eliminar')
                                        <button type="submit" form="eliminar-perm-{{ $equipo->id }}" class="btn btn-outline-danger" title="Eliminar permanente">
                                            <i class="fas fa-trash-can"></i>
                                        </button>
                                    @endcan
                                </div>

                                @can('equipos.restaurar')
                                    <form id="restaurar-{{ $equipo->id }}" method="POST" action="{{ route('equipo.restore', $equipo->id) }}" class="d-none">
                                        @csrf
                                        @method('PATCH')
                                    </form>
                                @endcan
                                @can('equipos.eliminar')
                                    <form id="eliminar-perm-{{ $equipo->id }}" method="POST" action="{{ route('equipo.forceDelete', $equipo->id) }}" class="d-none"
                                          onsubmit="return confirm('¿Eliminar permanentemente {{ addslashes($equipo->marca.' '.$equipo->modelo) }}? Esta acción no se puede deshacer.')">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-secondary py-5">
                                <i class="fas fa-box-open fa-2x mb-2 d-block"></i>
                                La papelera está vacía.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($equipos->hasPages())
            <div class="card-footer bg-body-tertiary">
                {{ $equipos->links() }}
            </div>
        @endif
    </div>
</div>
@endsection