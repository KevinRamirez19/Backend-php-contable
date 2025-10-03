<?php

namespace App\Repositories;

use App\Models\Vehiculo;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class VehiculoRepository
{
    public function getAll(array $filters = []): LengthAwarePaginator
    {
        $query = Vehiculo::with(['proveedor']);

        if (!empty($filters['search'])) {
            $query->search($filters['search']);
        }

        if (!empty($filters['estado'])) {
            $query->where('estado', $filters['estado']);
        }

        if (!empty($filters['marca'])) {
            $query->byMarca($filters['marca']);
        }

        if (!empty($filters['año'])) {
            $query->byAño($filters['año']);
        }

        if (!empty($filters['proveedor_id'])) {
            $query->where('proveedor_id', $filters['proveedor_id']);
        }

        $sortField = $filters['sort_field'] ?? 'created_at';
        $sortDirection = $filters['sort_direction'] ?? 'desc';

        return $query->orderBy($sortField, $sortDirection)
                    ->paginate($filters['per_page'] ?? 15);
    }

    public function getDisponibles(array $filters = []): LengthAwarePaginator
    {
        $filters['estado'] = Vehiculo::ESTADO_DISPONIBLE;
        return $this->getAll($filters);
    }

    public function getById(int $id): ?Vehiculo
    {
        return Vehiculo::with(['proveedor', 'compraDetalles', 'ventaDetalles'])->find($id);
    }

    public function getByPlaca(string $placa): ?Vehiculo
    {
        return Vehiculo::where('placa', $placa)->first();
    }

    public function getByVin(string $vin): ?Vehiculo
    {
        return Vehiculo::where('vin', $vin)->first();
    }

    public function create(array $data): Vehiculo
    {
        return Vehiculo::create($data);
    }

    public function update(int $id, array $data): bool
    {
        $vehiculo = $this->getById($id);
        
        if (!$vehiculo) {
            return false;
        }

        return $vehiculo->update($data);
    }

    public function delete(int $id): bool
    {
        $vehiculo = $this->getById($id);
        
        if (!$vehiculo) {
            return false;
        }

        return $vehiculo->delete();
    }

    public function updateStock(int $id, int $cantidad): bool
    {
        $vehiculo = $this->getById($id);
        
        if (!$vehiculo) {
            return false;
        }

        if ($cantidad < 0 && abs($cantidad) > $vehiculo->stock) {
            return false;
        }

        $vehiculo->increment('stock', $cantidad);

        // Actualizar estado si es necesario
        if ($vehiculo->stock === 0) {
            $vehiculo->update(['estado' => Vehiculo::ESTADO_VENDIDO]);
        } elseif ($vehiculo->stock > 0 && $vehiculo->estado === Vehiculo::ESTADO_VENDIDO) {
            $vehiculo->update(['estado' => Vehiculo::ESTADO_DISPONIBLE]);
        }

        return true;
    }

    public function getMarcas(): Collection
    {
        return Vehiculo::select('marca')
                      ->distinct()
                      ->orderBy('marca')
                      ->get();
    }

    public function getEstadisticas(): array
    {
        return [
            'total' => Vehiculo::count(),
            'disponibles' => Vehiculo::disponible()->count(),
            'vendidos' => Vehiculo::vendido()->count(),
            'mantenimiento' => Vehiculo::enMantenimiento()->count(),
            'valor_inventario' => Vehiculo::disponible()->sum('precio_venta'),
        ];
    }
}