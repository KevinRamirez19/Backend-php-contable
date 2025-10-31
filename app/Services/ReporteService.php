<?php

namespace App\Services;

use App\Models\AsientoContable;
use App\Models\Cuenta;

class ReporteService
{
    /** 🔹 Libro Diario */
    public function generarLibroDiario(array $filters = []): array
    {
        $fechaInicio = $filters['fecha_inicio'] ?? now()->startOfMonth()->format('Y-m-d');
        $fechaFin = $filters['fecha_fin'] ?? now()->format('Y-m-d');

        $asientos = AsientoContable::with(['partidas.cuenta'])
            ->whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->orderBy('fecha')
            ->orderBy('id')
            ->get();

        return $asientos->map(function ($asiento) {
            return [
                'fecha' => $asiento->fecha,
                'descripcion' => $asiento->descripcion,
                'partidas' => $asiento->partidas->map(function ($p) {
                    return [
                        'cuenta_codigo' => $p->cuenta->codigo,
                        'cuenta_nombre' => $p->cuenta->nombre,
                        'debe' => $p->debe,
                        'haber' => $p->haber,
                        'descripcion' => $p->descripcion,
                        'contrapartida' => $p->haber > 0 ? 'Venta' : 'Compra',
                    ];
                }),
            ];
        })->toArray();
    }

    /** 🔹 Mayor de Cuentas */
    public function generarMayorCuentas(array $filters = []): array
    {
        $fechaInicio = $filters['fecha_inicio'] ?? now()->startOfMonth()->format('Y-m-d');
        $fechaFin = $filters['fecha_fin'] ?? now()->format('Y-m-d');

        $cuentas = Cuenta::with(['partidas.asiento' => function($q) use ($fechaInicio, $fechaFin) {
            $q->whereBetween('fecha', [$fechaInicio, $fechaFin]);
        }])->get();

        $resultado = $cuentas->map(function($cuenta) {
            $debe = $cuenta->partidas->sum('debe');
            $haber = $cuenta->partidas->sum('haber');
            return [
                'cuenta' => $cuenta,
                'debe' => $debe,
                'haber' => $haber,
                'saldo' => $debe - $haber,
                'movimientos' => $cuenta->partidas->count(),
            ];
        });

        return [
            'cuentas' => $resultado,
            'totales' => [
                'total_debe' => $resultado->sum('debe'),
                'total_haber' => $resultado->sum('haber'),
                'diferencia' => abs($resultado->sum('debe') - $resultado->sum('haber')),
            ],
        ];
    }

    /** 🔹 Balance General (con contrapartida de ventas / utilidad del ejercicio) */
    public function generarBalanceGeneral(array $filters = []): array
    {
        $fechaCorte = $filters['fecha_corte'] ?? now()->format('Y-m-d');

        $tipos = ['ACTIVO', 'PASIVO', 'PATRIMONIO'];
        $resultados = [];

        foreach ($tipos as $tipo) {
            $cuentas = Cuenta::where('tipo', $tipo)
                ->with(['partidas.asiento' => function ($q) use ($fechaCorte) {
                    $q->whereDate('fecha', '<=', $fechaCorte);
                }])
                ->get()
                ->map(function ($cuenta) {
                    $debe = $cuenta->partidas->sum('debe');
                    $haber = $cuenta->partidas->sum('haber');
                    $saldo = $cuenta->tipo === 'ACTIVO' ? $debe - $haber : $haber - $debe;
                    return [
                        'cuenta' => $cuenta,
                        'saldo' => $saldo,
                    ];
                });

            $resultados[$tipo] = [
                'cuentas' => $cuentas,
                'total' => $cuentas->sum('saldo'),
            ];
        }

        // 🔸 Calcular utilidad o pérdida del ejercicio (contrapartida de ventas)
        $ingresos = Cuenta::where('tipo', 'INGRESO')->with('partidas')->get();
        $gastos = Cuenta::whereIn('tipo', ['GASTO', 'EGRESO'])->with('partidas')->get();

        $totalIngresos = $ingresos->sum(fn($c) => $c->partidas->sum('haber') - $c->partidas->sum('debe'));
        $totalGastos = $gastos->sum(fn($c) => $c->partidas->sum('debe') - $c->partidas->sum('haber'));

        $utilidad = $totalIngresos - $totalGastos;

        // 🔹 Añadir contrapartida (ventas) dentro del patrimonio
        $contrapartida = [
            'cuenta' => (object)[
                'codigo' => '3900',
                'nombre' => $utilidad >= 0 ? 'Utilidad del ejercicio (contrapartida de ventas)' : 'Pérdida del ejercicio',
            ],
            'saldo' => $utilidad,
        ];

        $resultados['PATRIMONIO']['cuentas']->push($contrapartida);
        $resultados['PATRIMONIO']['total'] += $utilidad;

        return [
            'activos' => $resultados['ACTIVO'],
            'pasivos' => $resultados['PASIVO'],
            'patrimonio' => $resultados['PATRIMONIO'],
            'balance' => [
                'activos' => $resultados['ACTIVO']['total'],
                'pasivos_patrimonio' => $resultados['PASIVO']['total'] + $resultados['PATRIMONIO']['total'],
                'diferencia' => abs(
                    $resultados['ACTIVO']['total'] - ($resultados['PASIVO']['total'] + $resultados['PATRIMONIO']['total'])
                ),
            ],
            'utilidad_ejercicio' => $utilidad,
            'contrapartida' => $contrapartida,
            'fecha_corte' => $fechaCorte,
        ];
    }

    /** 🔹 Estado de Resultados */
    public function generarEstadoResultados(array $filters = []): array
    {
        $fechaInicio = $filters['fecha_inicio'] ?? now()->startOfMonth()->format('Y-m-d');
        $fechaFin = $filters['fecha_fin'] ?? now()->format('Y-m-d');

        $ingresos = Cuenta::where('tipo', 'INGRESO')
            ->with(['partidas.asiento' => function ($q) use ($fechaInicio, $fechaFin) {
                $q->whereBetween('fecha', [$fechaInicio, $fechaFin]);
            }])
            ->get()
            ->map(function ($cuenta) {
                $debe = $cuenta->partidas->sum('debe');
                $haber = $cuenta->partidas->sum('haber');
                $saldo = $haber - $debe;
                return ['cuenta' => $cuenta, 'saldo' => $saldo];
            });

        $gastos = Cuenta::whereIn('tipo', ['GASTO', 'EGRESO'])
            ->with(['partidas.asiento' => function ($q) use ($fechaInicio, $fechaFin) {
                $q->whereBetween('fecha', [$fechaInicio, $fechaFin]);
            }])
            ->get()
            ->map(function ($cuenta) {
                $debe = $cuenta->partidas->sum('debe');
                $haber = $cuenta->partidas->sum('haber');
                $saldo = $debe - $haber;
                return ['cuenta' => $cuenta, 'saldo' => $saldo];
            });

        $totalIngresos = $ingresos->sum('saldo');
        $totalGastos = $gastos->sum('saldo');
        $utilidadNeta = $totalIngresos - $totalGastos;

        return [
            'ingresos' => ['cuentas' => $ingresos, 'total' => $totalIngresos],
            'gastos' => ['cuentas' => $gastos, 'total' => $totalGastos],
            'resultado' => [
                'utilidad_neta' => $utilidadNeta,
                'periodo' => $utilidadNeta >= 0 ? 'UTILIDAD' : 'PÉRDIDA',
            ],
            'periodo' => [
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
            ],
        ];
    }
}
