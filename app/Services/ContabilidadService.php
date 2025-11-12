<?php

namespace App\Services;

use App\Models\AsientoContable;
use App\Models\PartidaContable;
use App\Models\Venta;
use App\Models\Compra;
use Illuminate\Support\Facades\DB;

class ContabilidadService
{
    public function registrarVenta(Venta $venta): AsientoContable
    {
        return DB::transaction(function () use ($venta) {
            $asiento = AsientoContable::create([
                'codigo' => 'AS-V-' . $venta->id,
                'descripcion' => "Venta de vehículos - Factura {$venta->numero_factura}",
                'fecha' => $venta->fecha_venta,
                'venta_id' => $venta->id,
                'created_by' => auth()->id(),
            ]);

            // Partida 1: Débito a Caja
            PartidaContable::create([
                'asiento_id' => $asiento->id,
                'cuenta_id' => 1, // Caja (1105)
                'debe' => $venta->total,
                'haber' => 0,
                'descripcion' => "Ingreso por venta {$venta->numero_factura}",
            ]);

            // Partida 2: Crédito a Ventas
            PartidaContable::create([
                'asiento_id' => $asiento->id,
                'cuenta_id' => 4, // Ventas (4135)
                'debe' => 0,
                'haber' => $venta->subtotal,
                'descripcion' => "Venta de vehículos {$venta->numero_factura}",
            ]);

            // Partida 3: Crédito a IVA por Pagar
            PartidaContable::create([
                'asiento_id' => $asiento->id,
                'cuenta_id' => 3, // IVA por Pagar (2408)
                'debe' => 0,
                'haber' => $venta->iva,
                'descripcion' => "IVA venta {$venta->numero_factura}",
            ]);

            return $asiento;
        });
    }

    public function registrarCompra(Compra $compra): AsientoContable
    {
        return DB::transaction(function () use ($compra) {
            $asiento = AsientoContable::create([
    'codigo' => 'AS-V-' . $venta->id,
    'descripcion' => "Venta de vehículos - Factura {$venta->numero_factura}",
    'fecha' => $venta->fecha_venta,
    'venta_id' => $venta->id,
    'total' => $venta->total, // ✅ agrega esto si deseas reflejar el total
    'created_by' => auth()->id(),
]);


            // Partida 1: Débito a Inventario
            PartidaContable::create([
                'asiento_id' => $asiento->id,
                'cuenta_id' => 2, // Inventario (1435)
                'debe' => $compra->subtotal,
                'haber' => 0,
                'descripcion' => "Compra de inventario {$compra->numero_factura}",
            ]);

            // Partida 2: Débito a IVA
            PartidaContable::create([
                'asiento_id' => $asiento->id,
                'cuenta_id' => 3, // IVA (2408)
                'debe' => $compra->iva,
                'haber' => 0,
                'descripcion' => "IVA compra {$compra->numero_factura}",
            ]);

            // Partida 3: Crédito a Proveedores
            PartidaContable::create([
                'asiento_id' => $asiento->id,
                'cuenta_id' => 5, // Proveedores (2205)
                'debe' => 0,
                'haber' => $compra->total,
                'descripcion' => "Compra a {$compra->proveedor->nombre}",
            ]);

            return $asiento;
        });
    }

    public function obtenerAsientoPorVenta(int $ventaId): ?AsientoContable
    {
        return AsientoContable::where('venta_id', $ventaId)
            ->with(['partidas.cuenta'])
            ->first();
    }

    public function obtenerAsientoPorCompra(int $compraId): ?AsientoContable
    {
        return AsientoContable::where('compra_id', $compraId)
            ->with(['partidas.cuenta'])
            ->first();
    }
}
