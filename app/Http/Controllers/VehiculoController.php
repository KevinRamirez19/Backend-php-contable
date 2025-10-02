<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVehiculoRequest;
use App\Http\Requests\UpdateVehiculoRequest;
use App\Http\Resources\VehiculoResource;
use App\Services\VehiculoService;
use App\Traits\ApiResponser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VehiculoController extends Controller
{
    use ApiResponser;

    public function __construct(private VehiculoService $vehiculoService) {}

    public function index(Request $request): JsonResponse
    {
        try {
            $vehiculos = $this->vehiculoService->obtenerVehiculos($request->all());
            
            return $this->successResponse(VehiculoResource::collection($vehiculos), 'Vehículos obtenidos exitosamente');
            
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener vehículos: ' . $e->getMessage(), 500);
        }
    }

    public function disponibles(Request $request): JsonResponse
    {
        try {
            $vehiculos = $this->vehiculoService->obtenerVehiculosDisponibles($request->all());
            
            return $this->successResponse(VehiculoResource::collection($vehiculos), 'Vehículos disponibles obtenidos exitosamente');
            
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener vehículos disponibles: ' . $e->getMessage(), 500);
        }
    }

    public function store(StoreVehiculoRequest $request): JsonResponse
    {
        try {
            $vehiculo = $this->vehiculoService->crearVehiculo($request->validated());
            
            return $this->createdResponse(new VehiculoResource($vehiculo), 'Vehículo creado exitosamente');
            
        } catch (\Exception $e) {
            return $this->errorResponse('Error al crear vehículo: ' . $e->getMessage(), 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $vehiculo = $this->vehiculoService->obtenerVehiculo($id);
            
            return $this->successResponse(new VehiculoResource($vehiculo), 'Vehículo obtenido exitosamente');
            
        } catch (\Exception $e) {
            return $this->notFoundResponse($e->getMessage());
        }
    }

    public function update(UpdateVehiculoRequest $request, int $id): JsonResponse
    {
        try {
            $vehiculo = $this->vehiculoService->actualizarVehiculo($id, $request->validated());
            
            return $this->successResponse(new VehiculoResource($vehiculo), 'Vehículo actualizado exitosamente');
            
        } catch (\Exception $e) {
            return $this->errorResponse('Error al actualizar vehículo: ' . $e->getMessage(), 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->vehiculoService->eliminarVehiculo($id);
            
            return $this->successResponse(null, 'Vehículo eliminado exitosamente');
            
        } catch (\Exception $e) {
            return $this->errorResponse('Error al eliminar vehículo: ' . $e->getMessage(), 500);
        }
    }
}