<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cliente extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nombre',
        'direccion',
        'tipo_documento',
        'numero_documento',
        'telefono',
        'email',
    ];

    protected $casts = [
        'deleted_at' => 'datetime',
    ];

    /**
     * Relaciones
     */
    public function ventas()
    {
        return $this->hasMany(Venta::class);
    }

    /**
     * Scopes
     */
    public function scopeSearch($query, $search)
    {
        return $query->where('nombre', 'like', "%{$search}%")
                    ->orWhere('numero_documento', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
    }

    public function scopeByTipoDocumento($query, $tipo)
    {
        return $query->where('tipo_documento', $tipo);
    }

    /**
     * Métodos de utilidad
     */
    public function getDocumentoCompletoAttribute()
    {
        return "{$this->tipo_documento} {$this->numero_documento}";
    }

    public function getVentasCountAttribute()
    {
        return $this->ventas()->count();
    }

    public function getTotalComprasAttribute()
    {
        return $this->ventas()->sum('total');
    }

    /**
     * Validaciones
     */
    public static function rules($id = null)
    {
        return [
            'nombre' => 'required|string|max:100',
            'direccion' => 'nullable|string',
            'tipo_documento' => 'required|in:CC,NIT,CE,PASAPORTE',
            'numero_documento' => 'required|string|max:20|unique:clientes,numero_documento,' . $id,
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
        ];
    }
}