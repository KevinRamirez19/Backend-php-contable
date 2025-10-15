<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class VentaDetalleResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
             'producto' => $this->vehiculo?->descripcion_completa ?? null,
            'cantidad' => $this->cantidad,
            'precio_unitario' => $this->precio_unitario,
            'subtotal' => $this->subtotal,
        ];
    }
}
