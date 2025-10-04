<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVentaRequest;
use App\Http\Resources\VentaResource;
use App\Models\Venta; // 👈 importante si quieres hacer consultas directas
use App\Services\VentaService;
use App\Traits\ApiResponser;
use Illuminate\Http\JsonResponse;

class VentaController extends Controller
{
    use ApiResponser;

    public function __construct(private VentaService $ventaService) {}

    /**
     * Listar todas las ventas
     */
    public function index(): JsonResponse
    {
        try {
            // si tienes el método en tu servicio
            // $ventas = $this->ventaService->obtenerVentas();

            // versión rápida sin tocar el service:
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

    /**
     * Registrar una nueva venta
     */
    public function store(StoreVentaRequest $request): JsonResponse
    {
        // Temporalmente sin verificación de rol
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
}
