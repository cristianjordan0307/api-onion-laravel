<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Interfaces\IEmpleadoRepository;
use App\Models\Empleado;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class EmpleadoRepository implements IEmpleadoRepository
{
    public function getAll(): Collection
    {
        return Empleado::with('compania')->get();
    }

    public function paginate(array $filters): LengthAwarePaginator
    {
        $allowedOrder = ['id', 'nombre', 'apellido', 'correo', 'cargo', 'salario', 'compania_id'];
        $order = in_array($filters['orden'] ?? '', $allowedOrder, true) ? $filters['orden'] : 'id';
        $dir = strtolower($filters['dir'] ?? 'asc') === 'desc' ? 'desc' : 'asc';
        $page = max((int) ($filters['pagina'] ?? 1), 1);
        $size = min(max((int) ($filters['tamano'] ?? 10), 1), 100);

        return Empleado::with('compania')
            ->when($filters['buscar'] ?? null, function ($query, string $buscar) {
                $query->where(function ($q) use ($buscar) {
                    $q->where('nombre', 'like', "%{$buscar}%")
                        ->orWhere('apellido', 'like', "%{$buscar}%")
                        ->orWhere('correo', 'like', "%{$buscar}%");
                });
            })
            ->when($filters['compania_id'] ?? null, fn ($query, $id) => $query->where('compania_id', $id))
            ->orderBy($order, $dir)
            ->paginate($size, ['*'], 'pagina', $page);
    }

    public function getById(int $id): ?Empleado
    {
        return Empleado::with('compania')->find($id);
    }

    public function create(array $data): Empleado
    {
        return Empleado::create($data);
    }

    public function createMany(array $items): Collection
    {
        return collect($items)->map(fn (array $data) => Empleado::create($data));
    }

    public function update(int $id, array $data): ?Empleado
    {
        $empleado = Empleado::find($id);
        if (!$empleado) return null;
        $empleado->update($data);
        return $empleado->load('compania');
    }

    public function patch(int $id, array $data): ?Empleado
    {
        $empleado = Empleado::find($id);
        if (!$empleado) return null;
        $empleado->fill($data);
        $empleado->save();
        return $empleado->load('compania');
    }

    public function delete(int $id): bool
    {
        $empleado = Empleado::find($id);
        if (!$empleado) return false;
        return $empleado->delete();
    }

    public function deleteMany(array $ids): int
    {
        return Empleado::whereIn('id', $ids)->delete();
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
