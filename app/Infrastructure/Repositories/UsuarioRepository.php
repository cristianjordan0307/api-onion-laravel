<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Interfaces\IUsuarioRepository;
use App\Models\User;

class UsuarioRepository implements IUsuarioRepository
{
    public function create(array $data): User
    {
        return User::create($data);
    }

    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    public function findById(int $id): ?User
    {
        return User::find($id);
    }
}
