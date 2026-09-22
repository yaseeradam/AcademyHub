@echo off
title AcademyHub - Refresh Docker Containers
color 0A

echo.
echo  ============================================
echo   AcademyHub Docker - Rebuild & Refresh
echo  ============================================
echo.

cd /d "%~dp0"

:: 1. Check if Docker is running
docker info >nul 2>&1
if %ERRORLEVEL% NEQ 0 (
    echo  [X] ERROR: Docker is not running!
    echo      Please start Docker Desktop and try again.
    echo.
    pause
    exit /b 1
)

:: 2. Rebuild container images with code changes
echo  [1/4] Rebuilding Docker images (compiling assets & PHP updates)...
docker compose build --no-cache

if %ERRORLEVEL% NEQ 0 (
    echo.
    echo  [X] ERROR: Docker build failed! Check errors above.
    echo.
    pause
    exit /b 1
)

:: 3. Recreate and start updated containers
echo.
echo  [2/4] Restarting updated containers...
docker compose up -d --force-recreate

if %ERRORLEVEL% NEQ 0 (
    echo.
    echo  [X] ERROR: Failed to start containers!
    echo.
    pause
    exit /b 1
)

:: 4. Run database migrations inside container
echo.
echo  [3/4] Running database migrations...
docker exec academyhub-app php artisan migrate --force

:: 5. Clear Caches & Restart Supervisor Services
echo.
echo  [4/4] Clearing application caches & restarting background workers...
docker exec academyhub-app php artisan config:clear
docker exec academyhub-app php artisan route:clear
docker exec academyhub-app php artisan view:clear
docker exec academyhub-app php artisan cache:clear
docker exec academyhub-app supervisorctl restart php-fpm queue-worker reverb-server 2>nul

echo.
echo  ============================================
echo   Refresh Complete! All services updated.
echo  ============================================
echo.
docker compose ps
echo.
echo  App Access: http://localhost
echo.

pause
