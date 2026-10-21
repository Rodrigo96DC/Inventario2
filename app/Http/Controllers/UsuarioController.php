<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UsuarioController extends Controller
{
    public function index()
    {
        $usuarios = User::with('roles')->orderBy('name')->get();
        $roles    = Role::orderBy('name')->get();

        return view('usuarios.index', compact('usuarios', 'roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
            'roles'    => 'nullable|array',
            'roles.*'  => 'exists:roles,name',
        ], [
            'name.required'      => 'El nombre es obligatorio.',
            'email.required'     => 'El correo es obligatorio.',
            'email.unique'       => 'Ya existe una cuenta con ese correo.',
            'password.required'  => 'La contraseña es obligatoria.',
            'password.min'       => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
        ]);

        $usuario = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'activo'   => true,
        ]);

        if ($request->roles) {
            $usuario->syncRoles($request->roles);
        }

        return redirect()->route('usuario.index')->with('info', 'Usuario creado correctamente.');
    }

    public function update(Request $request, User $usuario)
    {
        $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $usuario->id,
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,name',
        ]);

        $usuario->update([
            'name'  => $request->name,
            'email' => $request->email,
        ]);

        $usuario->syncRoles($request->roles ?? []);

        return redirect()->route('usuario.index')->with('info', 'Usuario actualizado correctamente.');
    }

    public function cambiarPassword(Request $request, User $usuario)
    {
        $request->validate([
            'password' => 'required|min:8|confirmed',
        ], [
            'password.required'  => 'La contraseña es obligatoria.',
            'password.min'       => 'Mínimo 8 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
        ]);

        $usuario->update(['password' => Hash::make($request->password)]);

        return redirect()->route('usuario.index')->with('info', 'Contraseña actualizada.');
    }

    public function toggleActivo(User $usuario)
    {
        if ($usuario->id === auth()->id()) {
            return redirect()->route('usuario.index')->with('info', 'No puedes desactivar tu propia cuenta.');
        }

        $usuario->update(['activo' => ! $usuario->activo]);
        $msg = $usuario->activo ? 'Usuario activado.' : 'Usuario desactivado.';

        return redirect()->route('usuario.index')->with('info', $msg);
    }

    public function destroy(User $usuario)
    {
        if ($usuario->id === auth()->id()) {
            return redirect()->route('usuario.index')->with('info', 'No puedes eliminar tu propia cuenta.');
        }

        $usuario->delete();

        return redirect()->route('usuario.index')->with('info', 'Usuario eliminado.');
    }

    // Copiar permisos de un usuario a otros
    public function copiarRoles(Request $request)
    {
        $request->validate([
            'origen_id'   => 'required|exists:users,id',
            'destinos'    => 'required|array|min:1',
            'destinos.*'  => 'exists:users,id',
        ]);

        $origen  = User::findOrFail($request->origen_id);
        $roles   = $origen->roles->pluck('name')->toArray();

        foreach ($request->destinos as $destinoId) {
            User::findOrFail($destinoId)->syncRoles($roles);
        }

        return redirect()->route('usuario.index')
            ->with('info', 'Roles copiados a ' . count($request->destinos) . ' usuario(s).');
    }

    // Asignación masiva de roles
    public function asignarRolesMasivo(Request $request)
    {
        $request->validate([
            'usuarios' => 'required|array|min:1',
            'usuarios.*' => 'exists:users,id',
            'roles'    => 'required|array|min:1',
            'roles.*'  => 'exists:roles,name',
        ]);

        foreach ($request->usuarios as $userId) {
            User::findOrFail($userId)->syncRoles($request->roles);
        }

        return redirect()->route('usuario.index')
            ->with('info', 'Roles asignados a ' . count($request->usuarios) . ' usuario(s).');
    }
}