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
        $data = [];

        foreach ($this->asientos as $asiento) {
            foreach ($asiento['partidas'] as $p) {
                $data[] = [
                    'Código Cuenta' => $p['cuenta_codigo'],
                    'Nombre Cuenta' => $p['cuenta_nombre'],
                    'Debe' => $p['debe'],
                    'Haber' => $p['haber'],
                    'Detalle' => $p['descripcion'],
                ];
            }
        }

        return $data;
    }

    public function headings(): array
    {
        return ['Código Cuenta', 'Nombre Cuenta', 'Debe', 'Haber', 'Detalle'];
    }
}
