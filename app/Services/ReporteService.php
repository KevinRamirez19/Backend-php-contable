<?php

namespace App\Services;

use App\Models\AsientoContable;

class ReporteService
{
    /** 📘 Generar Libro Diario */
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
            // Mapea cada partida contable
            $partidas = $asiento->partidas->map(function ($p) {
                // Si la cuenta existe, tomamos su tipo (ACTIVO, PASIVO, etc.)
                $tipoCuenta = $p->cuenta->tipo ?? 'DESCONOCIDO';

                return [
                    'cuenta_codigo' => $p->cuenta->codigo ?? '',
                    'cuenta_nombre' => $p->cuenta->nombre ?? '',
                    'tipo_cuenta'   => $tipoCuenta,
                    'debe'          => $p->debe ?? 0,
                    'haber'         => $p->haber ?? 0,
                    'descripcion'   => $p->descripcion ?? '',
                ];
            });

            return [
                'fecha'       => $asiento->fecha,
                'descripcion' => $asiento->descripcion,
                'partidas'    => $partidas,
            ];
        })->toArray();
    }
}
