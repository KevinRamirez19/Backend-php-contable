<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVentaRequest;
use App\Http\Resources\VentaResource;
use App\Services\VentaService;
use App\Traits\ApiResponser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VentaController extends Controller
{
    use ApiResponser;

    public function __construct(private VentaService $ventaService) {}

    public function index(Request $request): JsonResponse
    {
        try {
            $ventas = $this->ventaService->obtenerVentas($request->all());
            
            return $this->successResponse(VentaResource::collection($ventas), 'Ventas obtenidas exitosamente');
            
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener ventas: ' . $e->getMessage(), 500);
        }
    }

    public function store(StoreVentaRequest $request): JsonResponse
    {
        try {
            $venta = $this->ventaService->crearVenta($request->validated());
            
            return $this->createdResponse(new VentaResource($venta), 'Venta registrada exitosamente');
            
        } catch (\Exception $e) {
            return $this->errorResponse('Error al registrar venta: ' . $e->getMessage(), 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $venta = $this->ventaService->obtenerVenta($id);
            
            return $this->successResponse(new VentaResource($venta), 'Venta obtenida exitosamente');
            
        } catch (\Exception $e) {
            return $this->notFoundResponse($e->getMessage());
        }
    }

    public function reenviarDian(int $id): JsonResponse
    {
        try {
            $venta = $this->ventaService->reenviarFacturaDian($id);
            
            return $this->successResponse(new VentaResource($venta), 'Factura reenviada a DIAN exitosamente');
            
        } catch (\Exception $e) {
            return $this->errorResponse('Error al reenviar factura a DIAN: ' . $e->getMessage(), 500);
        }
    }
}