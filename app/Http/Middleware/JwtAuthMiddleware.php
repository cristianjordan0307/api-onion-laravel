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

    public function handle(Request $request, Closure $next): Response
    {
        $header = $request->header('Authorization', '');
        if (!str_starts_with($header, 'Bearer ')) {
            return response()->json(['error' => 'Token JWT requerido.'], 401);
        }

        try {
            $payload = $this->jwt->validate(substr($header, 7));
            $user = User::find($payload['sub'] ?? null);

            if (!$user) {
                return response()->json(['error' => 'Usuario del token no existe.'], 401);
            }

            Auth::setUser($user);
            $request->setUserResolver(fn () => $user);

            return $next($request);
        } catch (\Throwable $e) {
            return response()->json(['error' => 'Token JWT invalido o expirado.'], 401);
        }
    }
}
