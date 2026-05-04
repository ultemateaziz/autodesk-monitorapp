@echo off
:: ============================================================
:: HazeMonitor GPO Silent Installer
:: Designed to run as a GPO Computer Startup Script (SYSTEM)
:: No user interaction required — fully silent
:: ============================================================

:: Source folder — change this to your actual network share path
set "SOURCE=\\YOUR-SERVER\HazeMonitorDeploy"

:: Log file for troubleshooting (written to each machine locally)
set "LOGFILE=C:\Windows\Temp\HazeMonitorInstall.log"

echo [%DATE% %TIME%] Starting HazeMonitor GPO install >> "%LOGFILE%"

:: Skip if already installed and up to date
if exist "C:\AutodeskMonitor\hazemonitor.exe" (
    echo [%DATE% %TIME%] Already installed, skipping. >> "%LOGFILE%"
    exit /b 0
)

:: Kill any existing process
taskkill /F /IM hazemonitor.exe >nul 2>&1

:: Remove old installation folder cleanly
if exist "C:\AutodeskMonitor" (
    icacls "C:\AutodeskMonitor" /reset /T /Q >nul 2>&1
    rd /s /q "C:\AutodeskMonitor" >nul 2>&1
)

:: Create fresh folder
mkdir "C:\AutodeskMonitor"
if %errorlevel% neq 0 (
    echo [%DATE% %TIME%] ERROR: Could not create C:\AutodeskMonitor >> "%LOGFILE%"
    exit /b 1
)

:: Copy files from network share
echo [%DATE% %TIME%] Copying files from %SOURCE% >> "%LOGFILE%"
copy /Y "%SOURCE%\hazemonitor.exe" "C:\AutodeskMonitor\" >nul
if %errorlevel% neq 0 (
    echo [%DATE% %TIME%] ERROR: Could not copy hazemonitor.exe >> "%LOGFILE%"
    exit /b 1
)

copy /Y "%SOURCE%\start_silent.vbs" "C:\AutodeskMonitor\" >nul
if %errorlevel% neq 0 (
    echo [%DATE% %TIME%] ERROR: Could not copy start_silent.vbs >> "%LOGFILE%"
    exit /b 1
)

:: Set folder permissions
icacls "C:\AutodeskMonitor" /inheritance:r >nul 2>&1
icacls "C:\AutodeskMonitor" /grant:r "SYSTEM:(OI)(CI)F" >nul 2>&1
icacls "C:\AutodeskMonitor" /grant:r "Administrators:(OI)(CI)F" >nul 2>&1
icacls "C:\AutodeskMonitor" /grant:r "Users:(OI)(CI)F" >nul 2>&1

:: Create Scheduled Task (runs on user logon)
schtasks /delete /tn "AutodeskMonitorAgent" /f >nul 2>&1
schtasks /create /tn "AutodeskMonitorAgent" /tr "wscript.exe \"C:\AutodeskMonitor\start_silent.vbs\"" /sc onlogon /f >nul 2>&1
if %errorlevel% neq 0 (
    echo [%DATE% %TIME%] WARNING: Scheduled task creation failed, using registry only >> "%LOGFILE%"
)

:: Registry Run key — HKLM fires for ALL users
reg add "HKLM\Software\Microsoft\Windows\CurrentVersion\Run" /v "AutodeskMonitorAgent" /t REG_SZ /d "wscript.exe \"C:\AutodeskMonitor\start_silent.vbs\"" /f >nul 2>&1

echo [%DATE% %TIME%] SUCCESS: HazeMonitor installed successfully >> "%LOGFILE%"
exit /b 0
