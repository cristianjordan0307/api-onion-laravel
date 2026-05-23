<?php

namespace App\Domain\Interfaces;

use App\Models\Compania;
use Illuminate\Support\Collection;

interface ICompaniaRepository
{
    public function getAll(): Collection;
    public function getById(int $id): ?Compania;
    public function create(array $data): Compania;
    public function update(int $id, array $data): ?Compania;
    public function delete(int $id): bool;
    public function getWithEmpleados(int $id): ?Compania;
}