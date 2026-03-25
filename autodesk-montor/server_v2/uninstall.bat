@echo off
cd /d "%~dp0"

:: ─────────────────────────────────────────────
::  HazeMonitor v2 Uninstaller
:: ─────────────────────────────────────────────

:: Check for Admin Rights
net session >nul 2>&1
if %errorLevel% neq 0 (
    echo ERROR: Please right-click and Run as Administrator.
    pause
    exit /b 1
)

set INSTALL_DIR=C:\AutodeskMonitor
set SERVICE_NAME=HazeMonitor

echo.
echo ===================================================
echo  Removing HazeMonitor Service v2
echo ===================================================

:: Stop and remove the service
echo Stopping service...
net stop %SERVICE_NAME% >nul 2>&1

echo Removing service...
"%INSTALL_DIR%\nssm.exe" remove %SERVICE_NAME% confirm >nul 2>&1

:: Kill any leftover process
taskkill /F /IM hazemonitor.exe >nul 2>&1
taskkill /F /IM wscript.exe >nul 2>&1

:: Also remove old Task Scheduler entry if it exists
schtasks /delete /tn "AutodeskMonitorAgent" /f >nul 2>&1

:: Delete install folder
echo Deleting files...
rmdir /S /Q "%INSTALL_DIR%"

echo.
echo ===================================================
echo  SUCCESS! HazeMonitor has been completely removed.
echo  It will NOT start again on reboot.
echo ===================================================
echo.
pause
