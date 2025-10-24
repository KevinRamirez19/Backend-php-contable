<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Compra extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'compras';

    protected $fillable = [
        'proveedor_id',
        'numero_factura',
        'fecha_compra',
        'subtotal',
        'iva',
        'total',
        'estado',
    ];

    protected $casts = [
        'fecha_compra' => 'date',
        'subtotal' => 'decimal:2',
        'iva' => 'decimal:2',
        'total' => 'decimal:2',
        'deleted_at' => 'datetime',
    ];

    /**
     * Constantes para estados
     */
    const ESTADO_PENDIENTE = 'PENDIENTE';
    const ESTADO_PAGADA = 'PAGADA';
    const ESTADO_ANULADA = 'ANULADA';

    /**
     * Relaciones
     */
    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'proveedor_id');
    }

    public function detalles()
    {
        return $this->hasMany(CompraDetalle::class);
    }
    public function vehiculos()
{
    return $this->belongsToMany(Vehiculo::class, 'compra_vehiculo')
                ->withPivot('precio_unitario', 'cantidad')
                ->withTimestamps();
}

    public function asientoContable()
    {
        return $this->hasOne(AsientoContable::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scopes
     */
    public function scopeByProveedor($query, $proveedorId)
    {
        return $query->where('proveedor_id', $proveedorId);
    }

    public function scopeByEstado($query, $estado)
    {
        return $query->where('estado', $estado);
    }

    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('fecha_compra', [$startDate, $endDate]);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where('numero_factura', 'like', "%{$search}%")
                    ->orWhereHas('proveedor', function($q) use ($search) {
                        $q->where('nombre', 'like', "%{$search}%");
                    });
    }

    /**
     * Métodos de utilidad
     */
    public function getCantidadVehiculosAttribute()
    {
        return $this->detalles()->sum('cantidad');
    }

    public function estaPendiente()
    {
        return $this->estado === self::ESTADO_PENDIENTE;
    }

    public function estaPagada()
    {
        return $this->estado === self::ESTADO_PAGADA;
    }

    public function estaAnulada()
    {
        return $this->estado === self::ESTADO_ANULADA;
    }

    public function marcarComoPagada()
    {
        $this->update(['estado' => self::ESTADO_PAGADA]);
    }

    public function marcarComoAnulada()
    {
        $this->update(['estado' => self::ESTADO_ANULADA]);
    }

    public function tieneAsientoContable()
    {
        return $this->asientoContable()->exists();
    }

    /**
     * Validaciones
     */
    public static function rules($id = null)
    {
        return [
            'proveedor_id' => 'required|exists:proveedores,id',
            'numero_factura' => 'required|string|max:50|unique:compras,numero_factura,' . $id,
            'fecha_compra' => 'required|date',
            'detalles' => 'required|array|min:1',
            'detalles.*.vehiculo_id' => 'required|exists:vehiculos,id',
            'detalles.*.cantidad' => 'required|integer|min:1',
            'detalles.*.precio_unitario' => 'required|numeric|min:0',
        ];
    }

    /**
     * Boot del modelo
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($compra) {
            if (empty($compra->created_by) && auth()->check()) {
                $compra->created_by = auth()->id();
            }
        });
    }
}