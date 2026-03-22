@echo off
:: ============================================================
:: Autodesk Monitor Agent - Group Policy Silent Installer
:: Runs as SYSTEM via GP Startup Script - No UI, No pause
:: ============================================================

:: Skip if already installed — prevents re-running on every boot
if exist "C:\AutodeskMonitor\hazemonitor.exe" goto :EOF

:: Kill any existing process silently
taskkill /F /IM hazemonitor.exe >nul 2>&1

:: Create install folder
if not exist "C:\AutodeskMonitor" mkdir "C:\AutodeskMonitor" >nul 2>&1

:: Copy files from network share
xcopy /Y /Q "\\192.168.0.200\MonitorDeploy\hazemonitor.exe" "C:\AutodeskMonitor\" >nul 2>&1
xcopy /Y /Q "\\192.168.0.200\MonitorDeploy\start_silent.vbs" "C:\AutodeskMonitor\" >nul 2>&1

:: Register Scheduled Task — fires on every user login, runs as SYSTEM
schtasks /create /tn "AutodeskMonitorAgent" /tr "wscript.exe \"C:\AutodeskMonitor\start_silent.vbs\"" /sc onlogon /ru SYSTEM /rl highest /f >nul 2>&1

:: Log install time for audit
echo %date% %time% - Installed on %computername% >> "C:\AutodeskMonitor\install.log"

:EOF
