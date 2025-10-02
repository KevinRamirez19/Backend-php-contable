<?php

namespace Database\Seeders;

use App\Models\Rol;
use Illuminate\Database\Seeder;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['nombre' => 'admin', 'descripcion' => 'Administrador del sistema con acceso completo'],
            ['nombre' => 'contable', 'descripcion' => 'Contable con acceso a módulos financieros'],
            ['nombre' => 'vendedor', 'descripcion' => 'Vendedor con acceso a ventas y clientes'],
        ];

        foreach ($roles as $rol) {
            Rol::create($rol);
        }
    }
}