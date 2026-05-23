<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Compania extends Model
{
    protected $table = 'companias';

    protected $fillable = [
        'nombre',
        'direccion',
        'telefono',
        'fecha_creacion',
    ];

    protected $casts = [
        'fecha_creacion' => 'datetime',
    ];

    public function empleados(): HasMany
    {
        return $this->hasMany(Empleado::class, 'compania_id');
    }
}