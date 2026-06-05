@echo off
title AcademyHub Cloudflare Tunnel
cls
echo ====================================================
echo 🚀 AcademyHub Cloudflare Tunnel Activator
echo ====================================================
echo.

set DEFAULT_HOST=yis.academyhub-laravel.test
set /p LOCAL_HOST="Enter your local virtual host [%DEFAULT_HOST%]: "
if "%LOCAL_HOST%"=="" set LOCAL_HOST=%DEFAULT_HOST%

echo.
echo Checking for cloudflared.exe...

:: Check if cloudflared is already in the project directory
if exist "cloudflared.exe" (
    set CLOUDFLARED_PATH=.\cloudflared.exe
    echo [FOUND] cloudflared.exe located in project root.
    goto menu
)

:: Check if cloudflared is in system path
where cloudflared >nul 2>nul
if %ERRORLEVEL% equ 0 (
    set CLOUDFLARED_PATH=cloudflared
    echo [FOUND] cloudflared found in system PATH.
    goto menu
)

echo [INFO] cloudflared.exe was not found. Downloading it now...
echo Please wait, this might take a few moments...
powershell -Command "Invoke-WebRequest -Uri 'https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-windows-amd64.exe' -OutFile 'cloudflared.exe'"

if exist "cloudflared.exe" (
    set CLOUDFLARED_PATH=.\cloudflared.exe
    echo [SUCCESS] cloudflared.exe downloaded successfully!
    goto menu
) else (
    echo [ERROR] Failed to download cloudflared.exe automatically.
    echo 💡 Please download it manually from:
    echo https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-windows-amd64.exe
    echo Rename the file to 'cloudflared.exe' and place it in this project folder.
    echo.
    pause
    exit
)

:menu
echo.
echo ====================================================
echo Select your Cloudflare Tunnel Mode:
echo ====================================================
echo 1) Quick Tunnel (Free, No Sign-up/No Domain, random .trycloudflare.com link)
echo 2) Custom Domain Setup (Permanent setup, requires Cloudflare account + domain)
echo ====================================================
set /p MODE="Choose option (1 or 2): "

if "%MODE%"=="1" goto quick
if "%MODE%"=="2" goto custom
echo Invalid choice.
goto menu

:quick
echo.
echo ----------------------------------------------------
echo 📡 Starting Quick Tunnel for: %LOCAL_HOST%
echo 🔒 Look for the 'https://xxxx.trycloudflare.com' link in the output!
echo ----------------------------------------------------
echo.
%CLOUDFLARED_PATH% tunnel --url http://%LOCAL_HOST%
pause
exit

:custom
echo.
echo ====================================================
echo 🔒 Custom Domain Authentication
echo ====================================================
echo 1. A browser window will open. Log in to Cloudflare and authorize your domain.
echo 2. Return here when authorization is complete.
echo ====================================================
pause
%CLOUDFLARED_PATH% tunnel login
echo.
set /p TUNNEL_NAME="Enter a name for your tunnel [academy-tunnel]: "
if "%TUNNEL_NAME%"=="" set TUNNEL_NAME=academy-tunnel

echo Creating tunnel: %TUNNEL_NAME%...
%CLOUDFLARED_PATH% tunnel create %TUNNEL_NAME%
echo.
set /p MY_DOMAIN="Enter your domain (e.g. academyhub.com): "
if "%MY_DOMAIN%"=="" (
    echo [ERROR] Domain name is required for custom setup.
    pause
    goto menu
)

echo.
echo Routing main domain and subdomains to the tunnel...
%CLOUDFLARED_PATH% tunnel route dns %TUNNEL_NAME% %MY_DOMAIN%
%CLOUDFLARED_PATH% tunnel route dns %TUNNEL_NAME% *.%MY_DOMAIN%

echo.
echo ----------------------------------------------------
echo 📡 Starting Tunnel: %TUNNEL_NAME% for: %LOCAL_HOST%
echo 🔗 You can now access your site on your domain: %MY_DOMAIN%
echo ----------------------------------------------------
echo.
%CLOUDFLARED_PATH% tunnel run --url http://%LOCAL_HOST% %TUNNEL_NAME%
pause
