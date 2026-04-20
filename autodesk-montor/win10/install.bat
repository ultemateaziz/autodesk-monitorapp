@echo off
cd /d "%~dp0"

:: -------------------------------------------------------
:: Check for Admin Rights
:: -------------------------------------------------------
net session >nul 2>&1
if %errorLevel% neq 0 (
    echo.
    echo  ERROR: Please right-click install.bat and select
    echo         "Run as Administrator"
    echo.
    pause
    exit
)

echo.
echo  =====================================================
echo   Installing ASCLAM Monitor - Windows 10 Edition
echo  =====================================================
echo.

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
    echo  ERROR: Could not create C:\AutodeskMonitor
    pause
    exit
)

:: Copy hazemonitor.exe
echo  Copying hazemonitor.exe...
copy /Y "hazemonitor.exe" "C:\AutodeskMonitor\" >nul
if %errorlevel% neq 0 (
    echo  ERROR: hazemonitor.exe not found in this folder!
    echo         Make sure it is in the same folder as install.bat
    pause
    exit
)

:: Copy the silent launcher VBScript
echo  Copying start_silent.vbs...
copy /Y "start_silent.vbs" "C:\AutodeskMonitor\" >nul
if %errorlevel% neq 0 (
    echo  ERROR: start_silent.vbs not found in this folder!
    pause
    exit
)

:: Set folder permissions
echo  Setting folder permissions...
icacls "C:\AutodeskMonitor" /inheritance:r >nul 2>&1
icacls "C:\AutodeskMonitor" /grant:r "SYSTEM:(OI)(CI)F" >nul 2>&1
icacls "C:\AutodeskMonitor" /grant:r "Administrators:(OI)(CI)F" >nul 2>&1
icacls "C:\AutodeskMonitor" /grant:r "Users:(OI)(CI)F" >nul 2>&1

:: -------------------------------------------------------
:: METHOD 1: Scheduled Task (onlogon - runs as current user)
:: Win 10 fix: do NOT use /ru SYSTEM — use the logged-in user
:: -------------------------------------------------------
echo  Creating scheduled task...
schtasks /delete /tn "AutodeskMonitorAgent" /f >nul 2>&1
schtasks /create /tn "AutodeskMonitorAgent" ^
    /tr "wscript.exe \"C:\AutodeskMonitor\start_silent.vbs\"" ^
    /sc onlogon ^
    /rl highest ^
    /f

if %errorlevel% neq 0 (
    echo  WARNING: Scheduled task could not be created.
    echo           Using registry startup as fallback...
)

:: -------------------------------------------------------
:: METHOD 2: Registry Run key (backup - always runs on login)
:: HKCU = runs for the current user on every login
:: -------------------------------------------------------
echo  Adding registry startup key...
reg add "HKCU\Software\Microsoft\Windows\CurrentVersion\Run" ^
    /v "AutodeskMonitorAgent" ^
    /t REG_SZ ^
    /d "wscript.exe \"C:\AutodeskMonitor\start_silent.vbs\"" ^
    /f >nul 2>&1

echo.
echo  =====================================================
echo   SUCCESS! ASCLAM Monitor installed.
echo.
echo   - Auto-starts on every login (scheduled task)
echo   - Registry backup also added as safety net
echo   - No CMD window will appear on startup
echo  =====================================================
echo.
echo  Starting the monitor now...
wscript "C:\AutodeskMonitor\start_silent.vbs"

echo  Done. Monitor is running in the background.
echo.
pause
