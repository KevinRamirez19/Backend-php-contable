<?php

namespace App\Http\Controllers;
use App\Models\AsientoContable;
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
            
            return $this->successResponse(AsientoContableService::collection($asientos), 'Asientos contables obtenidos exitosamente');
            
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener asientos contables: ' . $e->getMessage(), 500);
        }
    }
    public function store(Request $request)
{
    $request->validate([
        'codigo' => 'required|unique:asientos_contables',
        'descripcion' => 'required',
        'fecha' => 'required|date',
        'partidas' => 'required|array|min:1',
        'partidas.*.cuenta_id' => 'required|exists:cuentas,id',
        'partidas.*.tipo' => 'required|in:debe,haber',
        'partidas.*.monto' => 'required|numeric|min:0',
    ]);

    $asiento = AsientoContable::create([
        'codigo' => $request->codigo,
        'descripcion' => $request->descripcion,
        'fecha' => $request->fecha,
        'total' => array_sum(array_column($request->partidas, 'monto')),
    ]);

    foreach ($request->partidas as $p) {
        $asiento->partidas()->create($p);
    }

    return response()->json($asiento->load('partidas'), 201);
}


    public function show(int $id): JsonResponse
    {
        try {
            $asiento = $this->asientoService->obtenerAsiento($id);
            
            return $this->successResponse(new AsientoContableService($asiento), 'Asiento contable obtenido exitosamente');
            
        } catch (\Exception $e) {
            return $this->notFoundResponse($e->getMessage());
        }
    }

    public function libroDiario(Request $request): JsonResponse
    {
        try {
            $asientos = $this->asientoService->obtenerLibroDiario($request->all());
            
            return $this->successResponse(AsientoContableService::collection($asientos), 'Libro diario obtenido exitosamente');
            
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener libro diario: ' . $e->getMessage(), 500);
        }
    }
}