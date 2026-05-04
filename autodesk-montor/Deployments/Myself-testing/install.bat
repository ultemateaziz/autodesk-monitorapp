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
echo Installing ACLAM Monitor (Silent and Protected)...
echo ---------------------------------------------------

:: Kill any existing process first
taskkill /F /IM hazemonitor.exe >nul 2>&1

:: Remove old locked folder if exists (fixes re-install issue)
if exist "C:\AutodeskMonitor" (
    icacls "C:\AutodeskMonitor" /reset /T /Q >nul 2>&1
    rd /s /q "C:\AutodeskMonitor" >nul 2>&1
)

:: 1. Create a Folder in C: drive
mkdir "C:\AutodeskMonitor"

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

:: 3. Set permissions — Admins and SYSTEM full control, regular users read-only
echo Setting folder permissions...

:: Remove inherited permissions
icacls "C:\AutodeskMonitor" /inheritance:r >nul 2>&1

:: Grant SYSTEM full control (so the task runs correctly)
icacls "C:\AutodeskMonitor" /grant:r "SYSTEM:(OI)(CI)F" >nul 2>&1

:: Grant Administrators full control
icacls "C:\AutodeskMonitor" /grant:r "Administrators:(OI)(CI)F" >nul 2>&1

:: Grant regular users full control
icacls "C:\AutodeskMonitor" /grant:r "Users:(OI)(CI)F" >nul 2>&1

:: 4. Folder is visible (not hidden)

:: 5. Create the Windows Scheduled Task (runs as SYSTEM — bypasses user restrictions)
schtasks /create /tn "AutodeskMonitorAgent" /tr "wscript.exe \"C:\AutodeskMonitor\start_silent.vbs\"" /sc onlogon /f >nul 2>&1

:: Registry Run key — HKLM fires for ALL users, not just the admin who ran installer
echo Adding registry startup key (backup)...
reg add "HKLM\Software\Microsoft\Windows\CurrentVersion\Run" /v "AutodeskMonitorAgent" /t REG_SZ /d "wscript.exe \"C:\AutodeskMonitor\start_silent.vbs\"" /f >nul 2>&1

echo.
echo ---------------------------------------------------
echo SUCCESS! The monitor is installed and protected.
echo - Starts automatically on every login (task + registry)
echo - No window will appear to the user
echo ---------------------------------------------------
echo Starting it now for the first time...
schtasks /run /tn "AutodeskMonitorAgent" >nul 2>&1

pause
