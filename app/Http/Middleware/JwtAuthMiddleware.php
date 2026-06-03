<?php

namespace App\Http\Middleware;

use App\Application\Services\JwtService;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class JwtAuthMiddleware
{
    public function __construct(private JwtService $jwt) {}

    /**
     * Valida el token JWT del header Authorization.
     *
     * En lugar de consultar la base de datos, construye el objeto User
     * directamente desde los claims del token (sub, name, email, role,
     * compania_id). Esto elimina una query por request y aprovecha la
     * información que el JwtService ya embebió al momento del login.
     *
     * Si se necesitara el User con todos sus atributos de Eloquent
     * (relaciones, etc.), descomentar la línea con User::find().
     */
    public function handle(Request $request, Closure $next): Response
    {
        $header = $request->header('Authorization', '');

        if (!str_starts_with($header, 'Bearer ')) {
            return response()->json(['error' => 'Token JWT requerido.'], 401);
        }

        try {
            $payload = $this->jwt->validate(substr($header, 7));

            // Construimos el User desde los claims del token (sin query a la BD).
            $user = new User([
                'name'        => $payload['name']        ?? '',
                'email'       => $payload['email']       ?? '',
                'role'        => $payload['role']        ?? '',
                'compania_id' => $payload['compania_id'] ?? null,
            ]);
            // Asignamos el ID manualmente porque está en $hidden / cast especial.
            $user->id = (int) $payload['sub'];

            Auth::setUser($user);
            $request->setUserResolver(fn () => $user);

            // Exponemos los permisos del claim para que el frontend o
            // middleware adicional los pueda leer si lo necesita.
            $request->attributes->set('jwt_permissions', $payload['permissions'] ?? []);

            return $next($request);
        } catch (\Throwable $e) {
            return response()->json(['error' => 'Token JWT invalido o expirado.'], 401);
        }
    }
}
