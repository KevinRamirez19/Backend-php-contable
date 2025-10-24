<?php

namespace App\Http\Controllers;

use App\Traits\ApiResponser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Services\CompraService;
use App\Http\Resources\CompraResource;
use App\Models\Compra;

class CompraController extends Controller
{
    use ApiResponser;

    protected $compraService;

    public function __construct(CompraService $compraService)
    {
        $this->compraService = $compraService;
        $this->middleware('auth:api');
    }

    /**
     * 📦 Listar todas las compras
     */
    public function index(): JsonResponse
    {
        $compras = Compra::with(['proveedor', 'vehiculos'])->get();
        return $this->successResponse(
            CompraResource::collection($compras),
            'Lista de compras obtenida correctamente'
        );
    }

    /**
     * 🔍 Mostrar una compra específica
     */
    public function show($id): JsonResponse
    {
        $compra = Compra::with(['proveedor', 'vehiculos'])->findOrFail($id);
        return $this->successResponse(
            new CompraResource($compra),
            'Compra encontrada correctamente'
        );
    }

    /**
     * 📝 Crear una nueva compra con vehículos y actualizar stock
     */
    public function store(Request $request): JsonResponse
{
    try {
        $data = $request->validate([
            'proveedor_id' => 'required|integer|exists:proveedores,id',
            'fecha_compra' => 'nullable|date',
            'vehiculos' => 'required|array|min:1',
            'vehiculos.*.vehiculo_id' => 'required|exists:vehiculos,id',
            'vehiculos.*.precio_unitario' => 'required|numeric|min:0',
            'vehiculos.*.cantidad' => 'required|integer|min:1',
        ]);

        $compra = $this->compraService->crearCompra($data);

        return $this->successResponse(
            new CompraResource($compra),
            '✅ Compra creada y stock actualizado correctamente'
        );

    } catch (\Illuminate\Validation\ValidationException $e) {
        return $this->errorResponse($e->errors(), 422);
    } catch (\Exception $e) {
        return $this->errorResponse('❌ Error al crear la compra: ' . $e->getMessage(), 500);
    }
}

    /**
     * 🗑️ Eliminar una compra
     */
    public function destroy($id): JsonResponse
    {
        $compra = Compra::findOrFail($id);
        $compra->delete();

        return $this->successResponse(null, 'Compra eliminada correctamente');
    }
}
