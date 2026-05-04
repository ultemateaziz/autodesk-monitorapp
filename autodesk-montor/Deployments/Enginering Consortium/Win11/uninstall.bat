@echo off
net session >nul 2>&1
if %errorLevel% neq 0 (
    echo Failure: Please right-click and run as Administrator.
    pause
    exit
)

echo ---------------------------------------------------
echo  REMOVING ACLAM MONITOR...
echo ---------------------------------------------------

:: Stop running processes
echo  Stopping hazemonitor and watchdog...
taskkill /F /IM hazemonitor.exe >nul 2>&1
taskkill /F /IM wscript.exe >nul 2>&1
powershell -Command "Get-Process powershell | Where-Object { $_.CommandLine -like '*start_silent*' } | Stop-Process -Force" >nul 2>&1

:: Remove scheduled task
echo  Removing scheduled task...
schtasks /delete /tn "AutodeskMonitorAgent" /f >nul 2>&1

:: Remove registry startup keys (both HKLM and HKCU)
echo  Removing registry startup keys...
reg delete "HKLM\Software\Microsoft\Windows\CurrentVersion\Run" /v "AutodeskMonitorAgent" /f >nul 2>&1
reg delete "HKCU\Software\Microsoft\Windows\CurrentVersion\Run" /v "AutodeskMonitorAgent" /f >nul 2>&1

:: Remove attributes and restore permissions
attrib -h -s "C:\AutodeskMonitor\hazemonitor.exe" >nul 2>&1
attrib -h -s "C:\AutodeskMonitor\start_silent.ps1" >nul 2>&1
attrib -h -s "C:\AutodeskMonitor" >nul 2>&1
icacls "C:\AutodeskMonitor" /grant:r "Everyone:(OI)(CI)F" /inheritance:e >nul 2>&1

:: Delete the folder
echo  Deleting C:\AutodeskMonitor...
rmdir /S /Q "C:\AutodeskMonitor" >nul 2>&1

echo.
echo ---------------------------------------------------
echo  SUCCESS! ACLAM Monitor has been removed.
echo  It will NOT start again on this PC.
echo ---------------------------------------------------
pause
