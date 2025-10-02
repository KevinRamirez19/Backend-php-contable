<?php

namespace App\Services;

use App\Models\AsientoContable;
use App\Models\Cuenta;
use App\Models\Venta;
use App\Models\Compra;
use App\Models\Vehiculo;
use Illuminate\Support\Facades\DB;

class ReporteService
{
    public function generarLibroDiario(array $filters = []): array
    {
        $fechaInicio = $filters['fecha_inicio'] ?? now()->startOfMonth()->format('Y-m-d');
        $fechaFin = $filters['fecha_fin'] ?? now()->format('Y-m-d');

        $asientos = AsientoContable::with(['partidas.cuenta', 'compra', 'venta'])
            ->whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->orderBy('fecha')
            ->orderBy('id')
            ->get();

        $totalDebe = $asientos->sum('total_debe');
        $totalHaber = $asientos->sum('total_haber');

        return [
            'asientos' => $asientos,
            'totales' => [
                'total_debe' => $totalDebe,
                'total_haber' => $totalHaber,
                'diferencia' => abs($totalDebe - $totalHaber),
                'balanceado' => $totalDebe == $totalHaber,
            ],
            'periodo' => [
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
            ]
        ];
    }

    public function generarMayorCuentas(array $filters = []): array
    {
        $fechaInicio = $filters['fecha_inicio'] ?? now()->startOfMonth()->format('Y-m-d');
        $fechaFin = $filters['fecha_fin'] ?? now()->format('Y-m-d');

        $cuentas = Cuenta::with(['partidas' => function($query) use ($fechaInicio, $fechaFin) {
            $query->whereHas('asiento', function($q) use ($fechaInicio, $fechaFin) {
                $q->whereBetween('fecha', [$fechaInicio, $fechaFin]);
            });
        }])->get();

        $resultado = $cuentas->map(function($cuenta) use ($fechaInicio, $fechaFin) {
            $debe = $cuenta->partidas->sum('debe');
            $haber = $cuenta->partidas->sum('haber');
            $saldo = $debe - $haber;

            return [
                'cuenta' => $cuenta,
                'debe' => $debe,
                'haber' => $haber,
                'saldo' => $saldo,
                'movimientos' => $cuenta->partidas->count(),
            ];
        });

        return [
            'cuentas' => $resultado,
            'periodo' => [
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
            ],
            'totales' => [
                'total_debe' => $resultado->sum('debe'),
                'total_haber' => $resultado->sum('haber'),
                'diferencia' => abs($resultado->sum('debe') - $resultado->sum('haber')),
            ]
        ];
    }

    public function generarBalanceGeneral(array $filters = []): array
    {
        $fechaCorte = $filters['fecha_corte'] ?? now()->format('Y-m-d');

        $activos = Cuenta::where('tipo', 'ACTIVO')
            ->with(['partidas' => function($query) use ($fechaCorte) {
                $query->whereHas('asiento', function($q) use ($fechaCorte) {
                    $q->where('fecha', '<=', $fechaCorte);
                });
            }])
            ->get()
            ->map(function($cuenta) {
                $saldo = $cuenta->partidas->sum('debe') - $cuenta->partidas->sum('haber');
                return [
                    'cuenta' => $cuenta,
                    'saldo' => $saldo,
                ];
            });

        $pasivos = Cuenta::where('tipo', 'PASIVO')
            ->with(['partidas' => function($query) use ($fechaCorte) {
                $query->whereHas('asiento', function($q) use ($fechaCorte) {
                    $q->where('fecha', '<=', $fechaCorte);
                });
            }])
            ->get()
            ->map(function($cuenta) {
                $saldo = $cuenta->partidas->sum('debe') - $cuenta->partidas->sum('haber');
                return [
                    'cuenta' => $cuenta,
                    'saldo' => $saldo,
                ];
            });

        $patrimonio = Cuenta::where('tipo', 'PATRIMONIO')
            ->with(['partidas' => function($query) use ($fechaCorte) {
                $query->whereHas('asiento', function($q) use ($fechaCorte) {
                    $q->where('fecha', '<=', $fechaCorte);
                });
            }])
            ->get()
            ->map(function($cuenta) {
                $saldo = $cuenta->partidas->sum('debe') - $cuenta->partidas->sum('haber');
                return [
                    'cuenta' => $cuenta,
                    'saldo' => $saldo,
                ];
            });

        $totalActivos = $activos->sum('saldo');
        $totalPasivos = $pasivos->sum('saldo');
        $totalPatrimonio = $patrimonio->sum('saldo');

        return [
            'activos' => [
                'cuentas' => $activos,
                'total' => $totalActivos,
            ],
            'pasivos' => [
                'cuentas' => $pasivos,
                'total' => $totalPasivos,
            ],
            'patrimonio' => [
                'cuentas' => $patrimonio,
                'total' => $totalPatrimonio,
            ],
            'balance' => [
                'activos' => $totalActivos,
                'pasivos_patrimonio' => $totalPasivos + $totalPatrimonio,
                'diferencia' => abs($totalActivos - ($totalPasivos + $totalPatrimonio)),
                'balanceado' => $totalActivos == ($totalPasivos + $totalPatrimonio),
            ],
            'fecha_corte' => $fechaCorte,
        ];
    }

