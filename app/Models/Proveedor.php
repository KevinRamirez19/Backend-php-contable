<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Proveedor extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'proveedores';

    protected $fillable = [
        'nombre',
        'nit',
        'direccion',
        'telefono',
        'email',
    ];

    protected $casts = [
        'deleted_at' => 'datetime',
    ];

    /**
     * Relaciones
     */
    public function vehiculos()
    {
        return $this->hasMany(Vehiculo::class);
    }

    public function compras()
    {
        return $this->hasMany(Compra::class);
    }

    /**
     * Scopes
     */
    public function scopeSearch($query, $search)
    {
        return $query->where('nombre', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
    }

    public function scopeActive($query)
    {
        return $query->whereNull('deleted_at');
    }

    /**
     * Métodos de utilidad
     */
    public function getVehiculosCountAttribute()
    {
        return $this->vehiculos()->count();
    }

    public function getComprasCountAttribute()
    {
        return $this->compras()->count();
    }

    public function getTotalComprasAttribute()
    {
        return $this->compras()->sum('total');
    }

    public function isUsed()
    {
        return $this->vehiculos()->exists() || $this->compras()->exists();
    }

    /**
     * Validaciones
     */
    public static function rules($id = null)
    {
        return [
            'nombre' => 'required|string|max:100',
            'nit' => 'required|string|max:50|unique:proveedores' . ($id ? ",nit,$id" : ''),
            'direccion' => 'nullable|string',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
        ];
    }
}