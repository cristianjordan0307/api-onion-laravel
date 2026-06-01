<?php

namespace App\Http\Controllers;

use App\Application\DTOs\CompaniaDTO;
use App\Application\Services\CompaniaService;
use App\Jobs\CrearCompaniaConEmpleadosJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CompaniaController extends Controller
{
    public function __construct(private CompaniaService $service) {}

    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->validate([
                'pagina' => 'sometimes|integer|min:1',
                'tamano' => 'sometimes|integer|min:1|max:100',
                'orden' => 'sometimes|string|in:id,nombre,direccion,telefono,fecha_creacion',
                'dir' => 'sometimes|string|in:asc,desc',
                'buscar' => 'sometimes|string|max:100',
            ]);

            return response()->json($this->service->getPaginated($filters), 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('[CompaniaController] Error en index: ' . $e->getMessage());
            return response()->json(['error' => 'Error interno del servidor.'], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $compania = $this->service->getById($id);
            if (!$compania) {
                return response()->json(['error' => 'Compania no encontrada.'], 404);
            }
            return response()->json($compania, 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('[CompaniaController] Error en show: ' . $e->getMessage());
            return response()->json(['error' => 'Error interno del servidor.'], 500);
        }
    }

    public function empleados(int $id): JsonResponse
    {
        try {
            $compania = $this->service->getWithEmpleados($id);
            if (!$compania) {
                return response()->json(['error' => 'Compania no encontrada.'], 404);
            }
            return response()->json($compania, 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('[CompaniaController] Error en empleados: ' . $e->getMessage());
            return response()->json(['error' => 'Error interno del servidor.'], 500);
        }
    }

    /**
     * Crear compania.
     *
     * @bodyParam nombre string required Nombre de la compania. Example: Tech Solutions
     * @bodyParam direccion string required Direccion de la compania. Example: Calle 1 # 2-3
     * @bodyParam telefono string required Telefono de contacto. Example: 3001234567
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate($this->rules());

            return response()->json($this->service->create(CompaniaDTO::fromRequest($validated)), 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('[CompaniaController] Error en store: ' . $e->getMessage());
            return response()->json(['error' => 'Error interno del servidor.'], 500);
        }
    }

    /**
     * Actualizar compania completa.
     *
     * @bodyParam nombre string required Nombre de la compania. Example: Tech Solutions
     * @bodyParam direccion string required Direccion de la compania. Example: Calle 1 # 2-3
     * @bodyParam telefono string required Telefono de contacto. Example: 3001234567
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validate($this->rules());
            $compania = $this->service->update($id, CompaniaDTO::fromRequest($validated));

            if (!$compania) {
                return response()->json(['error' => 'Compania no encontrada.'], 404);
            }
            return response()->json($compania, 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('[CompaniaController] Error en update: ' . $e->getMessage());
            return response()->json(['error' => 'Error interno del servidor.'], 500);
        }
    }

    /**
     * Actualizar compania parcialmente.
     *
     * En PATCH solo se envian los campos que se quieren cambiar.
     *
     * @bodyParam nombre string Nombre de la compania. Example: Tech Actualizada
     * @bodyParam direccion string Direccion de la compania. Example: Carrera 10 # 20-30
     * @bodyParam telefono string Telefono de contacto. Example: 3101234567
     */
    public function patch(Request $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'nombre' => 'sometimes|required|string|max:150',
                'direccion' => 'sometimes|required|string|max:255',
                'telefono' => 'sometimes|required|string|max:20',
            ]);

            $compania = $this->service->patch($id, $validated);

            if (!$compania) {
                return response()->json(['error' => 'Compania no encontrada.'], 404);
            }
            return response()->json($compania, 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('[CompaniaController] Error en patch: ' . $e->getMessage());
            return response()->json(['error' => 'Error interno del servidor.'], 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $result = $this->service->delete($id);
            if (!$result) {
                return response()->json(['error' => 'Compania no encontrada.'], 404);
            }
            return response()->json(null, 204);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('[CompaniaController] Error en destroy: ' . $e->getMessage());
            return response()->json(['error' => 'Error interno del servidor.'], 500);
        }
    }

    /**
     * Eliminar companias masivamente.
     *
     * @bodyParam ids integer[] required IDs de companias existentes. Example: [1,2,3]
     */
    public function destroyMany(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'ids' => 'required|array|min:1',
                'ids.*' => 'required|integer|exists:companias,id',
            ]);

            $deleted = $this->service->deleteMany($validated['ids']);

            return response()->json([
                'mensaje' => 'Companias eliminadas.',
                'eliminadas' => $deleted,
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('[CompaniaController] Error en destroyMany: ' . $e->getMessage());
            return response()->json(['error' => 'Error interno del servidor.'], 500);
        }
    }

    /**
     * Crear compania con empleados.
     *
     * Crea la compania y sus empleados dentro de una sola transaccion.
     *
     * @bodyParam nombre string required Nombre de la compania. Example: Empresa Demo
     * @bodyParam direccion string required Direccion de la compania. Example: Calle 20 # 30-40
     * @bodyParam telefono string required Telefono de contacto. Example: 3009876543
     * @bodyParam empleados array required Lista de empleados de la compania.
     * @bodyParam empleados[].nombre string required Nombre del empleado. Example: Ana
     * @bodyParam empleados[].apellido string required Apellido del empleado. Example: Gomez
     * @bodyParam empleados[].correo string required Correo unico del empleado. Example: ana.demo@example.com
     * @bodyParam empleados[].cargo string required Cargo del empleado. Example: Dev
     * @bodyParam empleados[].salario number required Salario positivo. Example: 3500000
     */
    public function storeConEmpleados(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate($this->rulesConEmpleados());

            return response()->json($this->service->createConEmpleados(CompaniaDTO::fromRequest($validated)), 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('[CompaniaController] Error en storeConEmpleados: ' . $e->getMessage());
            return response()->json(['error' => 'Error interno del servidor.'], 500);
        }
    }

    /**
     * Crear compania con empleados asincronicamente.
     *
     * Encola la creacion transaccional para que la procese un worker.
     *
     * @bodyParam nombre string required Nombre de la compania. Example: Empresa Async
     * @bodyParam direccion string required Direccion de la compania. Example: Calle 50 # 10-20
     * @bodyParam telefono string required Telefono de contacto. Example: 3005556677
     * @bodyParam empleados array required Lista de empleados de la compania.
     * @bodyParam empleados[].nombre string required Nombre del empleado. Example: Carlos
     * @bodyParam empleados[].apellido string required Apellido del empleado. Example: Rojas
     * @bodyParam empleados[].correo string required Correo unico del empleado. Example: carlos.async@example.com
     * @bodyParam empleados[].cargo string required Cargo del empleado. Example: QA
     * @bodyParam empleados[].salario number required Salario positivo. Example: 2800000
     */
    public function storeConEmpleadosAsync(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate($this->rulesConEmpleados());

            CrearCompaniaConEmpleadosJob::dispatch($validated);

            return response()->json([
                'mensaje' => 'Compania con empleados recibida y encolada para procesamiento asincrono.',
                'estado' => 'pendiente',
                'cola' => 'companias',
            ], 202);

        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('[CompaniaController] Error en storeConEmpleadosAsync: ' . $e->getMessage());
            return response()->json(['error' => 'Error interno del servidor.'], 500);
        }
    }

    private function rules(): array
    {
        return [
            'nombre' => 'required|string|max:150',
            'direccion' => 'required|string|max:255',
            'telefono' => 'required|string|max:20',
        ];
    }

    private function rulesConEmpleados(): array
    {
        return [
            'nombre' => 'required|string|max:150',
            'direccion' => 'required|string|max:255',
            'telefono' => 'required|string|max:20',
            'empleados' => 'required|array|min:1',
            'empleados.*.nombre' => 'required|string|max:100',
            'empleados.*.apellido' => 'required|string|max:100',
            'empleados.*.correo' => 'required|email|unique:empleados,correo',
            'empleados.*.cargo' => 'required|string|max:100',
            'empleados.*.salario' => 'required|numeric|min:0.01',
        ];
    }
}
