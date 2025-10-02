<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $table = 'usuarios';

    protected $fillable = [
        'nombre',
        'email',
        'password',
        'activo',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'activo' => 'boolean',
        'password' => 'hashed',
    ];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    /**
     * Relaciones
     */
    public function roles()
    {
        return $this->belongsToMany(Rol::class, 'usuario_rol', 'usuario_id', 'rol_id')
                    ->withTimestamps();
    }

    public function ventas()
    {
        return $this->hasMany(Venta::class, 'created_by');
    }

    public function compras()
    {
        return $this->hasMany(Compra::class, 'created_by');
    }

    public function asientosContables()
    {
        return $this->hasMany(AsientoContable::class, 'created_by');
    }

    /**
     * Métodos de utilidad
     */
    public function hasRole($role)
    {
        if (is_string($role)) {
            return $this->roles()->where('nombre', $role)->exists();
        }

        return $role->intersect($this->roles)->isNotEmpty();
    }

    public function assignRole($role)
    {
        if (is_string($role)) {
            $role = Rol::where('nombre', $role)->firstOrFail();
        }

        $this->roles()->syncWithoutDetaching($role);
    }

    public function removeRole($role)
    {
        if (is_string($role)) {
            $role = Rol::where('nombre', $role)->firstOrFail();
        }

        $this->roles()->detach($role);
    }

    public function isActive()
    {
        return $this->activo;
    }

    public function getNombreCompletoAttribute()
    {
        return $this->nombre;
    }
}