<?php

namespace App\Console\Commands;

use App\Models\Venta;
use App\Services\DianService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncDianStatusCommand extends Command
{
    /**
     * El nombre y la firma del comando.
     *
     * @var string
     */
    protected $signature = 'dian:sync-status 
                            {--venta-id= : ID específico de venta a sincronizar}
                            {--all : Sincronizar todas las ventas pendientes}';

    /**
     * La descripción del comando.
     *
     * @var string
     */
    protected $description = 'Sincroniza el estado de las facturas con la DIAN';

    /**
     * Ejecutar el comando.
     */
    public function handle(DianService $dianService): int
    {
        $this->info('Iniciando sincronización de estados DIAN...');

        $ventaId = $this->option('venta-id');
        $syncAll = $this->option('all');

        if ($ventaId) {
            // Sincronizar una venta específica
            $this->syncSingleVenta($ventaId, $dianService);
        } elseif ($syncAll) {
            // Sincronizar todas las ventas pendientes
            $this->syncAllPendingVentas($dianService);
        } else {
            $this->error('Debe especificar --venta-id=ID o --all');
            return Command::FAILURE;
        }

        $this->info('Sincronización completada.');
        return Command::SUCCESS;
    }

    private function syncSingleVenta(int $ventaId, DianService $dianService): void
    {
        $venta = Venta::where('id', $ventaId)
            ->whereIn('estado_dian', ['PENDIENTE', 'ENVIADA'])
            ->first();

        if (!$venta) {
            $this->error("Venta {$ventaId} no encontrada o no requiere sincronización");
            return;
        }

        $this->info("Sincronizando venta ID: {$ventaId}");

        try {
            if ($venta->cufe) {
                // Consultar estado por CUFE
                $estado = $dianService->consultarEstadoFactura($venta->cufe);
                $this->processDianResponse($venta, $estado);
            } else {
                $this->warn("Venta {$ventaId} no tiene CUFE, no se puede consultar estado");
            }
        } catch (\Exception $e) {
            $this->error("Error sincronizando venta {$ventaId}: " . $e->getMessage());
            Log::error("Error en SyncDianStatus para venta {$ventaId}", [
                'error' => $e->getMessage(),
                'venta_id' => $ventaId
            ]);
        }
    }

    private function syncAllPendingVentas(DianService $dianService): void
    {
        $ventas = Venta::whereIn('estado_dian', ['PENDIENTE', 'ENVIADA'])
            ->whereNotNull('cufe')
            ->get();

        $this->info("Encontradas {$ventas->count()} ventas para sincronizar");

        $bar = $this->output->createProgressBar($ventas->count());
        $bar->start();

        $successCount = 0;
        $errorCount = 0;

        foreach ($ventas as $venta) {
            try {
                $estado = $dianService->consultarEstadoFactura($venta->cufe);
                $this->processDianResponse($venta, $estado);
                $successCount++;
            } catch (\Exception $e) {
                $errorCount++;
                Log::error("Error sincronizando venta {$venta->id}", [
                    'error' => $e->getMessage(),
                    'venta_id' => $venta->id
                ]);
            }

            $bar->advance();
            sleep(1); // Evitar sobrecargar el servicio DIAN
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("Sincronización completada: {$successCount} exitosas, {$errorCount} errores");
    }

    private function processDianResponse(Venta $venta, array $estado): void
    {
        $nuevoEstado = $estado['estado'] ?? 'PENDIENTE';
        
        switch ($nuevoEstado) {
            case 'ACEPTADA':
                $venta->marcarComoAceptadaDian();
                $this->info("Venta {$venta->id} aceptada por DIAN");
                break;
            case 'RECHAZADA':
                $venta->marcarComoRechazadaDian();
                $this->warn("Venta {$venta->id} rechazada por DIAN");
                break;
            default:
                $this->info("Venta {$venta->id} aún pendiente en DIAN");
                break;
        }
    }
}