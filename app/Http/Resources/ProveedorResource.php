<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProveedorResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'nit' => $this->nit,          // ✅ Asegúrate que esté aquí
            'email' => $this->email,
            'telefono' => $this->telefono,
            'direccion' => $this->direccion,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
