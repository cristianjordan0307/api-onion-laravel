# API REST de Companias y Empleados

API REST construida con Laravel aplicando Onion Architecture, Repository Pattern, Unit of Work, Jobs/Queues, validaciones, pruebas automatizadas y seguridad con JWT, roles y policies.

## Caracteristicas

- CRUD de companias y empleados.
- Operaciones sobre colecciones: paginacion, filtrado, ordenamiento, bulk insert, PATCH y eliminacion multiple.
- Procesamiento asincrono con Jobs y Queues de Laravel.
- Validaciones de entrada con respuestas JSON uniformes.
- Autenticacion JWT con Bearer Token.
- Autorizacion por roles (`ADMIN`, `USUARIO`).
- Autorizacion por policies para reglas de propiedad.
- Documentacion generada con Scribe.
- Pruebas con PHPUnit.

## Arquitectura

El proyecto conserva el flujo por capas:

```text
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
Eloquent ORM / Database
```

Los controladores no ejecutan consultas de negocio directamente contra Eloquent. Las transacciones se coordinan desde los servicios mediante `UnitOfWork`.

## Estructura principal

```text
app/
  Application/
    DTOs/
    Services/
  Domain/
    Interfaces/
  Http/
    Controllers/
    Middleware/
  Infrastructure/
    Repositories/
    UnitOfWork/
  Jobs/
  Policies/
database/
  migrations/
  seeders/
routes/
  api.php
tests/
```

## Requisitos

- PHP compatible con la version definida en `composer.json`.
- Composer.
- Base de datos soportada por Laravel.
- Extensiones PHP necesarias para el driver de base de datos usado.

## Instalacion local

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

Para ejecutar el worker de colas:

```bash
php artisan queue:work --queue=companias,empleados,default --tries=3
```

## Variables de entorno

Configura las variables sensibles solo en `.env` o en el panel del proveedor de despliegue. No las publiques en el repositorio.

Variables relevantes:

```env
APP_URL=
JWT_SECRET=
QUEUE_CONNECTION=database
DB_CONNECTION=
DB_HOST=
DB_PORT=
DB_DATABASE=
DB_USERNAME=
DB_PASSWORD=
SCRIBE_BASE_URL=
```

Buenas practicas:

- Usa una clave larga y privada para `JWT_SECRET`.
- No subas archivos `.env`.
- En produccion, define `APP_URL` y `SCRIBE_BASE_URL` con la URL publica de la API.
- Usa credenciales diferentes para desarrollo, pruebas y produccion.

## Documentacion API

La documentacion se genera con Scribe:

```bash
php artisan scribe:generate
```

Ruta:

```text
/docs
```

Tambien se generan:

```text
/docs.openapi
/docs.postman
```

Para probar endpoints protegidos desde la documentacion, primero inicia sesion en `/api/auth/login`, copia el token y usa:

```http
Authorization: Bearer <TOKEN>
```

## Autenticacion

### Registro

```http
POST /api/auth/registro
```

Body:

```json
{
  "name": "Nombre del usuario",
  "email": "usuario@example.com",
  "password": "contrasena-segura",
  "role": "USUARIO",
  "compania_id": 1
}
```

### Login

```http
POST /api/auth/login
```

Body:

```json
{
  "email": "usuario@example.com",
  "password": "contrasena-segura"
}
```

Respuesta:

```json
{
  "token_type": "Bearer",
  "access_token": "<TOKEN>",
  "usuario": {
    "id": 1,
    "name": "Nombre del usuario",
    "email": "usuario@example.com",
    "role": "USUARIO",
    "compania_id": 1
  }
}
```

## Roles y autorizacion

| Operacion | Autorizacion |
|---|---|
| GET de recursos | Usuario autenticado |
| POST / PUT / PATCH | `ADMIN` o `USUARIO` |
| DELETE de companias | Solo `ADMIN` |
| Creacion transaccional de compania con empleados | Solo `ADMIN` |
| Actualizar o eliminar empleados | `ADMIN` o usuario propietario segun policy |

