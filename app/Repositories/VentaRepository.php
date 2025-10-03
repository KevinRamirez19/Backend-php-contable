<?php

namespace App\Repositories;

use App\Models\Venta;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class VentaRepository
{
    public function getAll(array $filters = []): LengthAwarePaginator
    {
        $query = Venta::with(['cliente', 'detalles.vehiculo', 'usuario']);

        if (!empty($filters['search'])) {
            $query->search($filters['search']);
        }

        if (!empty($filters['cliente_id'])) {
            $query->byCliente($filters['cliente_id']);
        }

        if (!empty($filters['estado_dian'])) {
            $query->byEstadoDian($filters['estado_dian']);
        }

        if (!empty($filters['fecha_inicio']) && !empty($filters['fecha_fin'])) {
            $query->betweenDates($filters['fecha_inicio'], $filters['fecha_fin']);
        }

        $sortField = $filters['sort_field'] ?? 'created_at';
        $sortDirection = $filters['sort_direction'] ?? 'desc';

        return $query->orderBy($sortField, $sortDirection)
                    ->paginate($filters['per_page'] ?? 15);
    }

    public function getById(int $id): ?Venta
    {
        return Venta::with([
            'cliente', 
            'detalles.vehiculo', 
            'usuario',
            'asientoContable.partidas.cuenta'
        ])->find($id);
    }

    public function getByNumeroFactura(string $numeroFactura): ?Venta
    {
        return Venta::where('numero_factura', $numeroFactura)->first();
    }

    public function getByCufe(string $cufe): ?Venta
    {
        return Venta::where('cufe', $cufe)->first();
    }

    public function create(array $data): Venta
    {
        return DB::transaction(function () use ($data) {
            $venta = Venta::create($data['venta']);

            foreach ($data['detalles'] as $detalle) {
                $venta->detalles()->create($detalle);
            }

            return $venta->load(['cliente', 'detalles.vehiculo']);
        });
    }

    public function updateEstadoDian(int $id, string $estado, ?string $cufe = null, ?string $qrCode = null): bool
    {
        $venta = $this->getById($id);
        
        if (!$venta) {
            return false;
        }

        return $venta->update([
            'estado_dian' => $estado,
            'cufe' => $cufe,
            'qr_code' => $qrCode,
        ]);
    }

    public function getVentasPorPeriodo(string $fechaInicio, string $fechaFin): Collection
    {
        return Venta::with(['cliente', 'detalles.vehiculo'])
                   ->whereBetween('fecha_venta', [$fechaInicio, $fechaFin])
                   ->orderBy('fecha_venta')
                   ->get();
    }

    public function getEstadisticasPorPeriodo(string $fechaInicio, string $fechaFin): array
    {
        $ventas = Venta::whereBetween('fecha_venta', [$fechaInicio, $fechaFin])
                      ->select(
                          DB::raw('COUNT(*) as total_ventas'),
                          DB::raw('SUM(subtotal) as total_subtotal'),
                          DB::raw('SUM(iva) as total_iva'),
                          DB::raw('SUM(total) as total_ventas_monto'),
                          DB::raw('AVG(total) as promedio_venta')
                      )
                      ->first();

        $ventasPorEstado = Venta::whereBetween('fecha_venta', [$fechaInicio, $fechaFin])
                               ->select('estado_dian', DB::raw('COUNT(*) as cantidad'))
                               ->groupBy('estado_dian')
                               ->get()
                               ->pluck('cantidad', 'estado_dian');

        return [
            'total_ventas' => $ventas->total_ventas ?? 0,
            'total_subtotal' => $ventas->total_subtotal ?? 0,
            'total_iva' => $ventas->total_iva ?? 0,
            'total_ventas_monto' => $ventas->total_ventas_monto ?? 0,
            'promedio_venta' => $ventas->promedio_venta ?? 0,
            'ventas_por_estado' => $ventasPorEstado,
        ];
    }

    public function getTopClientes(int $limit = 10): Collection
    {
        return Venta::join('clientes', 'ventas.cliente_id', '=', 'clientes.id')
                   ->select(
                       'clientes.id',
                       'clientes.nombre',
                       'clientes.numero_documento',
                       DB::raw('COUNT(ventas.id) as total_ventas'),
                       DB::raw('SUM(ventas.total) as total_compras')
                   )
                   ->groupBy('clientes.id', 'clientes.nombre', 'clientes.numero_documento')
                   ->orderByDesc('total_compras')
                   ->limit($limit)
                   ->get();
    }
}