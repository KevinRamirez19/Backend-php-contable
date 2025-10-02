<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PartidaContable extends Model
{
    use HasFactory;

    protected $table = 'partidas_contables';

    protected $fillable = [
        'asiento_id',
        'cuenta_id',
        'debe',
        'haber',
        'descripcion',
    ];

    protected $casts = [
        'debe' => 'decimal:2',
        'haber' => 'decimal:2',
    ];

    public $timestamps = false;

    /**
     * Relaciones
     */
    public function asiento()
    {
        return $this->belongsTo(AsientoContable::class);
    }

    public function cuenta()
    {
        return $this->belongsTo(Cuenta::class);
    }

    /**
     * Scopes
     */
    public function scopeByCuenta($query, $cuentaId)
    {
        return $query->where('cuenta_id', $cuentaId);
    }

    public function scopeDebe($query)
    {
        return $query->where('debe', '>', 0);
    }

    public function scopeHaber($query)
    {
        return $query->where('haber', '>', 0);
    }

    /**
     * Métodos de utilidad
     */
    public function getMovimientoAttribute()
    {
        if ($this->debe > 0) {
            return 'Débito';
        } else {
            return 'Crédito';
        }
    }

    public function getMontoAttribute()
    {
        return $this->debe > 0 ? $this->debe : $this->haber;
    }

    public function getNombreCuentaAttribute()
    {
        return $this->cuenta ? $this->cuenta->nombre : 'Cuenta no encontrada';
    }

    public function getCodigoCuentaAttribute()
    {
        return $this->cuenta ? $this->cuenta->codigo : 'N/A';
    }
}