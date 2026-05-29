<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use App\Models\Empleado;

class EmpleadoSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        Empleado::truncate();
        Schema::enableForeignKeyConstraints();

        Empleado::insert([
            ['nombre' => 'Ana',     'apellido' => 'Gómez',    'correo' => 'ana.gomez@tech.com',        'cargo' => 'Desarrolladora', 'salario' => 3500000, 'compania_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Carlos',  'apellido' => 'Rojas',    'correo' => 'carlos.rojas@tech.com',     'cargo' => 'Tester',         'salario' => 2800000, 'compania_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Lucía',   'apellido' => 'Martínez', 'correo' => 'lucia.martinez@tech.com',   'cargo' => 'Scrum Master',   'salario' => 4200000, 'compania_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Pedro',   'apellido' => 'Sánchez',  'correo' => 'pedro.sanchez@tech.com',    'cargo' => 'DevOps',         'salario' => 4500000, 'compania_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Sofía',   'apellido' => 'Torres',   'correo' => 'sofia.torres@innova.com',   'cargo' => 'Diseñadora UI',  'salario' => 3100000, 'compania_id' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Miguel',  'apellido' => 'Herrera',  'correo' => 'miguel.herrera@innova.com', 'cargo' => 'Backend Dev',    'salario' => 3800000, 'compania_id' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Valeria', 'apellido' => 'López',    'correo' => 'valeria.lopez@innova.com',  'cargo' => 'Analista QA',    'salario' => 2900000, 'compania_id' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Andrés',  'apellido' => 'Castro',   'correo' => 'andres.castro@datacore.com','cargo' => 'Data Engineer',  'salario' => 4800000, 'compania_id' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Camila',  'apellido' => 'Vargas',   'correo' => 'camila.vargas@datacore.com','cargo' => 'DBA',            'salario' => 4300000, 'compania_id' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Felipe',  'apellido' => 'Mora',     'correo' => 'felipe.mora@datacore.com',  'cargo' => 'ML Engineer',    'salario' => 5000000, 'compania_id' => 3, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
