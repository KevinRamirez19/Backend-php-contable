<?php

namespace App\Services;

use App\Models\Compra;
use App\Models\Vehiculo;
use Illuminate\Support\Facades\DB;
use Exception;

class CompraService
{
    public function crearCompra(array $data)
    {
        try {
            return DB::transaction(function () use ($data) {

                // 🔹 Calcular totales
                $subtotal = collect($data['vehiculos'])->sum(fn($v) => $v['precio_unitario'] * $v['cantidad']);
                $iva = $subtotal * 0.19;
                $total = $subtotal + $iva;

                // 🔹 Crear la compra principal
                $compra = Compra::create([
                    'proveedor_id'   => $data['proveedor_id'],
                    'numero_factura' => $data['numero_factura'] ?? 'FAC-' . now()->timestamp,
                    'fecha_compra'   => $data['fecha_compra'] ?? now(),
                    'subtotal'       => $subtotal,
                    'iva'            => $iva,
                    'total'          => $total,
                    'estado'         => $data['estado'] ?? 'Pendiente',
                ]);

                // 🔹 Asociar vehículos y actualizar stock
                foreach ($data['vehiculos'] as $v) {
                    $compra->vehiculos()->attach($v['vehiculo_id'], [
                        'precio_unitario' => $v['precio_unitario'],
                        'cantidad' => $v['cantidad'],
                    ]);

                    $vehiculo = Vehiculo::find($v['vehiculo_id']);
                    if ($vehiculo) {
                        $vehiculo->increment('stock', $v['cantidad']);
                        if ($vehiculo->estado === Vehiculo::ESTADO_VENDIDO) {
                            $vehiculo->update(['estado' => Vehiculo::ESTADO_DISPONIBLE]);
                        }
                    }
                }

                return $compra->load(['proveedor', 'vehiculos']);
            });
        } catch (Exception $e) {
            throw new Exception("No se pudo crear la compra: " . $e->getMessage());
        }
    }
}
