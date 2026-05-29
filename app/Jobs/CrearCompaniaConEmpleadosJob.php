<?php

namespace App\Jobs;

use App\Application\DTOs\CompaniaDTO;
use App\Application\Services\CompaniaService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class CrearCompaniaConEmpleadosJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(private readonly array $payload)
    {
        $this->onQueue('companias');
    }

    public function handle(CompaniaService $service): void
    {
        Log::info('[CrearCompaniaConEmpleadosJob] Procesando compania asincrona: ' . $this->payload['nombre']);

        $service->createConEmpleados(CompaniaDTO::fromRequest($this->payload));

        Log::info('[CrearCompaniaConEmpleadosJob] Compania con empleados creada asincronicamente: ' . $this->payload['nombre']);
    }
}
