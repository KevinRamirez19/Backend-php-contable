<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVentaRequest;
use App\Http\Resources\VentaResource;
use App\Models\Venta;
use App\Services\VentaService;
use App\Traits\ApiResponser;
use Illuminate\Http\JsonResponse;

class VentaController extends Controller
{
    use ApiResponser;

    public function __construct(private VentaService $ventaService) {}

    // Listar todas las ventas
    public function index(): JsonResponse
    {
        try {
            $ventas = Venta::with(['cliente', 'detalles.vehiculo'])->get();

            return $this->successResponse(
                VentaResource::collection($ventas),
                'Listado de ventas obtenido exitosamente'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Error al obtener ventas: ' . $e->getMessage(),
                500
            );
        }
    }

    // Registrar venta
    public function store(StoreVentaRequest $request): JsonResponse
    {
        try {
            $venta = $this->ventaService->crearVenta($request->validated());

            return $this->createdResponse(
                new VentaResource($venta),
                'Venta registrada exitosamente'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Error al registrar la venta: ' . $e->getMessage(),
                500
            );
        }
    }

    // Mostrar detalle de una venta
    public function show($id): JsonResponse
    {
        try {
            $venta = Venta::with(['cliente', 'detalles.vehiculo'])->find($id);

            if (!$venta) {
                return $this->errorResponse('Venta no encontrada', 404);
            }

            return $this->successResponse(
                new VentaResource($venta),
                'Detalle de la venta obtenido exitosamente'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Error al obtener la venta: ' . $e->getMessage(),
                500
            );
        }
    }
}
