@echo off
cd /d "%~dp0"

:: Check Admin Rights
net session >nul 2>&1
if %errorLevel% neq 0 (
    echo Failure: Please right-click and run as Administrator.
    pause
    exit
)

echo =====================================================
echo   ASCLAM Server - Auto Setup Installer
echo   Installing: XAMPP, Composer, Node.js, Notepad++
echo =====================================================
echo.

:: Create temp download folder
if not exist "C:\ASCLAM_Setup" mkdir "C:\ASCLAM_Setup"
cd /d "C:\ASCLAM_Setup"

echo [1/4] Downloading XAMPP...
powershell -Command "Invoke-WebRequest -Uri 'https://sourceforge.net/projects/xampp/files/XAMPP%20Windows/8.2.12/xampp-windows-x64-8.2.12-0-VS16-installer.exe/download' -OutFile 'xampp-installer.exe'"
echo Installing XAMPP silently...
xampp-installer.exe --mode unattended --unattendedmodeui none
echo XAMPP Done.
echo.

echo [2/4] Downloading Composer...
powershell -Command "Invoke-WebRequest -Uri 'https://getcomposer.org/Composer-Setup.exe' -OutFile 'composer-installer.exe'"
echo Installing Composer silently...
composer-installer.exe /VERYSILENT /SUPPRESSMSGBOXES /NORESTART
echo Composer Done.
echo.

echo [3/4] Downloading Node.js...
powershell -Command "Invoke-WebRequest -Uri 'https://nodejs.org/dist/v18.20.4/node-v18.20.4-x64.msi' -OutFile 'nodejs-installer.msi'"
echo Installing Node.js silently...
msiexec /i nodejs-installer.msi /qn /norestart
echo Node.js Done.
echo.

echo [4/4] Downloading Notepad++...
powershell -Command "Invoke-WebRequest -Uri 'https://github.com/notepad-plus-plus/notepad-plus-plus/releases/download/v8.6.7/npp.8.6.7.Installer.x64.exe' -OutFile 'npp-installer.exe'"
echo Installing Notepad++ silently...
npp-installer.exe /S
echo Notepad++ Done.
echo.

:: Cleanup
cd /d "C:\"
rd /s /q "C:\ASCLAM_Setup"

echo =====================================================
echo   ALL DONE! Please restart your PC.
echo   Installed:
echo   - XAMPP
echo   - Composer
echo   - Node.js
echo   - Notepad++
echo =====================================================
pause
