<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreClienteRequest;
use App\Http\Requests\UpdateClienteRequest;
use App\Http\Resources\ClienteResource;
use App\Services\ClienteService;
use App\Traits\ApiResponser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    use ApiResponser;

    public function __construct(private ClienteService $clienteService) {}

    public function index(Request $request): JsonResponse
    {
        try {
            $clientes = $this->clienteService->obtenerClientes($request->all());
            
            return $this->successResponse(ClienteResource::collection($clientes), 'Clientes obtenidos exitosamente');
            
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener clientes: ' . $e->getMessage(), 500);
        }
    }

    public function store(StoreClienteRequest $request): JsonResponse
    {
        try {
            $cliente = $this->clienteService->crearCliente($request->validated());
            
            return $this->createdResponse(new ClienteResource($cliente), 'Cliente creado exitosamente');
            
        } catch (\Exception $e) {
            return $this->errorResponse('Error al crear cliente: ' . $e->getMessage(), 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $cliente = $this->clienteService->obtenerCliente($id);
            
            return $this->successResponse(new ClienteResource($cliente), 'Cliente obtenido exitosamente');
            
        } catch (\Exception $e) {
            return $this->notFoundResponse($e->getMessage());
        }
    }

    public function update(UpdateClienteRequest $request, int $id): JsonResponse
    {
        try {
            $cliente = $this->clienteService->actualizarCliente($id, $request->validated());
            
            return $this->successResponse(new ClienteResource($cliente), 'Cliente actualizado exitosamente');
            
        } catch (\Exception $e) {
            return $this->errorResponse('Error al actualizar cliente: ' . $e->getMessage(), 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->clienteService->eliminarCliente($id);
            
            return $this->successResponse(null, 'Cliente eliminado exitosamente');
            
        } catch (\Exception $e) {
            return $this->errorResponse('Error al eliminar cliente: ' . $e->getMessage(), 500);
        }
    }
}