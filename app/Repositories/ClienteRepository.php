<?php

namespace App\Repositories;

use App\Models\Cliente;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ClienteRepository
{
    public function getAll(array $filters = []): LengthAwarePaginator
    {
        $query = Cliente::query();

        if (!empty($filters['search'])) {
            $query->search($filters['search']);
        }

        if (!empty($filters['tipo_documento'])) {
            $query->byTipoDocumento($filters['tipo_documento']);
        }

        $sortField = $filters['sort_field'] ?? 'created_at';
        $sortDirection = $filters['sort_direction'] ?? 'desc';

        return $query->orderBy($sortField, $sortDirection)
                    ->paginate($filters['per_page'] ?? 15);
    }

    public function getById(int $id): ?Cliente
    {
        return Cliente::with(['ventas'])->find($id);
    }

    public function getByDocumento(string $numeroDocumento): ?Cliente
    {
        return Cliente::where('numero_documento', $numeroDocumento)->first();
    }

    public function create(array $data): Cliente
    {
        return Cliente::create($data);
    }

    public function update(int $id, array $data): bool
    {
        $cliente = $this->getById($id);
        
        if (!$cliente) {
            return false;
        }

        return $cliente->update($data);
    }

    public function delete(int $id): bool
    {
        $cliente = $this->getById($id);
        
        if (!$cliente) {
            return false;
        }

        return $cliente->delete();
    }

    public function getClientesConVentas(): Collection
    {
        return Cliente::has('ventas')
                     ->withCount('ventas')
                     ->withSum('ventas', 'total')
                     ->get();
    }
}