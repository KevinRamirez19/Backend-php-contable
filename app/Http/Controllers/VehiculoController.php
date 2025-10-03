<?php

namespace App\Http\Controllers;

use App\Models\Vehiculo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VehiculoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        try {
            $vehiculos = Vehiculo::with(['proveedor'])->get();
            
            return response()->json([
                'success' => true,
                'data' => $vehiculos,
                'message' => 'Vehículos obtenidos exitosamente'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener vehículos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display a listing of available vehicles.
     */
    public function disponibles(): JsonResponse
    {
        try {
            $vehiculos = Vehiculo::with(['proveedor'])
                ->where('estado', 'DISPONIBLE')
                ->where('stock', '>', 0)
                ->get();
                
            return response()->json([
                'success' => true,
                'data' => $vehiculos,
                'message' => 'Vehículos disponibles obtenidos exitosamente'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener vehículos disponibles: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'proveedor_id' => 'required|exists:proveedores,id',
                'marca' => 'required|string|max:50',
                'modelo' => 'required|string|max:50',
                'año' => 'required|integer|min:1900|max:' . (date('Y') + 1),
                'color' => 'nullable|string|max:30',
                'placa' => 'nullable|string|max:15|unique:vehiculos,placa',
                'vin' => 'nullable|string|max:17|unique:vehiculos,vin',
                'precio_compra' => 'required|numeric|min:0',
                'precio_venta' => 'required|numeric|min:0',
                'estado' => 'required|in:DISPONIBLE,VENDIDO,MANTENIMIENTO',
                'stock' => 'required|integer|min:0',
            ]);

            $vehiculo = Vehiculo::create($validated);

            return response()->json([
                'success' => true,
                'data' => $vehiculo->load('proveedor'),
                'message' => 'Vehículo creado exitosamente'
            ], 201);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear vehículo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Vehiculo $vehiculo): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'data' => $vehiculo->load(['proveedor']),
                'message' => 'Vehículo obtenido exitosamente'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener vehículo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Vehiculo $vehiculo): JsonResponse
    {
        try {
            $validated = $request->validate([
                'proveedor_id' => 'sometimes|exists:proveedores,id',
                'marca' => 'sometimes|string|max:50',
                'modelo' => 'sometimes|string|max:50',
                'año' => 'sometimes|integer|min:1900|max:' . (date('Y') + 1),
                'color' => 'nullable|string|max:30',
                'placa' => 'sometimes|string|max:15|unique:vehiculos,placa,' . $vehiculo->id,
                'vin' => 'sometimes|string|max:17|unique:vehiculos,vin,' . $vehiculo->id,
                'precio_compra' => 'sometimes|numeric|min:0',
                'precio_venta' => 'sometimes|numeric|min:0',
                'estado' => 'sometimes|in:DISPONIBLE,VENDIDO,MANTENIMIENTO',
                'stock' => 'sometimes|integer|min:0',
            ]);

            $vehiculo->update($validated);

            return response()->json([
                'success' => true,
                'data' => $vehiculo->fresh(['proveedor']),
                'message' => 'Vehículo actualizado exitosamente'
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar vehículo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Vehiculo $vehiculo): JsonResponse
    {
        try {
            $vehiculo->delete();

            return response()->json([
                'success' => true,
                'message' => 'Vehículo eliminado exitosamente'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar vehículo: ' . $e->getMessage()
            ], 500);
        }
    }
}