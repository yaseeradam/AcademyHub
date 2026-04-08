@echo off
echo ========================================
echo MyAcademy WhatsApp Bot - Restart
echo ========================================
echo.

echo Stopping any running bot processes...
taskkill /F /IM node.exe 2>nul

echo.
echo Starting bot (keeping existing session)...
echo.
node index.js
