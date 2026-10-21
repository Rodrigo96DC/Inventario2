<thead class="table-light">
    <tr>
        <th>Nombre</th>
        <th class="text-center">Tipo</th>
        <th class="text-center">Items</th>
        <th class="text-center">Estado</th>
        <th class="text-center">Visibilidad</th>
        <th>Creador</th>
        <th>Fecha</th>
        <th class="text-end">Acciones</th>
    </tr>
</thead>
<tbody>
    @forelse ($coleccion as $lista)
        @php
            $colorEstado = match($lista->estado) {
                'en_proceso' => 'warning',
                'realizada'  => 'success',
                'cancelada'  => 'danger',
            };
            $labelEstado = match($lista->estado) {
                'en_proceso' => 'En proceso',
                'realizada'  => 'Realizada',
                'cancelada'  => 'Cancelada',
            };
        @endphp
        <tr>
            <td class="fw-medium">
                {{ $lista->nombre }}
                @if ($lista->auto_baja)
                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle ms-1">
                        <i class="fas fa-arrow-down me-1"></i>Baja
                    </span>
                @endif
            </td>
            <td class="text-center">
                @if ($lista->tipo === 'equipos')
                    <span class="badge bg-primary-subtle text-primary-emphasis border border-primary-subtle">
                        <i class="fas fa-desktop me-1"></i>Equipos
                    </span>
                @else
                    <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle">
                        <i class="fas fa-users me-1"></i>Funcionarios
                    </span>
                @endif
            </td>
            <td class="text-center">
                <span class="badge bg-secondary">{{ $lista->items->count() }}</span>
            </td>
            <td class="text-center">
                <span class="badge bg-{{ $colorEstado }}-subtle text-{{ $colorEstado }}-emphasis border border-{{ $colorEstado }}-subtle">
                    {{ $labelEstado }}
                </span>
            </td>
            <td class="text-center">
                @if ($lista->publica)
                    <i class="fas fa-globe text-success" title="Pública"></i>
                @else
                    <i class="fas fa-lock text-secondary" title="Privada"></i>
                @endif
            </td>
            <td class="small text-secondary">{{ $lista->user->name ?? '—' }}</td>
            <td class="small text-secondary">{{ $lista->created_at->format('d-m-Y H:i') }}</td>
            <td class="text-end">
                <div class="btn-group btn-group-sm">
                    @if (!$mostrarRestore)
                        <a href="{{ route('lista.show', $lista) }}" class="btn btn-outline-info">
                            <i class="fas fa-eye"></i>
                        </a>
                        @if ($lista->user_id === auth()->id())
                            <button class="btn btn-outline-primary btn-editar-lista"
                                    data-id="{{ $lista->id }}"
                                    data-nombre="{{ $lista->nombre }}"
                                    data-estado="{{ $lista->estado }}"
                                    data-publica="{{ $lista->publica ? '1' : '0' }}"
                                    data-descripcion="{{ $lista->descripcion }}">
                                <i class="fas fa-pen"></i>
                            </button>
                            <form method="POST" action="{{ route('lista.destroy', $lista) }}"
                                  class="d-inline"
                                  onsubmit="return confirm('¿Eliminar esta lista?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        @endif
                    @else
                        <form method="POST" action="{{ route('lista.restore', $lista->id) }}" class="d-inline">
                            @csrf @method('PATCH')
                            <button type="submit" class="btn btn-outline-success" title="Restaurar">
                                <i class="fas fa-rotate-left"></i>
                            </button>
                        </form>
                    @endif
                </div>
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="8" class="text-center text-secondary py-5">
                <i class="fas fa-list fa-2x mb-2 d-block"></i>
                No hay listas.
            </td>
        </tr>
    @endforelse
</tbody>