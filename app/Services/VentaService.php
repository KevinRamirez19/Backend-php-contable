<?php

namespace App\Services;

use App\Models\Venta;
use App\Models\VentaDetalle;
use App\Models\Vehiculo;
use App\Services\ContabilidadService;
use App\Services\DianService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class VentaService
{
    private ContabilidadService $contabilidadService;
    private DianService $dianService;

    public function __construct(ContabilidadService $contabilidadService, DianService $dianService)
    {
        $this->contabilidadService = $contabilidadService;
        $this->dianService = $dianService;
    }

    /**
     * Obtener lista de ventas con filtros.
     */
    public function obtenerVentas(array $filters = []): LengthAwarePaginator
    {
        $query = Venta::with(['cliente', 'detalles.vehiculo', 'usuario']);

        if (!empty($filters['search'])) {
            $query->search($filters['search']);
        }

        if (!empty($filters['cliente_id'])) {
            $query->where('cliente_id', $filters['cliente_id']);
        }

        if (!empty($filters['estado_dian'])) {
            $query->where('estado_dian', $filters['estado_dian']);
        }

        if (!empty($filters['fecha_inicio']) && !empty($filters['fecha_fin'])) {
            $query->whereBetween('fecha_venta', [$filters['fecha_inicio'], $filters['fecha_fin']]);
        }

        $sortField = $filters['sort_field'] ?? 'created_at';
        $sortDirection = $filters['sort_direction'] ?? 'desc';

        return $query->orderBy($sortField, $sortDirection)
                     ->paginate($filters['per_page'] ?? 15);
    }

    /**
     * Obtener una venta con sus relaciones.
     */
    public function obtenerVenta(int $id): Venta
    {
        $venta = Venta::with([
            'cliente',
            'detalles.vehiculo',
            'usuario',
            'asientoContable.partidas.cuenta'
        ])->find($id);

        if (!$venta) {
            throw new Exception('Venta no encontrada.');
        }

        return $venta;
    }

    /**
     * Crear una nueva venta.
     */
    public function crearVenta(array $data): Venta
    {
        return DB::transaction(function () use ($data) {
            $venta = Venta::create([
                'cliente_id' => $data['cliente_id'],
                'fecha_venta' => now(),
                'subtotal' => 0,
                'iva' => 0,
                'total' => 0,
                'estado_dian' => Venta::ESTADO_DIAN_PENDIENTE,
                'created_by' => auth()->id(),
            ]);

            $subtotal = 0;

            foreach ($data['detalles'] as $detalle) {
                $vehiculo = Vehiculo::findOrFail($detalle['vehiculo_id']);

                // Validar stock disponible
                if ($vehiculo->stock < $detalle['cantidad']) {
                    throw new Exception("Stock insuficiente para el vehículo: {$vehiculo->descripcion_completa}");
                }

                $precioUnitario = $vehiculo->precio_venta ?? 0;
                $subtotalDetalle = $precioUnitario * $detalle['cantidad'];

                VentaDetalle::create([
                    'venta_id' => $venta->id,
                    'vehiculo_id' => $detalle['vehiculo_id'],
                    'cantidad' => $detalle['cantidad'],
                    'precio_unitario' => $precioUnitario,
                    'subtotal' => $subtotalDetalle,
                ]);

                // Actualizar stock correctamente
                $vehiculo->decrement('stock', $detalle['cantidad']);

                $subtotal += $subtotalDetalle;
            }

            // Calcular IVA (19%)
            $iva = round($subtotal * 0.19, 2);
            $total = $subtotal + $iva;

            $venta->update([
                'subtotal' => $subtotal,
                'iva' => $iva,
                'total' => $total,
            ]);

            // Registrar asiento contable
            $this->contabilidadService->registrarVenta($venta);

            // Enviar factura a la DIAN (simulado)
            $this->enviarFacturaDianAsync($venta->id);

            return $venta->fresh(['cliente', 'detalles.vehiculo', 'usuario']);
        });
    }

    /**
     * Reenviar factura a la DIAN.
     */
    public function reenviarFacturaDian(int $id): Venta
    {
        $venta = $this->obtenerVenta($id);

        if ($venta->estaAceptadaDian()) {
            throw new Exception('La factura ya fue aceptada por la DIAN.');
        }

        $venta->update(['estado_dian' => Venta::ESTADO_DIAN_ENVIADA]);
        $this->enviarFacturaDianAsync($venta->id);

        return $venta->fresh();
    }

    /**
     * Enviar factura a la DIAN (asíncrono).
     */
    private function enviarFacturaDianAsync(int $ventaId): void
    {
        try {
            $venta = Venta::with(['cliente', 'detalles.vehiculo'])->find($ventaId);
            if ($venta) {
                $this->dianService->enviarFactura($venta);
            }
        } catch (Exception $e) {
            Log::error("Error enviando factura a DIAN: {$e->getMessage()}", [
                'venta_id' => $ventaId,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
