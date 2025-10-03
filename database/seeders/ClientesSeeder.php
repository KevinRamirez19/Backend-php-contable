<?php

namespace Database\Seeders;

use App\Models\Cliente;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ClientesSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $clientes = [
            // Personas naturales con CC
            [
                'nombre' => 'Juan Carlos Pérez Rodríguez',
                'direccion' => 'Carrera 15 #45-23, Bogotá',
                'tipo_documento' => 'CC',
                'numero_documento' => '1234567890',
                'telefono' => '+57 300 1234567',
                'email' => 'juan.perez@email.com'
            ],
            [
                'nombre' => 'María Fernanda García López',
                'direccion' => 'Calle 72 #12-34, Bogotá',
                'tipo_documento' => 'CC',
                'numero_documento' => '0987654321',
                'telefono' => '+57 310 2345678',
                'email' => 'maria.garcia@email.com'
            ],
            [
                'nombre' => 'Carlos Andrés Martínez Silva',
                'direccion' => 'Avenida 68 #56-78, Bogotá',
                'tipo_documento' => 'CC',
                'numero_documento' => '1122334455',
                'telefono' => '+57 320 3456789',
                'email' => 'carlos.martinez@email.com'
            ],

            // Empresas con NIT
            [
                'nombre' => 'Inversiones Comerciales S.A.S.',
                'direccion' => 'Carrera 50 #23-45, Bogotá',
                'tipo_documento' => 'NIT',
                'numero_documento' => '900123456-1',
                'telefono' => '+57 1 6789012',
                'email' => 'compras@inversionescomerciales.com'
            ],
            [
                'nombre' => 'Constructora Edificar Ltda.',
                'direccion' => 'Avenida Boyacá #34-56, Bogotá',
                'tipo_documento' => 'NIT',
                'numero_documento' => '800234567-2',
                'telefono' => '+57 1 7890123',
                'email' => 'administracion@constructoraedificar.com'
            ],
        ];

        foreach ($clientes as $cliente) {
            Cliente::create($cliente);
        }

        $this->command->info('✅ Clientes creados: ' . count($clientes) . ' clientes');
    }
}