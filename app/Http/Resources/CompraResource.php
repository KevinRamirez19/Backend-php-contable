<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompraResource extends JsonResource
{
    /**
     * Transformar la compra en un array JSON.
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'proveedor' => new ProveedorResource($this->whenLoaded('proveedor')),
            'numero_factura' => $this->numero_factura,
            'fecha_compra' => $this->fecha_compra,
            'subtotal' => $this->subtotal,
            'iva' => $this->iva,
            'total' => $this->total,
            'estado' => $this->estado,
            'vehiculos' => $this->whenLoaded('vehiculos', function () {
                return $this->vehiculos->map(function ($vehiculo) {
                    return [
                        'id' => $vehiculo->id,
                        'marca' => $vehiculo->marca,
                        'modelo' => $vehiculo->modelo,
                        'precio_unitario' => $vehiculo->pivot->precio_unitario,
                        'cantidad' => $vehiculo->pivot->cantidad,
                    ];
                });
            }),
        ];
    }
}
