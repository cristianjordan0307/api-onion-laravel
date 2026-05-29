<?php

namespace App\Policies;

use App\Models\Empleado;
use App\Models\User;

class EmpleadoPolicy
{
    public function before(User $user): ?bool
    {
        return $user->role === 'ADMIN' ? true : null;
    }

    public function update(User $user, Empleado $empleado): bool
    {
        return (int) $user->compania_id === (int) $empleado->compania_id;
    }

    public function delete(User $user, Empleado $empleado): bool
    {
        return (int) $user->compania_id === (int) $empleado->compania_id;
    }
}
