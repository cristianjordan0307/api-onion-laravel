# API REST — Compañías y Empleados

API REST construida con **Laravel 13** aplicando **Onion Architecture**, Repository Pattern, Unit of Work, Jobs/Queues, autenticación JWT propia, sistema de roles + policies, y documentación automática con Scribe.

---

## Tabla de contenido

1. [Características](#características)
2. [Arquitectura](#arquitectura)
3. [Estructura de archivos](#estructura-de-archivos)
4. [Requisitos](#requisitos)
5. [Instalación local](#instalación-local)
6. [Variables de entorno](#variables-de-entorno)
7. [Usuarios de prueba](#usuarios-de-prueba)
8. [Autenticación y JWT](#autenticación-y-jwt)
9. [Claims del token](#claims-del-token)
10. [Roles y permisos](#roles-y-permisos)
11. [Policies](#policies)
12. [Endpoints](#endpoints)
13. [Colecciones y paginación](#colecciones-y-paginación)
14. [Programación asíncrona](#programación-asíncrona)
15. [Validaciones y errores](#validaciones-y-errores)
16. [CORS](#cors)
17. [Documentación API (Scribe)](#documentación-api-scribe)
18. [Pruebas](#pruebas)
19. [Integración con frontend](#integración-con-frontend)
20. [Comparación con ASP.NET Core](#comparación-con-aspnet-core)
21. [Seguridad](#seguridad)

---

## Características

- CRUD completo de **compañías** y **empleados**.
- Paginación, filtrado, ordenamiento y búsqueda en colecciones.
- Inserción masiva (*bulk insert*) y eliminación múltiple.
- Actualización parcial con **PATCH**.
- Procesamiento **asíncrono** con Jobs y Queues de Laravel.
- Creación transaccional (compañía + empleados en una sola operación con rollback automático).
- Autenticación **JWT** implementada desde cero (sin librería externa).
- **Claims estándar RFC 7519** (`iss`, `sub`, `aud`, `iat`, `exp`, `jti`) + claims privados de negocio.
- Sistema de **roles** (`ADMIN`, `ADMIN_BOG`, `ADMIN_MED`, `USUARIO`).
- **Policies** de Laravel para control de acceso granular por recurso.
- **CORS** configurado para consumo desde cualquier frontend.
- Respuestas de error en JSON uniforme (404, 405, 422, 500).
- Documentación automática con **Scribe** (OpenAPI + Postman).
- Pruebas con **PHPUnit**.

---

## Arquitectura

El proyecto sigue el flujo de **Onion Architecture** (capas de adentro hacia afuera):

```
HTTP Request
     │
     ▼
┌──────────────────────────────┐
│  Controller / Middleware      │  ← Entrada HTTP, validación de request
└──────────────┬───────────────┘
               │
               ▼
┌──────────────────────────────┐
│  Application Services + DTOs │  ← Lógica de aplicación, orquestación
└──────────────┬───────────────┘
               │
               ▼
┌──────────────────────────────┐
│  Unit of Work                │  ← Gestión de transacciones
└──────────────┬───────────────┘
               │
               ▼
┌──────────────────────────────┐
│  Domain Interfaces           │  ← Contratos del dominio (puros PHP)
└──────────────┬───────────────┘
               │
               ▼
┌──────────────────────────────┐
│  Infrastructure Repositories │  ← Implementación con Eloquent ORM
└──────────────┬───────────────┘
               │
               ▼
         Base de datos
```

Los controladores **nunca** ejecutan consultas directamente contra Eloquent. Toda la lógica de negocio y acceso a datos pasa por los servicios y repositorios.

---

## Estructura de archivos

```text
app/
├── Application/
│   ├── DTOs/              # Objetos de transferencia de datos
│   └── Services/          # AuthService, CompaniaService, EmpleadoService, JwtService
├── Domain/
│   └── Interfaces/        # ICompaniaRepository, IEmpleadoRepository, IUsuarioRepository, IUnitOfWork
├── Http/
│   ├── Controllers/       # AuthController, CompaniaController, EmpleadoController
│   └── Middleware/        # JwtAuthMiddleware, RoleMiddleware, LogRequestMiddleware
├── Infrastructure/
│   ├── Repositories/      # Implementaciones Eloquent de los repositorios
│   └── UnitOfWork/        # Implementación de transacciones
├── Jobs/                  # CrearEmpleadoJob, CrearCompaniaConEmpleadosJob
├── Models/                # User, Compania, Empleado
├── Policies/              # CompaniaPolicy, EmpleadoPolicy
└── Providers/             # AppServiceProvider (binding de interfaces)

bootstrap/
└── app.php                # Registro de middleware, CORS y manejo de errores

config/
└── cors.php               # Configuración de orígenes permitidos

database/
├── migrations/            # Esquema de tablas
└── seeders/               # Datos iniciales (usuarios de prueba, compañías, empleados)

routes/
└── api.php                # Definición de rutas con middleware de rol

tests/
└── Feature/               # Pruebas de integración (PHPUnit)
```

---

## Requisitos

| Herramienta | Versión mínima |
|-------------|---------------|
| PHP | 8.3+ |
| Composer | 2.x |
| SQLite | (incluido en PHP, para desarrollo) |
| MySQL / PostgreSQL | (opcional, para producción) |

Extensiones PHP requeridas: `pdo_sqlite`, `mbstring`, `openssl`, `fileinfo`, `curl`, `zip`, `sodium`.

---

## Instalación local

```bash
# 1. Clonar el repositorio
git clone https://github.com/cristianjordan0307/api-onion-laravel.git
cd api-onion-laravel

# 2. Copiar variables de entorno
cp .env.example .env

# 3. Instalar dependencias
composer install

# 4. Generar clave de aplicación
php artisan key:generate

# 5. Crear base de datos SQLite y ejecutar migraciones + seeders
touch database/database.sqlite
php artisan migrate --seed

# 6. Iniciar servidor de desarrollo
php artisan serve
```

El API quedará disponible en: **`http://127.0.0.1:8000`**

Para procesar las colas asíncronas (Jobs), en una terminal separada:

```bash
php artisan queue:work --queue=companias,empleados,default --tries=3
```

---

## Variables de entorno

```env
APP_NAME=Laravel
APP_ENV=local
APP_KEY=          # Generada con php artisan key:generate
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

JWT_SECRET=       # Clave larga y privada para firmar tokens JWT
SCRIBE_BASE_URL=http://127.0.0.1:8000

DB_CONNECTION=sqlite
# Para MySQL, descomentar y configurar:
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=nombre_base_datos
# DB_USERNAME=usuario
# DB_PASSWORD=contraseña

QUEUE_CONNECTION=database
CACHE_STORE=database
SESSION_DRIVER=database
```

> **⚠️ Importante:** Nunca subas el archivo `.env` al repositorio. Contiene claves privadas.

---

## Usuarios de prueba

El seeder crea los siguientes usuarios de prueba:

| Email | Contraseña | Rol | Compañía | Puede |
|-------|-----------|-----|----------|-------|
| `admin@api.com` | `Admin123` | `ADMIN` | — | Todo |
| `admin.bog@api.com` | `AdminBog123` | `ADMIN_BOG` | 1 | CRUD sin DELETE |
| `admin.med@api.com` | `AdminMed123` | `ADMIN_MED` | 2 | CRUD sin PATCH |
| `usuario@api.com` | `Usuario123` | `USUARIO` | 1 | Leer + crear + actualizar (solo su compañía) |

Para regenerar los datos de prueba:

```bash
php artisan migrate:fresh --seed
```

---

## Autenticación y JWT

El sistema implementa **JWT (JSON Web Tokens)** desde cero con `hash_hmac` + HS256, sin dependencias de terceros.

### Registro

```http
POST /api/auth/registro
Content-Type: application/json

{
  "name": "Juan Pérez",
  "email": "juan@example.com",
  "password": "miContrasena123",
  "role": "USUARIO",
  "compania_id": 1
}
```

Roles válidos: `ADMIN`, `ADMIN_BOG`, `ADMIN_MED`, `USUARIO`.

### Login

```http
POST /api/auth/login
Content-Type: application/json

{
  "email": "juan@example.com",
  "password": "miContrasena123"
}
```

**Respuesta:**

```json
{
  "token_type": "Bearer",
  "access_token": "<TOKEN_JWT>",
  "usuario": {
    "id": 1,
    "name": "Juan Pérez",
    "email": "juan@example.com",
    "role": "USUARIO",
    "compania_id": 1
  }
}
```

### Usar el token

Incluye el token en el header de todas las rutas protegidas:

```http
Authorization: Bearer <TOKEN_JWT>
```

### Ver perfil

```http
GET /api/auth/perfil
Authorization: Bearer <TOKEN_JWT>
```

---

## Claims del token

El token JWT contiene los siguientes claims:

### Claims estándar (RFC 7519)

| Claim | Nombre | Descripción |
|-------|--------|-------------|
| `iss` | Issuer | URL de la API que emitió el token |
| `sub` | Subject | ID del usuario autenticado |
| `aud` | Audience | Aplicación destino (`api-onion`) |
| `iat` | Issued At | Timestamp de emisión |
| `exp` | Expires | Timestamp de expiración (8 horas) |
| `jti` | JWT ID | Identificador único del token |

### Claims privados (negocio)

| Claim | Descripción |
|-------|-------------|
| `name` | Nombre del usuario |
| `email` | Correo electrónico |
| `role` | Rol asignado |
| `compania_id` | ID de la compañía a la que pertenece |
| `permissions` | Lista de acciones permitidas según el rol |

### Permisos por rol (claim `permissions`)

```json
// ADMIN
["companias:read", "companias:create", "companias:update", "companias:patch", "companias:delete",
 "empleados:read", "empleados:create", "empleados:update", "empleados:patch", "empleados:delete"]

// ADMIN_BOG (sin delete)
["companias:read", "companias:create", "companias:update", "companias:patch",
 "empleados:read", "empleados:create", "empleados:update", "empleados:patch"]

// ADMIN_MED (sin patch)
["companias:read", "companias:create", "companias:update", "companias:delete",
 "empleados:read", "empleados:create", "empleados:update", "empleados:delete"]

// USUARIO
["companias:read", "companias:create", "companias:update",
 "empleados:read", "empleados:create", "empleados:update"]
```

> El frontend puede leer el claim `permissions` directamente del token (decodificando el payload en base64) para mostrar u ocultar botones de la UI sin necesidad de consultas adicionales al servidor.

---

## Roles y permisos

La autorización usa **doble capa de seguridad**:

1. **Middleware `role`** → filtra por rol antes de entrar al controlador.
2. **Policy** → evalúa permisos granulares dentro del controlador.

### Tabla de permisos por rol

| Acción | ADMIN | ADMIN_BOG | ADMIN_MED | USUARIO |
|--------|:-----:|:---------:|:---------:|:-------:|
| GET (listar / ver) | ✅ | ✅ | ✅ | ✅ |
| POST (crear) | ✅ | ✅ | ✅ | ✅ |
| PUT (actualizar completo) | ✅ | ✅ | ✅ | ✅ * |
| PATCH (actualizar parcial) | ✅ | ✅ | ❌ | ❌ |
| DELETE (eliminar) | ✅ | ❌ | ✅ | ❌ |
| POST /con-empleados (transaccional) | ✅ | ❌ | ❌ | ❌ |

> \* `USUARIO` solo puede actualizar empleados de su propia compañía (controlado por Policy).

### Descripción de roles

| Rol | Descripción |
|-----|-------------|
| `ADMIN` | Acceso total, sin restricciones. |
| `ADMIN_BOG` | Administrador de Bogotá: CRUD completo **excepto** DELETE. |
| `ADMIN_MED` | Administrador de Medellín: CRUD completo **excepto** PATCH. |
| `USUARIO` | Puede leer, crear y actualizar, solo sobre recursos de su compañía. |

---

## Policies

Las **Policies** de Laravel aplican reglas de negocio adicionales más allá del rol.

### `CompaniaPolicy`

| Método | Acción | ADMIN | ADMIN_BOG | ADMIN_MED | USUARIO |
|--------|--------|:-----:|:---------:|:---------:|:-------:|
| `create` | Crear compañía | ✅ bypass | ✅ | ✅ | ✅ |
| `update` | PUT compañía | ✅ bypass | ✅ | ✅ | ❌ |
| `patch` | PATCH compañía | ✅ bypass | ✅ | ❌ | ❌ |
| `delete` | DELETE compañía | ✅ bypass | ❌ | ✅ | ❌ |
| `deleteMany` | DELETE masivo | ✅ bypass | ❌ | ✅ | ❌ |

### `EmpleadoPolicy`

| Método | Acción | ADMIN | ADMIN_BOG | ADMIN_MED | USUARIO |
|--------|--------|:-----:|:---------:|:---------:|:-------:|
| `update` | PUT empleado | ✅ bypass | ✅ (misma cía) | ✅ (misma cía) | ✅ (misma cía) |
| `patch` | PATCH empleado | ✅ bypass | ✅ (misma cía) | ❌ | ❌ |
| `delete` | DELETE empleado | ✅ bypass | ❌ | ✅ (misma cía) | ❌ |
| `deleteMany` | DELETE masivo | ✅ bypass | ❌ | ✅ | ❌ |

> El `before()` de ambas policies le da pase libre al `ADMIN` en todas las acciones.

---

## Endpoints

### Auth

```text
POST /api/auth/registro     → Crear cuenta
POST /api/auth/login        → Iniciar sesión (devuelve JWT)
GET  /api/auth/perfil       → Ver perfil del usuario autenticado  [JWT]
```

### Compañías

```text
GET    /api/companias                    → Listar (paginado)           [JWT]
POST   /api/companias                    → Crear                       [JWT + rol]
DELETE /api/companias                    → Eliminar múltiples          [JWT + rol]
GET    /api/companias/{id}               → Ver detalle                 [JWT]
GET    /api/companias/{id}/empleados     → Empleados de la compañía    [JWT]
PUT    /api/companias/{id}               → Actualizar completo         [JWT + rol + policy]
PATCH  /api/companias/{id}              → Actualizar parcial           [JWT + rol + policy]
DELETE /api/companias/{id}              → Eliminar                     [JWT + rol + policy]
POST   /api/companias/con-empleados      → Crear con empleados (transaccional) [JWT + rol]
POST   /api/companias/con-empleados/async → Crear de forma asíncrona  [JWT + rol]
```

### Empleados

```text
GET    /api/empleados           → Listar (paginado)               [JWT]
POST   /api/empleados           → Crear uno                       [JWT + rol]
POST   /api/empleados/bulk      → Crear masivamente               [JWT + rol]
POST   /api/empleados/async     → Crear de forma asíncrona        [JWT + rol]
DELETE /api/empleados           → Eliminar múltiples              [JWT + rol + policy]
GET    /api/empleados/{id}      → Ver detalle                     [JWT]
PUT    /api/empleados/{id}      → Actualizar completo             [JWT + rol + policy]
PATCH  /api/empleados/{id}     → Actualizar parcial               [JWT + rol + policy]
DELETE /api/empleados/{id}     → Eliminar                         [JWT + rol + policy]
```

---

## Colecciones y paginación

### Parámetros de consulta

| Parámetro | Tipo | Descripción |
|-----------|------|-------------|
| `pagina` | integer | Número de página (default: 1) |
| `tamano` | integer | Registros por página, máx. 100 (default: 15) |
| `orden` | string | Campo por el que ordenar |
| `dir` | string | `asc` o `desc` |
| `buscar` | string | Texto de búsqueda libre |

**Ejemplo:**

```http
GET /api/empleados?pagina=2&tamano=10&orden=apellido&dir=asc&buscar=gomez
Authorization: Bearer <TOKEN>
```

**Respuesta:**

```json
{
  "datos": [ ... ],
  "paginacion": {
    "pagina_actual": 2,
    "tamano": 10,
    "total": 47,
    "ultima_pagina": 5
  }
}
```

### Inserción masiva (bulk)

```http
POST /api/empleados/bulk
Authorization: Bearer <TOKEN>
Content-Type: application/json

{
  "empleados": [
    { "nombre": "Ana", "apellido": "Gomez", "correo": "ana@example.com", "cargo": "Dev", "salario": 3500000, "compania_id": 1 },
    { "nombre": "Luis", "apellido": "Rojas", "correo": "luis@example.com", "cargo": "QA", "salario": 2800000, "compania_id": 1 }
  ]
}
```

### Eliminación múltiple

```http
DELETE /api/empleados
Authorization: Bearer <TOKEN>
Content-Type: application/json

{
  "ids": [1, 2, 3]
}
```

### Actualización parcial (PATCH)

```http
PATCH /api/empleados/1
Authorization: Bearer <TOKEN>
Content-Type: application/json

{
  "cargo": "Líder técnico",
  "salario": 5000000
}
```

---

## Programación asíncrona

Laravel gestiona el procesamiento en segundo plano con **Jobs y Queues**.

| Tipo | Endpoint | Respuesta |
|------|----------|-----------|
| Síncrono | `POST /api/empleados` | `201 Created` con el empleado creado |
| Asíncrono | `POST /api/empleados/async` | `202 Accepted` inmediato, proceso en cola |
| Transaccional síncrono | `POST /api/companias/con-empleados` | `201 Created` con rollback si falla |
| Transaccional asíncrono | `POST /api/companias/con-empleados/async` | `202 Accepted`, proceso en cola |

**Flujo asíncrono:**

```
Controller → Job::dispatch() → Respuesta 202
                 ↓ (en segundo plano)
            Worker → Job → Service → Repository → DB
```

Iniciar el worker de colas:

```bash
php artisan queue:work --queue=companias,empleados,default --tries=3
```

---

## Validaciones y errores

### Formato de error de validación (`422`)

```json
{
  "mensaje": "Error de validacion",
  "errores": [
    { "campo": "email", "detalle": "El campo email ya fue tomado." },
    { "campo": "salario", "detalle": "El campo salario debe ser un número." }
  ]
}
```

### Otros errores

| Código | Causa | Respuesta |
|--------|-------|-----------|
| `401` | Token ausente, inválido o expirado | `{ "error": "Token JWT requerido." }` |
| `403` | Rol o policy insuficiente | `{ "error": "Rol insuficiente." }` |
| `404` | Ruta o recurso no encontrado | `{ "error": "Ruta no encontrada." }` |
| `405` | Método HTTP no permitido | `{ "error": "Metodo HTTP no permitido." }` |
| `422` | Error de validación de entrada | Ver formato arriba |
| `500` | Error interno del servidor | `{ "error": "Error interno del servidor." }` |

---

## CORS

El archivo [`config/cors.php`](config/cors.php) permite peticiones desde los puertos de desarrollo más comunes:

```
http://localhost:3000   → React / Next.js
http://localhost:5173   → Vite / Vue
http://localhost:4200   → Angular
```

Para **producción**, edita `allowed_origins` con el dominio real del frontend:

```php
'allowed_origins' => ['https://mi-frontend.com'],
```

---

## Documentación API (Scribe)

La documentación se genera automáticamente con **Scribe**:

```bash
php artisan scribe:generate
```

URLs disponibles tras iniciar el servidor:

| URL | Contenido |
|-----|-----------|
| `http://127.0.0.1:8000/docs` | Documentación interactiva (UI) |
| `http://127.0.0.1:8000/docs.openapi` | Spec OpenAPI (YAML) |
| `http://127.0.0.1:8000/docs.postman` | Colección Postman (JSON) |

Para probar endpoints protegidos desde la UI, haz login en `/api/auth/login`, copia el `access_token` y agrégalo como:

```
Authorization: Bearer <TOKEN>
```

---

## Pruebas

```bash
php artisan test
```

Las pruebas cubren:

| Test | Qué verifica |
|------|-------------|
| `test_login_devuelve_jwt` | Login retorna token JWT con estructura correcta |
| `test_listado_paginado_y_bulk_de_empleados` | Creación masiva y paginación funcionan |
| `test_policy_de_propiedad_en_patch_de_empleado` | Un usuario no puede modificar empleados de otra compañía |
| `test_rollback_en_creacion_transaccional_con_empleados` | Rollback completo si falla la transacción |

> Para ejecutar pruebas se necesita la extensión `pdo_sqlite` habilitada en PHP.

---

## Integración con frontend (Cliente Oficial React)

Este proyecto incluye un cliente web interactivo oficial desarrollado en **React + Vite** dentro de la carpeta [FRONTEND](file:///C:/API_ONION/FRONTEND/).

### Características del Cliente Oficial
- **Diseño Premium Glassmorphic:** Aspecto visual moderno y sofisticado que utiliza variables HSL dinámicas para soportar temas claros y oscuros, desenfoques de fondo (glassmorphism), y micro-animaciones (como la mano saludando `👋` oscilante del dashboard).
- **Responsividad Móvil Completa (Totalmente Adaptable):**
  - **Barra de Navegación Deslizable (Sidebar Drawer):** Se contrae en un cajón lateral en tabletas y móviles (< 1024px) controlado por un botón de hamburguesa en la cabecera y cerrado mediante un fondo difuminado (backdrop) o al cambiar de ruta.
  - **Diseño Reorganizable:** Las tarjetas de métricas, formularios, encabezados y barras de búsqueda se apilan verticalmente de forma adaptativa en pantallas pequeñas (< 576px).
  - **Tablas Desplazables:** El contenedor de las grillas de datos (`.table-wrapper`) posee desplazamiento táctil horizontal para evitar desbordes.
- **Acceso Directo al Perfil:** El avatar con las iniciales del usuario en la cabecera derecha redirige dinámicamente a la ruta de perfil (`/perfil`) y cuenta con animaciones de escala al pasar el cursor.
- **Diseño Simétrico y Compacto:** El Dashboard principal ordena las listas de últimos empleados registrados y compañías de forma compacta (eliminando vacíos verticales y configurando espaciados constantes de `12px`), logrando una perfecta simetría de grilla en dos columnas.
- **Estado de API en Vivo y Documentación:** Incluye una tarjeta de verificación de conexión en tiempo real en la página de perfil que mide la latencia de respuesta (ms) del servidor e integra un redireccionamiento automático a la especificación Swagger UI (`/swagger`).
- **Formulario de login inteligente (Opción B):** Cuenta con un menú flotante en la esquina inferior derecha para autocompletar credenciales de prueba con un solo clic.

### Scripts de Inicio Rápido

Para simplificar las pruebas y el desarrollo local, disponemos de dos scripts de lote (`.bat`) en el directorio raíz:

* **`start-dev.bat` (Modo Local):** Inicia en simultáneo el backend Laravel local (`http://127.0.0.1:8000`), el procesador de colas en segundo plano (`artisan queue:work`) y el servidor frontend de Vite. (Ideal para probar las cuentas regionales y políticas de Bogotá/Medellín).
* **`start-frontend.bat` (Modo API Remota):** Inicia únicamente el servidor de desarrollo del frontend de Vite conectado directamente a la API de producción alojada en **Railway** (`https://api-onion-laravel-production.up.railway.app/api`).

### Arranque Manual desde Consola

1. Instala las dependencias del frontend:
   ```bash
   cd FRONTEND
   npm install
   ```
2. Ejecuta el servidor de desarrollo de Vite:
   ```bash
   npm run dev
   ```
   El frontend estará accesible en `http://localhost:5173` (o en su defecto `http://localhost:5174` si el puerto anterior está ocupado).
3. Compilación de producción:
   ```bash
   npm run build
   ```

---

## Comparación con ASP.NET Core

| ASP.NET Core | Laravel |
|---|---|
| Controller | Controller |
| Service Layer | Application Service |
| Repository Interface | Domain Interface (Onion) |
| EF Core DbContext | Eloquent ORM |
| Transaction / SaveChanges | UnitOfWork commit / rollback |
| async/await + Task | Jobs y Queues |
| DataAnnotations / FluentValidation | `Request::validate()` |
| xUnit / NUnit | PHPUnit |
| `AddJwtBearer` | JwtAuthMiddleware (JWT propio) |
| `[Authorize(Roles="ADMIN")]` | Middleware `role:ADMIN` |
| `[Authorize(Policy="...")]` | Laravel Policy |
| CORS Middleware | `config/cors.php` + HandleCors |
| Claims Principal | JWT Claims (iss, sub, aud, permissions...) |

---

## Seguridad

- **`.env` nunca debe subirse al repositorio** (está en `.gitignore`).
- `JWT_SECRET` debe ser una cadena larga, aleatoria y privada.
- Los tokens expiran en **8 horas**.
- Las contraseñas se almacenan con **hash bcrypt** (vía Laravel).
- No incluyas tokens, credenciales ni claves en commits, issues ni capturas públicas.
- En producción, configura `APP_DEBUG=false` y usa HTTPS.
- Ajusta `allowed_origins` en `config/cors.php` al dominio exacto del frontend.
