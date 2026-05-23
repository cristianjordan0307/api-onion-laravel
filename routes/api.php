<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CompaniaController;
use App\Http\Controllers\EmpleadoController;

// Rutas de Compañías
Route::prefix('companias')->group(function () {
    Route::get('/',                    [CompaniaController::class, 'index']);
    Route::get('/{id}',                [CompaniaController::class, 'show']);
    Route::get('/{id}/empleados',      [CompaniaController::class, 'empleados']);
    Route::post('/',                   [CompaniaController::class, 'store']);
    Route::post('/con-empleados',      [CompaniaController::class, 'storeConEmpleados']);
    Route::put('/{id}',                [CompaniaController::class, 'update']);
    Route::delete('/{id}',             [CompaniaController::class, 'destroy']);
});

// Rutas de Empleados
Route::prefix('empleados')->group(function () {
    Route::get('/',        [EmpleadoController::class, 'index']);
    Route::get('/{id}',    [EmpleadoController::class, 'show']);
    Route::post('/',       [EmpleadoController::class, 'store']);
    Route::put('/{id}',    [EmpleadoController::class, 'update']);
    Route::delete('/{id}', [EmpleadoController::class, 'destroy']);
});