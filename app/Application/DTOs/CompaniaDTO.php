<?php

namespace App\Application\DTOs;

class CompaniaDTO
{
    public function __construct(
        public readonly string $nombre,
        public readonly string $direccion,
        public readonly string $telefono,
        public readonly array  $empleados = [],
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            nombre:    $data['nombre'],
            direccion: $data['direccion'],
            telefono:  $data['telefono'],
            empleados: $data['empleados'] ?? [],
        );
    }
}