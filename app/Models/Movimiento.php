<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Movimiento extends Model
{
    use HasFactory;

    protected $fillable = [
        'asiento_id',
        'cuenta_id',
        'naturaleza', // debe o haber
        'fecha',
        'descripcion',
    ];

    public function asiento()
    {
        return $this->belongsTo(AsientoContable::class);
    }

    public function cuenta()
    {
        return $this->belongsTo(Cuenta::class);
    }
}
