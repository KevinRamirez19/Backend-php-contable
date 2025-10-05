<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCompraRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // cambiar a false si quieres control de permisos
    }

   public function rules(): array
{
    return [
        'proveedor_id' => 'required|exists:proveedores,id',
        'numero_factura' => 'required|string',
        'fecha_compra' => 'required|date',
        'detalles' => 'required|array|min:1',
        'detalles.*.vehiculo_id' => 'required|exists:vehiculos,id',
        'detalles.*.cantidad' => 'required|integer|min:1',
        'detalles.*.precio_unitario' => 'required|numeric|min:0',
    ];
}


    public function messages(): array
    {
        return [
            'proveedor_id.required' => 'El proveedor es obligatorio',
            'proveedor_id.exists' => 'El proveedor seleccionado no existe',
            'detalles.required' => 'Debe agregar al menos un vehículo a la compra',
            'detalles.*.vehiculo_id.required' => 'El vehículo es obligatorio',
            'detalles.*.vehiculo_id.exists' => 'El vehículo seleccionado no existe',
            'detalles.*.cantidad.required' => 'La cantidad es obligatoria',
            'detalles.*.cantidad.min' => 'La cantidad debe ser al menos 1',
            'detalles.*.precio_unitario.required' => 'El precio unitario es obligatorio',
            'detalles.*.precio_unitario.min' => 'El precio unitario debe ser mayor o igual a 0',
        ];
    }
}
