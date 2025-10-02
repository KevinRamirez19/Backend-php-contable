<?php

namespace App\Services;

use App\Models\Proveedor;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ProveedorService
{
    public function obtenerProveedores(array $filters = []): LengthAwarePaginator
    {
        $query = Proveedor::query();

        // Filtro de búsqueda
        if (!empty($filters['search'])) {
            $query->search($filters['search']);
        }

        // Ordenamiento
        $sortField = $filters['sort_field'] ?? 'created_at';
        $sortDirection = $filters['sort_direction'] ?? 'desc';
        $query->orderBy($sortField, $sortDirection);

        return $query->paginate($filters['per_page'] ?? 15);
    }

    public function obtenerProveedor(int $id): Proveedor
    {
        $proveedor = Proveedor::with(['vehiculos', 'compras'])->find($id);

        if (!$proveedor) {
            throw new \Exception('Proveedor no encontrado');
        }

        return $proveedor;
    }

    public function crearProveedor(array $data): Proveedor
    {
        return Proveedor::create($data);
    }

    public function actualizarProveedor(int $id, array $data): Proveedor
    {
        $proveedor = $this->obtenerProveedor($id);
        $proveedor->update($data);

        return $proveedor->fresh();
    }

    public function eliminarProveedor(int $id): bool
    {
        $proveedor = $this->obtenerProveedor($id);

        // Verificar si tiene vehículos o compras asociadas
        if ($proveedor->vehiculos()->exists() || $proveedor->compras()->exists()) {
            throw new \Exception('No se puede eliminar el proveedor porque tiene vehículos o compras asociadas');
        }

        return $proveedor->delete();
    }

    public function obtenerProveedoresActivos(): LengthAwarePaginator
    {
        return Proveedor::whereNull('deleted_at')
            ->orderBy('nombre')
            ->paginate(20);
    }
}