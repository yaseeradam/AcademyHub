@echo off
echo Granting write permission to hosts file...
icacls "C:\Windows\System32\drivers\etc\hosts" /grant "Everyone:(M)"
if %errorlevel% == 0 (
    echo SUCCESS! PHP can now write to the hosts file automatically.
    echo You only need to run this script once.
) else (
    echo FAILED. Make sure you are running this as Administrator.
)
pause
