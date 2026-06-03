<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CompaniaController;
use App\Http\Controllers\EmpleadoController;
use Illuminate\Support\Facades\Route;

Route::get('/openapi.yaml', function () {
    return response()->file(public_path('docs.openapi.yaml'), [
        'Content-Type' => 'application/yaml',
    ]);
});

Route::prefix('auth')->group(function () {
    Route::post('/registro', [AuthController::class, 'registro']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/perfil', [AuthController::class, 'perfil'])->middleware('jwt');
});

Route::prefix('companias')->middleware('jwt')->group(function () {
    Route::get('/', [CompaniaController::class, 'index']);
    Route::post('/', [CompaniaController::class, 'store'])->middleware('role:ADMIN,ADMIN_BOG,ADMIN_MED,USUARIO');
    Route::delete('/', [CompaniaController::class, 'destroyMany'])->middleware('role:ADMIN,ADMIN_MED');
    Route::post('/con-empleados', [CompaniaController::class, 'storeConEmpleados'])->middleware('role:ADMIN');
    Route::post('/con-empleados/async', [CompaniaController::class, 'storeConEmpleadosAsync'])->middleware('role:ADMIN');
    Route::get('/{id}', [CompaniaController::class, 'show']);
    Route::get('/{id}/empleados', [CompaniaController::class, 'empleados']);
    Route::put('/{id}', [CompaniaController::class, 'update'])->middleware('role:ADMIN,ADMIN_BOG,ADMIN_MED');
    Route::patch('/{id}', [CompaniaController::class, 'patch'])->middleware('role:ADMIN,ADMIN_BOG');
    Route::delete('/{id}', [CompaniaController::class, 'destroy'])->middleware('role:ADMIN,ADMIN_MED');
});

Route::prefix('empleados')->middleware('jwt')->group(function () {
    Route::get('/', [EmpleadoController::class, 'index']);
    Route::post('/', [EmpleadoController::class, 'store'])->middleware('role:ADMIN,ADMIN_BOG,ADMIN_MED,USUARIO');
    Route::delete('/', [EmpleadoController::class, 'destroyMany'])->middleware('role:ADMIN,ADMIN_MED');
    Route::post('/bulk', [EmpleadoController::class, 'storeBulk'])->middleware('role:ADMIN,ADMIN_BOG,ADMIN_MED,USUARIO');
    Route::post('/async', [EmpleadoController::class, 'storeAsync'])->middleware('role:ADMIN,ADMIN_BOG,ADMIN_MED,USUARIO');
    Route::get('/{id}', [EmpleadoController::class, 'show']);
    Route::put('/{id}', [EmpleadoController::class, 'update'])->middleware('role:ADMIN,ADMIN_BOG,ADMIN_MED,USUARIO');
    Route::patch('/{id}', [EmpleadoController::class, 'patch'])->middleware('role:ADMIN,ADMIN_BOG');
    Route::delete('/{id}', [EmpleadoController::class, 'destroy'])->middleware('role:ADMIN,ADMIN_MED');
});