    public function generarEstadoResultados(array $filters = []): array
    {
        $fechaInicio = $filters['fecha_inicio'] ?? now()->startOfMonth()->format('Y-m-d');
        $fechaFin = $filters['fecha_fin'] ?? now()->format('Y-m-d');

        $ingresos = Cuenta::where('tipo', 'INGRESO')
            ->with(['partidas' => function($query) use ($fechaInicio, $fechaFin) {
                $query->whereHas('asiento', function($q) use ($fechaInicio, $fechaFin) {
                    $q->whereBetween('fecha', [$fechaInicio, $fechaFin]);
                });
            }])
            ->get()
            ->map(function($cuenta) {
                $saldo = $cuenta->partidas->sum('debe') - $cuenta->partidas->sum('haber');
                return [
                    'cuenta' => $cuenta,
                    'saldo' => $saldo,
                ];
            });

        $gastos = Cuenta::where('tipo', 'GASTO')
            ->with(['partidas' => function($query) use ($fechaInicio, $fechaFin) {
                $query->whereHas('asiento', function($q) use ($fechaInicio, $fechaFin) {
                    $q->whereBetween('fecha', [$fechaInicio, $fechaFin]);
                });
            }])
            ->get()
            ->map(function($cuenta) {
                $saldo = $cuenta->partidas->sum('debe') - $cuenta->partidas->sum('haber');
                return [
                    'cuenta' => $cuenta,
                    'saldo' => $saldo,
                ];
            });

        $totalIngresos = $ingresos->sum('saldo');
        $totalGastos = $gastos->sum('saldo');
        $utilidadNeta = $totalIngresos - $totalGastos;

        return [
            'ingresos' => [
                'cuentas' => $ingresos,
                'total' => $totalIngresos,
            ],
            'gastos' => [
                'cuentas' => $gastos,
                'total' => $totalGastos,
            ],
            'resultado' => [
                'utilidad_neta' => $utilidadNeta,
                'periodo' => $utilidadNeta >= 0 ? 'UTILIDAD' : 'PERDIDA',
            ],
            'periodo' => [
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
            ]
        ];
    }

    public function generarReporteVentas(array $filters = []): array
    {
        $fechaInicio = $filters['fecha_inicio'] ?? now()->startOfMonth()->format('Y-m-d');
        $fechaFin = $filters['fecha_fin'] ?? now()->format('Y-m-d');

        $ventas = Venta::with(['cliente', 'detalles.vehiculo'])
            ->whereBetween('fecha_venta', [$fechaInicio, $fechaFin])
            ->orderBy('fecha_venta')
            ->get();

        $totalVentas = $ventas->sum('total');
        $totalIva = $ventas->sum('iva');
        $cantidadVentas = $ventas->count();

        return [
            'ventas' => $ventas,
            'resumen' => [
                'total_ventas' => $totalVentas,
                'total_iva' => $totalIva,
                'cantidad_ventas' => $cantidadVentas,
                'promedio_venta' => $cantidadVentas > 0 ? $totalVentas / $cantidadVentas : 0,
            ],
            'periodo' => [
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
            ]
        ];
    }

    public function generarReporteInventario(array $filters = []): array
    {
        $vehiculos = Vehiculo::with(['proveedor'])
            ->orderBy('marca')
            ->orderBy('modelo')
            ->get();

        $totalValorInventario = $vehiculos->sum(function($vehiculo) {
            return $vehiculo->precio_compra * $vehiculo->stock;
        });

        $vehiculosDisponibles = $vehiculos->where('estado', 'DISPONIBLE')->count();
        $vehiculosVendidos = $vehiculos->where('estado', 'VENDIDO')->count();

        return [
            'vehiculos' => $vehiculos,
            'resumen' => [
                'total_vehiculos' => $vehiculos->count(),
                'vehiculos_disponibles' => $vehiculosDisponibles,
                'vehiculos_vendidos' => $vehiculosVendidos,
                'total_valor_inventario' => $totalValorInventario,
                'valor_promedio_vehiculo' => $vehiculos->count() > 0 ? $totalValorInventario / $vehiculos->count() : 0,
            ]
        ];
    }
}