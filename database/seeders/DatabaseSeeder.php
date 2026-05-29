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

        User::updateOrCreate(
            ['email' => 'admin@api.com'],
            [
                'name' => 'Administrador',
                'password' => Hash::make('Admin123'),
                'role' => 'ADMIN',
                'compania_id' => null,
            ]
        );

        User::updateOrCreate(
            ['email' => 'usuario@api.com'],
            [
                'name' => 'Usuario Compania 1',
                'password' => Hash::make('Usuario123'),
                'role' => 'USUARIO',
                'compania_id' => 1,
            ]
        );
    }
}
