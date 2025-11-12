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
        'tipo', // Opcional: indica si la partida es "debe" o "haber"
        'descripcion',
    ];

    protected $casts = [
        'debe' => 'decimal:2',
        'haber' => 'decimal:2',
    ];

    public $timestamps = true;

    // ==========================
    // 🔹 Relaciones
    // ==========================
    public function asiento()
    {
        return $this->belongsTo(AsientoContable::class, 'asiento_id');
    }

    public function cuenta()
    {
        return $this->belongsTo(Cuenta::class, 'cuenta_id');
    }

    // ==========================
    // 🔹 Métodos de utilidad
    // ==========================
    public function getNombreCuentaAttribute(): string
    {
        return $this->cuenta?->nombre ?? 'Cuenta no encontrada';
    }

    public function getCodigoCuentaAttribute(): string
    {
        return $this->cuenta?->codigo ?? 'N/A';
    }

    public function getTipoCuentaAttribute(): string
    {
        return $this->cuenta?->tipo ?? 'Desconocido';
    }
}
