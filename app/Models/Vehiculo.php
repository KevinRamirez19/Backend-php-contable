<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vehiculo extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'proveedor_id',
        'marca',
        'modelo',
        'año',
        'color',
        'placa',
        'vin',
        'precio_compra',
        'precio_venta',
        'estado',
        'stock',
    ];

    protected $casts = [
        'precio_compra' => 'decimal:2',
        'precio_venta' => 'decimal:2',
        'año' => 'integer',
        'stock' => 'integer',
        'deleted_at' => 'datetime',
    ];

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class);
    }

    public function compraDetalles()
    {
        return $this->hasMany(CompraDetalle::class);
    }

    public function ventaDetalles()
    {
        return $this->hasMany(VentaDetalle::class);
    }

    public function getDescripcionCompletaAttribute()
    {
        return "{$this->marca} {$this->modelo} {$this->año} - {$this->color}";
    }

    public function scopeDisponible($query)
    {
        return $query->where('estado', 'DISPONIBLE')->where('stock', '>', 0);
    }
}