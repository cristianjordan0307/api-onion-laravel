<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Compania;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Solo corre si la tabla está vacía
        if (Compania::count() === 0) {
            $this->call([
                CompaniaSeeder::class,
                EmpleadoSeeder::class,
            ]);
        }
    }
}