<?php

namespace App\Domain\Interfaces;

interface IUnitOfWork
{
    public function beginTransaction(): void;
    public function commit(): void;
    public function rollback(): void;
    public function companias(): ICompaniaRepository;
    public function empleados(): IEmpleadoRepository;
}