@extends('layouts.app')

@push('scripts')
    <script src="{{ asset('js/usuarios.js') }}"></script>
@endpush

@section('content')
<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0"><i class="fas fa-users-gear text-primary me-2"></i>Usuarios</h3>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal-nuevo">
            <i class="fas fa-plus me-1"></i>Nuevo usuario
        </button>
    </div>

    {{-- Acciones masivas --}}
    <div class="card shadow-sm mb-3" id="panel-masivo" style="display:none!important;">
        <div class="card-body py-2 d-flex align-items-center gap-3 flex-wrap">
            <span class="fw-bold text-primary">
                <i class="fas fa-check-square me-1"></i>
                <span id="contador-seleccionados">0</span> seleccionado(s)
            </span>
            <div class="d-flex gap-2 ms-auto flex-wrap">
                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modal-masivo">
                    <i class="fas fa-shield me-1"></i>Asignar roles
                </button>
                <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#modal-copiar">
                    <i class="fas fa-copy me-1"></i>Copiar roles de...
                </button>
                <button class="btn btn-sm btn-outline-secondary" id="btn-limpiar-sel">
                    <i class="fas fa-xmark me-1"></i>Limpiar
                </button>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:40px;">
                            <input type="checkbox" id="check-todos" class="form-check-input">
                        </th>
                        <th>Nombre</th>
                        <th>Correo</th>
                        <th>Roles</th>
                        <th class="text-center">Estado</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($usuarios as $usuario)
                        <tr>
                            <td>
                                @if ($usuario->id !== auth()->id())
                                    <input type="checkbox" class="form-check-input check-usuario"
                                           value="{{ $usuario->id }}"
                                           data-nombre="{{ $usuario->name }}">
                                @endif
                            </td>
                            <td class="fw-medium">{{ $usuario->name }}</td>
                            <td class="text-secondary">{{ $usuario->email }}</td>
                            <td>
                                @forelse ($usuario->roles as $rol)
                                    <span class="badge bg-primary-subtle text-primary-emphasis border border-primary-subtle">
                                        {{ $rol->name }}
                                    </span>
                                @empty
                                    <span class="text-secondary small">Sin rol</span>
                                @endforelse
                            </td>
                            <td class="text-center">
                                @if ($usuario->activo)
                                    <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle">
                                        <i class="fas fa-circle-check me-1"></i>Activo
                                    </span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger-emphasis border border-danger-subtle">
                                        <i class="fas fa-circle-xmark me-1"></i>Inactivo
                                    </span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary btn-editar"
                                            data-id="{{ $usuario->id }}"
                                            data-name="{{ $usuario->name }}"
                                            data-email="{{ $usuario->email }}"
                                            data-roles="{{ $usuario->roles->pluck('name')->toJson() }}"
                                            title="Editar">
                                        <i class="fas fa-pen"></i>
                                    </button>
                                    <button class="btn btn-outline-secondary btn-password"
                                            data-id="{{ $usuario->id }}"
                                            data-name="{{ $usuario->name }}"
                                            title="Cambiar contraseña">
                                        <i class="fas fa-key"></i>
                                    </button>
                                    @if ($usuario->id !== auth()->id())
                                        <form method="POST" action="{{ route('usuario.toggle', $usuario) }}" class="d-inline">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="btn btn-outline-{{ $usuario->activo ? 'warning' : 'success' }}"
                                                    title="{{ $usuario->activo ? 'Desactivar' : 'Activar' }}">
                                                <i class="fas fa-{{ $usuario->activo ? 'ban' : 'check' }}"></i>
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('usuario.destroy', $usuario) }}" class="d-inline"
                                              onsubmit="return confirm('¿Eliminar a {{ addslashes($usuario->name) }}?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" title="Eliminar">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-secondary py-5">
                                <i class="fas fa-users fa-2x mb-2 d-block"></i>
                                No hay usuarios registrados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Modal nuevo usuario --}}
