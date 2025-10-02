<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Venta extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'ventas';

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

    /**
     * Constantes para estados DIAN
     */
    const ESTADO_DIAN_PENDIENTE = 'PENDIENTE';
    const ESTADO_DIAN_ACEPTADA = 'ACEPTADA';
    const ESTADO_DIAN_RECHAZADA = 'RECHAZADA';
    const ESTADO_DIAN_ENVIADA = 'ENVIADA';

    /**
     * Relaciones
     */
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

    /**
     * Scopes
     */
    public function scopeByCliente($query, $clienteId)
    {
        return $query->where('cliente_id', $clienteId);
    }

    public function scopeByEstadoDian($query, $estado)
    {
        return $query->where('estado_dian', $estado);
    }

    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('fecha_venta', [$startDate, $endDate]);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where('numero_factura', 'like', "%{$search}%")
                    ->orWhere('cufe', 'like', "%{$search}%")
                    ->orWhereHas('cliente', function($q) use ($search) {
                        $q->where('nombre', 'like', "%{$search}%")
                          ->orWhere('numero_documento', 'like', "%{$search}%");
                    });
    }

    /**
     * Métodos de utilidad
     */
    public function generarNumeroFactura()
    {
        $year = now()->year;
        $sequence = Venta::whereYear('created_at', $year)->count() + 1;
        return "FV-{$year}-" . str_pad($sequence, 6, '0', STR_PAD_LEFT);
    }

    public function getCantidadVehiculosAttribute()
    {
        return $this->detalles()->sum('cantidad');
    }

    public function estaAceptadaDian()
    {
        return $this->estado_dian === self::ESTADO_DIAN_ACEPTADA;
    }

    public function estaRechazadaDian()
    {
        return $this->estado_dian === self::ESTADO_DIAN_RECHAZADA;
    }

    public function estaPendienteDian()
    {
        return $this->estado_dian === self::ESTADO_DIAN_PENDIENTE;
    }

    public function marcarComoAceptadaDian($cufe = null, $qrCode = null)
    {
        $this->update([
            'estado_dian' => self::ESTADO_DIAN_ACEPTADA,
            'cufe' => $cufe,
            'qr_code' => $qrCode
        ]);
    }

    public function marcarComoRechazadaDian()
    {
        $this->update(['estado_dian' => self::ESTADO_DIAN_RECHAZADA]);
    }

    public function marcarComoEnviadaDian()
    {
        $this->update(['estado_dian' => self::ESTADO_DIAN_ENVIADA]);
    }

    public function tieneAsientoContable()
    {
        return $this->asientoContable()->exists();
    }

    public function getPorcentajeIvaAttribute()
    {
        if ($this->subtotal > 0) {
            return ($this->iva / $this->subtotal) * 100;
        }
        return 0;
    }

    /**
     * Validaciones
     */
    public static function rules($id = null)
    {
        return [
            'cliente_id' => 'required|exists:clientes,id',
            'detalles' => 'required|array|min:1',
            'detalles.*.vehiculo_id' => 'required|exists:vehiculos,id',
            'detalles.*.cantidad' => 'required|integer|min:1',
        ];
    }

    /**
     * Boot del modelo
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($venta) {
            if (empty($venta->numero_factura)) {
                $venta->numero_factura = $venta->generarNumeroFactura();
            }
            
            if (empty($venta->created_by) && auth()->check()) {
                $venta->created_by = auth()->id();
            }
        });
    }
}