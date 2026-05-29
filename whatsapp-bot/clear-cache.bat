@echo off
echo 🧹 Clearing HubGenie WhatsApp session cache...
echo.

if exist "%~dp0.wwebjs_auth" (
    rmdir /s /q "%~dp0.wwebjs_auth"
    echo ✅ Deleted: .wwebjs_auth
) else (
    echo ℹ️ Already clean: .wwebjs_auth
)

if exist "%~dp0.wwebjs_cache" (
    rmdir /s /q "%~dp0.wwebjs_cache"
    echo ✅ Deleted: .wwebjs_cache
) else (
    echo ℹ️ Already clean: .wwebjs_cache
)

echo.
echo 🎉 Cache clearing completed successfully!
pause
