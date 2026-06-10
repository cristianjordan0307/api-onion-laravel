<?php

namespace App\Application\Services;

use App\Domain\Interfaces\IUsuarioRepository;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function __construct(
        private IUsuarioRepository $usuarios,
        private JwtService $jwt,
    ) {}

    public function registrar(array $data): array
    {
        $user = $this->usuarios->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'] ?? 'USUARIO',
            'compania_id' => $data['compania_id'] ?? null,
            'ciudad' => $data['ciudad'] ?? null,
            'permisos' => $data['permisos'] ?? [],
        ]);

        return $this->authResponse($user);
    }

    public function login(array $data): array
    {
        $user = $this->usuarios->findByEmail($data['email']);

        if (!$user || !Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Credenciales invalidas.'],
            ]);
        }

        return $this->authResponse($user);
    }

    public function authResponse(User $user): array
    {
        return [
            'token_type' => 'Bearer',
            'access_token' => $this->jwt->generate($user),
            'usuario' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'compania_id' => $user->compania_id,
                'ciudad' => $user->ciudad,
                'permisos' => $user->permisos ?? [],
            ],
        ];
    }
}
