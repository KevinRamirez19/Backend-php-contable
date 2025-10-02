<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VentaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'numero_factura' => $this->numero_factura,
            'fecha_venta' => $this->fecha_venta->format('Y-m-d'),
            'cliente' => new ClienteResource($this->whenLoaded('cliente')),
            'subtotal' => (float) $this->subtotal,
            'iva' => (float) $this->iva,
            'total' => (float) $this->total,
            'estado_dian' => $this->estado_dian,
            'cufe' => $this->cufe,
            'detalles' => VentaDetalleResource::collection($this->whenLoaded('detalles')),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}