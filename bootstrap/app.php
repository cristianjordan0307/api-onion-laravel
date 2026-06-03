<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'jwt'  => \App\Http\Middleware\JwtAuthMiddleware::class,
        'role' => \App\Http\Middleware\RoleMiddleware::class,
    ]);

    $middleware->appendToGroup('api', [
        \App\Http\Middleware\LogRequestMiddleware::class,
        \Illuminate\Http\Middleware\HandleCors::class,
    ]);
})
    ->withExceptions(function (Exceptions $exceptions) {
        // ── Errores de validación → JSON uniforme ───────────────────────────
        $exceptions->render(function (\Illuminate\Validation\ValidationException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'mensaje' => 'Error de validacion',
                    'errores' => collect($e->errors())->flatMap(
                        fn (array $messages, string $field) => collect($messages)->map(
                            fn (string $message) => ['campo' => $field, 'detalle' => $message]
                        )
                    )->values(),
                ], 422);
            }
        });

        // ── Ruta no encontrada → JSON en lugar de HTML ──────────────────────
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json(['error' => 'Ruta no encontrada.'], 404);
            }
        });

        // ── Método no permitido → JSON ───────────────────────────────────────
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json(['error' => 'Metodo HTTP no permitido.'], 405);
            }
        });
    })->create();
