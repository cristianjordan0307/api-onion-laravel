<?php

namespace App\Application\Services;

use App\Models\User;
use Illuminate\Support\Carbon;
use RuntimeException;

class JwtService
{
    /**
     * Genera un token JWT firmado con HS256.
     *
     * Claims estándar (RFC 7519):
     *   - iss  Issuer:    quién emitió el token (APP_URL)
     *   - sub  Subject:   ID del usuario autenticado
     *   - aud  Audience:  aplicación destino del token
     *   - iat  Issued At: timestamp de emisión
     *   - exp  Expires:   timestamp de expiración (8 h)
     *   - jti  JWT ID:    identificador único por token
     *
     * Claims privados (negocio):
     *   - name        nombre del usuario
     *   - email       correo del usuario
     *   - role        rol asignado
     *   - compania_id compañía a la que pertenece
     *   - permissions lista de acciones permitidas según el rol
     */
    public function generate(User $user): string
    {
        $header = ['alg' => 'HS256', 'typ' => 'JWT'];

        $payload = [
            // ── Claims estándar ──────────────────────────────
            'iss' => config('app.url'),
            'sub' => (string) $user->id,
            'aud' => 'api-onion',
            'iat' => Carbon::now()->timestamp,
            'exp' => Carbon::now()->addHours(8)->timestamp,
            'jti' => uniqid('jwt_', true),

            // ── Claims privados ──────────────────────────────
            'name'        => $user->name,
            'email'       => $user->email,
            'role'        => $user->role,
            'compania_id' => $user->compania_id,
            'permissions' => $this->resolvePermissions($user->role),
        ];

        $segments = [
            $this->base64UrlEncode(json_encode($header)),
            $this->base64UrlEncode(json_encode($payload)),
        ];

        $signature = hash_hmac('sha256', implode('.', $segments), $this->secret(), true);
        $segments[] = $this->base64UrlEncode($signature);

        return implode('.', $segments);
    }

    /**
     * Valida la firma, estructura y expiración del token.
     * Devuelve el payload decodificado si es válido.
     *
     * @throws RuntimeException si el token es inválido, con firma incorrecta o expirado.
     */
    public function validate(string $token): array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            throw new RuntimeException('Token JWT invalido: estructura incorrecta.');
        }

        [$encodedHeader, $encodedPayload, $signature] = $parts;

        $expected = $this->base64UrlEncode(
            hash_hmac('sha256', "{$encodedHeader}.{$encodedPayload}", $this->secret(), true)
        );

        if (!hash_equals($expected, $signature)) {
            throw new RuntimeException('Token JWT invalido: firma incorrecta.');
        }

        $payload = json_decode($this->base64UrlDecode($encodedPayload), true);

        if (!is_array($payload) || ($payload['exp'] ?? 0) < Carbon::now()->timestamp) {
            throw new RuntimeException('Token JWT expirado.');
        }

        return $payload;
    }

    /**
     * Resuelve la lista de permisos según el rol del usuario.
     * Estos permisos van embebidos en el JWT (claim 'permissions')
     * y pueden ser leídos por el frontend o por middleware sin
     * necesidad de consultar la base de datos en cada request.
     *
     * Convención: "<recurso>:<acción>"
     */
    private function resolvePermissions(string $role): array
    {
        return match ($role) {
            'ADMIN' => [
                'companias:read', 'companias:create', 'companias:update',
                'companias:patch', 'companias:delete',
                'empleados:read', 'empleados:create', 'empleados:update',
                'empleados:patch', 'empleados:delete',
            ],
            'ADMIN_BOG' => [
                'companias:read', 'companias:create', 'companias:update', 'companias:patch',
                'empleados:read', 'empleados:create', 'empleados:update', 'empleados:patch',
            ],
            'ADMIN_MED' => [
                'companias:read', 'companias:create', 'companias:update', 'companias:delete',
                'empleados:read', 'empleados:create', 'empleados:update', 'empleados:delete',
            ],
            'USUARIO' => [
                'companias:read', 'companias:create', 'companias:update',
                'empleados:read', 'empleados:create', 'empleados:update',
            ],
            default => ['companias:read', 'empleados:read'],
        };
    }

    /**
     * Clave secreta usada para firmar el token.
     * Se lee de services.jwt.secret; si no existe, usa APP_KEY como fallback.
     */
    private function secret(): string
    {
        return (string) config('services.jwt.secret', config('app.key'));
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    private function base64UrlDecode(string $value): string
    {
        return base64_decode(strtr($value, '-_', '+/'));
    }
}
