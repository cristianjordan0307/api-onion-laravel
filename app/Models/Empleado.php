<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Empleado extends Model
{
    protected $table = 'empleados';

    protected $fillable = [
        'nombre',
        'apellido',
        'correo',
        'cargo',
        'salario',
        'compania_id',
    ];

    protected $casts = [
        'salario' => 'decimal:2',
    ];

    public function compania(): BelongsTo
    {
        return $this->belongsTo(Compania::class, 'compania_id');
    }
}