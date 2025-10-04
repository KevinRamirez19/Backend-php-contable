<?php

namespace Database\Seeders;

use App\Models\Proveedor;
use App\Models\Vehiculo;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class VehiculosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $proveedores = Proveedor::all();
        
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
            [
                'marca' => 'Toyota',
                'modelo' => 'Hilux',
                'año' => 2023,
                'color' => 'Negro',
                'placa' => 'GHI789',
                'vin' => 'MR0FZ22G006123458',
                'precio_compra' => 95000000,
                'precio_venta' => 110000000,
                'estado' => 'DISPONIBLE',
                'stock' => 1
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
            [
                'marca' => 'Nissan',
                'modelo' => 'Frontier',
                'año' => 2023,
                'color' => 'Blanco',
                'placa' => 'PQR678',
                'vin' => '1N6AD0EV9LC123461',
                'precio_compra' => 82000000,
                'precio_venta' => 95000000,
                'estado' => 'DISPONIBLE',
                'stock' => 1
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
            [
                'marca' => 'Mazda',
                'modelo' => 'CX-5',
                'año' => 2023,
                'color' => 'Negro',
                'placa' => 'VWX234',
                'vin' => 'JM3KE2DY1P123463',
                'precio_compra' => 72000000,
                'precio_venta' => 83000000,
                'estado' => 'DISPONIBLE',
                'stock' => 3
            ],
            [
                'marca' => 'Mazda',
                'modelo' => 'CX-30',
                'año' => 2024,
                'color' => 'Rojo',
                'placa' => 'YZA567',
                'vin' => 'JM1DKAM72K123464',
                'precio_compra' => 65000000,
                'precio_venta' => 75000000,
                'estado' => 'DISPONIBLE',
                'stock' => 2
            ],

            // Hyundai
            [
                'marca' => 'Hyundai',
                'modelo' => 'Accent',
                'año' => 2023,
                'color' => 'Blanco',
                'placa' => 'BCD890',
                'vin' => 'KMHCU4AE3PU123465',
                'precio_compra' => 42000000,
                'precio_venta' => 49000000,
                'estado' => 'DISPONIBLE',
                'stock' => 5
            ],
            [
                'marca' => 'Hyundai',
                'modelo' => 'Tucson',
                'año' => 2024,
                'color' => 'Gris',
                'placa' => 'EFG123',
                'vin' => 'KM8J3CA46NU123466',
                'precio_compra' => 75000000,
                'precio_venta' => 86000000,
                'estado' => 'DISPONIBLE',
                'stock' => 2
            ],
            [
                'marca' => 'Hyundai',
                'modelo' => 'Creta',
                'año' => 2023,
                'color' => 'Azul',
                'placa' => 'HIJ456',
                'vin' => 'KMHJ3818PMU123467',
                'precio_compra' => 58000000,
                'precio_venta' => 67000000,
                'estado' => 'DISPONIBLE',
                'stock' => 3
            ],

            // Kia
            [
                'marca' => 'Kia',
                'modelo' => 'Rio',
                'año' => 2024,
                'color' => 'Blanco',
                'placa' => 'KLM789',
                'vin' => '3KPF24AD8PE123468',
                'precio_compra' => 40000000,
                'precio_venta' => 47000000,
                'estado' => 'DISPONIBLE',
                'stock' => 4
            ],
            [
                'marca' => 'Kia',
                'modelo' => 'Sportage',
                'año' => 2023,
                'color' => 'Negro',
                'placa' => 'NOP012',
                'vin' => '5XYPHDA59PG123469',
                'precio_compra' => 72000000,
                'precio_venta' => 83000000,
                'estado' => 'DISPONIBLE',
                'stock' => 2
            ],
            [
                'marca' => 'Kia',
                'modelo' => 'Seltos',
                'año' => 2024,
                'color' => 'Rojo',
                'placa' => 'QRS345',
                'vin' => '3KPF24AD8PE123470',
                'precio_compra' => 55000000,
                'precio_venta' => 64000000,
                'estado' => 'DISPONIBLE',
                'stock' => 3
            ],

            // Chevrolet
            [
                'marca' => 'Chevrolet',
                'modelo' => 'Aveo',
                'año' => 2023,
                'color' => 'Gris',
                'placa' => 'TUV678',
                'vin' => 'KL1TD66628B123471',
                'precio_compra' => 38000000,
                'precio_venta' => 45000000,
                'estado' => 'DISPONIBLE',
                'stock' => 6
            ],
            [
                'marca' => 'Chevrolet',
                'modelo' => 'Tracker',
                'año' => 2024,
                'color' => 'Azul',
                'placa' => 'WXY901',
                'vin' => 'KL1MH66628B123472',
                'precio_compra' => 60000000,
                'precio_venta' => 69000000,
                'estado' => 'DISPONIBLE',
                'stock' => 3
            ],
            [
                'marca' => 'Chevrolet',
                'modelo' => 'Equinox',
                'año' => 2023,
                'color' => 'Blanco',
                'placa' => 'ZAB234',
                'vin' => '3GNAXUEV6LS123473',
                'precio_compra' => 68000000,
                'precio_venta' => 78000000,
                'estado' => 'DISPONIBLE',
                'stock' => 2
            ],

            // Ford
            [
                'marca' => 'Ford',
                'modelo' => 'Escape',
                'año' => 2024,
                'color' => 'Negro',
                'placa' => 'CDE567',
                'vin' => '1FMCU0GD0RUB123474',
                'precio_compra' => 72000000,
                'precio_venta' => 83000000,
                'estado' => 'DISPONIBLE',
                'stock' => 2
            ],
            [
                'marca' => 'Ford',
                'modelo' => 'Ranger',
                'año' => 2023,
                'color' => 'Blanco',
                'placa' => 'FGH890',
                'vin' => '1FTER1FH2PLA123475',
                'precio_compra' => 88000000,
                'precio_venta' => 101000000,
                'estado' => 'DISPONIBLE',
                'stock' => 1
            ],

            // Renault
            [
                'marca' => 'Renault',
                'modelo' => 'Sandero',
                'año' => 2023,
                'color' => 'Rojo',
                'placa' => 'IJK123',
                'vin' => 'VF1HZ0A0H453123476',
                'precio_compra' => 35000000,
                'precio_venta' => 42000000,
                'estado' => 'DISPONIBLE',
                'stock' => 7
            ],
            [
                'marca' => 'Renault',
                'modelo' => 'Duster',
                'año' => 2024,
                'color' => 'Gris',
                'placa' => 'LMN456',
                'vin' => 'VF1JS0A0H453123477',
                'precio_compra' => 52000000,
                'precio_venta' => 60000000,
                'estado' => 'DISPONIBLE',
                'stock' => 4
            ],
            [
                'marca' => 'Renault',
                'modelo' => 'Koleos',
                'año' => 2023,
                'color' => 'Negro',
                'placa' => 'OPQ789',
                'vin' => 'VF1SK0A0H453123478',
                'precio_compra' => 78000000,
                'precio_venta' => 89000000,
                'estado' => 'DISPONIBLE',
                'stock' => 1
            ],

            // Vehículos vendidos (stock 0)
            [
                'marca' => 'Toyota',
                'modelo' => 'Corolla',
                'año' => 2023,
                'color' => 'Azul',
                'placa' => 'RST012',
                'vin' => '1HGCM82633A123479',
                'precio_compra' => 65000000,
                'precio_venta' => 75000000,
                'estado' => 'VENDIDO',
                'stock' => 0
            ],
            [
                'marca' => 'Nissan',
                'modelo' => 'Versa',
                'año' => 2023,
                'color' => 'Blanco',
                'placa' => 'UVW345',
                'vin' => '3N1CN7APXKL123480',
                'precio_compra' => 45000000,
                'precio_venta' => 52000000,
                'estado' => 'VENDIDO',
                'stock' => 0
            ],

            // Vehículos en mantenimiento
            [
                'marca' => 'Mazda',
                'modelo' => 'CX-5',
                'año' => 2023,
                'color' => 'Rojo',
                'placa' => 'XYZ678',
                'vin' => 'JM3KE2DY1P123481',
                'precio_compra' => 72000000,
                'precio_venta' => 83000000,
                'estado' => 'MANTENIMIENTO',
                'stock' => 1
            ],
            [
                'marca' => 'Hyundai',
                'modelo' => 'Tucson',
                'año' => 2024,
                'color' => 'Negro',
                'placa' => 'ABC901',
                'vin' => 'KM8J3CA46NU123482',
                'precio_compra' => 75000000,
                'precio_venta' => 86000000,
                'estado' => 'MANTENIMIENTO',
                'stock' => 1
            ]
        ];

        foreach ($vehiculos as $vehiculoData) {
            // Asignar proveedor aleatorio
            $proveedor = $proveedores->random();
            
            Vehiculo::create(array_merge($vehiculoData, [
                'proveedor_id' => $proveedor->id
            ]));
        }

        $this->command->info('✅ Vehículos creados: ' . count($vehiculos) . ' vehículos');
        $this->command->info('   - 22 vehículos disponibles');
        $this->command->info('   - 2 vehículos vendidos');
        $this->command->info('   - 2 vehículos en mantenimiento');
    }
}