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
        Log::info('[CompaniaService] Consultando todas las compañías.');
        return $this->uow->companias()->getAll()->toArray();
    }

    public function getById(int $id): ?array
    {
        Log::info("[CompaniaService] Consultando compañía ID: $id");
        $compania = $this->uow->companias()->getById($id);
        return $compania?->toArray();
    }

    public function getWithEmpleados(int $id): ?array
    {
        Log::info("[CompaniaService] Consultando compañía con empleados ID: $id");
        $compania = $this->uow->companias()->getWithEmpleados($id);
        return $compania?->toArray();
    }

    public function create(CompaniaDTO $dto): array
    {
        try {
            $this->uow->beginTransaction();
            Log::info('[CompaniaService] Creando compañía: ' . $dto->nombre);

            $compania = $this->uow->companias()->create([
                'nombre'         => $dto->nombre,
                'direccion'      => $dto->direccion,
                'telefono'       => $dto->telefono,
                'fecha_creacion' => now(),
            ]);

            $this->uow->commit();
            Log::info('[CompaniaService] Compañía creada con ID: ' . $compania->id);
            return $compania->toArray();

        } catch (\Exception $e) {
            $this->uow->rollback();
            Log::error('[CompaniaService] Error al crear compañía: ' . $e->getMessage());
            throw $e;
        }
    }

    public function update(int $id, CompaniaDTO $dto): ?array
    {
        try {
            $this->uow->beginTransaction();

            $compania = $this->uow->companias()->update($id, [
                'nombre'    => $dto->nombre,
                'direccion' => $dto->direccion,
                'telefono'  => $dto->telefono,
            ]);

            $this->uow->commit();
            Log::info("[CompaniaService] Compañía ID: $id actualizada.");
            return $compania?->toArray();

        } catch (\Exception $e) {
            $this->uow->rollback();
            Log::error('[CompaniaService] Error al actualizar: ' . $e->getMessage());
            throw $e;
        }
    }

    public function delete(int $id): bool
    {
        try {
            $this->uow->beginTransaction();
            $result = $this->uow->companias()->delete($id);
            $this->uow->commit();
            Log::info("[CompaniaService] Compañía ID: $id eliminada.");
            return $result;

        } catch (\Exception $e) {
            $this->uow->rollback();
            Log::error('[CompaniaService] Error al eliminar: ' . $e->getMessage());
            throw $e;
        }
    }

    public function createConEmpleados(CompaniaDTO $dto): array
    {
        try {
            $this->uow->beginTransaction();
            Log::info('[CompaniaService] Iniciando creación transaccional con empleados.');

            $compania = $this->uow->companias()->create([
                'nombre'         => $dto->nombre,
                'direccion'      => $dto->direccion,
                'telefono'       => $dto->telefono,
                'fecha_creacion' => now(),
            ]);

            foreach ($dto->empleados as $emp) {
                $this->uow->empleados()->create([
                    'nombre'      => $emp['nombre'],
                    'apellido'    => $emp['apellido'],
                    'correo'      => $emp['correo'],
                    'cargo'       => $emp['cargo'],
                    'salario'     => $emp['salario'],
                    'compania_id' => $compania->id,
                ]);
            }

            $this->uow->commit();
            Log::info('[CompaniaService] Transacción completada. Compañía ID: ' . $compania->id);
            return $this->uow->companias()->getWithEmpleados($compania->id)->toArray();

        } catch (\Exception $e) {
            $this->uow->rollback();
            Log::error('[CompaniaService] Rollback ejecutado: ' . $e->getMessage());
            throw $e;
        }
    }
}