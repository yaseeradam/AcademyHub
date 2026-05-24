@echo off
title MyAcademy - Post-Pull Setup
color 0A

echo.
echo  ============================================
echo   MyAcademy Post-Pull Setup
echo  ============================================
echo.

cd /d "%~dp0"

:: 1. Install/update PHP dependencies
echo [1/6] Installing Composer dependencies...
php composer.phar install --no-interaction --prefer-dist
if %ERRORLEVEL% NEQ 0 (
    echo  ** composer install failed. Trying with composer command...
    composer install --no-interaction --prefer-dist
)
echo.

:: 2. Run any new database migrations
echo [2/6] Running database migrations...
php artisan migrate --force
if %ERRORLEVEL% NEQ 0 (
    echo.
    echo  ** Migration failed! Check your DB connection in .env
    echo     DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD
    echo.
)
echo.

:: 3. Clear all caches (config, routes, views, app cache)
echo [3/6] Clearing all caches...
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
echo.

:: 4. Ensure storage symlink exists
echo [4/6] Ensuring storage link...
php artisan storage:link 2>nul
echo.

:: 5. Install NPM dependencies if package.json changed
echo [5/6] Checking NPM dependencies...
if exist "node_modules" (
    echo  NPM modules found, skipping install. Run 'npm install' manually if needed.
) else (
    echo  Installing NPM dependencies...
    npm install
)
echo.

:: 6. Health check - verify the app boots
echo [6/6] Health check...
php artisan about 2>nul | findstr "Laravel" >nul
if %ERRORLEVEL% EQU 0 (
    echo.
    echo  ============================================
    echo   All good! App is ready.
    echo  ============================================
    echo.
    echo   Open: http://myacademy-laravel.test
    echo   Or:   php artisan serve
    echo.
) else (
    php artisan --version >nul 2>&1
    if %ERRORLEVEL% EQU 0 (
        echo.
        echo  ============================================
        echo   Setup complete! App is ready.
        echo  ============================================
        echo.
    ) else (
        echo.
        echo  ** WARNING: App failed to boot. Check errors above.
        echo.
    )
)

pause
