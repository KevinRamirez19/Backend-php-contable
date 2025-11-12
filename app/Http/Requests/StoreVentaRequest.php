<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreVentaRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Permitir solo usuarios autenticados
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'cliente_id' => 'required|exists:clientes,id',
            'numero_factura' => 'required|string|max:50|unique:ventas,numero_factura',
            'fecha_venta' => 'required|date',
            'subtotal' => 'nullable|numeric|min:0',
            'iva' => 'nullable|numeric|min:0',
            'total' => 'nullable|numeric|min:0',
            'estado_dian' => 'nullable|string|max:50',
            'detalles' => 'required|array|min:1',
            'detalles.*.vehiculo_id' => 'required|exists:vehiculos,id',
            'detalles.*.cantidad' => 'required|integer|min:1',
            'detalles.*.precio_unitario' => 'nullable|numeric|min:0',
            'detalles.*.subtotal' => 'nullable|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'cliente_id.required' => 'El cliente es obligatorio.',
            'cliente_id.exists' => 'El cliente seleccionado no existe.',
            'numero_factura.required' => 'El número de factura es obligatorio.',
            'numero_factura.unique' => 'Ya existe una venta con ese número de factura.',
            'fecha_venta.required' => 'La fecha de venta es obligatoria.',
            'detalles.required' => 'Debe agregar al menos un vehículo a la venta.',
            'detalles.*.vehiculo_id.required' => 'Debe seleccionar un vehículo.',
            'detalles.*.vehiculo_id.exists' => 'El vehículo seleccionado no existe.',
            'detalles.*.cantidad.required' => 'Debe indicar la cantidad del vehículo.',
            'detalles.*.cantidad.min' => 'La cantidad mínima es 1.',
        ];
    }
}
