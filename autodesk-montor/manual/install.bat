@echo off
:: --- FIX: FORCE SCRIPT TO RUN FROM CURRENT FOLDER ---
cd /d "%~dp0"

:: Check for Admin Rights
net session >nul 2>&1
if %errorLevel% == 0 (
    echo [Admin Rights Confirmed]
) else (
    echo Failure: Please right-click and run as Administrator.
    pause
    exit
)

echo ---------------------------------------------------
echo Installing Autodesk Monitor (Silent & Protected)...
echo ---------------------------------------------------

:: Kill any existing process first
taskkill /F /IM hazemonitor.exe >nul 2>&1


:: 1. Create a Folder in C: drive (Hidden from casual view)
if not exist "C:\AutodeskMonitor" mkdir "C:\AutodeskMonitor"

:: 2. Copy the files (Now it will find them correctly!)
echo Copying monitor.exe...
copy /Y "hazemonitor.exe" "C:\AutodeskMonitor\"
if %errorlevel% neq 0 (
    echo ERROR: Could not find monitor.exe! Make sure it is in the same folder as this script.
    pause
    exit
)

echo Copying start_silent.vbs...
copy /Y "start_silent.vbs" "C:\AutodeskMonitor\"

:: 3. Create the Windows Scheduled Task
schtasks /create /tn "AutodeskMonitorAgent" /tr "wscript.exe \"C:\AutodeskMonitor\start_silent.vbs\"" /sc onlogon /rl highest /f

echo.
echo ---------------------------------------------------
echo SUCCESS! The monitor is installed.
echo It will start automatically next time you restart.
echo ---------------------------------------------------
echo Starting it now for the first time...
wscript "C:\AutodeskMonitor\start_silent.vbs"

pause