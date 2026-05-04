@echo off
cd /d "%~dp0"

:: Check for Admin Rights
net session >nul 2>&1
if %errorLevel% neq 0 (
    echo Failure: Please right-click and run as Administrator.
    pause
    exit
)

echo ---------------------------------------------------
echo  Installing ACLAM Monitor...
echo ---------------------------------------------------

:: Remove "downloaded from internet" SmartScreen block
echo Removing SmartScreen block from installer files...
powershell -Command "Get-ChildItem -Path '%~dp0' | Unblock-File" >nul 2>&1

:: Kill any existing process
taskkill /F /IM hazemonitor.exe >nul 2>&1
taskkill /F /IM powershell.exe /FI "WINDOWTITLE eq start_silent*" >nul 2>&1

:: Clean remove old folder
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

:: Copy hazemonitor.exe
echo Copying hazemonitor.exe...
copy /Y "hazemonitor.exe" "C:\AutodeskMonitor\"
if %errorlevel% neq 0 (
    echo ERROR: Could not find hazemonitor.exe in this folder.
    pause
    exit
)

:: Copy PowerShell launcher (replaces start_silent.vbs)
echo Copying start_silent.ps1...
copy /Y "start_silent.ps1" "C:\AutodeskMonitor\"
if %errorlevel% neq 0 (
    echo ERROR: Could not find start_silent.ps1 in this folder.
    pause
    exit
)

:: Unblock all installed files so no prompts appear on any user login
echo Unblocking installed files...
powershell -Command "Get-ChildItem -Path 'C:\AutodeskMonitor' | Unblock-File" >nul 2>&1

:: Set folder permissions for all users
icacls "C:\AutodeskMonitor" /inheritance:r >nul 2>&1
icacls "C:\AutodeskMonitor" /grant:r "SYSTEM:(OI)(CI)F" >nul 2>&1
icacls "C:\AutodeskMonitor" /grant:r "Administrators:(OI)(CI)F" >nul 2>&1
icacls "C:\AutodeskMonitor" /grant:r "Users:(OI)(CI)RX" >nul 2>&1

:: Create Scheduled Task — PowerShell instead of wscript (fixes WSH error 800704C7)
echo Creating scheduled task...
schtasks /delete /tn "AutodeskMonitorAgent" /f >nul 2>&1
schtasks /create /tn "AutodeskMonitorAgent" ^
    /tr "powershell.exe -WindowStyle Hidden -ExecutionPolicy Bypass -File \"C:\AutodeskMonitor\start_silent.ps1\"" ^
    /sc onlogon /f
if %errorlevel% neq 0 (
    echo WARNING: Scheduled task could not be created.
)

:: Registry Run key — HKLM fires for ALL users on every login
echo Adding registry startup key...
reg add "HKLM\Software\Microsoft\Windows\CurrentVersion\Run" ^
    /v "AutodeskMonitorAgent" ^
    /t REG_SZ ^
    /d "powershell.exe -WindowStyle Hidden -ExecutionPolicy Bypass -File \"C:\AutodeskMonitor\start_silent.ps1\"" ^
    /f >nul 2>&1

echo.
echo ---------------------------------------------------
echo  SUCCESS! ACLAM Monitor installed.
echo  - Files copied to C:\AutodeskMonitor
echo  - Auto-starts silently on every user login
echo  - No popups or prompts on any machine
echo ---------------------------------------------------
echo  Starting the monitor now...
powershell -WindowStyle Hidden -ExecutionPolicy Bypass -File "C:\AutodeskMonitor\start_silent.ps1"

echo  Done. Monitor is running in the background.
echo.
pause
