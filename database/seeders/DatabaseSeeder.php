<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    private const FULL_PERMISSIONS = [
        'companias:leer',
        'companias:crear',
        'companias:actualizar',
        'companias:eliminar',
        'empleados:leer',
        'empleados:crear',
        'empleados:actualizar',
        'empleados:eliminar',
    ];

    public function run(): void
    {
        $this->call([
            CompaniaSeeder::class,
            EmpleadoSeeder::class,
        ]);

        User::updateOrCreate(
            ['email' => 'admin@api.com'],
            [
                'name' => 'Administrador',
                'password' => Hash::make(env('DEFAULT_ADMIN_PASSWORD') ?: Str::random(32)),
                'role' => 'ADMIN',
                'compania_id' => null,
                'ciudad' => null,
                'permisos' => self::FULL_PERMISSIONS,
            ]
        );

        User::updateOrCreate(
            ['email' => 'usuario@api.com'],
            [
                'name' => 'Usuario Compania 1',
                'password' => Hash::make(env('DEFAULT_USER_PASSWORD') ?: Str::random(32)),
                'role' => 'USUARIO',
                'compania_id' => 1,
                'ciudad' => null,
                'permisos' => self::FULL_PERMISSIONS,
            ]
        );

        User::updateOrCreate(
            ['email' => env('ADMIN_BOGOTA_EMAIL', 'admin.bogota@example.com')],
            [
                'name' => 'Administrador Bogota',
                'password' => Hash::make(env('ADMIN_BOGOTA_PASSWORD') ?: Str::random(32)),
                'role' => 'ADMIN',
                'compania_id' => null,
                'ciudad' => 'Bogota',
                'permisos' => [
                    'companias:leer',
                    'companias:crear',
                    'companias:actualizar',
                    'empleados:leer',
                    'empleados:crear',
                    'empleados:actualizar',
                ],
            ]
        );

        User::updateOrCreate(
            ['email' => env('ADMIN_MEDELLIN_EMAIL', 'admin.medellin@example.com')],
            [
                'name' => 'Administrador Medellin',
                'password' => Hash::make(env('ADMIN_MEDELLIN_PASSWORD') ?: Str::random(32)),
                'role' => 'ADMIN',
                'compania_id' => null,
                'ciudad' => 'Medellin',
                'permisos' => [
                    'companias:leer',
                    'companias:crear',
                    'companias:eliminar',
                    'empleados:leer',
                    'empleados:crear',
                    'empleados:eliminar',
                ],
            ]
        );
    }
}
