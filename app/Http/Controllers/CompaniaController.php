<?php

namespace App\Http\Controllers;

use App\Application\Services\CompaniaService;
use App\Application\DTOs\CompaniaDTO;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class CompaniaController extends Controller
{
    public function __construct(private CompaniaService $service) {}

    public function index(): JsonResponse
    {
        try {
            $companias = $this->service->getAll();
            return response()->json($companias, 200);
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
                return response()->json(['error' => 'Compañía no encontrada.'], 404);
            }
            return response()->json($compania, 200);
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
                return response()->json(['error' => 'Compañía no encontrada.'], 404);
            }
            return response()->json($compania, 200);
        } catch (\Exception $e) {
            Log::error('[CompaniaController] Error en empleados: ' . $e->getMessage());
            return response()->json(['error' => 'Error interno del servidor.'], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'nombre'    => 'required|string|max:150',
                'direccion' => 'required|string|max:255',
                'telefono'  => 'required|string|max:20',
            ]);

            $dto = CompaniaDTO::fromRequest($validated);
            $compania = $this->service->create($dto);
            return response()->json($compania, 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['error' => $e->errors()], 400);
        } catch (\Exception $e) {
            Log::error('[CompaniaController] Error en store: ' . $e->getMessage());
            return response()->json(['error' => 'Error interno del servidor.'], 500);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'nombre'    => 'required|string|max:150',
                'direccion' => 'required|string|max:255',
                'telefono'  => 'required|string|max:20',
            ]);

            $dto = CompaniaDTO::fromRequest($validated);
            $compania = $this->service->update($id, $dto);

            if (!$compania) {
                return response()->json(['error' => 'Compañía no encontrada.'], 404);
            }
            return response()->json($compania, 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['error' => $e->errors()], 400);
        } catch (\Exception $e) {
            Log::error('[CompaniaController] Error en update: ' . $e->getMessage());
            return response()->json(['error' => 'Error interno del servidor.'], 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $result = $this->service->delete($id);
            if (!$result) {
                return response()->json(['error' => 'Compañía no encontrada.'], 404);
            }
            return response()->json(null, 204);
        } catch (\Exception $e) {
            Log::error('[CompaniaController] Error en destroy: ' . $e->getMessage());
            return response()->json(['error' => 'Error interno del servidor.'], 500);
        }
    }

    public function storeConEmpleados(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'nombre'              => 'required|string|max:150',
                'direccion'           => 'required|string|max:255',
                'telefono'            => 'required|string|max:20',
                'empleados'           => 'required|array|min:1',
                'empleados.*.nombre'  => 'required|string|max:100',
                'empleados.*.apellido'=> 'required|string|max:100',
                'empleados.*.correo'  => 'required|email|unique:empleados,correo',
                'empleados.*.cargo'   => 'required|string|max:100',
                'empleados.*.salario' => 'required|numeric|min:0',
            ]);

            $dto = CompaniaDTO::fromRequest($validated);
            $resultado = $this->service->createConEmpleados($dto);
            return response()->json($resultado, 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['error' => $e->errors()], 400);
        } catch (\Exception $e) {
            Log::error('[CompaniaController] Error en storeConEmpleados: ' . $e->getMessage());
            return response()->json(['error' => 'Error interno del servidor.'], 500);
        }
    }
}