<?php

namespace App\Http\Controllers;

use App\Application\Services\EmpleadoService;
use App\Application\DTOs\EmpleadoDTO;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class EmpleadoController extends Controller
{
    public function __construct(private EmpleadoService $service) {}

    public function index(): JsonResponse
    {
        try {
            $empleados = $this->service->getAll();
            return response()->json($empleados, 200);
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
        } catch (\Exception $e) {
            Log::error('[EmpleadoController] Error en show: ' . $e->getMessage());
            return response()->json(['error' => 'Error interno del servidor.'], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'nombre'      => 'required|string|max:100',
                'apellido'    => 'required|string|max:100',
                'correo'      => 'required|email|unique:empleados,correo',
                'cargo'       => 'required|string|max:100',
                'salario'     => 'required|numeric|min:0',
                'compania_id' => 'required|integer|exists:companias,id',
            ]);

            $dto = EmpleadoDTO::fromRequest($validated);
            $empleado = $this->service->create($dto);
            return response()->json($empleado, 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['error' => $e->errors()], 400);
        } catch (\Exception $e) {
            Log::error('[EmpleadoController] Error en store: ' . $e->getMessage());
            return response()->json(['error' => 'Error interno del servidor.'], 500);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'nombre'      => 'required|string|max:100',
                'apellido'    => 'required|string|max:100',
                'correo'      => 'required|email|unique:empleados,correo,' . $id,
                'cargo'       => 'required|string|max:100',
                'salario'     => 'required|numeric|min:0',
                'compania_id' => 'required|integer|exists:companias,id',
            ]);

            $dto = EmpleadoDTO::fromRequest($validated);
            $empleado = $this->service->update($id, $dto);

            if (!$empleado) {
                return response()->json(['error' => 'Empleado no encontrado.'], 404);
            }
            return response()->json($empleado, 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['error' => $e->errors()], 400);
        } catch (\Exception $e) {
            Log::error('[EmpleadoController] Error en update: ' . $e->getMessage());
            return response()->json(['error' => 'Error interno del servidor.'], 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $result = $this->service->delete($id);
            if (!$result) {
                return response()->json(['error' => 'Empleado no encontrado.'], 404);
            }
            return response()->json(null, 204);
        } catch (\Exception $e) {
            Log::error('[EmpleadoController] Error en destroy: ' . $e->getMessage());
            return response()->json(['error' => 'Error interno del servidor.'], 500);
        }
    }
}