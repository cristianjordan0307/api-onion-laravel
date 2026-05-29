<?php

namespace App\Domain\Interfaces;

use App\Models\User;

interface IUsuarioRepository
{
    public function create(array $data): User;
    public function findByEmail(string $email): ?User;
    public function findById(int $id): ?User;
}
