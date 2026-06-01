<?php

namespace App\Http\Controllers;

use App\Application\DTOs\EmpleadoDTO;
use App\Application\Services\EmpleadoService;
use App\Jobs\CrearEmpleadoJob;
use App\Models\Empleado;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

class EmpleadoController extends Controller
{
    public function __construct(private EmpleadoService $service) {}

    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->validate([
                'pagina' => 'sometimes|integer|min:1',
                'tamano' => 'sometimes|integer|min:1|max:100',
                'orden' => 'sometimes|string|in:id,nombre,apellido,correo,cargo,salario,compania_id',
                'dir' => 'sometimes|string|in:asc,desc',
                'buscar' => 'sometimes|string|max:100',
                'compania_id' => 'sometimes|integer|exists:companias,id',
            ]);

            return response()->json($this->service->getPaginated($filters), 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('[EmpleadoController] Error en index: ' . $e->getMessage());
            return response()->json(['error' => 'Error interno del servidor.'], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $empleado = $this->service->getById($id);
            if (!$empleado) {
                return response()->json(['error' => 'Empleado no encontrado.'], 404);
            }
            return response()->json($empleado, 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('[EmpleadoController] Error en show: ' . $e->getMessage());
            return response()->json(['error' => 'Error interno del servidor.'], 500);
        }
    }

    /**
     * Crear empleado.
     *
     * @bodyParam nombre string required Nombre del empleado. Example: Ana
     * @bodyParam apellido string required Apellido del empleado. Example: Gomez
     * @bodyParam correo string required Correo unico del empleado. Example: ana.gomez@example.com
     * @bodyParam cargo string required Cargo del empleado. Example: Desarrolladora
     * @bodyParam salario number required Salario positivo. Example: 3500000
     * @bodyParam compania_id integer required ID de la compania existente. Example: 1
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate($this->rules());

            $empleado = $this->service->create(EmpleadoDTO::fromRequest($validated));
            return response()->json($empleado, 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('[EmpleadoController] Error en store: ' . $e->getMessage());
            return response()->json(['error' => 'Error interno del servidor.'], 500);
        }
    }

    /**
     * Crear empleados masivamente.
     *
     * @bodyParam empleados array required Lista de empleados a crear en una sola transaccion.
     * @bodyParam empleados[].nombre string required Nombre del empleado. Example: Ana
     * @bodyParam empleados[].apellido string required Apellido del empleado. Example: Gomez
     * @bodyParam empleados[].correo string required Correo unico del empleado. Example: ana.bulk@example.com
     * @bodyParam empleados[].cargo string required Cargo del empleado. Example: Dev
     * @bodyParam empleados[].salario number required Salario positivo. Example: 3500000
     * @bodyParam empleados[].compania_id integer required ID de la compania existente. Example: 1
     */
    public function storeBulk(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'empleados' => 'required|array|min:1',
                'empleados.*.nombre' => 'required|string|max:100',
                'empleados.*.apellido' => 'required|string|max:100',
                'empleados.*.correo' => 'required|email|distinct|unique:empleados,correo',
                'empleados.*.cargo' => 'required|string|max:100',
                'empleados.*.salario' => 'required|numeric|min:0.01',
                'empleados.*.compania_id' => 'required|integer|exists:companias,id',
            ]);

