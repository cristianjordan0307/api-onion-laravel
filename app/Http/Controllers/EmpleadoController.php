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
