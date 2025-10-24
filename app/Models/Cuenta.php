<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cuenta extends Model
{

    protected $table = 'cuentas';

    protected $fillable = [
        'codigo',
        'nombre',
        'tipo',
    ];

    /**
     * Constantes para tipos de cuenta
     */
    const TIPO_ACTIVO = 'ACTIVO';
    const TIPO_PASIVO = 'PASIVO';
    const TIPO_PATRIMONIO = 'PATRIMONIO';
    const TIPO_INGRESO = 'INGRESO';
    const TIPO_GASTO = 'GASTO';

    /**
     * Relaciones
     */
    public function partidas()
    {
        return $this->hasMany(PartidaContable::class, 'cuenta_id');
    }

    /**
     * Scopes
     */
    public function scopeByTipo($query, $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    public function scopeByCodigo($query, $codigo)
    {
        return $query->where('codigo', $codigo);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where('codigo', 'like', "%{$search}%")
            ->orWhere('nombre', 'like', "%{$search}%");
    }

    /**
     * Métodos de utilidad
     */
    public function getSaldoAttribute()
    {
        $debe = $this->partidas()->sum('debe');
        $haber = $this->partidas()->sum('haber');

        return $debe - $haber;
    }

    public function getSaldoPeriodo($fechaInicio, $fechaFin)
    {
        $debe = $this->partidas()
            ->whereHas('asiento', function ($q) use ($fechaInicio, $fechaFin) {
                $q->whereBetween('fecha', [$fechaInicio, $fechaFin]);
            })
            ->sum('debe');

        $haber = $this->partidas()
            ->whereHas('asiento', function ($q) use ($fechaInicio, $fechaFin) {
                $q->whereBetween('fecha', [$fechaInicio, $fechaFin]);
            })
            ->sum('haber');

        return $debe - $haber;
    }

    public function esActivo()
    {
        return $this->tipo === self::TIPO_ACTIVO;
    }

    public function esPasivo()
    {
        return $this->tipo === self::TIPO_PASIVO;
    }

    public function esPatrimonio()
    {
        return $this->tipo === self::TIPO_PATRIMONIO;
    }

    public function esIngreso()
    {
        return $this->tipo === self::TIPO_INGRESO;
    }

    public function esGasto()
    {
        return $this->tipo === self::TIPO_GASTO;
    }

    public function getTipoCompletoAttribute()
    {
        $tipos = [
            self::TIPO_ACTIVO => 'Activo',
            self::TIPO_PASIVO => 'Pasivo',
            self::TIPO_PATRIMONIO => 'Patrimonio',
            self::TIPO_INGRESO => 'Ingreso',
            self::TIPO_GASTO => 'Gasto',
        ];

        return $tipos[$this->tipo] ?? $this->tipo;
    }

    /**
     * Validaciones
     */
    public static function rules($id = null)
    {
        return [
            'codigo' => 'required|string|max:20|unique:cuentas,codigo,' . $id,
            'nombre' => 'required|string|max:100',
            'tipo' => 'required|in:ACTIVO,PASIVO,PATRIMONIO,INGRESO,GASTO',
        ];
    }
}
