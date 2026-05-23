<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Interfaces\IEmpleadoRepository;
use App\Models\Empleado;
use Illuminate\Support\Collection;

class EmpleadoRepository implements IEmpleadoRepository
{
    public function getAll(): Collection
    {
        return Empleado::with('compania')->get();
    }

    public function getById(int $id): ?Empleado
    {
        return Empleado::with('compania')->find($id);
    }

    public function create(array $data): Empleado
    {
        return Empleado::create($data);
    }

    public function update(int $id, array $data): ?Empleado
    {
        $empleado = Empleado::find($id);
        if (!$empleado) return null;
        $empleado->update($data);
        return $empleado;
    }

    public function delete(int $id): bool
    {
        $empleado = Empleado::find($id);
        if (!$empleado) return false;
        return $empleado->delete();
    }

    public function findByCondition(array $conditions): Collection
    {
        return Empleado::where($conditions)->get();
    }

    public function getByCompania(int $companiaId): Collection
    {
        return Empleado::where('compania_id', $companiaId)->get();
    }
}