<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClienteResource extends JsonResource
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
            'nombre' => $this->nombre,
            'direccion' => $this->direccion,
            'tipo_documento' => $this->tipo_documento,
            'numero_documento' => $this->numero_documento,
            'telefono' => $this->telefono,
            'email' => $this->email,
            'documento_completo' => $this->documento_completo,
            'ventas_count' => $this->whenLoaded('ventas', $this->ventas_count),
            'total_compras' => $this->whenLoaded('ventas', $this->total_compras),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'deleted_at' => $this->deleted_at?->toISOString(),
            
            // Relaciones
            'ventas' => VentaResource::collection($this->whenLoaded('ventas')),
        ];
    }
}