<div class="modal fade" id="modal-nuevo" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-user-plus me-2"></i>Nuevo Usuario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('usuario.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label class="form-label">Nombre</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="form-group mb-3">
                        <label class="form-label">Correo electrónico</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="form-group mb-3">
                        <label class="form-label">Contraseña</label>
                        <input type="password" name="password" class="form-control" required minlength="8">
                    </div>
                    <div class="form-group mb-3">
                        <label class="form-label">Confirmar contraseña</label>
                        <input type="password" name="password_confirmation" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Roles</label>
                        @foreach ($roles as $rol)
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="roles[]"
                                       value="{{ $rol->name }}" id="rol-nuevo-{{ $rol->id }}">
                                <label class="form-check-label" for="rol-nuevo-{{ $rol->id }}">
                                    {{ $rol->name }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>Crear usuario
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal editar usuario --}}
<div class="modal fade" id="modal-editar" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-pen me-2"></i>Editar Usuario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="form-editar">
                @csrf @method('PUT')
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label class="form-label">Nombre</label>
                        <input type="text" name="name" id="editar-name" class="form-control" required>
                    </div>
                    <div class="form-group mb-3">
                        <label class="form-label">Correo electrónico</label>
                        <input type="email" name="email" id="editar-email" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Roles</label>
                        @foreach ($roles as $rol)
                            <div class="form-check">
                                <input class="form-check-input check-rol-editar" type="checkbox"
                                       name="roles[]" value="{{ $rol->name }}"
                                       id="rol-editar-{{ $rol->id }}">
                                <label class="form-check-label" for="rol-editar-{{ $rol->id }}">
                                    {{ $rol->name }}
                                </label>
                            </div>
                        @endforeach
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

{{-- Modal cambiar contraseña --}}
<div class="modal fade" id="modal-password" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-key me-2"></i>Cambiar Contraseña — <span id="password-nombre"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="form-password">
                @csrf @method('PATCH')
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label class="form-label">Nueva contraseña</label>
                        <input type="password" name="password" class="form-control" required minlength="8">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Confirmar contraseña</label>
                        <input type="password" name="password_confirmation" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>Cambiar contraseña
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal asignación masiva --}}
<div class="modal fade" id="modal-masivo" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-shield me-2"></i>Asignar Roles Masivo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('usuario.masivo') }}" id="form-masivo">
                @csrf
                <div id="inputs-masivo"></div>
                <div class="modal-body">
                    <p class="text-secondary small mb-3">
                        Se asignarán los roles seleccionados a <strong id="masivo-cantidad">0</strong> usuario(s).
                        Los roles anteriores serán reemplazados.
                    </p>
                    @foreach ($roles as $rol)
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="roles[]"
                                   value="{{ $rol->name }}" id="rol-masivo-{{ $rol->id }}">
                            <label class="form-check-label" for="rol-masivo-{{ $rol->id }}">
                                {{ $rol->name }}
                            </label>
                        </div>
                    @endforeach
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-shield me-1"></i>Asignar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal copiar roles --}}
<div class="modal fade" id="modal-copiar" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-copy me-2"></i>Copiar Roles De...</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('usuario.copiarRoles') }}" id="form-copiar">
                @csrf
                <div id="inputs-copiar"></div>
                <div class="modal-body">
                    <p class="text-secondary small mb-3">
                        Selecciona el usuario del que quieres copiar los roles hacia
                        <strong id="copiar-cantidad">0</strong> usuario(s) seleccionado(s).
                    </p>
                    <div class="form-group">
                        <label class="form-label">Copiar roles de:</label>
                        <select name="origen_id" class="form-select" required>
                            <option value="">Seleccione usuario origen</option>
                            @foreach ($usuarios as $u)
                                <option value="{{ $u->id }}">
                                    {{ $u->name }}
                                    ({{ $u->roles->pluck('name')->join(', ') ?: 'Sin rol' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-copy me-1"></i>Copiar roles
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection