<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CompaniaSeeder::class,
            EmpleadoSeeder::class,
        ]);

        // ── ADMIN total ────────────────────────────────────────────────────
        User::updateOrCreate(
            ['email' => 'admin@api.com'],
            [
                'name'        => 'Administrador',
                'password'    => Hash::make('Admin123'),
                'role'        => 'ADMIN',
                'compania_id' => null,
            ]
        );

        // ── ADMIN Bogotá (CRUD sin DELETE) ─────────────────────────────────
        User::updateOrCreate(
            ['email' => 'admin.bog@api.com'],
            [
                'name'        => 'Admin Bogota',
                'password'    => Hash::make('AdminBog123'),
                'role'        => 'ADMIN_BOG',
                'compania_id' => 1,
            ]
        );

        // ── ADMIN Medellín (CRUD sin PATCH) ────────────────────────────────
        User::updateOrCreate(
            ['email' => 'admin.med@api.com'],
            [
                'name'        => 'Admin Medellin',
                'password'    => Hash::make('AdminMed123'),
                'role'        => 'ADMIN_MED',
                'compania_id' => 2,
            ]
        );

        // ── USUARIO normal ─────────────────────────────────────────────────
        User::updateOrCreate(
            ['email' => 'usuario@api.com'],
            [
                'name'        => 'Usuario Compania 1',
                'password'    => Hash::make('Usuario123'),
                'role'        => 'USUARIO',
                'compania_id' => 1,
            ]
        );
    }
}