            return response()->json($this->service->createMany($validated['empleados']), 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('[EmpleadoController] Error en storeBulk: ' . $e->getMessage());
            return response()->json(['error' => 'Error interno del servidor.'], 500);
        }
    }

    /**
     * Crear empleado asincronicamente.
     *
     * @bodyParam nombre string required Nombre del empleado. Example: Laura
     * @bodyParam apellido string required Apellido del empleado. Example: Perez
     * @bodyParam correo string required Correo unico del empleado. Example: laura.async@example.com
     * @bodyParam cargo string required Cargo del empleado. Example: Dev
     * @bodyParam salario number required Salario positivo. Example: 3500000
     * @bodyParam compania_id integer required ID de la compania existente. Example: 1
     */
    public function storeAsync(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate($this->rules());

            CrearEmpleadoJob::dispatch($validated);

            return response()->json([
                'mensaje' => 'Empleado recibido y encolado para procesamiento asincrono.',
                'estado' => 'pendiente',
                'cola' => 'empleados',
            ], 202);

        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('[EmpleadoController] Error en storeAsync: ' . $e->getMessage());
            return response()->json(['error' => 'Error interno del servidor.'], 500);
        }
    }

    /**
     * Actualizar empleado completo.
     *
     * @bodyParam nombre string required Nombre del empleado. Example: Ana
     * @bodyParam apellido string required Apellido del empleado. Example: Gomez
     * @bodyParam correo string required Correo unico del empleado. Example: ana.update@example.com
     * @bodyParam cargo string required Cargo del empleado. Example: Lider tecnico
     * @bodyParam salario number required Salario positivo. Example: 4200000
     * @bodyParam compania_id integer required ID de la compania existente. Example: 1
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $empleadoModel = Empleado::find($id);
            if (!$empleadoModel) {
                return response()->json(['error' => 'Empleado no encontrado.'], 404);
            }
            Gate::authorize('update', $empleadoModel);

            $validated = $request->validate($this->rules($id));
            $empleado = $this->service->update($id, EmpleadoDTO::fromRequest($validated));

            return response()->json($empleado, 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json(['error' => 'No autorizado para modificar este empleado.'], 403);
        } catch (\Exception $e) {
            Log::error('[EmpleadoController] Error en update: ' . $e->getMessage());
            return response()->json(['error' => 'Error interno del servidor.'], 500);
        }
    }

    /**
     * Actualizar empleado parcialmente.
     *
     * En PATCH solo se envian los campos que se quieren cambiar.
     *
     * @bodyParam nombre string Nombre del empleado. Example: Ana
     * @bodyParam apellido string Apellido del empleado. Example: Gomez
     * @bodyParam correo string Correo unico del empleado. Example: ana.patch@example.com
     * @bodyParam cargo string Cargo del empleado. Example: Lider QA
     * @bodyParam salario number Salario positivo. Example: 5200000
     * @bodyParam compania_id integer ID de la compania existente. Example: 1
     */
    public function patch(Request $request, int $id): JsonResponse
    {
        try {
            $empleadoModel = Empleado::find($id);
            if (!$empleadoModel) {
                return response()->json(['error' => 'Empleado no encontrado.'], 404);
            }
            Gate::authorize('update', $empleadoModel);

            $validated = $request->validate([
                'nombre' => 'sometimes|required|string|max:100',
                'apellido' => 'sometimes|required|string|max:100',
                'correo' => 'sometimes|required|email|unique:empleados,correo,' . $id,
                'cargo' => 'sometimes|required|string|max:100',
                'salario' => 'sometimes|required|numeric|min:0.01',
                'compania_id' => 'sometimes|required|integer|exists:companias,id',
            ]);

            return response()->json($this->service->patch($id, $validated), 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json(['error' => 'No autorizado para modificar este empleado.'], 403);
        } catch (\Exception $e) {
            Log::error('[EmpleadoController] Error en patch: ' . $e->getMessage());
            return response()->json(['error' => 'Error interno del servidor.'], 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $empleadoModel = Empleado::find($id);
            if (!$empleadoModel) {
                return response()->json(['error' => 'Empleado no encontrado.'], 404);
            }
            Gate::authorize('delete', $empleadoModel);

            $this->service->delete($id);
            return response()->json(null, 204);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json(['error' => 'No autorizado para eliminar este empleado.'], 403);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('[EmpleadoController] Error en destroy: ' . $e->getMessage());
            return response()->json(['error' => 'Error interno del servidor.'], 500);
        }
    }

    /**
     * Eliminar empleados masivamente.
     *
     * @bodyParam ids integer[] required IDs de empleados existentes. Example: [1,2,3]
     */
    public function destroyMany(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'ids' => 'required|array|min:1',
                'ids.*' => 'required|integer|exists:empleados,id',
            ]);

            $deleted = $this->service->deleteMany($validated['ids']);

            return response()->json([
                'mensaje' => 'Empleados eliminados.',
                'eliminados' => $deleted,
            ], 200);
        } catch (\Exception $e) {
            Log::error('[EmpleadoController] Error en destroyMany: ' . $e->getMessage());
            return response()->json(['error' => 'Error interno del servidor.'], 500);
        }
    }

    private function rules(?int $id = null): array
    {
        return [
            'nombre' => 'required|string|max:100',
            'apellido' => 'required|string|max:100',
            'correo' => 'required|email|unique:empleados,correo' . ($id ? ',' . $id : ''),
            'cargo' => 'required|string|max:100',
            'salario' => 'required|numeric|min:0.01',
            'compania_id' => 'required|integer|exists:companias,id',
        ];
    }
}
