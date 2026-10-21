<span data-total="{{ $auditorias->total() }}" class="d-none"></span>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Fecha</th>
                    <th>Módulo</th>
                    <th>Registro</th>
                    <th>Acción</th>
                    <th>Usuario</th>
                    <th>Cambios</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($auditorias as $auditoria)
                    <tr>
                        <td class="text-secondary small text-nowrap">
                            {{ $auditoria->created_at->format('d-m-Y H:i') }}
                        </td>
                        <td>
                            <span class="badge bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle">
                                {{ $auditoria->nombre_modulo }}
                            </span>
                        </td>
                        <td class="fw-medium">{{ $auditoria->descripcion ?? '—' }}</td>
                        <td>
                            <span class="badge bg-{{ $auditoria->color_accion }}-subtle text-{{ $auditoria->color_accion }}-emphasis border border-{{ $auditoria->color_accion }}-subtle">
                                {{ ucfirst(str_replace('_', ' ', $auditoria->accion)) }}
                            </span>
                        </td>
                        <td class="text-secondary small">{{ $auditoria->user->name ?? 'Sistema' }}</td>
                        <td>
                            @if ($auditoria->cambios)
                                <button class="btn btn-sm btn-outline-secondary" type="button"
                                        data-bs-toggle="collapse"
                                        data-bs-target="#cambios-{{ $auditoria->id }}">
                                    <i class="fas fa-code-compare"></i>
                                </button>
                                <div class="collapse mt-1" id="cambios-{{ $auditoria->id }}">
                                    <div class="cambios-grid">
                                        @foreach ($auditoria->cambios['despues'] as $campo => $despues)
                                            <div class="cambios-campo">{{ $campo }}</div>
                                            <div class="cambios-antes">{{ $auditoria->cambios['antes'][$campo] ?? '—' }}</div>
                                            <div class="cambios-flecha"><i class="fas fa-arrow-right"></i></div>
                                            <div class="cambios-despues">{{ $despues }}</div>
                                        @endforeach
                                    </div>
                                </div>
                            @else
                                <span class="text-secondary small">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-secondary py-5">
                            <i class="fas fa-folder-open fa-2x mb-2 d-block"></i>
                            No hay registros de auditoría.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($auditorias->hasPages())
        <div class="card-footer bg-body-tertiary">
            {{ $auditorias->links() }}
        </div>
    @endif
</div>