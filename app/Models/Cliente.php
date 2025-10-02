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

    public function ventas()
    {
        return $this->hasMany(Venta::class);
    }

    public function getDocumentoCompletoAttribute()
    {
        return "{$this->tipo_documento} {$this->numero_documento}";
    }
}