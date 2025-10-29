<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class LibroDiarioExport implements FromArray, WithHeadings
{
    protected $asientos;

    public function __construct(array $asientos)
    {
        $this->asientos = $asientos;
    }

    public function array(): array
    {
        $rows = [];

        foreach ($this->asientos as $asiento) {
            foreach ($asiento['partidas'] as $p) {
                $rows[] = [
                    $asiento['fecha'],
                    $asiento['descripcion'],
                    $p['cuenta_codigo'] ?? '',
                    $p['cuenta_nombre'] ?? '',
                    $p['debe'] ?? 0,
                    $p['haber'] ?? 0,
                    $p['descripcion'] ?? ''
                ];
            }
        }

        return $rows;
    }

    public function headings(): array
    {
        return [
            'Fecha',
            'Asiento',
            'Código Cuenta',
            'Nombre Cuenta',
            'Debe',
            'Haber',
            'Descripción'
        ];
    }
}
