<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/openapi.yaml', function () {
    return response()->file(public_path('docs.openapi.yaml'), [
        'Content-Type' => 'application/yaml',
    ]);
});

Route::get('/swagger', function () {
    return view('swagger');
});
