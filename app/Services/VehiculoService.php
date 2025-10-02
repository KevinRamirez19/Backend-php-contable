<?php

namespace App\Services;

use App\Models\Vehiculo;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class VehiculoService
{
    public function obtenerVehiculos(array $filters = []): LengthAwarePaginator
    {
        $query = Vehiculo::with(['proveedor']);

        // Filtro de búsqueda
        if (!empty($filters['search'])) {
            $query->search($filters['search']);
        }

        // Filtro por estado
        if (!empty($filters['estado'])) {
            $query->where('estado', $filters['estado']);
        }

        // Filtro por marca
        if (!empty($filters['marca'])) {
            $query->byMarca($filters['marca']);
        }

        // Filtro por año
        if (!empty($filters['año'])) {
            $query->byAño($filters['año']);
        }

        // Ordenamiento
        $sortField = $filters['sort_field'] ?? 'created_at';
        $sortDirection = $filters['sort_direction'] ?? 'desc';
        $query->orderBy($sortField, $sortDirection);

        return $query->paginate($filters['per_page'] ?? 15);
    }

    public function obtenerVehiculosDisponibles(array $filters = []): LengthAwarePaginator
    {
        $filters['estado'] = Vehiculo::ESTADO_DISPONIBLE;
        return $this->obtenerVehiculos($filters);
    }

    public function obtenerVehiculo(int $id): Vehiculo
    {
        $vehiculo = Vehiculo::with(['proveedor', 'compraDetalles', 'ventaDetalles'])->find($id);

        if (!$vehiculo) {
            throw new \Exception('Vehículo no encontrado');
        }

        return $vehiculo;
    }

    public function crearVehiculo(array $data): Vehiculo
    {
        return Vehiculo::create($data);
    }

    public function actualizarVehiculo(int $id, array $data): Vehiculo
    {
        $vehiculo = $this->obtenerVehiculo($id);
        $vehiculo->update($data);

        return $vehiculo->fresh(['proveedor']);
    }

    public function eliminarVehiculo(int $id): bool
    {
        $vehiculo = $this->obtenerVehiculo($id);

        // Verificar si tiene compras o ventas asociadas
        if ($vehiculo->compraDetalles()->exists() || $vehiculo->ventaDetalles()->exists()) {
            throw new \Exception('No se puede eliminar el vehículo porque tiene compras o ventas asociadas');
        }

        return $vehiculo->delete();
    }

    public function actualizarStock(int $id, int $cantidad): Vehiculo
    {
        $vehiculo = $this->obtenerVehiculo($id);
        
        if ($cantidad < 0 && abs($cantidad) > $vehiculo->stock) {
            throw new \Exception('Stock insuficiente');
        }

        $vehiculo->increment('stock', $cantidad);

        // Actualizar estado si es necesario
        if ($vehiculo->stock === 0) {
            $vehiculo->marcarComoVendido();
        } elseif ($vehiculo->stock > 0 && $vehiculo->estado === Vehiculo::ESTADO_VENDIDO) {
            $vehiculo->marcarComoDisponible();
        }

        return $vehiculo->fresh();
    }
}