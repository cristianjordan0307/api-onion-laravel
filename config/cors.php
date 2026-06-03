<?php

/**
 * Configuración de CORS (Cross-Origin Resource Sharing).
 *
 * Controla qué orígenes externos pueden consumir esta API.
 * Ajusta 'allowed_origins' según el dominio real del frontend
 * antes de subir a producción.
 *
 * Documentación: https://github.com/fruitcake/laravel-cors
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Rutas donde se aplica CORS
    |--------------------------------------------------------------------------
    | Se aplica a todas las rutas del API (/api/*) y a la documentación.
    */
    'paths' => ['api/*', 'docs', 'docs.openapi', 'openapi.yaml'],

    /*
    |--------------------------------------------------------------------------
    | Métodos HTTP permitidos
    |--------------------------------------------------------------------------
    */
    'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],

    /*
    |--------------------------------------------------------------------------
    | Orígenes permitidos
    |--------------------------------------------------------------------------
    | Agrega aquí el dominio de tu frontend.
    | Ejemplo producción: 'https://mi-frontend.com'
    */
    'allowed_origins' => [
        'http://localhost:3000',   // React / Next.js (dev)
        'http://localhost:5173',   // Vite / Vue (dev)
        'http://localhost:4200',   // Angular (dev)
        'http://127.0.0.1:3000',
        'http://127.0.0.1:5173',
    ],

    /*
    |--------------------------------------------------------------------------
    | Patrones de origen (alternativa a lista exacta)
    |--------------------------------------------------------------------------
    */
    'allowed_origins_patterns' => [],

    /*
    |--------------------------------------------------------------------------
    | Headers permitidos en las peticiones del cliente
    |--------------------------------------------------------------------------
    */
    'allowed_headers' => ['Content-Type', 'Authorization', 'X-Requested-With', 'Accept'],

    /*
    |--------------------------------------------------------------------------
    | Headers expuestos al cliente en la respuesta
    |--------------------------------------------------------------------------
    */
    'exposed_headers' => [],

    /*
    |--------------------------------------------------------------------------
    | Tiempo de caché del preflight (segundos)
    |--------------------------------------------------------------------------
    */
    'max_age' => 0,

    /*
    |--------------------------------------------------------------------------
    | Permitir credenciales (cookies, Authorization header)
    |--------------------------------------------------------------------------
    | Debe ser true si el frontend envía el header Authorization.
    */
    'supports_credentials' => true,

];
