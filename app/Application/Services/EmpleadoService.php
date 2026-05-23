<?php

namespace App\Application\Services;

use App\Domain\Interfaces\IUnitOfWork;
use App\Application\DTOs\EmpleadoDTO;
use Illuminate\Support\Facades\Log;

class EmpleadoService
{
    public function __construct(private IUnitOfWork $uow) {}

    public function getAll(): array
    {
        Log::info('[EmpleadoService] Consultando todos los empleados.');
        return $this->uow->empleados()->getAll()->toArray();
    }

    public function getById(int $id): ?array
    {
        Log::info("[EmpleadoService] Consultando empleado ID: $id");
        $empleado = $this->uow->empleados()->getById($id);
        return $empleado?->toArray();
    }

    public function create(EmpleadoDTO $dto): array
    {
        try {
            $this->uow->beginTransaction();
            Log::info('[EmpleadoService] Creando empleado: ' . $dto->nombre);

            $empleado = $this->uow->empleados()->create([
                'nombre'      => $dto->nombre,
                'apellido'    => $dto->apellido,
                'correo'      => $dto->correo,
                'cargo'       => $dto->cargo,
                'salario'     => $dto->salario,
                'compania_id' => $dto->compania_id,
            ]);

            $this->uow->commit();
            Log::info('[EmpleadoService] Empleado creado con ID: ' . $empleado->id);
            return $empleado->toArray();

        } catch (\Exception $e) {
            $this->uow->rollback();
            Log::error('[EmpleadoService] Error al crear empleado: ' . $e->getMessage());
            throw $e;
        }
    }

    public function update(int $id, EmpleadoDTO $dto): ?array
    {
        try {
            $this->uow->beginTransaction();

            $empleado = $this->uow->empleados()->update($id, [
                'nombre'      => $dto->nombre,
                'apellido'    => $dto->apellido,
                'correo'      => $dto->correo,
                'cargo'       => $dto->cargo,
                'salario'     => $dto->salario,
                'compania_id' => $dto->compania_id,
            ]);

            $this->uow->commit();
            Log::info("[EmpleadoService] Empleado ID: $id actualizado.");
            return $empleado?->toArray();

        } catch (\Exception $e) {
            $this->uow->rollback();
            Log::error('[EmpleadoService] Error al actualizar: ' . $e->getMessage());
            throw $e;
        }
    }

    public function delete(int $id): bool
    {
        try {
            $this->uow->beginTransaction();
            $result = $this->uow->empleados()->delete($id);
            $this->uow->commit();
            Log::info("[EmpleadoService] Empleado ID: $id eliminado.");
            return $result;

        } catch (\Exception $e) {
            $this->uow->rollback();
            Log::error('[EmpleadoService] Error al eliminar: ' . $e->getMessage());
            throw $e;
        }
    }
}