<?php

namespace App\Services;

use App\Models\AsientoContable;
use App\Models\PartidaContable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class AsientoContableService
{
    public function obtenerAsientos(array $filters = []): LengthAwarePaginator
    {
        $query = AsientoContable::with(['partidas.cuenta', 'compra', 'venta', 'usuario']);

        // Filtro de búsqueda
        if (!empty($filters['search'])) {
            $query->search($filters['search']);
        }

        // Filtro por fecha
        if (!empty($filters['fecha_inicio']) && !empty($filters['fecha_fin'])) {
            $query->betweenDates($filters['fecha_inicio'], $filters['fecha_fin']);
        }

        // Filtro por tipo
        if (!empty($filters['tipo'])) {
            $query->byTipo($filters['tipo']);
        }

        // Ordenamiento
        $sortField = $filters['sort_field'] ?? 'fecha';
        $sortDirection = $filters['sort_direction'] ?? 'desc';
        $query->orderBy($sortField, $sortDirection);

        return $query->paginate($filters['per_page'] ?? 15);
    }

    public function obtenerAsiento(int $id): AsientoContable
    {
        $asiento = AsientoContable::with([
            'partidas.cuenta', 
            'compra', 
            'venta', 
            'usuario'
        ])->find($id);

        if (!$asiento) {
            throw new \Exception('Asiento contable no encontrado');
        }

        return $asiento;
    }

    public function obtenerLibroDiario(array $filters = []): Collection
    {
        $query = AsientoContable::with(['partidas.cuenta']);

        // Filtro por fecha (obligatorio para libro diario)
        if (empty($filters['fecha_inicio']) || empty($filters['fecha_fin'])) {
            // Por defecto, último mes
            $filters['fecha_inicio'] = now()->subMonth()->format('Y-m-d');
            $filters['fecha_fin'] = now()->format('Y-m-d');
        }

        $query->betweenDates($filters['fecha_inicio'], $filters['fecha_fin']);

        return $query->orderBy('fecha')->orderBy('id')->get();
    }

    public function crearAsientoManual(array $data): AsientoContable
    {
        return DB::transaction(function () use ($data) {
            // Validar que el asiento esté balanceado
            $totalDebe = collect($data['partidas'])->sum('debe');
            $totalHaber = collect($data['partidas'])->sum('haber');

            if ($totalDebe != $totalHaber) {
                throw new \Exception("El asiento no está balanceado. Débito: {$totalDebe}, Crédito: {$totalHaber}");
            }

            // Generar código único para el asiento
            $codigo = 'AS-M-' . now()->format('Ymd') . '-' . 
                     AsientoContable::whereDate('created_at', today())->count() + 1;

            // Crear asiento
            $asiento = AsientoContable::create([
                'codigo' => $codigo,
                'descripcion' => $data['descripcion'],
                'fecha' => $data['fecha'],
                'created_by' => auth()->id(),
            ]);

            // Crear partidas
            foreach ($data['partidas'] as $partidaData) {
                PartidaContable::create([
                    'asiento_id' => $asiento->id,
                    'cuenta_id' => $partidaData['cuenta_id'],
                    'debe' => $partidaData['debe'],
                    'haber' => $partidaData['haber'],
                    'descripcion' => $partidaData['descripcion'] ?? null,
                ]);
            }

            return $asiento->fresh(['partidas.cuenta', 'usuario']);
        });
    }

    public function eliminarAsiento(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $asiento = $this->obtenerAsiento($id);

            // Verificar que no esté asociado a compras/ventas
            if ($asiento->compra_id || $asiento->venta_id) {
                throw new \Exception('No se puede eliminar un asiento asociado a una compra o venta');
            }

            // Eliminar partidas primero
            $asiento->partidas()->delete();

            // Eliminar asiento
            return $asiento->delete();
        });
    }

    public function obtenerEstadisticasPeriodo(string $fechaInicio, string $fechaFin): array
    {
        $asientos = AsientoContable::with('partidas')
            ->whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->get();

        $totalAsientos = $asientos->count();
        $totalDebe = 0;
        $totalHaber = 0;

        foreach ($asientos as $asiento) {
            $totalDebe += $asiento->partidas->sum('debe');
            $totalHaber += $asiento->partidas->sum('haber');
        }

        return [
            'total_asientos' => $totalAsientos,
            'total_debe' => $totalDebe,
            'total_haber' => $totalHaber,
            'diferencia' => abs($totalDebe - $totalHaber),
            'esta_balanceado' => $totalDebe == $totalHaber,
            'periodo' => [
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
            ]
        ];
    }

    public function obtenerAsientosPorCuenta(int $cuentaId, array $filters = []): Collection
    {
        $query = PartidaContable::with(['asiento', 'cuenta'])
            ->where('cuenta_id', $cuentaId);

        // Filtro por fecha
        if (!empty($filters['fecha_inicio']) && !empty($filters['fecha_fin'])) {
            $query->whereHas('asiento', function ($q) use ($filters) {
                $q->whereBetween('fecha', [$filters['fecha_inicio'], $filters['fecha_fin']]);
            });
        }

        return $query->orderByDesc('id')->get();
    }

    public function obtenerSaldoCuenta(int $cuentaId, ?string $fechaCorte = null): float
    {
        $query = PartidaContable::where('cuenta_id', $cuentaId);

        if ($fechaCorte) {
            $query->whereHas('asiento', function ($q) use ($fechaCorte) {
                $q->where('fecha', '<=', $fechaCorte);
            });
        }

        $totalDebe = (float) $query->sum('debe');
        $totalHaber = (float) $query->sum('haber');

        return $totalDebe - $totalHaber;
    }

    public function generarReporteMayor(array $filters = []): array
    {
        $fechaInicio = $filters['fecha_inicio'] ?? now()->startOfMonth()->format('Y-m-d');
        $fechaFin = $filters['fecha_fin'] ?? now()->format('Y-m-d');

        $cuentas = \App\Models\Cuenta::with(['partidas' => function ($query) use ($fechaInicio, $fechaFin) {
            $query->whereHas('asiento', function ($q) use ($fechaInicio, $fechaFin) {
                $q->whereBetween('fecha', [$fechaInicio, $fechaFin]);
            });
        }])->get();

        $reporte = [];

        foreach ($cuentas as $cuenta) {
            $totalDebe = $cuenta->partidas->sum('debe');
            $totalHaber = $cuenta->partidas->sum('haber');
            $saldo = $totalDebe - $totalHaber;

            if ($totalDebe > 0 || $totalHaber > 0) {
                $reporte[] = [
                    'cuenta' => $cuenta->only(['id', 'codigo', 'nombre', 'tipo']),
                    'total_debe' => $totalDebe,
                    'total_haber' => $totalHaber,
                    'saldo' => $saldo,
                    'movimientos' => $cuenta->partidas->count(),
                ];
            }
        }

        return [
            'reporte' => $reporte,
            'periodo' => [
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
            ],
            'resumen' => [
                'total_cuentas' => count($reporte),
                'total_debe' => collect($reporte)->sum('total_debe'),
                'total_haber' => collect($reporte)->sum('total_haber'),
                'diferencia' => abs(collect($reporte)->sum('total_debe') - collect($reporte)->sum('total_haber')),
            ]
        ];
    }
}