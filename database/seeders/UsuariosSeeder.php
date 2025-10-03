<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Rol;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsuariosSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear usuario administrador
        $admin = User::create([
            'nombre' => 'Administrador Principal',
            'email' => 'admin@concesionario.com',
            'password' => Hash::make('admin123'),
            'activo' => true,
        ]);
        $admin->roles()->attach(Rol::where('nombre', 'admin')->first());

        // Crear usuario contable
        $contable = User::create([
            'nombre' => 'Contable Principal',
            'email' => 'contable@concesionario.com',
            'password' => Hash::make('contable123'),
            'activo' => true,
        ]);
        $contable->roles()->attach(Rol::where('nombre', 'contable')->first());

        // Crear usuario vendedor
        $vendedor = User::create([
            'nombre' => 'Vendedor Principal',
            'email' => 'vendedor@concesionario.com',
            'password' => Hash::make('vendedor123'),
            'activo' => true,
        ]);
        $vendedor->roles()->attach(Rol::where('nombre', 'vendedor')->first());

        $this->command->info('✅ Usuarios creados:');
        $this->command->info('   - Admin: admin@concesionario.com / admin123');
        $this->command->info('   - Contable: contable@concesionario.com / contable123');
        $this->command->info('   - Vendedor: vendedor@concesionario.com / vendedor123');
    }
}