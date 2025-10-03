<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolesSeeder::class,
            UsuariosSeeder::class,
            ClientesSeeder::class,
            ProveedoresSeeder::class,
            VehiculosSeeder::class,
            CuentasSeeder::class,
            
            
            
        ]);
        
        $this->command->info('✅ Base de datos poblada exitosamente!');
        $this->command->info('👤 Usuarios de prueba:');
        $this->command->info('   - Admin: admin@concesionario.com / admin123');
        $this->command->info('   - Contable: contable@concesionario.com / contable123');
        $this->command->info('   - Vendedor: vendedor@concesionario.com / vendedor123');
    }
}