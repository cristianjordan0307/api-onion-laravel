<?php

namespace App\Jobs;

use App\Application\DTOs\EmpleadoDTO;
use App\Application\Services\EmpleadoService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class CrearEmpleadoJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(private readonly array $payload)
    {
        $this->onQueue('empleados');
    }

    public function handle(EmpleadoService $service): void
    {
        Log::info('[CrearEmpleadoJob] Procesando empleado asincrono: ' . $this->payload['correo']);

        $service->create(EmpleadoDTO::fromRequest($this->payload));

        Log::info('[CrearEmpleadoJob] Empleado creado asincronicamente: ' . $this->payload['correo']);
    }
}
