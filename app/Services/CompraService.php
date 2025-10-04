<?php

namespace App\Services;

use App\Models\Compra;
use App\Models\CompraDetalle;
use App\Models\Vehiculo;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class CompraService
{
    public function __construct(private ContabilidadService $contabilidadService) {}

    public function obtenerCompras(array $filters = []): LengthAwarePaginator
    {
        $query = Compra::with(['proveedor', 'detalles.vehiculo', 'usuario']);

        if (!empty($filters['search'])) {
            $query->search($filters['search']);
        }

        $sortField = $filters['sort_field'] ?? 'created_at';
        $sortDirection = $filters['sort_direction'] ?? 'desc';

        return $query->orderBy($sortField, $sortDirection)
                    ->paginate($filters['per_page'] ?? 15);
    }

    public function obtenerCompra(int $id): Compra
    {
        $compra = Compra::with(['proveedor', 'detalles.vehiculo', 'usuario'])->find($id);

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
                $subtotalDetalle = $detalle['cantidad'] * $detalle['precio_unitario'];
                $subtotal += $subtotalDetalle;
            }

            $iva = $subtotal * 0.19;
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

            // Crear detalles de compra
            foreach ($data['detalles'] as $detalle) {
                CompraDetalle::create([
                    'compra_id' => $compra->id,
                    'vehiculo_id' => $detalle['vehiculo_id'],
                    'cantidad' => $detalle['cantidad'],
                    'precio_unitario' => $detalle['precio_unitario'],
                    'subtotal' => $detalle['cantidad'] * $detalle['precio_unitario'],
                ]);

                // Actualizar vehículo
                $vehiculo = Vehiculo::find($detalle['vehiculo_id']);
                $vehiculo->aumentarStock($detalle['cantidad']);
            }

            return $compra->fresh(['proveedor', 'detalles.vehiculo', 'usuario']);
        });
    }

    public function marcarComoPagada(int $id): Compra
    {
        $compra = $this->obtenerCompra($id);
        $compra->marcarComoPagada();

        return $compra->fresh();
    }

    public function marcarComoAnulada(int $id): Compra
    {
        return DB::transaction(function () use ($id) {
            $compra = $this->obtenerCompra($id);
            
            // Revertir stock de vehículos
            foreach ($compra->detalles as $detalle) {
                $detalle->vehiculo->reducirStock($detalle->cantidad);
            }

            $compra->marcarComoAnulada();
            return $compra->fresh();
        });
    }
}