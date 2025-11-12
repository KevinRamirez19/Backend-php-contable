<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class LibroDiarioExport implements FromArray, WithHeadings, WithTitle, ShouldAutoSize
{
    protected array $asientos;

    public function __construct(array $asientos)
    {
        $this->asientos = $asientos;
    }

    public function array(): array
    {
        $data = [];

        foreach ($this->asientos as $asiento) {
            $fecha = $asiento['fecha'] ?? 'N/A';
            $descripcion = $asiento['descripcion'] ?? '';

            $totalDebe = 0;
            $totalHaber = 0;

            // ✅ Filas de detalle
            foreach ($asiento['partidas'] as $p) {
                $totalDebe += $p['debe'] ?? 0;
                $totalHaber += $p['haber'] ?? 0;

                $data[] = [
                    'Fecha'          => $fecha,
                    'Descripción'    => $descripcion,
                    'Código Cuenta'  => $p['cuenta_codigo'] ?? '',
                    'Nombre Cuenta'  => $p['cuenta_nombre'] ?? '',
                    'Tipo de Cuenta' => $p['tipo_cuenta'] ?? '',
                    'Debe'           => $p['debe'] ?? 0,
                    'Haber'          => $p['haber'] ?? 0,
                    'Detalle'        => $p['descripcion'] ?? '',
                ];
            }

            // ✅ Fila de totales por asiento
            $data[] = [
                'Fecha'          => '',
                'Descripción'    => 'Totales del asiento:',
                'Código Cuenta'  => '',
                'Nombre Cuenta'  => '',
                'Tipo de Cuenta' => '',
                'Debe'           => $totalDebe,
                'Haber'          => $totalHaber,
                'Detalle'        => '',
            ];

            // ✅ Línea en blanco para separar asientos
            $data[] = array_fill(0, 8, '');
        }

        return $data;
    }

    public function headings(): array
    {
        return [
            'Fecha',
            'Descripción',
            'Código Cuenta',
            'Nombre Cuenta',
            'Tipo de Cuenta',
            'Debe',
            'Haber',
            'Detalle'
        ];
    }

    public function title(): string
    {
        return 'Libro Diario';
    }
}
