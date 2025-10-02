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

class VentaService
{
    public function __construct(
        private ContabilidadService $contabilidadService,
        private DianService $dianService
    ) {}

    public function obtenerVentas(array $filters = []): LengthAwarePaginator
    {
        $query = Venta::with(['cliente', 'detalles.vehiculo', 'usuario']);

        // Filtro de búsqueda
        if (!empty($filters['search'])) {
            $query->search($filters['search']);
        }

        // Filtro por cliente
        if (!empty($filters['cliente_id'])) {
            $query->byCliente($filters['cliente_id']);
        }

        // Filtro por estado DIAN
        if (!empty($filters['estado_dian'])) {
            $query->byEstadoDian($filters['estado_dian']);
        }

        // Filtro por fecha
        if (!empty($filters['fecha_inicio']) && !empty($filters['fecha_fin'])) {
            $query->betweenDates($filters['fecha_inicio'], $filters['fecha_fin']);
        }

        // Ordenamiento
        $sortField = $filters['sort_field'] ?? 'created_at';
        $sortDirection = $filters['sort_direction'] ?? 'desc';
        $query->orderBy($sortField, $sortDirection);

        return $query->paginate($filters['per_page'] ?? 15);
    }

    public function obtenerVenta(int $id): Venta
    {
        $venta = Venta::with([
            'cliente', 
            'detalles.vehiculo', 
            'usuario',
            'asientoContable.partidas.cuenta'
        ])->find($id);

        if (!$venta) {
            throw new \Exception('Venta no encontrada');
        }

        return $venta;
    }

    public function crearVenta(array $data): Venta
    {
        return DB::transaction(function () use ($data) {
            // Crear venta inicial
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

            // Procesar detalles de venta
            foreach ($data['detalles'] as $detalle) {
                $vehiculo = Vehiculo::findOrFail($detalle['vehiculo_id']);
                
                // Validar stock
                if (!$vehiculo->tieneStock() || $vehiculo->stock < $detalle['cantidad']) {
                    throw new \Exception("Stock insuficiente para el vehículo: {$vehiculo->descripcion_completa}");
                }

                $precioUnitario = $vehiculo->precio_venta;
                $subtotalDetalle = $precioUnitario * $detalle['cantidad'];

                // Crear detalle de venta
                VentaDetalle::create([
                    'venta_id' => $venta->id,
                    'vehiculo_id' => $detalle['vehiculo_id'],
                    'cantidad' => $detalle['cantidad'],
                    'precio_unitario' => $precioUnitario,
                    'subtotal' => $subtotalDetalle,
                ]);

                // Actualizar stock del vehículo
                $vehiculo->reducirStock($detalle['cantidad']);

                $subtotal += $subtotalDetalle;
            }

            // Calcular impuestos y total
            $iva = $subtotal * 0.19; // 19% IVA Colombia
            $total = $subtotal + $iva;

            // Actualizar venta con totales
            $venta->update([
                'subtotal' => $subtotal,
                'iva' => $iva,
                'total' => $total,
            ]);

            // Crear asiento contable
            $this->contabilidadService->registrarVenta($venta);

            // Enviar a DIAN de forma asíncrona
            $this->enviarFacturaDianAsync($venta->id);

            return $venta->fresh(['cliente', 'detalles.vehiculo', 'usuario']);
        });
    }

    public function reenviarFacturaDian(int $id): Venta
    {
        $venta = $this->obtenerVenta($id);
        
        if ($venta->estaAceptadaDian()) {
            throw new \Exception('La factura ya fue aceptada por la DIAN');
        }

        $venta->marcarComoEnviadaDian();
        $this->enviarFacturaDianAsync($venta->id);

        return $venta->fresh();
    }

    private function enviarFacturaDianAsync(int $ventaId): void
    {
        // En producción, esto se enviaría a una queue
        try {
            $venta = Venta::with(['cliente', 'detalles.vehiculo'])->find($ventaId);
            $this->dianService->enviarFactura($venta);
        } catch (\Exception $e) {
            Log::error("Error enviando factura a DIAN: {$e->getMessage()}", [
                'venta_id' => $ventaId,
                'error' => $e->getMessage()
            ]);
        }
    }
}