# API de Companias y Empleados - Parte II

API REST en PHP/Laravel con Onion Architecture, Repository Pattern, Unit of Work, Jobs asincronos, validaciones, pruebas y seguridad JWT por roles y policies.

## Arquitectura

```
Controller / Middleware
        ->
Application Services + DTOs
        ->
Unit of Work
        ->
Domain Interfaces
        ->
Infrastructure Repositories
        ->
Eloquent ORM / DB
```

Los controladores no acceden directamente a repositorios ni a Eloquent para guardar datos. Las transacciones se controlan desde los servicios mediante `UnitOfWork`.

## Instalacion y ejecucion

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

Worker para Jobs asincronos:

```bash
php artisan queue:work --queue=companias,empleados,default --tries=3
```

Documentacion Scribe:

```bash
php artisan scribe:generate
```

Abrir:

```text
http://127.0.0.1:8000/docs
```

## Variables de entorno

```env
APP_URL=http://127.0.0.1:8000
JWT_SECRET=clave_larga_para_firmar_tokens
QUEUE_CONNECTION=database
```

Si `JWT_SECRET` queda vacio, el sistema usa `APP_KEY` como respaldo.

## Usuarios de prueba

Despues de `php artisan migrate --seed`:

| Rol | Email | Password |
|---|---|---|
| ADMIN | `admin@api.com` | `Admin123` |
| USUARIO | `usuario@api.com` | `Usuario123` |

## Seguridad JWT

### Registro

```http
POST /api/auth/registro
```

```json
{
  "name": "Usuario Dos",
  "email": "usuario2@api.com",
  "password": "Usuario123",
  "role": "USUARIO",
  "compania_id": 1
}
```

### Login

```http
POST /api/auth/login
```

```json
{
  "email": "admin@api.com",
  "password": "Admin123"
}
```

La respuesta devuelve:

```json
{
  "token_type": "Bearer",
  "access_token": "eyJ...",
  "usuario": {
    "role": "ADMIN"
  }
}
```

Para probar endpoints protegidos:

```http
Authorization: Bearer TOKEN_JWT
Accept: application/json
Content-Type: application/json
```

## Modulo 1 - CRUD de colecciones

### Listado paginado, filtrado y ordenado

```http
GET /api/empleados?pagina=1&tamano=10&orden=apellido&dir=asc&buscar=gomez
```

Respuesta:

```json
{
  "datos": [],
  "paginacion": {
    "pagina_actual": 1,
    "tamano": 10,
    "total": 0,
    "ultima_pagina": 1
  }
}
```

Tambien aplica para:

```http
GET /api/companias?pagina=1&tamano=10&orden=nombre&dir=asc&buscar=tech
```

### Creacion masiva de empleados

```http
POST /api/empleados/bulk
```

```json
{
  "empleados": [
    {
      "nombre": "Ana",
      "apellido": "Gomez",
      "correo": "ana.bulk@test.com",
      "cargo": "Dev",
      "salario": 3500000,
      "compania_id": 1
    },
    {
      "nombre": "Carlos",
      "apellido": "Rojas",
      "correo": "carlos.bulk@test.com",
      "cargo": "QA",
      "salario": 2800000,
      "compania_id": 1
    }
  ]
}
```

### PATCH parcial

```http
PATCH /api/empleados/1
```

```json
{
  "cargo": "Lider tecnico",
  "salario": 5200000
}
```

### Eliminacion multiple

```http
DELETE /api/empleados
```

```json
{
  "ids": [4, 5]
}
```

Tambien existe:

```http
DELETE /api/companias
```

## Modulo 2 - Programacion asincrona

Laravel con Eloquent trabaja normalmente de forma sincronica. La alternativa idiomatica para asincronia en Laravel son Jobs y Queues.

Flujo sincronico:

```text
Controller -> Service -> UnitOfWork -> Repository -> DB -> Respuesta 201
```

Flujo asincrono:

```text
Controller -> Queue -> Respuesta 202
Worker -> Job -> Service -> UnitOfWork -> Repository -> DB
```

Endpoints:

