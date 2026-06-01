@echo off
title AcademyHub Webhook Tunnel (ngrok)
cls
echo ====================================================
echo 🚀 AcademyHub WhatsApp Webhook Tunnel Activator
echo ====================================================
echo.

set DEFAULT_HOST=yis.myacademy-laravel.test
set /p LOCAL_HOST="Enter your local virtual host [%DEFAULT_HOST%]: "
if "%LOCAL_HOST%"=="" set LOCAL_HOST=%DEFAULT_HOST%

echo.
echo Searching for ngrok...

:: Check Laragon standard bin folder
if exist "C:\laragon\bin\ngrok\ngrok.exe" (
    set NGROK_PATH="C:\laragon\bin\ngrok\ngrok.exe"
    echo [FOUND] Laragon ngrok located at: C:\laragon\bin\ngrok\ngrok.exe
    goto run
)

:: Check basic Laragon folder
if exist "C:\laragon\bin\ngrok.exe" (
    set NGROK_PATH="C:\laragon\bin\ngrok.exe"
    echo [FOUND] Laragon ngrok located at: C:\laragon\bin\ngrok.exe
    goto run
)

:: Check system PATH
where ngrok >nul 2>nul
if %ERRORLEVEL% equ 0 (
    set NGROK_PATH=ngrok
    echo [FOUND] Global ngrok found in system PATH
    goto run
)

echo.
echo [ERROR] ngrok was not found on your system!
echo 💡 Please download ngrok from https://ngrok.com
echo and place the 'ngrok.exe' file inside: C:\laragon\bin\ngrok\ngrok.exe
echo.
pause
exit

:run
echo.
echo ----------------------------------------------------
echo 📡 Starting secure HTTP tunnel for: %LOCAL_HOST%
echo 🔒 COPY the 'https://xxxx.ngrok-free.app' URL
echo 🔗 Append '/api/whatsapp/webhook' to it for Meta!
echo ----------------------------------------------------
echo.
%NGROK_PATH% http 80 --host-header=%LOCAL_HOST%
pause
