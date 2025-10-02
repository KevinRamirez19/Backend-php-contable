<?php

namespace App\Services;

use App\Models\Venta;
use App\Models\VentaDetalle;
use App\Models\Vehiculo;
use App\Services\ContabilidadService;
use App\Services\DianService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VentaService
{
    public function __construct(
        private ContabilidadService $contabilidadService,
        private DianService $dianService
    ) {}

    public function crearVenta(array $data): Venta
    {
        return DB::transaction(function () use ($data) {
            // 1. Crear la venta
            $venta = Venta::create([
                'cliente_id' => $data['cliente_id'],
                'fecha_venta' => now(),
                'subtotal' => 0,
                'iva' => 0,
                'total' => 0,
                'created_by' => auth()->id(),
            ]);

            $subtotal = 0;

            // 2. Procesar detalles de venta
            foreach ($data['detalles'] as $detalle) {
                $vehiculo = Vehiculo::findOrFail($detalle['vehiculo_id']);
                
                if ($vehiculo->stock < $detalle['cantidad']) {
                    throw new \Exception("Stock insuficiente para el vehículo: {$vehiculo->marca} {$vehiculo->modelo}");
                }

                $precioUnitario = $vehiculo->precio_venta;
                $subtotalDetalle = $precioUnitario * $detalle['cantidad'];

                VentaDetalle::create([
                    'venta_id' => $venta->id,
                    'vehiculo_id' => $detalle['vehiculo_id'],
                    'cantidad' => $detalle['cantidad'],
                    'precio_unitario' => $precioUnitario,
                    'subtotal' => $subtotalDetalle,
                ]);

                // 3. Actualizar stock
                $vehiculo->decrement('stock', $detalle['cantidad']);
                if ($vehiculo->stock === 0) {
                    $vehiculo->update(['estado' => 'VENDIDO']);
                }

                $subtotal += $subtotalDetalle;
            }

            // 4. Calcular impuestos y total
            $iva = $subtotal * 0.19; // 19% IVA Colombia
            $total = $subtotal + $iva;

            $venta->update([
                'subtotal' => $subtotal,
                'iva' => $iva,
                'total' => $total,
            ]);

            // 5. Crear asiento contable
            $this->contabilidadService->registrarVenta($venta);

            // 6. Enviar a DIAN (async)
            dispatch(function () use ($venta) {
                try {
                    $this->dianService->enviarFactura($venta);
                } catch (\Exception $e) {
                    Log::error("Error enviando factura a DIAN: {$e->getMessage()}");
                }
            });

            return $venta->fresh(['cliente', 'detalles.vehiculo']);
        });
    }

    public function obtenerVentas()
    {
        return Venta::with(['cliente', 'detalles.vehiculo'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);
    }

    public function obtenerVenta(int $id): Venta
    {
        $venta = Venta::with(['cliente', 'detalles.vehiculo', 'asientoContable.partidas.cuenta'])
            ->find($id);

        if (!$venta) {
            throw new \Exception('Venta no encontrada');
        }

        return $venta;
    }
}