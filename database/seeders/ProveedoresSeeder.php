<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProveedoresSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('proveedores')->insert([
            [
                'nombre' => 'Toyota Colombia S.A.',
                'nit' => '900123456-1',
                'direccion' => 'Autopista Norte #245-67, Bogotá',
                'telefono' => '+57 1 3456789',
                'email' => 'ventas@toyota-colombia.com',
            ],
            [
                'nombre' => 'Nissan Motors de Colombia',
                'nit' => '900234567-2',
                'direccion' => 'Calle 100 #45-23, Bogotá',
                'telefono' => '+57 1 4567890',
                'email' => 'contacto@nissan-colombia.com',
            ],
            [
                'nombre' => 'Mazda Importadores S.A.',
                'nit' => '900345678-3',
                'direccion' => 'Avenida Boyacá #78-90, Bogotá',
                'telefono' => '+57 1 5678901',
                'email' => 'proveedores@mazda-colombia.com',
            ],
        ]);
    }
}
