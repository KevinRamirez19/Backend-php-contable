<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CompraDetalleResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'vehiculo' => $this->vehiculo->descripcion_completa ?? 'Vehículo no encontrado',
            'cantidad' => $this->cantidad,
            'precio_unitario' => (float) $this->precio_unitario,
            'subtotal' => (float) $this->subtotal,
        ];
    }
}
