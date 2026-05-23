<?php

namespace App\Infrastructure\UnitOfWork;

use App\Domain\Interfaces\IUnitOfWork;
use App\Domain\Interfaces\ICompaniaRepository;
use App\Domain\Interfaces\IEmpleadoRepository;
use App\Infrastructure\Repositories\CompaniaRepository;
use App\Infrastructure\Repositories\EmpleadoRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UnitOfWork implements IUnitOfWork
{
    private ICompaniaRepository $companiaRepository;
    private IEmpleadoRepository $empleadoRepository;

    public function __construct()
    {
        $this->companiaRepository = new CompaniaRepository();
        $this->empleadoRepository = new EmpleadoRepository();
    }

    public function beginTransaction(): void
    {
        DB::beginTransaction();
        Log::info('[UnitOfWork] Transacción iniciada.');
    }

    public function commit(): void
    {
        DB::commit();
        Log::info('[UnitOfWork] Transacción confirmada (commit).');
    }

    public function rollback(): void
    {
        DB::rollBack();
        Log::warning('[UnitOfWork] Transacción revertida (rollback).');
    }

    public function companias(): ICompaniaRepository
    {
        return $this->companiaRepository;
    }

    public function empleados(): IEmpleadoRepository
    {
        return $this->empleadoRepository;
    }
}