@echo off
title AcademyHub - Start Docker Services
color 0B

echo.
echo  ============================================
echo   AcademyHub Docker - Start Services
echo  ============================================
echo.

cd /d "%~dp0"

:: 1. Check if .env exists
if not exist ".env" (
    echo  [!] .env file missing. Copying .env.docker to .env...
    if exist ".env.docker" (
        copy .env.docker .env >nul
        echo  [+] Created .env from .env.docker
    ) else if exist ".env.example" (
        copy .env.example .env >nul
        echo  [+] Created .env from .env.example
    ) else (
        echo  [X] ERROR: No .env, .env.docker, or .env.example found!
        echo.
        pause
        exit /b 1
    )
)

:: 2. Check if Docker is running
docker info >nul 2>&1
if %ERRORLEVEL% NEQ 0 (
    echo  [X] ERROR: Docker is not running!
    echo      Please start Docker Desktop and try again.
    echo.
    pause
    exit /b 1
)

:: 3. Start containers
echo  [*] Starting Docker containers (app, mysql, redis)...
docker compose up -d

if %ERRORLEVEL% NEQ 0 (
    echo.
    echo  [X] Failed to start Docker containers. Check output above.
    echo.
    pause
    exit /b 1
)

:: 4. Wait for database and app to become healthy
echo.
echo  [*] Waiting for database, cache, and services to become ready...
set ATTEMPTS=0

:WAIT_LOOP
set /a ATTEMPTS+=1
timeout /t 2 /nobreak >nul 2>&1

docker exec academyhub-app curl -sf http://localhost/api/health >nul 2>&1
if %ERRORLEVEL% EQU 0 goto READY
if %ATTEMPTS% GEQ 25 goto READY

echo      Initializing application and database (attempt %ATTEMPTS%/25)...
goto WAIT_LOOP

:READY
echo.
echo  =============================================================
echo               All Services Running and Verified!
echo  =============================================================
echo.
echo  [Container Status]
docker compose ps
echo.
echo  [Background Daemons (Supervisor)]
docker exec academyhub-app supervisorctl status
echo.
echo  -------------------------------------------------------------
echo   ACCESS URLS:
echo   - Platform SuperAdmin:   http://localhost/superadmin
echo   - School Portal:         http://green.localhost
echo   - WebSocket Server:      ws://localhost:8080 (Reverb)
echo  -------------------------------------------------------------
echo.
echo   TIPS:
echo   - All background services (Queue, Reverb, Scheduler) run automatically.
echo   - If you edit PHP code, Blade views, or CSS on Windows, run
echo     "docker-refresh.bat" to rebuild and update the containers.
echo   - If school subdomains do not open in your browser, run
echo     "setup-hosts-permission.bat" as Administrator.
echo.

pause
