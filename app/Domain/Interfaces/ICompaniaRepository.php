<?php

namespace App\Domain\Interfaces;

use App\Models\Compania;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface ICompaniaRepository
{
    public function getAll(): Collection;
    public function paginate(array $filters): LengthAwarePaginator;
    public function getById(int $id): ?Compania;
    public function create(array $data): Compania;
    public function update(int $id, array $data): ?Compania;
    public function patch(int $id, array $data): ?Compania;
    public function delete(int $id): bool;
    public function deleteMany(array $ids): int;
    public function getWithEmpleados(int $id): ?Compania;
}
