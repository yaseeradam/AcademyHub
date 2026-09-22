@echo off
echo Starting MyAcademy Development Servers...
echo.

start "NPM Dev Server" cmd /k "npm run dev"
start "PHP Artisan Serve" cmd /k "php artisan serve"

echo.
echo Both servers are starting in separate windows.
echo Close this window or press any key to exit.
pause > nul
