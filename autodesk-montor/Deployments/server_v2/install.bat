@echo off
cd /d "%~dp0"

:: ─────────────────────────────────────────────
::  HazeMonitor v2 Installer
::  Uses NSSM to run as a Windows Service
::  Starts automatically on boot, no user login needed
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
set EXE_NAME=hazemonitor.exe
set NSSM=%INSTALL_DIR%\nssm.exe

echo.
echo ===================================================
echo  Installing HazeMonitor Service v2
echo ===================================================

:: Kill any old running instance
taskkill /F /IM %EXE_NAME% >nul 2>&1
taskkill /F /IM wscript.exe >nul 2>&1

:: Remove old Task Scheduler entry if it exists
schtasks /delete /tn "AutodeskMonitorAgent" /f >nul 2>&1

:: Create install folder
if not exist "%INSTALL_DIR%" mkdir "%INSTALL_DIR%"

:: Copy files
echo Copying hazemonitor.exe...
copy /Y "%~dp0%EXE_NAME%" "%INSTALL_DIR%\%EXE_NAME%"
if %errorlevel% neq 0 (
    echo ERROR: hazemonitor.exe not found in this folder!
    pause
    exit /b 1
)

echo Copying nssm.exe...
copy /Y "%~dp0nssm.exe" "%INSTALL_DIR%\nssm.exe"
if %errorlevel% neq 0 (
    echo ERROR: nssm.exe not found in this folder!
    echo Download it from: https://nssm.cc/download
    pause
    exit /b 1
)

:: Remove old service if it already exists
%NSSM% stop %SERVICE_NAME% >nul 2>&1
%NSSM% remove %SERVICE_NAME% confirm >nul 2>&1

:: Install as Windows Service
echo Installing Windows Service...
%NSSM% install %SERVICE_NAME% "%INSTALL_DIR%\%EXE_NAME%"
%NSSM% set %SERVICE_NAME% DisplayName "Autodesk Monitor Agent"
%NSSM% set %SERVICE_NAME% Description "Monitors Autodesk software usage and reports to dashboard."
%NSSM% set %SERVICE_NAME% Start SERVICE_AUTO_START
%NSSM% set %SERVICE_NAME% AppDirectory "%INSTALL_DIR%"
%NSSM% set %SERVICE_NAME% AppRestartDelay 5000
%NSSM% set %SERVICE_NAME% AppStdout "%INSTALL_DIR%\monitor.log"
%NSSM% set %SERVICE_NAME% AppStderr "%INSTALL_DIR%\error.log"

:: Start the service now
echo Starting service...
net start %SERVICE_NAME%

echo.
echo ===================================================
echo  SUCCESS! HazeMonitor is now running as a service.
echo  It will start automatically on every Windows boot.
echo  No login required.
echo ===================================================
echo.
pause
