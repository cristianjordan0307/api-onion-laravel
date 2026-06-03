@echo off
title Onion Admin - Control Panel
echo ======================================================================
echo             INICIANDO ONION ADMIN (BACKEND + FRONTEND)
echo ======================================================================
echo.

REM Verificar si el PHP de herramientas existe
if not exist "C:\tools\php\php.exe" (
    echo [ERROR] No se encontro PHP en C:\tools\php\php.exe
    echo Por favor verifica la ubicacion de PHP 8.3.
    pause
    exit /b
)

REM 1. Iniciar el servidor Laravel Backend
echo [1/3] Levantando servidor Laravel en http://127.0.0.1:8000 ...
start "Laravel Backend" /min cmd /c "C:\tools\php\php.exe artisan serve --port=8000"

REM 2. Iniciar el Queue Worker para procesamiento asincrono
echo [2/3] Levantando procesador de colas (Queue Worker) ...
start "Laravel Queue" /min cmd /c "C:\tools\php\php.exe artisan queue:work --queue=companias,empleados,default --tries=3"

REM 3. Iniciar el Frontend (React + Vite)
echo [3/3] Iniciando servidor de desarrollo de Vite (Frontend) ...
echo.
cd FRONTEND
npm run dev

pause
