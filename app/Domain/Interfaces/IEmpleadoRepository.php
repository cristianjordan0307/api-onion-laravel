<?php

namespace App\Domain\Interfaces;

use App\Models\Empleado;
use Illuminate\Support\Collection;

interface IEmpleadoRepository
{
    public function getAll(): Collection;
    public function getById(int $id): ?Empleado;
    public function create(array $data): Empleado;
    public function update(int $id, array $data): ?Empleado;
    public function delete(int $id): bool;
    public function findByCondition(array $conditions): Collection;
    public function getByCompania(int $companiaId): Collection;
}