La policy `EmpleadoPolicy` permite que un usuario de rol `USUARIO` modifique empleados solo si pertenecen a su misma compania.

## Endpoints principales

### Auth

```text
POST /api/auth/registro
POST /api/auth/login
GET  /api/auth/perfil
```

### Companias

```text
GET    /api/companias
POST   /api/companias
DELETE /api/companias
GET    /api/companias/{id}
PUT    /api/companias/{id}
PATCH  /api/companias/{id}
DELETE /api/companias/{id}
GET    /api/companias/{id}/empleados
POST   /api/companias/con-empleados
POST   /api/companias/con-empleados/async
```

### Empleados

```text
GET    /api/empleados
POST   /api/empleados
POST   /api/empleados/bulk
POST   /api/empleados/async
DELETE /api/empleados
GET    /api/empleados/{id}
PUT    /api/empleados/{id}
PATCH  /api/empleados/{id}
DELETE /api/empleados/{id}
```

## Colecciones

### Paginacion, filtrado y ordenamiento

Ejemplo:

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

### Creacion masiva

```http
POST /api/empleados/bulk
```

```json
{
  "empleados": [
    {
      "nombre": "Ana",
      "apellido": "Gomez",
      "correo": "ana@example.com",
      "cargo": "Dev",
      "salario": 3500000,
      "compania_id": 1
    }
  ]
}
```

### Actualizacion parcial

```http
PATCH /api/empleados/{id}
```

```json
{
  "cargo": "Lider tecnico"
}
```

### Eliminacion multiple

```http
DELETE /api/empleados
```

```json
{
  "ids": [1, 2, 3]
}
```

## Programacion asincrona

Laravel con Eloquent trabaja normalmente de forma sincronica. Para procesamiento asincrono se usan Jobs y Queues.

Endpoint sincronico:

```text
POST /api/empleados
```

Endpoint asincrono:

```text
POST /api/empleados/async
```

Diferencia:

```text
Sincronico:
Controller -> Service -> UnitOfWork -> Repository -> DB -> Respuesta 201

Asincrono:
Controller -> Queue -> Respuesta 202
Worker -> Job -> Service -> UnitOfWork -> Repository -> DB
```

## Validaciones

Las validaciones se aplican antes de entrar a la capa de aplicacion. Los errores se devuelven con formato uniforme:

```json
{
  "mensaje": "Error de validacion",
  "errores": [
    {
      "campo": "correo",
      "detalle": "El campo no tiene un formato valido."
    }
  ]
}
```

## Pruebas

```bash
php artisan test
```

Las pruebas cubren:

- Login JWT.
- Creacion masiva.
- Listado paginado.
- Policy de propiedad.
- Rollback transaccional.

Para pruebas con SQLite en memoria, asegurese de tener habilitada la extension `pdo_sqlite`.

## Comparacion con ASP.NET Core

| ASP.NET Core | Laravel |
|---|---|
| Controller | Controller |
| Service Layer | Application Service |
| Repository Interface | Domain Interface |
| EF Core DbContext | Eloquent ORM |
| Transaction / SaveChanges | UnitOfWork commit / rollback |
| async/await + Task | Jobs y Queues |
| DataAnnotations / FluentValidation | `Request::validate()` |
| xUnit / NUnit | PHPUnit |
| AddJwtBearer | Middleware JWT |
| `[Authorize(Roles="ADMIN")]` | Middleware `role:ADMIN` |
| `[Authorize(Policy="...")]` | Laravel Policy |

## Seguridad

- Las credenciales reales deben vivir fuera del repositorio.
- `.env` no debe versionarse.
- `JWT_SECRET` debe ser privado y suficientemente largo.
- Los tokens no deben guardarse en el README, commits, issues ni capturas publicas.
- Las contrasenas se almacenan con hash mediante Laravel.
