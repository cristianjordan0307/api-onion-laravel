<?php

namespace App\Domain\Interfaces;

use App\Models\Empleado;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface IEmpleadoRepository
{
    public function getAll(): Collection;
    public function paginate(array $filters): LengthAwarePaginator;
    public function getById(int $id): ?Empleado;
    public function create(array $data): Empleado;
    public function createMany(array $items): Collection;
    public function update(int $id, array $data): ?Empleado;
    public function patch(int $id, array $data): ?Empleado;
    public function delete(int $id): bool;
    public function deleteMany(array $ids): int;
    public function findByCondition(array $conditions): Collection;
    public function getByCompania(int $companiaId): Collection;
}
