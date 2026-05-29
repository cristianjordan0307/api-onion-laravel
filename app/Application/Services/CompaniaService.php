<?php

namespace App\Application\Services;

use App\Domain\Interfaces\IUnitOfWork;
use App\Application\DTOs\CompaniaDTO;
use Illuminate\Support\Facades\Log;

class CompaniaService
{
    public function __construct(private IUnitOfWork $uow) {}

    public function getAll(): array
    {
        Log::info('[CompaniaService] Consultando todas las companias.');
        return $this->uow->companias()->getAll()->toArray();
    }

    public function getPaginated(array $filters): array
    {
        Log::info('[CompaniaService] Consultando companias con paginacion.');
        $page = $this->uow->companias()->paginate($filters);

        return [
            'datos' => $page->items(),
            'paginacion' => [
                'pagina_actual' => $page->currentPage(),
                'tamano' => $page->perPage(),
                'total' => $page->total(),
                'ultima_pagina' => $page->lastPage(),
            ],
        ];
    }

    public function getById(int $id): ?array
    {
        Log::info("[CompaniaService] Consultando compania ID: $id");
        $compania = $this->uow->companias()->getById($id);
        return $compania?->toArray();
    }

    public function getWithEmpleados(int $id): ?array
    {
        Log::info("[CompaniaService] Consultando compania con empleados ID: $id");
        $compania = $this->uow->companias()->getWithEmpleados($id);
        return $compania?->toArray();
    }

    public function create(CompaniaDTO $dto): array
    {
        try {
            $this->uow->beginTransaction();
            Log::info('[CompaniaService] Creando compania: ' . $dto->nombre);

            $compania = $this->uow->companias()->create([
                'nombre' => $dto->nombre,
                'direccion' => $dto->direccion,
                'telefono' => $dto->telefono,
                'fecha_creacion' => now(),
            ]);

            $this->uow->commit();
            Log::info('[CompaniaService] Compania creada con ID: ' . $compania->id);
            return $compania->toArray();

        } catch (\Exception $e) {
            $this->uow->rollback();
            Log::error('[CompaniaService] Error al crear compania: ' . $e->getMessage());
            throw $e;
        }
    }

    public function update(int $id, CompaniaDTO $dto): ?array
    {
        try {
            $this->uow->beginTransaction();

            $compania = $this->uow->companias()->update($id, [
                'nombre' => $dto->nombre,
                'direccion' => $dto->direccion,
                'telefono' => $dto->telefono,
            ]);

            $this->uow->commit();
            Log::info("[CompaniaService] Compania ID: $id actualizada.");
            return $compania?->toArray();

        } catch (\Exception $e) {
            $this->uow->rollback();
            Log::error('[CompaniaService] Error al actualizar: ' . $e->getMessage());
            throw $e;
        }
    }

    public function patch(int $id, array $data): ?array
    {
        try {
            $this->uow->beginTransaction();

            $compania = $this->uow->companias()->patch($id, $data);

            $this->uow->commit();
            Log::info("[CompaniaService] Compania ID: $id actualizada parcialmente.");
            return $compania?->toArray();

        } catch (\Exception $e) {
            $this->uow->rollback();
            Log::error('[CompaniaService] Error en patch: ' . $e->getMessage());
            throw $e;
        }
    }

    public function delete(int $id): bool
    {
        try {
            $this->uow->beginTransaction();
            $result = $this->uow->companias()->delete($id);
            $this->uow->commit();
            Log::info("[CompaniaService] Compania ID: $id eliminada.");
            return $result;

        } catch (\Exception $e) {
            $this->uow->rollback();
            Log::error('[CompaniaService] Error al eliminar: ' . $e->getMessage());
            throw $e;
        }
    }

    public function deleteMany(array $ids): int
    {
        try {
            $this->uow->beginTransaction();
            $deleted = $this->uow->companias()->deleteMany($ids);
            $this->uow->commit();
            Log::info('[CompaniaService] Companias eliminadas masivamente: ' . $deleted);
            return $deleted;

        } catch (\Exception $e) {
            $this->uow->rollback();
            Log::error('[CompaniaService] Error en eliminacion multiple: ' . $e->getMessage());
            throw $e;
        }
    }

    public function createConEmpleados(CompaniaDTO $dto): array
    {
        try {
            $this->uow->beginTransaction();
            Log::info('[CompaniaService] Iniciando creacion transaccional con empleados.');

            $compania = $this->uow->companias()->create([
                'nombre' => $dto->nombre,
                'direccion' => $dto->direccion,
                'telefono' => $dto->telefono,
                'fecha_creacion' => now(),
            ]);

            foreach ($dto->empleados as $emp) {
                $this->uow->empleados()->create([
                    'nombre' => $emp['nombre'],
                    'apellido' => $emp['apellido'],
                    'correo' => $emp['correo'],
                    'cargo' => $emp['cargo'],
                    'salario' => $emp['salario'],
                    'compania_id' => $compania->id,
                ]);
            }

            $this->uow->commit();
            Log::info('[CompaniaService] Transaccion completada. Compania ID: ' . $compania->id);
            return $this->uow->companias()->getWithEmpleados($compania->id)->toArray();

        } catch (\Exception $e) {
            $this->uow->rollback();
            Log::error('[CompaniaService] Rollback ejecutado: ' . $e->getMessage());
            throw $e;
        }
    }
}
