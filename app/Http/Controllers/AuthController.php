<?php

namespace App\Http\Controllers;

use App\Application\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(private AuthService $service) {}

    public function registro(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role' => 'sometimes|string|in:ADMIN,USUARIO',
            'compania_id' => 'nullable|integer|exists:companias,id',
        ]);

        return response()->json($this->service->registrar($validated), 201);
    }

    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        return response()->json($this->service->login($validated), 200);
    }

    public function perfil(Request $request): JsonResponse
    {
        return response()->json($request->user(), 200);
    }
}
