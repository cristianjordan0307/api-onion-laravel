<?php

namespace App\Application\Services;

use App\Models\User;
use Illuminate\Support\Carbon;
use RuntimeException;

class JwtService
{
    public function generate(User $user): string
    {
        $header = ['alg' => 'HS256', 'typ' => 'JWT'];
        $payload = [
            'sub' => $user->id,
            'correo' => $user->email,
            'rol' => $user->role,
            'compania_id' => $user->compania_id,
            'ciudad' => $user->ciudad,
            'permisos' => $user->permisos ?? [],
            'iat' => Carbon::now()->timestamp,
            'exp' => Carbon::now()->addHours(8)->timestamp,
        ];

        $segments = [
            $this->base64UrlEncode(json_encode($header)),
            $this->base64UrlEncode(json_encode($payload)),
        ];

        $signature = hash_hmac('sha256', implode('.', $segments), $this->secret(), true);
        $segments[] = $this->base64UrlEncode($signature);

        return implode('.', $segments);
    }

    public function validate(string $token): array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            throw new RuntimeException('Token JWT invalido.');
        }

        [$encodedHeader, $encodedPayload, $signature] = $parts;
        $expected = $this->base64UrlEncode(hash_hmac('sha256', "{$encodedHeader}.{$encodedPayload}", $this->secret(), true));

        if (!hash_equals($expected, $signature)) {
            throw new RuntimeException('Firma JWT invalida.');
        }

        $payload = json_decode($this->base64UrlDecode($encodedPayload), true);
        if (!is_array($payload) || ($payload['exp'] ?? 0) < Carbon::now()->timestamp) {
            throw new RuntimeException('Token JWT expirado.');
        }

        return $payload;
    }

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
