<?php

namespace App\Application\DTOs;

class EmpleadoDTO
{
    public function __construct(
        public readonly string $nombre,
        public readonly string $apellido,
        public readonly string $correo,
        public readonly string $cargo,
        public readonly float  $salario,
        public readonly int    $compania_id,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            nombre:      $data['nombre'],
            apellido:    $data['apellido'],
            correo:      $data['correo'],
            cargo:       $data['cargo'],
            salario:     $data['salario'],
            compania_id: $data['compania_id'],
        );
    }
}