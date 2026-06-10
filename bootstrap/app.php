<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'jwt' => \App\Http\Middleware\JwtAuthMiddleware::class,
        'role' => \App\Http\Middleware\RoleMiddleware::class,
        'permission' => \App\Http\Middleware\PermissionMiddleware::class,
    ]);

    $middleware->appendToGroup('api', [
        \App\Http\Middleware\LogRequestMiddleware::class,
    ]);
})
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (\Illuminate\Validation\ValidationException $e, \Illuminate\Http\Request $request) {
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
    })->create();
