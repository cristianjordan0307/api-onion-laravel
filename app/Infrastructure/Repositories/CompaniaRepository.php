<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Interfaces\ICompaniaRepository;
use App\Models\Compania;
use Illuminate\Support\Collection;

class CompaniaRepository implements ICompaniaRepository
{
    public function getAll(): Collection
    {
        return Compania::all();
    }

    public function getById(int $id): ?Compania
    {
        return Compania::find($id);
    }

    public function create(array $data): Compania
    {
        return Compania::create($data);
    }

    public function update(int $id, array $data): ?Compania
    {
        $compania = Compania::find($id);
        if (!$compania) return null;
        $compania->update($data);
        return $compania;
    }

    public function delete(int $id): bool
    {
        $compania = Compania::find($id);
        if (!$compania) return false;
        return $compania->delete();
    }

    public function getWithEmpleados(int $id): ?Compania
    {
        return Compania::with('empleados')->find($id);
    }
}