<?php

namespace Database\Seeders;

use App\Models\Proveedor;
use Illuminate\Database\Seeder;

class ProveedoresSeeder extends Seeder
{
    public function run(): void
    {
        $proveedores = [
            [
                'nombre' => 'Toyota Colombia S.A.',
                'direccion' => 'Autopista Norte #123-45, Bogotá',
                'telefono' => '+57 1 2345678',
                'email' => 'contacto@toyota.co',
            ],
            [
                'nombre' => 'Renault S.A.S.',
                'direccion' => 'Calle 100 #25-50, Bogotá',
                'telefono' => '+57 1 3456789',
                'email' => 'ventas@renault.co',
            ],
            [
                'nombre' => 'Chevrolet Colombia',
                'direccion' => 'Av. Boyacá #78-90, Bogotá',
                'telefono' => '+57 1 4567890',
                'email' => 'info@chevrolet.co',
            ],
            [
                'nombre' => 'Mazda Motors',
                'direccion' => 'Carrera 60 #25-35, Medellín',
                'telefono' => '+57 4 5678901',
                'email' => 'mazda@mazda.co',
            ],
        ];

        foreach ($proveedores as $proveedor) {
            Proveedor::create($proveedor);
        }
    }
}