<?php

namespace Database\Seeders;

use App\Models\Cuenta;
use Illuminate\Database\Seeder;

class CuentasSeeder extends Seeder
{
    public function run(): void
    {
        $cuentas = [
            // Activos
            ['codigo' => '1105', 'nombre' => 'Caja General', 'tipo' => 'ACTIVO'],
            ['codigo' => '110505', 'nombre' => 'Caja Chica', 'tipo' => 'ACTIVO'],
            ['codigo' => '1435', 'nombre' => 'Inventario de Vehículos', 'tipo' => 'ACTIVO'],
            ['codigo' => '1520', 'nombre' => 'Propiedades, Planta y Equipo', 'tipo' => 'ACTIVO'],
            
            // Pasivos
            ['codigo' => '2105', 'nombre' => 'Obligaciones Bancarias', 'tipo' => 'PASIVO'],
            ['codigo' => '2205', 'nombre' => 'Proveedores', 'tipo' => 'PASIVO'],
            ['codigo' => '2408', 'nombre' => 'IVA por Pagar', 'tipo' => 'PASIVO'],
            
            // Patrimonio
            ['codigo' => '3105', 'nombre' => 'Capital Social', 'tipo' => 'PATRIMONIO'],
            ['codigo' => '3110', 'nombre' => 'Reservas', 'tipo' => 'PATRIMONIO'],
            ['codigo' => '3120', 'nombre' => 'Utilidades del Ejercicio', 'tipo' => 'PATRIMONIO'],
            
            // Ingresos
            ['codigo' => '4135', 'nombre' => 'Ventas de Vehículos', 'tipo' => 'INGRESO'],
            ['codigo' => '4175', 'nombre' => 'Devoluciones en Ventas', 'tipo' => 'INGRESO'],
            
            // Gastos
            ['codigo' => '5105', 'nombre' => 'Gastos de Personal', 'tipo' => 'GASTO'],
            ['codigo' => '5110', 'nombre' => 'Gastos Administrativos', 'tipo' => 'GASTO'],
            ['codigo' => '5115', 'nombre' => 'Gastos de Ventas', 'tipo' => 'GASTO'],
            ['codigo' => '5120', 'nombre' => 'Gastos Financieros', 'tipo' => 'GASTO'],
            ['codigo' => '6135', 'nombre' => 'Compras de Vehículos', 'tipo' => 'GASTO'],
        ];

        foreach ($cuentas as $cuenta) {
            Cuenta::create($cuenta);
        }
    }
}