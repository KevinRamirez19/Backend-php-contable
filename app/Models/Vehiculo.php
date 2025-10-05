<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vehiculo extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'vehiculos';

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

    /**
     * Constantes para estados
     */
    const ESTADO_DISPONIBLE = 'DISPONIBLE';
    const ESTADO_VENDIDO = 'VENDIDO';
    const ESTADO_MANTENIMIENTO = 'MANTENIMIENTO';

    /**
     * Relaciones
     */
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

    /**
     * Scopes
     */
    public function scopeDisponible($query)
    {
        return $query->where('estado', self::ESTADO_DISPONIBLE)
                    ->where('stock', '>', 0);
    }

    public function scopeVendido($query)
    {
        return $query->where('estado', self::ESTADO_VENDIDO);
    }

    public function scopeEnMantenimiento($query)
    {
        return $query->where('estado', self::ESTADO_MANTENIMIENTO);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where('marca', 'like', "%{$search}%")
                    ->orWhere('modelo', 'like', "%{$search}%")
                    ->orWhere('placa', 'like', "%{$search}%")
                    ->orWhere('vin', 'like', "%{$search}%");
    }

    public function scopeByMarca($query, $marca)
    {
        return $query->where('marca', $marca);
    }

    public function scopeByAño($query, $año)
    {
        return $query->where('año', $año);
    }

    /**
     * Métodos de utilidad
     */
    public function getDescripcionCompletaAttribute()
    {
        return "{$this->marca} {$this->modelo} {$this->año} - {$this->color}";
    }

    public function getMargenGananciaAttribute()
    {
        if ($this->precio_compra > 0) {
            return (($this->precio_venta - $this->precio_compra) / $this->precio_compra) * 100;
        }
        return 0;
    }

    public function getGananciaNetaAttribute()
    {
        return $this->precio_venta - $this->precio_compra;
    }

    public function tieneStock(int $cantidad = 1): bool
    {
        return $this->stock >= $cantidad && $this->estado === self::ESTADO_DISPONIBLE;
    }

    public function reducirStock($cantidad = 1)
    {
        if ($this->stock >= $cantidad) {
            $this->decrement('stock', $cantidad);
            
            if ($this->stock === 0) {
                $this->update(['estado' => self::ESTADO_VENDIDO]);
            }
            
            return true;
        }
        
        return false;
    }

    public function aumentarStock($cantidad = 1)
    {
        $this->increment('stock', $cantidad);
        
        if ($this->estado === self::ESTADO_VENDIDO && $this->stock > 0) {
            $this->update(['estado' => self::ESTADO_DISPONIBLE]);
        }
        
        return true;
    }

    public function marcarComoDisponible()
    {
        $this->update(['estado' => self::ESTADO_DISPONIBLE]);
    }

    public function marcarComoVendido()
    {
        $this->update(['estado' => self::ESTADO_VENDIDO]);
    }

    public function marcarComoMantenimiento()
    {
        $this->update(['estado' => self::ESTADO_MANTENIMIENTO]);
    }

    /**
     * Validaciones
     */
    public static function rules($id = null)
    {
        return [
            'proveedor_id' => 'required|exists:proveedores,id',
            'marca' => 'required|string|max:50',
            'modelo' => 'required|string|max:50',
            'año' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'color' => 'nullable|string|max:30',
            'placa' => 'nullable|string|max:15|unique:vehiculos,placa,' . $id,
            'vin' => 'nullable|string|max:17|unique:vehiculos,vin,' . $id,
            'precio_compra' => 'required|numeric|min:0',
            'precio_venta' => 'required|numeric|min:0',
            'estado' => 'required|in:DISPONIBLE,VENDIDO,MANTENIMIENTO',
            'stock' => 'required|integer|min:0',
        ];
    }
}