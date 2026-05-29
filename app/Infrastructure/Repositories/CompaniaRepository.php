<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Interfaces\ICompaniaRepository;
use App\Models\Compania;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class CompaniaRepository implements ICompaniaRepository
{
    public function getAll(): Collection
    {
        return Compania::all();
    }

    public function paginate(array $filters): LengthAwarePaginator
    {
        $allowedOrder = ['id', 'nombre', 'direccion', 'telefono', 'fecha_creacion'];
        $order = in_array($filters['orden'] ?? '', $allowedOrder, true) ? $filters['orden'] : 'id';
        $dir = strtolower($filters['dir'] ?? 'asc') === 'desc' ? 'desc' : 'asc';
        $page = max((int) ($filters['pagina'] ?? 1), 1);
        $size = min(max((int) ($filters['tamano'] ?? 10), 1), 100);

        return Compania::query()
            ->when($filters['buscar'] ?? null, function ($query, string $buscar) {
                $query->where('nombre', 'like', "%{$buscar}%")
                    ->orWhere('direccion', 'like', "%{$buscar}%")
                    ->orWhere('telefono', 'like', "%{$buscar}%");
            })
            ->orderBy($order, $dir)
            ->paginate($size, ['*'], 'pagina', $page);
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

    public function patch(int $id, array $data): ?Compania
    {
        $compania = Compania::find($id);
        if (!$compania) return null;
        $compania->fill($data);
        $compania->save();
        return $compania;
    }

    public function delete(int $id): bool
    {
        $compania = Compania::find($id);
        if (!$compania) return false;
        return $compania->delete();
    }

    public function deleteMany(array $ids): int
    {
        return Compania::whereIn('id', $ids)->delete();
    }

    public function getWithEmpleados(int $id): ?Compania
    {
        return Compania::with('empleados')->find($id);
    }
}
