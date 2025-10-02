<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VentaDetalle extends Model
{
    use HasFactory;

    protected $table = 'venta_detalles';

    protected $fillable = [
        'venta_id',
        'vehiculo_id',
        'cantidad',
        'precio_unitario',
        'subtotal',
    ];

    protected $casts = [
        'cantidad' => 'integer',
        'precio_unitario' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public $timestamps = false;

    /**
     * Relaciones
     */
    public function venta()
    {
        return $this->belongsTo(Venta::class);
    }

    public function vehiculo()
    {
        return $this->belongsTo(Vehiculo::class);
    }

    /**
     * Métodos de utilidad
     */
    public function getDescripcionVehiculoAttribute()
    {
        return $this->vehiculo ? $this->vehiculo->descripcion_completa : 'Vehículo no encontrado';
    }

    public function getGananciaAttribute()
    {
        if ($this->vehiculo) {
            return ($this->precio_unitario - $this->vehiculo->precio_compra) * $this->cantidad;
        }
        return 0;
    }

    /**
     * Boot del modelo
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($detalle) {
            $detalle->subtotal = $detalle->cantidad * $detalle->precio_unitario;
        });

        static::updating(function ($detalle) {
            $detalle->subtotal = $detalle->cantidad * $detalle->precio_unitario;
        });
    }
}