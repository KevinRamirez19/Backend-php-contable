<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreVentaRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Permitir temporalmente a todos los usuarios autenticados
        return auth()->check(); 
        // return auth()->check() && auth()->user()->hasRole('vendedor');
    }

    public function rules(): array
    {
        return [
            'cliente_id' => 'required|exists:clientes,id',
            'detalles' => 'required|array|min:1',
            'detalles.*.vehiculo_id' => 'required|exists:vehiculos,id',
            'detalles.*.cantidad' => 'required|integer|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'cliente_id.required' => 'El cliente es obligatorio',
            'cliente_id.exists' => 'El cliente seleccionado no existe',
            'detalles.required' => 'Debe agregar al menos un vehículo a la venta',
            'detalles.*.vehiculo_id.required' => 'El vehículo es obligatorio',
            'detalles.*.vehiculo_id.exists' => 'El vehículo seleccionado no existe',
            'detalles.*.cantidad.required' => 'La cantidad es obligatoria',
            'detalles.*.cantidad.min' => 'La cantidad debe ser al menos 1',
        ];
    }
}
