<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolesSeeder::class,
            CuentasSeeder::class,
            UsuariosSeeder::class,
            ProveedoresSeeder::class,
            ClientesSeeder::class,
        ]);
    }
}