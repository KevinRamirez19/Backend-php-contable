<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Venta;
use App\Models\Compra;
use App\Models\Cliente;
use App\Models\Proveedor;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Obtener estadísticas generales del dashboard
     */
    public function getStats()
    {
        try {
            $total_clientes = Cliente::count();
            $total_proveedores = Proveedor::count();
            $total_ventas = Venta::sum('total');
            $total_compras = Compra::sum('total');

            $ventas_mes = Venta::whereMonth('fecha_venta', Carbon::now()->month)->sum('total');
            $compras_mes = Compra::whereMonth('fecha_compra', Carbon::now()->month)->sum('total');

            return response()->json([
                'data' => [
                    'total_clientes' => $total_clientes,
                    'total_proveedores' => $total_proveedores,
                    'total_ventas' => $total_ventas,
                    'total_compras' => $total_compras,
                    'ventas_mes' => $ventas_mes,
                    'compras_mes' => $compras_mes,
                ],
                'status' => 'success',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'data' => null,
                'status' => 'error',
                'message' => 'Error al obtener estadísticas del dashboard: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtener datos para los charts
     */
    public function getChartData()
    {
        try {
            $ventas = Venta::selectRaw('MONTH(fecha_venta) as mes, SUM(total) as total')
                ->groupBy('mes')
                ->get();

            $compras = Compra::selectRaw('MONTH(fecha_compra) as mes, SUM(total) as total')
                ->groupBy('mes')
                ->get();

            $labels = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];

            $chartData = collect($labels)->map(function ($label, $index) use ($ventas, $compras) {
                $venta = $ventas->firstWhere('mes', $index + 1);
                $compra = $compras->firstWhere('mes', $index + 1);
                return [
                    'name' => $label,
                    'ventas' => $venta->total ?? 0,
                    'compras' => $compra->total ?? 0,
                ];
            });

            return response()->json([
                'data' => $chartData,
                'status' => 'success',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'data' => null,
                'status' => 'error',
                'message' => 'Error al obtener datos del chart: ' . $e->getMessage(),
            ], 500);
        }
    }
}
