<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCompraRequest;
use App\Http\Resources\CompraResource;
use App\Models\Compra;
use App\Services\CompraService;      // ✅ Solo usar el service
use App\Traits\ApiResponser;
use Illuminate\Http\JsonResponse;

class CompraController extends Controller
{
    use ApiResponser;

    public function __construct(private CompraService $compraService) {}

    public function index(): JsonResponse
    {
        $compras = $this->compraService->obtenerCompras();
        return $this->successResponse(
            CompraResource::collection($compras),
            'Listado de compras obtenido exitosamente'
        );
    }

    public function store(StoreCompraRequest $request): JsonResponse
    {
        $compra = $this->compraService->crearCompra($request->validated());
        return $this->createdResponse(
            new CompraResource($compra),
            'Compra registrada exitosamente'
        );
    }

    public function marcarComoPagada(int $id): JsonResponse
    {
        $compra = $this->compraService->marcarComoPagada($id);
        return $this->successResponse(
            new CompraResource($compra),
            'Compra marcada como pagada'
        );
    }

    public function marcarComoAnulada(int $id): JsonResponse
    {
        $compra = $this->compraService->marcarComoAnulada($id);
        return $this->successResponse(
            new CompraResource($compra),
            'Compra anulada correctamente'
        );
    }
}
