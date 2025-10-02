<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rol extends Model
{
    use HasFactory;

    protected $table = 'roles';

    protected $fillable = [
        'nombre',
        'descripcion',
    ];

    public $timestamps = true;

    /**
     * Relaciones
     */
    public function usuarios()
    {
        return $this->belongsToMany(User::class, 'usuario_rol', 'rol_id', 'usuario_id')
                    ->withTimestamps();
    }

    /**
     * Scope para búsquedas
     */
    public function scopeByName($query, $name)
    {
        return $query->where('nombre', $name);
    }

    /**
     * Métodos de utilidad
     */
    public function getCantidadUsuariosAttribute()
    {
        return $this->usuarios()->count();
    }

    public function canBeDeleted()
    {
        return $this->usuarios()->count() === 0;
    }
}