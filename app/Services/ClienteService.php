<?php

namespace App\Services;

use App\Models\Cliente;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ClienteService
{
    public function obtenerClientes(array $filters = []): LengthAwarePaginator
    {
        $query = Cliente::query();

        // Filtro de búsqueda
        if (!empty($filters['search'])) {
            $query->search($filters['search']);
        }

        // Filtro por tipo de documento
        if (!empty($filters['tipo_documento'])) {
            $query->byTipoDocumento($filters['tipo_documento']);
        }

        // Ordenamiento
        $sortField = $filters['sort_field'] ?? 'created_at';
        $sortDirection = $filters['sort_direction'] ?? 'desc';
        $query->orderBy($sortField, $sortDirection);

        return $query->paginate($filters['per_page'] ?? 15);
    }

    public function obtenerCliente(int $id): Cliente
    {
        $cliente = Cliente::with(['ventas'])->find($id);

        if (!$cliente) {
            throw new \Exception('Cliente no encontrado');
        }

        return $cliente;
    }

    public function crearCliente(array $data): Cliente
    {
        return Cliente::create($data);
    }

    public function actualizarCliente(int $id, array $data): Cliente
    {
        $cliente = $this->obtenerCliente($id);
        $cliente->update($data);

        return $cliente->fresh();
    }

    public function eliminarCliente(int $id): bool
    {
        $cliente = $this->obtenerCliente($id);

        // Verificar si tiene ventas asociadas
        if ($cliente->ventas()->exists()) {
            throw new \Exception('No se puede eliminar el cliente porque tiene ventas asociadas');
        }

        return $cliente->delete();
    }
}