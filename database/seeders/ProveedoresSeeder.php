<?php

namespace Database\Seeders;

use App\Models\Proveedor;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProveedoresSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $proveedores = [
            [
                'nombre' => 'Toyota Colombia S.A.',
                'direccion' => 'Autopista Norte #245-67, Bogotá',
                'telefono' => '+57 1 3456789',
                'email' => 'ventas@toyota-colombia.com'
            ],
            [
                'nombre' => 'Nissan Motors de Colombia',
                'direccion' => 'Calle 100 #45-23, Bogotá',
                'telefono' => '+57 1 4567890',
                'email' => 'contacto@nissan-colombia.com'
            ],
            [
                'nombre' => 'Mazda Importadores S.A.',
                'direccion' => 'Avenida Boyacá #78-90, Bogotá',
                'telefono' => '+57 1 5678901',
                'email' => 'proveedores@mazda-colombia.com'
            ],
            [
                'nombre' => 'Hyundai Colombia',
                'direccion' => 'Carrera 60 #25-34, Bogotá',
                'telefono' => '+57 1 6789012',
                'email' => 'compras@hyundai-colombia.com'
            ],
            [
                'nombre' => 'Kia Motors Colombia',
                'direccion' => 'Autopista Sur #12-45, Bogotá',
                'telefono' => '+57 1 7890123',
                'email' => 'ventas@kia-colombia.com'
            ],
        ];

        foreach ($proveedores as $proveedor) {
            Proveedor::create($proveedor);
        }

        $this->command->info('✅ Proveedores creados: ' . count($proveedores) . ' proveedores');
    }
}