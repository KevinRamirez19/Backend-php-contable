<?php

namespace App\Repositories;

use App\Models\Compra;

class CompraRepository
{
    /**
     * Obtener todas las compras con sus relaciones
     */
    public function obtenerTodas()
    {
        return Compra::with(['proveedor', 'detalles'])->get();
    }

    /**
     * Obtener una compra por ID
     */
    public function obtenerPorId($id)
    {
        return Compra::with(['proveedor', 'detalles'])->find($id);
    }

    /**
     * Crear una nueva compra
     */
    public function crear(array $data)
    {
        return Compra::create($data);
    }

    /**
     * Actualizar una compra existente
     */
    public function actualizar($id, array $data)
    {
        $compra = Compra::findOrFail($id);
        $compra->update($data);
        return $compra;
    }

    /**
     * Eliminar una compra
     */
    public function eliminar($id)
    {
        $compra = Compra::findOrFail($id);
        return $compra->delete();
    }
}
