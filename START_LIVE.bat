@echo off
chcp 65001 >nul 2>&1
setlocal EnableDelayedExpansion

:: ============================================================
::   ATELIER 404 — Live on atelier404.store
::   Starts Laravel + Cloudflare Tunnel together
::   Usage: Double-click this file to go live!
:: ============================================================

set "ROOT=%~dp0"
if "%ROOT:~-1%"=="\" set "ROOT=%ROOT:~0,-1%"
set "CLOUDFLARED=C:\Program Files (x86)\cloudflared\cloudflared.exe"

echo.
echo  ============================================================
echo    ATELIER 404 — Going Live!
echo    https://atelier404.store
echo  ============================================================
echo.

:: Check PHP
where php >nul 2>&1
if errorlevel 1 (
    echo  [ERROR] PHP not found in PATH.
    pause & exit /b 1
)

:: Check cloudflared
if not exist "%CLOUDFLARED%" (
    echo  [ERROR] cloudflared not found at %CLOUDFLARED%
    pause & exit /b 1
)

:: Check .env
if not exist "%ROOT%\.env" (
    if exist "%ROOT%\.env.example" (
        echo  [FIX]   .env missing — copying from .env.example
        copy /y "%ROOT%\.env.example" "%ROOT%\.env" >nul
    )
)

:: Check vendor
if not exist "%ROOT%\vendor\autoload.php" (
    echo  [DEPS]  Running composer install...
    cd /d "%ROOT%"
    composer install --no-interaction --prefer-dist
)

:: Run migrations
echo  [SETUP] Running migrations...
cd /d "%ROOT%"
php artisan migrate --force --no-interaction >nul 2>&1
php artisan storage:link >nul 2>&1

:: Kill old processes on port 8000
for /f "tokens=5" %%p in ('netstat -aon 2^>nul ^| findstr ":8000 " ^| findstr "LISTENING"') do (
    if not "%%p"=="0" (
        echo  [PORT]  Freeing port 8000 (PID %%p)
        taskkill /PID %%p /F >nul 2>&1
    )
)

:: Start Laravel in background
echo.
echo  [1/2]  Starting Laravel API on port 8000...
start "Laravel API - atelier404.store" cmd /k "cd /d "%ROOT%" && php artisan serve --host=0.0.0.0 --port=8000"

:: Wait for Laravel to be ready
echo  [WAIT]  Waiting for Laravel...
timeout /t 3 /nobreak >nul

:: Start Cloudflare Tunnel
echo  [2/2]  Starting Cloudflare Tunnel...
echo.
echo  ============================================================
echo    ✅ SITE IS LIVE AT: https://atelier404.store
echo  ============================================================
echo.
echo    Laravel API:  http://localhost:8000
echo    Public URL:   https://atelier404.store
echo.
echo    Keep this window open to stay online.
echo    Press Ctrl+C to stop the tunnel (site goes offline).
echo  ============================================================
echo.

"%CLOUDFLARED%" tunnel run atelier
