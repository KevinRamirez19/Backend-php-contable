<?php

namespace Database\Seeders;

use App\Models\Proveedor;
use App\Models\Vehiculo;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class VehiculosSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $proveedores = Proveedor::all();
        
        if ($proveedores->isEmpty()) {
            $this->command->error('❌ No hay proveedores. Ejecuta ProveedoresSeeder primero.');
            return;
        }

        $vehiculos = [
            // Toyota
            [
                'marca' => 'Toyota',
                'modelo' => 'Corolla',
                'año' => 2023,
                'color' => 'Blanco',
                'placa' => 'ABC123',
                'vin' => '1HGCM82633A123456',
                'precio_compra' => 65000000,
                'precio_venta' => 75000000,
                'estado' => 'DISPONIBLE',
                'stock' => 3
            ],
            [
                'marca' => 'Toyota',
                'modelo' => 'RAV4',
                'año' => 2024,
                'color' => 'Gris',
                'placa' => 'DEF456',
                'vin' => '2T3ZFREV4LW123457',
                'precio_compra' => 85000000,
                'precio_venta' => 98000000,
                'estado' => 'DISPONIBLE',
                'stock' => 2
            ],

            // Nissan
            [
                'marca' => 'Nissan',
                'modelo' => 'Versa',
                'año' => 2023,
                'color' => 'Rojo',
                'placa' => 'JKL012',
                'vin' => '3N1CN7APXKL123459',
                'precio_compra' => 45000000,
                'precio_venta' => 52000000,
                'estado' => 'DISPONIBLE',
                'stock' => 4
            ],
            [
                'marca' => 'Nissan',
                'modelo' => 'X-Trail',
                'año' => 2024,
                'color' => 'Azul',
                'placa' => 'MNO345',
                'vin' => '5N1AT2MV8PC123460',
                'precio_compra' => 78000000,
                'precio_venta' => 89000000,
                'estado' => 'DISPONIBLE',
                'stock' => 2
            ],

            // Mazda
            [
                'marca' => 'Mazda',
                'modelo' => 'Mazda3',
                'año' => 2024,
                'color' => 'Gris',
                'placa' => 'STU901',
                'vin' => 'JM1BPACK1R123462',
                'precio_compra' => 68000000,
                'precio_venta' => 78000000,
                'estado' => 'DISPONIBLE',
                'stock' => 2
            ],
        ];

        foreach ($vehiculos as $vehiculoData) {
            // Asignar proveedor aleatorio
            $proveedor = $proveedores->random();
            
            Vehiculo::create(array_merge($vehiculoData, [
                'proveedor_id' => $proveedor->id
            ]));
        }

        $this->command->info('✅ Vehículos creados: ' . count($vehiculos) . ' vehículos');
    }
}