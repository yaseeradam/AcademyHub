@echo off
echo ========================================
echo MyAcademy WhatsApp Bot - Fresh Start
echo ========================================
echo.

echo Stopping any running bot processes...
taskkill /F /IM node.exe 2>nul

echo.
echo Cleaning old session...
if exist .wwebjs_auth (
    rmdir /s /q .wwebjs_auth
    echo - Deleted .wwebjs_auth
)
if exist .wwebjs_cache (
    rmdir /s /q .wwebjs_cache
    echo - Deleted .wwebjs_cache
)

echo.
echo Starting bot...
echo.
node index.js
