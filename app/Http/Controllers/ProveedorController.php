<?php

namespace App\Http\Controllers;

use App\Models\Proveedor;
use App\Http\Requests\StoreProveedorRequest;
use App\Http\Requests\UpdateProveedorRequest;
use App\Http\Resources\ProveedorResource;
use App\Services\ProveedorService;
use App\Traits\ApiResponser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProveedorController extends Controller
{
    use ApiResponser;

    public function __construct(private ProveedorService $proveedorService) {}

    public function index(Request $request): JsonResponse
    {
        try {
            $proveedores = $this->proveedorService->obtenerProveedores($request->all());
            return $this->successResponse(ProveedorResource::collection($proveedores), 'Proveedores obtenidos exitosamente');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener proveedores: ' . $e->getMessage(), 500);
        }
    }

   public function store(StoreProveedorRequest $request)
{
    $proveedor = Proveedor::create($request->validated());

    return response()->json([
        'message' => 'Proveedor creado exitosamente',
        'data' => $proveedor
    ], 201);
}


    public function show(int $id): JsonResponse
    {
        try {
            $proveedor = $this->proveedorService->obtenerProveedor($id);
            return $this->successResponse(new ProveedorResource($proveedor), 'Proveedor obtenido exitosamente');
        } catch (\Exception $e) {
            return $this->notFoundResponse($e->getMessage());
        }
    }

    public function update(UpdateProveedorRequest $request, int $id): JsonResponse
    {
        try {
            $proveedor = $this->proveedorService->actualizarProveedor($id, $request->validated());
            return $this->successResponse(new ProveedorResource($proveedor), 'Proveedor actualizado exitosamente');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al actualizar proveedor: ' . $e->getMessage(), 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->proveedorService->eliminarProveedor($id);
            return $this->successResponse(null, 'Proveedor eliminado exitosamente');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al eliminar proveedor: ' . $e->getMessage(), 500);
        }
    }
}