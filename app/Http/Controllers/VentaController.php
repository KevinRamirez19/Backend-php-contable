<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVentaRequest;
use App\Http\Resources\VentaResource;
use App\Services\VentaService;
use Illuminate\Http\JsonResponse;

class VentaController extends Controller
{
    public function __construct(private VentaService $ventaService) {}

    public function store(StoreVentaRequest $request): JsonResponse
    {
        try {
            $venta = $this->ventaService->crearVenta($request->validated());
            
            return response()->json([
                'success' => true,
                'data' => new VentaResource($venta),
                'message' => 'Venta registrada exitosamente'
            ], 201);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar la venta: ' . $e->getMessage()
            ], 500);
        }
    }

    public function index(): JsonResponse
    {
        $ventas = $this->ventaService->obtenerVentas();
        
        return response()->json([
            'success' => true,
            'data' => VentaResource::collection($ventas)
        ]);
    }

    public function show(int $id): JsonResponse
    {
        try {
            $venta = $this->ventaService->obtenerVenta($id);
            
            return response()->json([
                'success' => true,
                'data' => new VentaResource($venta)
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 404);
        }
    }
}