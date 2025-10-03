<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VehiculoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'marca' => $this->marca,
            'modelo' => $this->modelo,
            'año' => $this->año,
            'color' => $this->color,
            'placa' => $this->placa,
            'vin' => $this->vin,
            'precio_compra' => (float) $this->precio_compra,
            'precio_venta' => (float) $this->precio_venta,
            'estado' => $this->estado,
            'stock' => $this->stock,
            'descripcion_completa' => $this->descripcion_completa,
            'margen_ganancia' => $this->margen_ganancia,
            'ganancia_neta' => $this->ganancia_neta,
            'tiene_stock' => $this->tiene_stock(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'deleted_at' => $this->deleted_at?->toISOString(),
            
            // Relaciones
            'proveedor' => new ProveedorResource($this->whenLoaded('proveedor')),
            'compra_detalles' => CompraDetalleResource::collection($this->whenLoaded('compraDetalles')),
            'venta_detalles' => VentaDetalleResource::collection($this->whenLoaded('ventaDetalles')),
        ];
    }
}