<?php

namespace Database\Seeders;

use App\Models\Cliente;
use Illuminate\Database\Seeder;

class ClientesSeeder extends Seeder
{
    public function run(): void
    {
        $clientes = [
            [
                'nombre' => 'Juan Pérez González',
                'direccion' => 'Calle 45 #23-67, Bogotá',
                'tipo_documento' => 'CC',
                'numero_documento' => '123456789',
                'telefono' => '+57 300 1234567',
                'email' => 'juan.perez@email.com',
            ],
            [
                'nombre' => 'María Rodríguez López',
                'direccion' => 'Av. Caracas #56-89, Bogotá',
                'tipo_documento' => 'CC',
                'numero_documento' => '987654321',
                'telefono' => '+57 310 2345678',
                'email' => 'maria.rodriguez@email.com',
            ],
            [
                'nombre' => 'Empresa XYZ S.A.S.',
                'direccion' => 'Carrera 7 #71-52, Bogotá',
                'tipo_documento' => 'NIT',
                'numero_documento' => '900123456-7',
                'telefono' => '+57 1 3456789',
                'email' => 'contacto@empresaxyz.com',
            ],
            [
                'nombre' => 'Carlos Andrés García',
                'direccion' => 'Calle 127 #15-30, Bogotá',
                'tipo_documento' => 'CC',
                'numero_documento' => '456789123',
                'telefono' => '+57 320 3456789',
                'email' => 'carlos.garcia@email.com',
            ],
        ];

        foreach ($clientes as $cliente) {
            Cliente::create($cliente);
        }
    }
}