```http
POST /api/empleados/async
POST /api/companias/con-empleados/async
```

`/async` responde `202 Accepted` y el worker procesa despues.

## Modulo 3 - Validaciones

Se usan validaciones nativas de Laravel con `Request::validate()` antes de entrar al Service. Los errores se centralizan en `bootstrap/app.php` y devuelven formato uniforme:

```json
{
  "mensaje": "Error de validacion",
  "errores": [
    {
      "campo": "correo",
      "detalle": "The correo field must be a valid email address."
    }
  ]
}
```

Reglas principales:

| Campo | Reglas |
|---|---|
| nombre | requerido, string, longitud maxima |
| correo | requerido, email, unico |
| salario | requerido, numerico, mayor a 0 |
| compania_id | existe en `companias` |

## Modulo 4 - Pruebas

Comando:

```bash
php artisan test
```

Se agregaron pruebas en `tests/Feature/ParteIIApiTest.php` para:

- Login JWT.
- Bulk insert.
- Listado paginado.
- Policy de propiedad.
- Rollback del endpoint transaccional.

Nota local: este PHP no tiene `pdo_sqlite` habilitado; por eso las pruebas de integracion quedan marcadas como skipped en esta maquina. Al habilitar `pdo_sqlite`, se ejecutan con SQLite en memoria segun `phpunit.xml`.

## Modulo 5 - JWT por roles

Roles:

| Operacion | Rol |
|---|---|
| GET | ADMIN o USUARIO autenticado |
| POST / PUT / PATCH | ADMIN o USUARIO |
| DELETE companias | ADMIN |
| DELETE empleados | ADMIN o propietario por policy |
| POST /api/companias/con-empleados | ADMIN |

El JWT contiene claims:

```json
{
  "sub": 1,
  "correo": "admin@api.com",
  "rol": "ADMIN",
  "compania_id": null,
  "exp": 9999999999
}
```

## Modulo 6 - Policies

Se implemento `EmpleadoPolicy`.

Regla:

- ADMIN puede actualizar y eliminar cualquier empleado.
- USUARIO solo puede actualizar o eliminar empleados cuya `compania_id` coincida con la `compania_id` de su token.

Prueba manual:

1. Crear usuario `USUARIO` con `compania_id = 1`.
2. Hacer login.
3. Intentar `PATCH /api/empleados/{id}` de un empleado de compania 1: debe responder 200.
4. Intentar con empleado de otra compania: debe responder 403.

## Comparacion con ASP.NET Core

| ASP.NET Core | Laravel |
|---|---|
| Controllers | Controllers |
| Services | Application Services |
| Repository interfaces | Domain Interfaces |
| EF DbContext transaction | UnitOfWork con DB transactions |
| `async/await` + `Task<T>` | Jobs y Queues |
| DataAnnotations / FluentValidation | `Request::validate()` |
| xUnit / NUnit | PHPUnit |
| AddJwtBearer | Middleware JWT propio |
| `[Authorize(Roles="ADMIN")]` | Middleware `role:ADMIN` |
| `[Authorize(Policy="...")]` | `EmpleadoPolicy` + Gate |
| ClaimsPrincipal | Usuario resuelto desde JWT |

## Endpoints principales

```text
POST   /api/auth/registro
POST   /api/auth/login
GET    /api/auth/perfil

GET    /api/companias
POST   /api/companias
PATCH  /api/companias/{id}
DELETE /api/companias
DELETE /api/companias/{id}
POST   /api/companias/con-empleados
POST   /api/companias/con-empleados/async

GET    /api/empleados
POST   /api/empleados
POST   /api/empleados/bulk
POST   /api/empleados/async
PATCH  /api/empleados/{id}
DELETE /api/empleados
DELETE /api/empleados/{id}
```

## Conclusiones

- La API ahora opera sobre objetos y colecciones.
- La asincronia se resolvio con Jobs y Queues, el mecanismo natural de Laravel.
- Las transacciones siguen centralizadas en Unit of Work.
- JWT protege los endpoints y transporta claims de rol y compania.
- Las policies permiten reglas mas finas que los roles.
