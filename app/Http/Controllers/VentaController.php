<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVentaRequest;
use App\Http\Resources\VentaResource;
use App\Services\VentaService;
use App\Traits\ApiResponser;
use Illuminate\Http\JsonResponse;

class VentaController extends Controller
{
    use ApiResponser;

    public function __construct(private VentaService $ventaService) {}

    public function store(StoreVentaRequest $request): JsonResponse
    {
        // Comentar temporalmente la verificación de autorización
        // if (!auth()->user()->hasRole('vendedor')) {
        //     return $this->errorResponse('No tienes permisos para crear ventas', 403);
        // }

        try {
            $venta = $this->ventaService->crearVenta($request->validated());
            
            return $this->createdResponse(new VentaResource($venta), 'Venta registrada exitosamente');
            
        } catch (\Exception $e) {
            return $this->errorResponse('Error al registrar la venta: ' . $e->getMessage(), 500);
        }
    }

    // ... otros métodos
}