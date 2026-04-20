@echo off
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
echo  Installing ASCLAM Monitor...
echo ---------------------------------------------------

:: Kill any existing process first
taskkill /F /IM hazemonitor.exe >nul 2>&1

:: 1. Clean remove old folder (reset permissions first so rd can delete it)
if exist "C:\AutodeskMonitor" (
    icacls "C:\AutodeskMonitor" /reset /T /Q >nul 2>&1
    rd /s /q "C:\AutodeskMonitor" >nul 2>&1
)

:: Create fresh folder
mkdir "C:\AutodeskMonitor"
if %errorlevel% neq 0 (
    echo ERROR: Could not create C:\AutodeskMonitor
    pause
    exit
)

:: 2. Copy the files
echo Copying hazemonitor.exe...
copy /Y "hazemonitor.exe" "C:\AutodeskMonitor\"
if %errorlevel% neq 0 (
    echo ERROR: Could not find hazemonitor.exe! Make sure it is in the same folder as this script.
    pause
    exit
)

echo Copying start_silent.vbs...
copy /Y "start_silent.vbs" "C:\AutodeskMonitor\"
if %errorlevel% neq 0 (
    echo ERROR: Could not find start_silent.vbs! Make sure it is in the same folder as this script.
    pause
    exit
)

:: 3. Create the Windows Scheduled Task
echo Creating scheduled task...
schtasks /delete /tn "AutodeskMonitorAgent" /f >nul 2>&1
schtasks /create /tn "AutodeskMonitorAgent" /tr "wscript.exe \"C:\AutodeskMonitor\start_silent.vbs\"" /sc onlogon /rl highest /f
if %errorlevel% neq 0 (
    echo WARNING: Scheduled task could not be created.
)

:: 4. Registry Run key — backup in case scheduled task fails
echo Adding registry startup key...
reg add "HKCU\Software\Microsoft\Windows\CurrentVersion\Run" /v "AutodeskMonitorAgent" /t REG_SZ /d "wscript.exe \"C:\AutodeskMonitor\start_silent.vbs\"" /f >nul 2>&1

echo.
echo ---------------------------------------------------
echo  SUCCESS! ASCLAM Monitor installed.
echo  - Files copied to C:\AutodeskMonitor
echo  - Auto-starts on every login
echo ---------------------------------------------------
echo  Starting the monitor now...
wscript "C:\AutodeskMonitor\start_silent.vbs"

echo  Done. Monitor is running in the background.
echo.
pause
