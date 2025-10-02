<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Rol;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsuariosSeeder extends Seeder
{
    public function run(): void
    {
        // Usuario Administrador
        $admin = User::create([
            'nombre' => 'Administrador Sistema',
            'email' => 'admin@concesionario.com',
            'password' => Hash::make('admin123'),
            'activo' => true,
        ]);
        $admin->roles()->attach(Rol::where('nombre', 'admin')->first());

        // Usuario Contable
        $contable = User::create([
            'nombre' => 'Contable Principal',
            'email' => 'contable@concesionario.com',
            'password' => Hash::make('contable123'),
            'activo' => true,
        ]);
        $contable->roles()->attach(Rol::where('nombre', 'contable')->first());

        // Usuario Vendedor
        $vendedor = User::create([
            'nombre' => 'Vendedor Ejemplo',
            'email' => 'vendedor@concesionario.com',
            'password' => Hash::make('vendedor123'),
            'activo' => true,
        ]);
        $vendedor->roles()->attach(Rol::where('nombre', 'vendedor')->first());
    }
}