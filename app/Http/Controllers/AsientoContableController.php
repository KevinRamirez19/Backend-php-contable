<?php

namespace App\Http\Controllers;

use App\Http\Resources\AsientoContableResource;
use App\Services\AsientoContableService;
use App\Traits\ApiResponser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AsientoContableController extends Controller
{
    use ApiResponser;

    public function __construct(private AsientoContableService $asientoService) {}

    public function index(Request $request): JsonResponse
    {
        try {
            $asientos = $this->asientoService->obtenerAsientos($request->all());
            
            return $this->successResponse(AsientoContableResource::collection($asientos), 'Asientos contables obtenidos exitosamente');
            
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener asientos contables: ' . $e->getMessage(), 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $asiento = $this->asientoService->obtenerAsiento($id);
            
            return $this->successResponse(new AsientoContableResource($asiento), 'Asiento contable obtenido exitosamente');
            
        } catch (\Exception $e) {
            return $this->notFoundResponse($e->getMessage());
        }
    }

    public function libroDiario(Request $request): JsonResponse
    {
        try {
            $asientos = $this->asientoService->obtenerLibroDiario($request->all());
            
            return $this->successResponse(AsientoContableResource::collection($asientos), 'Libro diario obtenido exitosamente');
            
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener libro diario: ' . $e->getMessage(), 500);
        }
    }
}