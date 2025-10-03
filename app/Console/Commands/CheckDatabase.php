<?php

namespace App\Console\Commands;

use App\Models\Rol;
use App\Models\User;
use App\Models\Proveedor;
use App\Models\Cliente;
use App\Models\Vehiculo;
use App\Models\Cuenta;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB; // ✅ Añadir esta línea

class CheckDatabase extends Command
{
    protected $signature = 'db:check';
    protected $description = 'Verificar el estado de la base de datos';

    public function handle()
    {
        $this->info('=== Verificación de Base de Datos ===');

        // Verificar conteos
        $this->info('📊 Conteo de registros:');
        try {
            $this->line("   Roles: " . Rol::count());
            $this->line("   Usuarios: " . User::count());
            $this->line("   Proveedores: " . Proveedor::count());
            $this->line("   Clientes: " . Cliente::count());
            $this->line("   Vehículos: " . Vehiculo::count());
            $this->line("   Cuentas: " . Cuenta::count());
        } catch (\Exception $e) {
            $this->error("Error al contar registros: " . $e->getMessage());
        }

        // Verificar tablas
        $this->info('🗃️ Tablas existentes:');
        try {
            $tables = DB::select('SHOW TABLES');
            foreach ($tables as $table) {
                foreach ($table as $key => $value) {
                    $this->line("   - " . $value);
                }
            }
        } catch (\Exception $e) {
            $this->error("Error al listar tablas: " . $e->getMessage());
        }

        $this->info('✅ Verificación completada');
        return Command::SUCCESS;
    }
}