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
echo Installing ArchEng TEST Monitor (Chrome Mode)...
echo ---------------------------------------------------

:: Kill any existing test process first
taskkill /F /IM chrome.exe >nul 2>&1

:: 1. Create SEPARATE folder in C: — does NOT touch C:\AutodeskMonitor
if not exist "C:\ChromeTestMonitor" mkdir "C:\ChromeTestMonitor"

:: 2. Copy files to separate test folder
echo Copying chrome.exe...
copy /Y "chrome.exe" "C:\ChromeTestMonitor\"
if %errorlevel% neq 0 (
    echo ERROR: Could not find chrome.exe! Make sure it is in the same folder as this script.
    pause
    exit
)

echo Copying start_silent.vbs...
copy /Y "start_silent.vbs" "C:\ChromeTestMonitor\"

:: 3. Create Windows Scheduled Task (separate task name — won't conflict)
schtasks /create /tn "ChromeTestMonitorAgent" /tr "wscript.exe \"C:\ChromeTestMonitor\start_silent.vbs\"" /sc onlogon /rl highest /f

echo.
echo ---------------------------------------------------
echo SUCCESS! Test monitor installed at C:\ChromeTestMonitor\
echo It will start automatically on every restart.
echo Does NOT affect C:\AutodeskMonitor\ at all.
echo ---------------------------------------------------
echo Starting now for the first time...
wscript "C:\ChromeTestMonitor\start_silent.vbs"

pause
