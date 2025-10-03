<?php

namespace Database\Seeders;

use App\Models\Cuenta;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CuentasSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cuentas = [
            // Activos
            ['codigo' => '1105', 'nombre' => 'Caja General', 'tipo' => 'ACTIVO'],
            ['codigo' => '110505', 'nombre' => 'Caja Menuda', 'tipo' => 'ACTIVO'],
            ['codigo' => '1110', 'nombre' => 'Bancos', 'tipo' => 'ACTIVO'],
            ['codigo' => '111005', 'nombre' => 'Cuenta Corriente', 'tipo' => 'ACTIVO'],
            ['codigo' => '1435', 'nombre' => 'Inventario de Vehículos', 'tipo' => 'ACTIVO'],
            ['codigo' => '1520', 'nombre' => 'Mercancías No Fabricadas por la Empresa', 'tipo' => 'ACTIVO'],

            // Pasivos
            ['codigo' => '2105', 'nombre' => 'Obligaciones Financieras', 'tipo' => 'PASIVO'],
            ['codigo' => '2205', 'nombre' => 'Proveedores', 'tipo' => 'PASIVO'],
            ['codigo' => '2408', 'nombre' => 'Impuesto sobre las ventas por pagar', 'tipo' => 'PASIVO'],
            ['codigo' => '2416', 'nombre' => 'Retención en la fuente', 'tipo' => 'PASIVO'],

            // Patrimonio
            ['codigo' => '3105', 'nombre' => 'Capital Social', 'tipo' => 'PATRIMONIO'],
            ['codigo' => '3115', 'nombre' => 'Aportes Sociales', 'tipo' => 'PATRIMONIO'],
            ['codigo' => '3135', 'nombre' => 'Excedentes', 'tipo' => 'PATRIMONIO'],
            ['codigo' => '3195', 'nombre' => 'Utilidades Acumuladas', 'tipo' => 'PATRIMONIO'],

            // Ingresos
            ['codigo' => '4135', 'nombre' => 'Ventas', 'tipo' => 'INGRESO'],
            ['codigo' => '4175', 'nombre' => 'Devoluciones en Ventas', 'tipo' => 'INGRESO'],
            ['codigo' => '4210', 'nombre' => 'Ingresos por Servicios', 'tipo' => 'INGRESO'],

            // Gastos
            ['codigo' => '5105', 'nombre' => 'Gastos de Personal', 'tipo' => 'GASTO'],
            ['codigo' => '5110', 'nombre' => 'Gastos de Arrendamiento', 'tipo' => 'GASTO'],
            ['codigo' => '5120', 'nombre' => 'Gastos de Mantenimiento', 'tipo' => 'GASTO'],
            ['codigo' => '5135', 'nombre' => 'Gastos de Servicios Públicos', 'tipo' => 'GASTO'],
            ['codigo' => '5160', 'nombre' => 'Gastos de Impuestos', 'tipo' => 'GASTO'],
            ['codigo' => '5195', 'nombre' => 'Gastos Diversos', 'tipo' => 'GASTO'],
            ['codigo' => '6135', 'nombre' => 'Compras', 'tipo' => 'GASTO'],
            ['codigo' => '6175', 'nombre' => 'Devoluciones en Compras', 'tipo' => 'GASTO'],
        ];

        foreach ($cuentas as $cuenta) {
            Cuenta::create($cuenta);
        }

        $this->command->info('✅ Cuentas contables creadas: ' . count($cuentas) . ' cuentas');
    }
}