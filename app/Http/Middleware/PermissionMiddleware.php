<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PermissionMiddleware
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();
        $permissions = $user?->permisos ?? [];

        if (!$user || !in_array($permission, $permissions, true)) {
            return response()->json([
                'error' => 'Permiso insuficiente.',
                'permiso_requerido' => $permission,
            ], 403);
        }

        return $next($request);
    }
}
