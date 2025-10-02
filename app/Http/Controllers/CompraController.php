<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCompraRequest;
use App\Http\Resources\CompraResource;
use App\Services\CompraService;
use App\Traits\ApiResponser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CompraController extends Controller
{
    use ApiResponser;

    public function __construct(private CompraService $compraService) {}

    public function index(Request $request): JsonResponse
    {
        try {
            $compras = $this->compraService->obtenerCompras($request->all());
            
            return $this->successResponse(CompraResource::collection($compras), 'Compras obtenidas exitosamente');
            
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener compras: ' . $e->getMessage(), 500);
        }
    }

    public function store(StoreCompraRequest $request): JsonResponse
    {
        try {
            $compra = $this->compraService->crearCompra($request->validated());
            
            return $this->createdResponse(new CompraResource($compra), 'Compra registrada exitosamente');
            
        } catch (\Exception $e) {
            return $this->errorResponse('Error al registrar compra: ' . $e->getMessage(), 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $compra = $this->compraService->obtenerCompra($id);
            
            return $this->successResponse(new CompraResource($compra), 'Compra obtenida exitosamente');
            
        } catch (\Exception $e) {
            return $this->notFoundResponse($e->getMessage());
        }
    }

    public function marcarComoPagada(int $id): JsonResponse
    {
        try {
            $compra = $this->compraService->marcarComoPagada($id);
            
            return $this->successResponse(new CompraResource($compra), 'Compra marcada como pagada exitosamente');
            
        } catch (\Exception $e) {
            return $this->errorResponse('Error al marcar compra como pagada: ' . $e->getMessage(), 500);
        }
    }

    public function marcarComoAnulada(int $id): JsonResponse
    {
        try {
            $compra = $this->compraService->marcarComoAnulada($id);
            
            return $this->successResponse(new CompraResource($compra), 'Compra marcada como anulada exitosamente');
            
        } catch (\Exception $e) {
            return $this->errorResponse('Error al marcar compra como anulada: ' . $e->getMessage(), 500);
        }
    }
}