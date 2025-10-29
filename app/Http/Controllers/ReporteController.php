<?php

namespace App\Http\Controllers;

use App\Services\ReporteService;
use App\Traits\ApiResponser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\LibroDiarioExport;

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
    // Reportes contables

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
public function descargarLibroDiarioPDF(Request $request)
{
    $asientos = $this->reporteService->generarLibroDiario($request->all());

    // Asegurarse de que todas las partidas existan y los campos tengan valor
    foreach ($asientos as &$asiento) {
        $asiento['partidas'] = $asiento['partidas'] ?? [];
        foreach ($asiento['partidas'] as &$p) {
            $p['cuenta_codigo'] = $p['cuenta_codigo'] ?? '';
            $p['cuenta_nombre'] = $p['cuenta_nombre'] ?? '';
            $p['debe'] = $p['debe'] ?? 0;
            $p['haber'] = $p['haber'] ?? 0;
            $p['descripcion'] = $p['descripcion'] ?? '';
        }
    }

    $pdf = Pdf::loadView('reportes.libro_diario', compact('asientos'))
              ->setPaper('a4', 'portrait');

    return $pdf->download('libro_diario.pdf');
}

public function descargarLibroDiarioExcel(Request $request)
{
    $asientos = $this->reporteService->generarLibroDiario($request->all());

    return Excel::download(new LibroDiarioExport($asientos), 'libro_diario.xlsx');
}

}