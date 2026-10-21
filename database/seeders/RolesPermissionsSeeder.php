<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $modulos = [
            'equipos',
            'detalle_equipos',
            'funcionarios',
            'asignaciones',
            'insumos',
            'programas',
            'listas',
            'pisos',
            'mermas',
        ];

        $acciones = ['ver', 'crear', 'editar', 'eliminar', 'restaurar'];

        foreach ($modulos as $modulo) {
            foreach ($acciones as $accion) {
                Permission::firstOrCreate(['name' => "{$modulo}.{$accion}"]);
            }
        }

        $admin = Role::firstOrCreate(['name' => 'Administrador']);
        $admin->syncPermissions(Permission::all());

        $lector = Role::firstOrCreate(['name' => 'Lector']);
        $lector->syncPermissions(Permission::where('name', 'like', '%.ver')->get());
    }
}