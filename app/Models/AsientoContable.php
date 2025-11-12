<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AsientoContable extends Model
{
    use HasFactory;

    protected $table = 'asientos_contables';

    protected $fillable = [
        'codigo',
        'descripcion',
        'fecha',
        'compra_id',
        'venta_id',
        'created_by',
        'total'
    ];

    protected $casts = [
        'fecha' => 'date',
    ];

    // Relaciones
    public function partidas()
    {
        return $this->hasMany(PartidaContable::class, 'asiento_id');
    }

    public function compra()
    {
        return $this->belongsTo(Compra::class);
    }

    public function venta()
    {
        return $this->belongsTo(Venta::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Totales
    public function getTotalDebeAttribute()
    {
        return $this->partidas()->sum('debe');
    }

    public function getTotalHaberAttribute()
    {
        return $this->partidas()->sum('haber');
    }

    public function estaBalanceado()
    {
        return round($this->total_debe, 2) === round($this->total_haber, 2);
    }

    public function getDiferenciaAttribute()
    {
        return abs($this->total_debe - $this->total_haber);
    }

    public function getOrigenAttribute()
    {
        if ($this->compra_id) return 'Compra';
        if ($this->venta_id) return 'Venta';
        return 'Manual';
    }

    public function getReferenciaAttribute()
    {
        if ($this->compra) return $this->compra->numero_factura;
        if ($this->venta) return $this->venta->numero_factura;
        return 'N/A';
    }

    // Scopes
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('fecha', [$startDate, $endDate]);
    }

    public function scopeByTipo($query, $tipo)
    {
        if ($tipo === 'compra') return $query->whereNotNull('compra_id');
        if ($tipo === 'venta') return $query->whereNotNull('venta_id');
        return $query;
    }

    public function scopeSearch($query, $search)
    {
        return $query->where('codigo', 'like', "%{$search}%")
                     ->orWhere('descripcion', 'like', "%{$search}%");
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($asiento) {
            if (empty($asiento->created_by) && auth()->check()) {
                $asiento->created_by = auth()->id();
            }
        });
    }
}
