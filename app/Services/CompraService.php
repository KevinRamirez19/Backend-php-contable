<?php

namespace App\Services;

use App\Models\Compra;
use App\Models\CompraDetalle;
use App\Models\Vehiculo;
use App\Services\ContabilidadService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class CompraService
{
    public function __construct(private ContabilidadService $contabilidadService) {}

    public function obtenerCompras(array $filters = []): LengthAwarePaginator
    {
        $query = Compra::with(['proveedor', 'detalles.vehiculo', 'usuario']);

        // Filtro de búsqueda
        if (!empty($filters['search'])) {
            $query->search($filters['search']);
        }

        // Filtro por proveedor
        if (!empty($filters['proveedor_id'])) {
            $query->byProveedor($filters['proveedor_id']);
        }

        // Filtro por estado
        if (!empty($filters['estado'])) {
            $query->byEstado($filters['estado']);
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

    public function obtenerCompra(int $id): Compra
    {
        $compra = Compra::with([
            'proveedor', 
            'detalles.vehiculo', 
            'usuario',
            'asientoContable.partidas.cuenta'
        ])->find($id);

        if (!$compra) {
            throw new \Exception('Compra no encontrada');
        }

        return $compra;
    }

    public function crearCompra(array $data): Compra
    {
        return DB::transaction(function () use ($data) {
            // Calcular subtotal, IVA y total
            $subtotal = 0;
            
            foreach ($data['detalles'] as $detalle) {
                $vehiculo = Vehiculo::find($detalle['vehiculo_id']);
                $subtotalDetalle = $detalle['cantidad'] * $detalle['precio_unitario'];
                $subtotal += $subtotalDetalle;
            }

            $iva = $subtotal * 0.19; // 19% IVA Colombia
            $total = $subtotal + $iva;

            // Crear compra
            $compra = Compra::create([
                'proveedor_id' => $data['proveedor_id'],
                'numero_factura' => $data['numero_factura'],
                'fecha_compra' => $data['fecha_compra'],
                'subtotal' => $subtotal,
                'iva' => $iva,
                'total' => $total,
                'estado' => Compra::ESTADO_PENDIENTE,
                'created_by' => auth()->id(),
            ]);

            // Crear detalles de compra y actualizar vehículos
            foreach ($data['detalles'] as $detalle) {
                $vehiculo = Vehiculo::find($detalle['vehiculo_id']);
                
                CompraDetalle::create([
                    'compra_id' => $compra->id,
                    'vehiculo_id' => $detalle['vehiculo_id'],
                    'cantidad' => $detalle['cantidad'],
                    'precio_unitario' => $detalle['precio_unitario'],
                    'subtotal' => $detalle['cantidad'] * $detalle['precio_unitario'],
                ]);

                // Actualizar precio de compra y stock del vehículo
                $vehiculo->update([
                    'precio_compra' => $detalle['precio_unitario'],
                    'precio_venta' => $detalle['precio_unitario'] * 1.15, // 15% margen
                ]);

                $vehiculo->aumentarStock($detalle['cantidad']);
            }

            // Crear asiento contable
            $this->contabilidadService->registrarCompra($compra);

            return $compra->fresh(['proveedor', 'detalles.vehiculo', 'usuario']);
        });
    }

    public function marcarComoPagada(int $id): Compra
    {
        $compra = $this->obtenerCompra($id);
        
        if ($compra->estaAnulada()) {
            throw new \Exception('No se puede marcar como pagada una compra anulada');
        }

        $compra->marcarComoPagada();

        return $compra->fresh();
    }

    public function marcarComoAnulada(int $id): Compra
    {
        return DB::transaction(function () use ($id) {
            $compra = $this->obtenerCompra($id);
            
            if ($compra->estaPagada()) {
                throw new \Exception('No se puede anular una compra ya pagada');
            }

            // Revertir stock de vehículos
            foreach ($compra->detalles as $detalle) {
                $detalle->vehiculo->reducirStock($detalle->cantidad);
            }

            $compra->marcarComoAnulada();

            return $compra->fresh();
        });
    }
}