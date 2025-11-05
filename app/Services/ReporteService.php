<?php

namespace App\Services;

use App\Models\AsientoContable;
use App\Models\Cuenta;

class ReporteService
{
    /** 🔹 Libro Diario corregido */
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
            // Ordenar partidas para mostrar primero el Debe y luego el Haber
            $partidas = $asiento->partidas->sortByDesc(function ($p) {
                return $p->debe > 0 ? 1 : 0;
            })->map(function ($p) {
                // Forzar Debe/Haber según tipo de cuenta
                $debe = in_array($p->cuenta->tipo, ['ACTIVO', 'GASTO']) ? $p->debe : 0;
                $haber = in_array($p->cuenta->tipo, ['PASIVO', 'PATRIMONIO', 'INGRESO']) ? $p->haber : 0;

                return [
                    'cuenta_codigo' => $p->cuenta->codigo,
                    'cuenta_nombre' => $p->cuenta->nombre,
                    'debe' => $debe,
                    'haber' => $haber,
                    'descripcion' => $p->descripcion,
                    'contrapartida' => $haber > 0 ? 'Venta' : 'Compra',
                ];
            });

            return [
                'fecha' => $asiento->fecha,
                'descripcion' => $asiento->descripcion,
                'partidas' => $partidas,
            ];
        })->toArray();
    }
}
