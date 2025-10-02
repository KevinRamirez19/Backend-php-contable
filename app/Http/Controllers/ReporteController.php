<?php

namespace App\Http\Controllers;

use App\Services\ReporteService;
use App\Traits\ApiResponser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReporteController extends Controller
{
    use ApiResponser;

    public function __construct(private ReporteService $reporteService) {}

    public function libroDiario(Request $request): JsonResponse
    {
        try {
            $data = $this->reporteService->generarLibroDiario($request->all());
            
            return $this->successResponse($data, 'Libro diario generado exitosamente');
            
        } catch (\Exception $e) {
            return $this->errorResponse('Error al generar libro diario: ' . $e->getMessage(), 500);
        }
    }

    public function mayorCuentas(Request $request): JsonResponse
    {
        try {
            $data = $this->reporteService->generarMayorCuentas($request->all());
            
            return $this->successResponse($data, 'Mayor de cuentas generado exitosamente');
            
        } catch (\Exception $e) {
            return $this->errorResponse('Error al generar mayor de cuentas: ' . $e->getMessage(), 500);
        }
    }

    public function balanceGeneral(Request $request): JsonResponse
    {
        try {
            $data = $this->reporteService->generarBalanceGeneral($request->all());
            
            return $this->successResponse($data, 'Balance general generado exitosamente');
            
        } catch (\Exception $e) {
            return $this->errorResponse('Error al generar balance general: ' . $e->getMessage(), 500);
        }
    }

    public function estadoResultados(Request $request): JsonResponse
    {
        try {
            $data = $this->reporteService->generarEstadoResultados($request->all());
            
            return $this->successResponse($data, 'Estado de resultados generado exitosamente');
            
        } catch (\Exception $e) {
            return $this->errorResponse('Error al generar estado de resultados: ' . $e->getMessage(), 500);
        }
    }

    public function ventasPorPeriodo(Request $request): JsonResponse
    {
        try {
            $data = $this->reporteService->generarReporteVentas($request->all());
            
            return $this->successResponse($data, 'Reporte de ventas generado exitosamente');
            
        } catch (\Exception $e) {
            return $this->errorResponse('Error al generar reporte de ventas: ' . $e->getMessage(), 500);
        }
    }

    public function inventario(Request $request): JsonResponse
    {
        try {
            $data = $this->reporteService->generarReporteInventario($request->all());
            
            return $this->successResponse($data, 'Reporte de inventario generado exitosamente');
            
        } catch (\Exception $e) {
            return $this->errorResponse('Error al generar reporte de inventario: ' . $e->getMessage(), 500);
        }
    }
}