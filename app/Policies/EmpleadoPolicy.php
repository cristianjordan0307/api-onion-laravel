<?php

namespace App\Policies;

use App\Models\Empleado;
use App\Models\User;

class EmpleadoPolicy
{
    /**
     * ADMIN total tiene pase libre en todo.
     */
    public function before(User $user): ?bool
    {
        return $user->role === 'ADMIN' ? true : null;
    }

    /**
     * Actualizar completo (PUT): Bogotá ✅  Medellín ✅
     * Además, solo puede modificar empleados de su propia compañía.
     */
    public function update(User $user, Empleado $empleado): bool
    {
        if (!in_array($user->role, ['ADMIN_BOG', 'ADMIN_MED', 'USUARIO'])) {
            return false;
        }

        return (int) $user->compania_id === (int) $empleado->compania_id;
    }

    /**
     * Actualizar parcial (PATCH): Bogotá ✅  Medellín ❌
     */
    public function patch(User $user, Empleado $empleado): bool
    {
        if ($user->role !== 'ADMIN_BOG') {
            return false;
        }

        return (int) $user->compania_id === (int) $empleado->compania_id;
    }

    /**
     * Eliminar individual (DELETE): Bogotá ❌  Medellín ✅
     */
    public function delete(User $user, Empleado $empleado): bool
    {
        if ($user->role !== 'ADMIN_MED') {
            return false;
        }

        return (int) $user->compania_id === (int) $empleado->compania_id;
    }

    /**
     * Eliminar masivo: Bogotá ❌  Medellín ✅
     */
    public function deleteMany(User $user): bool
    {
        return $user->role === 'ADMIN_MED';
    }
}
