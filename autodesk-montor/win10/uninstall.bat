@echo off
cd /d "%~dp0"

:: -------------------------------------------------------
:: Check for Admin Rights
:: -------------------------------------------------------
net session >nul 2>&1
if %errorLevel% neq 0 (
    echo.
    echo  ERROR: Please right-click uninstall.bat and select
    echo         "Run as Administrator"
    echo.
    pause
    exit
)

echo.
echo  =====================================================
echo   Uninstalling ASCLAM Monitor - Windows 10 Edition
echo  =====================================================
echo.

:: Stop the running process
echo  Stopping monitor process...
taskkill /F /IM hazemonitor.exe >nul 2>&1
taskkill /F /IM wscript.exe >nul 2>&1

:: Remove scheduled task
echo  Removing scheduled task...
schtasks /delete /tn "AutodeskMonitorAgent" /f >nul 2>&1

:: Remove registry startup key
echo  Removing registry startup key...
reg delete "HKCU\Software\Microsoft\Windows\CurrentVersion\Run" ^
    /v "AutodeskMonitorAgent" /f >nul 2>&1

:: Remove installation folder
echo  Removing C:\AutodeskMonitor folder...
if exist "C:\AutodeskMonitor" (
    icacls "C:\AutodeskMonitor" /reset /T /Q >nul 2>&1
    rd /s /q "C:\AutodeskMonitor" >nul 2>&1
)

if exist "C:\AutodeskMonitor" (
    echo  WARNING: Folder could not be fully removed.
    echo           You may need to restart and delete it manually.
) else (
    echo  Folder removed successfully.
)

echo.
echo  =====================================================
echo   ASCLAM Monitor has been uninstalled.
echo  =====================================================
echo.
pause
