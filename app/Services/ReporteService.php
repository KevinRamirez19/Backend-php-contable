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
    // App/Services/ReporteService.php

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
                ];
            }),
        ];
    })->toArray();
}

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

    /** 🔹 CORREGIDO: incluye todos los asientos manuales por tipo de cuenta */
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
                    // Activos se calculan diferente de Pasivos/Patrimonio
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
            'fecha_corte' => $fechaCorte,
        ];
    }

    /** 🔹 ACTUALIZADO: acepta cuentas tipo GASTO o EGRESO */
public function generarEstadoResultados(array $filters = []): array
{
    $fechaInicio = $filters['fecha_inicio'] ?? now()->startOfMonth()->format('Y-m-d');
    $fechaFin = $filters['fecha_fin'] ?? now()->format('Y-m-d');

    // 🔸 Ingresos
    $ingresos = Cuenta::where('tipo', 'INGRESO')
        ->with(['partidas.asiento' => function ($q) use ($fechaInicio, $fechaFin) {
            $q->whereBetween('fecha', [$fechaInicio, $fechaFin]);
        }])
        ->get()
        ->map(function ($cuenta) {
            $debe = $cuenta->partidas->sum('debe');
            $haber = $cuenta->partidas->sum('haber');
            $saldo = $haber - $debe; // ingresos = haber - debe
            return ['cuenta' => $cuenta, 'saldo' => $saldo];
        });

    // 🔸 Gastos / Egresos
    $gastos = Cuenta::whereIn('tipo', ['GASTO', 'EGRESO'])
        ->with(['partidas.asiento' => function ($q) use ($fechaInicio, $fechaFin) {
            $q->whereBetween('fecha', [$fechaInicio, $fechaFin]);
        }])
        ->get()
        ->map(function ($cuenta) {
            $debe = $cuenta->partidas->sum('debe');
            $haber = $cuenta->partidas->sum('haber');
            $saldo = $debe - $haber; // gastos = debe - haber
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
