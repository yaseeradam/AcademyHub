@echo off
echo MyAcademy MySQL Setup Helper
echo =============================
echo.
echo This script will help you set up MySQL for MyAcademy.
echo.
echo STEP 1: Open Laragon Control Panel
echo - Right-click on Laragon icon in system tray
echo - Click "Start All" to ensure MySQL is running
echo.
echo STEP 2: Open HeidiSQL or phpMyAdmin
echo - In Laragon, click "Database" button
echo - This should open HeidiSQL
echo.
echo STEP 3: Connect to MySQL
echo - Host: 127.0.0.1 or localhost
echo - User: root
echo - Password: (try empty, "root", or check Laragon settings)
echo - Port: 3306
echo.
echo STEP 4: Create Database and User
echo - Run these SQL commands:
echo.
echo CREATE DATABASE IF NOT EXISTS myacademy;
echo CREATE USER IF NOT EXISTS 'myacademy'@'localhost' IDENTIFIED BY 'Myacademy@2026!';
echo GRANT ALL PRIVILEGES ON myacademy.* TO 'myacademy'@'localhost';
echo FLUSH PRIVILEGES;
echo.
echo STEP 5: Test Connection
echo - Run: php artisan migrate --force
echo.
echo If you're still having issues:
echo 1. Check Laragon's MySQL password in Laragon settings
echo 2. Try connecting with different passwords (empty, "root", "password")
echo 3. Restart Laragon completely
echo.
pause