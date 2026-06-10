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
    Route::get('/', [CompaniaController::class, 'index'])->middleware('permission:companias:leer');
    Route::post('/', [CompaniaController::class, 'store'])->middleware('role:ADMIN,USUARIO', 'permission:companias:crear');
    Route::delete('/', [CompaniaController::class, 'destroyMany'])->middleware('role:ADMIN', 'permission:companias:eliminar');
    Route::post('/con-empleados', [CompaniaController::class, 'storeConEmpleados'])->middleware('role:ADMIN', 'permission:companias:crear');
    Route::post('/con-empleados/async', [CompaniaController::class, 'storeConEmpleadosAsync'])->middleware('role:ADMIN', 'permission:companias:crear');
    Route::get('/{id}', [CompaniaController::class, 'show'])->middleware('permission:companias:leer');
    Route::get('/{id}/empleados', [CompaniaController::class, 'empleados'])->middleware('permission:companias:leer');
    Route::put('/{id}', [CompaniaController::class, 'update'])->middleware('role:ADMIN,USUARIO', 'permission:companias:actualizar');
    Route::patch('/{id}', [CompaniaController::class, 'patch'])->middleware('role:ADMIN,USUARIO', 'permission:companias:actualizar');
    Route::delete('/{id}', [CompaniaController::class, 'destroy'])->middleware('role:ADMIN', 'permission:companias:eliminar');
});

Route::prefix('empleados')->middleware('jwt')->group(function () {
    Route::get('/', [EmpleadoController::class, 'index'])->middleware('permission:empleados:leer');
    Route::post('/', [EmpleadoController::class, 'store'])->middleware('role:ADMIN,USUARIO', 'permission:empleados:crear');
    Route::delete('/', [EmpleadoController::class, 'destroyMany'])->middleware('role:ADMIN', 'permission:empleados:eliminar');
    Route::post('/bulk', [EmpleadoController::class, 'storeBulk'])->middleware('role:ADMIN,USUARIO', 'permission:empleados:crear');
    Route::post('/async', [EmpleadoController::class, 'storeAsync'])->middleware('role:ADMIN,USUARIO', 'permission:empleados:crear');
    Route::get('/{id}', [EmpleadoController::class, 'show'])->middleware('permission:empleados:leer');
    Route::put('/{id}', [EmpleadoController::class, 'update'])->middleware('role:ADMIN,USUARIO', 'permission:empleados:actualizar');
    Route::patch('/{id}', [EmpleadoController::class, 'patch'])->middleware('role:ADMIN,USUARIO', 'permission:empleados:actualizar');
    Route::delete('/{id}', [EmpleadoController::class, 'destroy'])->middleware('role:ADMIN,USUARIO', 'permission:empleados:eliminar');
});
