<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Domain\Interfaces\ICompaniaRepository;
use App\Domain\Interfaces\IEmpleadoRepository;
use App\Domain\Interfaces\IUnitOfWork;
use App\Infrastructure\Repositories\CompaniaRepository;
use App\Infrastructure\Repositories\EmpleadoRepository;
use App\Infrastructure\UnitOfWork\UnitOfWork;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ICompaniaRepository::class, CompaniaRepository::class);
        $this->app->bind(IEmpleadoRepository::class, EmpleadoRepository::class);
        $this->app->bind(IUnitOfWork::class, UnitOfWork::class);
    }

    public function boot(): void
    {
        //
    }
}