@echo off
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
echo REMOVING ASCLAM MONITOR...
echo ---------------------------------------------------

:: 1. Force kill the running process
echo Stopping hazemonitor.exe...
taskkill /F /IM hazemonitor.exe >nul 2>&1
taskkill /F /IM wscript.exe >nul 2>&1

:: 2. Delete the Windows Scheduled Task
echo Removing Auto-Start Schedule...
schtasks /delete /tn "AutodeskMonitorAgent" /f >nul 2>&1

:: 3. Remove hidden and system attributes so folder can be deleted
attrib -h -s "C:\AutodeskMonitor\hazemonitor.exe" >nul 2>&1
attrib -h -s "C:\AutodeskMonitor\start_silent.vbs" >nul 2>&1
attrib -h -s "C:\AutodeskMonitor" >nul 2>&1

:: 4. Restore full permissions so rmdir can delete it
icacls "C:\AutodeskMonitor" /grant:r "Everyone:(OI)(CI)F" /inheritance:e >nul 2>&1
icacls "C:\AutodeskMonitor" /remove:d "Users" >nul 2>&1
icacls "C:\AutodeskMonitor" /remove:d "Everyone" >nul 2>&1

:: 5. Delete the Files
echo Deleting files in C:\AutodeskMonitor...
rmdir /S /Q "C:\AutodeskMonitor"

echo.
echo ---------------------------------------------------
echo SUCCESS! The application is permanently removed.
echo It will NOT start again.
echo ---------------------------------------------------
pause
