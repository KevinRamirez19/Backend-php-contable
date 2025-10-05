<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CompraResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'proveedor' => $this->proveedor->nombre ?? null,
            'fecha_compra' => $this->fecha_compra,
            'total' => $this->total,
            'estado' => $this->estado,
            'detalle' => $this->detalle, // si tienes relación con detalle_compra
        ];
    }
}
