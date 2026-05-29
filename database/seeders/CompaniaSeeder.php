<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use App\Models\Compania;

class CompaniaSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        Compania::truncate();
        Schema::enableForeignKeyConstraints();

        Compania::insert([
            [
                'nombre'         => 'Tech Solutions S.A.S',
                'direccion'      => 'Calle 45 # 10-20, Bogotá',
                'telefono'       => '3001234567',
                'fecha_creacion' => now(),
                'created_at'     => now(),
                'updated_at'     => now(),
            ],
            [
                'nombre'         => 'Innovasoft Ltda',
                'direccion'      => 'Carrera 15 # 80-10, Medellín',
                'telefono'       => '3109876543',
                'fecha_creacion' => now(),
                'created_at'     => now(),
                'updated_at'     => now(),
            ],
            [
                'nombre'         => 'DataCore Colombia',
                'direccion'      => 'Av. 6N # 25-30, Cali',
                'telefono'       => '3205551234',
                'fecha_creacion' => now(),
                'created_at'     => now(),
                'updated_at'     => now(),
            ],
        ]);
    }
}
