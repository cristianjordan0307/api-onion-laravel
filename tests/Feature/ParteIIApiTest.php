<?php

namespace Tests\Feature;

use App\Application\Services\JwtService;
use App\Models\Compania;
use App\Models\Empleado;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ParteIIApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        if (!in_array('sqlite', \PDO::getAvailableDrivers(), true)) {
            $this->markTestSkipped('La extension pdo_sqlite no esta habilitada en este PHP.');
        }

        parent::setUp();
    }

    public function test_login_devuelve_jwt(): void
    {
        User::create([
            'name' => 'Admin',
            'email' => 'admin@test.com',
            'password' => Hash::make('secret123'),
            'role' => 'ADMIN',
        ]);

        $this->postJson('/api/auth/login', [
            'email' => 'admin@test.com',
            'password' => 'secret123',
        ])
            ->assertOk()
            ->assertJsonStructure(['token_type', 'access_token', 'usuario']);
    }

    public function test_listado_paginado_y_bulk_de_empleados(): void
    {
        $compania = Compania::create([
            'nombre' => 'Tech',
            'direccion' => 'Calle 1',
            'telefono' => '300',
            'fecha_creacion' => now(),
        ]);

        $token = $this->token('ADMIN');

        $this->withToken($token)->postJson('/api/empleados/bulk', [
            'empleados' => [
                [
                    'nombre' => 'Ana',
                    'apellido' => 'Gomez',
                    'correo' => 'ana@test.com',
                    'cargo' => 'Dev',
                    'salario' => 1000,
                    'compania_id' => $compania->id,
                ],
                [
                    'nombre' => 'Luis',
                    'apellido' => 'Rojas',
                    'correo' => 'luis@test.com',
                    'cargo' => 'QA',
                    'salario' => 1200,
                    'compania_id' => $compania->id,
                ],
            ],
        ])->assertCreated();

        $this->withToken($token)
            ->getJson('/api/empleados?pagina=1&tamano=1&orden=apellido&dir=asc&buscar=ana')
            ->assertOk()
            ->assertJsonStructure(['datos', 'paginacion']);
    }

    public function test_policy_de_propiedad_en_patch_de_empleado(): void
    {
        $companiaUno = Compania::create(['nombre' => 'Uno', 'direccion' => 'A', 'telefono' => '1', 'fecha_creacion' => now()]);
        $companiaDos = Compania::create(['nombre' => 'Dos', 'direccion' => 'B', 'telefono' => '2', 'fecha_creacion' => now()]);
        $empleado = Empleado::create([
            'nombre' => 'Ana',
            'apellido' => 'Gomez',
            'correo' => 'ana@test.com',
            'cargo' => 'Dev',
            'salario' => 1000,
            'compania_id' => $companiaUno->id,
        ]);

        $tokenOtraCompania = $this->token('USUARIO', $companiaDos->id);

        $this->withToken($tokenOtraCompania)
            ->patchJson("/api/empleados/{$empleado->id}", ['cargo' => 'Lead'])
            ->assertForbidden();

        $tokenPropietario = $this->token('USUARIO', $companiaUno->id);

        $this->withToken($tokenPropietario)
            ->patchJson("/api/empleados/{$empleado->id}", ['cargo' => 'Lead'])
            ->assertOk()
            ->assertJsonPath('cargo', 'Lead');
    }

    public function test_rollback_en_creacion_transaccional_con_empleados(): void
    {
        $token = $this->token('ADMIN');

        $this->withToken($token)->postJson('/api/companias/con-empleados', [
            'nombre' => 'Rollback SAS',
            'direccion' => 'Calle 99',
            'telefono' => '300999',
            'empleados' => [
                [
                    'nombre' => 'A',
                    'apellido' => 'Uno',
                    'correo' => 'duplicado@test.com',
                    'cargo' => 'Dev',
                    'salario' => 1000,
                ],
                [
                    'nombre' => 'B',
                    'apellido' => 'Dos',
                    'correo' => 'duplicado@test.com',
                    'cargo' => 'QA',
                    'salario' => 1200,
                ],
            ],
        ])->assertStatus(500);

        $this->assertDatabaseMissing('companias', ['nombre' => 'Rollback SAS']);
        $this->assertDatabaseMissing('empleados', ['correo' => 'duplicado@test.com']);
    }

    private function token(string $role, ?int $companiaId = null): string
    {
        $user = User::create([
            'name' => $role . uniqid(),
            'email' => strtolower($role) . uniqid() . '@test.com',
            'password' => Hash::make('secret123'),
            'role' => $role,
            'compania_id' => $companiaId,
            'permisos' => [
                'companias:leer',
                'companias:crear',
                'companias:actualizar',
                'companias:eliminar',
                'empleados:leer',
                'empleados:crear',
                'empleados:actualizar',
                'empleados:eliminar',
            ],
        ]);

        return app(JwtService::class)->generate($user);
    }
}
