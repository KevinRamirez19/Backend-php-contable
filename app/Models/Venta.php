<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Venta extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'cliente_id',
        'numero_factura',
        'fecha_venta',
        'subtotal',
        'iva',
        'total',
        'estado_dian',
        'cufe',
        'qr_code',
        'created_by'
    ];

    protected $casts = [
        'fecha_venta' => 'date',
        'subtotal' => 'decimal:2',
        'iva' => 'decimal:2',
        'total' => 'decimal:2',
        'deleted_at' => 'datetime',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function detalles()
    {
        return $this->hasMany(VentaDetalle::class);
    }

    public function asientoContable()
    {
        return $this->hasOne(AsientoContable::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function generarNumeroFactura()
    {
        $year = now()->year;
        $sequence = Venta::whereYear('created_at', $year)->count() + 1;
        return "FV-{$year}-" . str_pad($sequence, 6, '0', STR_PAD_LEFT);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($venta) {
            if (empty($venta->numero_factura)) {
                $venta->numero_factura = $venta->generarNumeroFactura();
            }
        });
    }
}