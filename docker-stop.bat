@echo off
title AcademyHub - Stop Docker Services
color 0C

echo.
echo  ============================================
echo   AcademyHub Docker - Stop Services
echo  ============================================
echo.

cd /d "%~dp0"

echo  [*] Stopping Docker containers...
docker compose down

echo.
echo  ============================================
echo   Containers Stopped.
echo  ============================================
echo.

pause
