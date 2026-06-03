<?php

namespace App\Policies;

use App\Models\Compania;
use App\Models\User;

class CompaniaPolicy
{
    /**
     * ADMIN total tiene pase libre en todo.
     */
    public function before(User $user): ?bool
    {
        return $user->role === 'ADMIN' ? true : null;
    }

    /**
     * Ver listado: todos los roles autenticados pueden.
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['ADMIN_BOG', 'ADMIN_MED', 'USUARIO']);
    }

    /**
     * Ver detalle: todos los roles autenticados pueden.
     */
    public function view(User $user, Compania $compania): bool
    {
        return in_array($user->role, ['ADMIN_BOG', 'ADMIN_MED', 'USUARIO']);
    }

    /**
     * Crear: Bogotá ✅  Medellín ✅  Usuario ✅
     */
    public function create(User $user): bool
    {
        return in_array($user->role, ['ADMIN_BOG', 'ADMIN_MED', 'USUARIO']);
    }

    /**
     * Actualizar completo (PUT): Bogotá ✅  Medellín ✅
     */
    public function update(User $user, Compania $compania): bool
    {
        return in_array($user->role, ['ADMIN_BOG', 'ADMIN_MED']);
    }

    /**
     * Actualizar parcial (PATCH): Bogotá ✅  Medellín ❌
     */
    public function patch(User $user, Compania $compania): bool
    {
        return $user->role === 'ADMIN_BOG';
    }

    /**
     * Eliminar individual (DELETE): Bogotá ❌  Medellín ✅
     */
    public function delete(User $user, Compania $compania): bool
    {
        return $user->role === 'ADMIN_MED';
    }

    /**
     * Eliminar masivo: Bogotá ❌  Medellín ✅
     */
    public function deleteMany(User $user): bool
    {
        return $user->role === 'ADMIN_MED';
    }
}
