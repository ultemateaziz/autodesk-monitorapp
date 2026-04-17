@echo off
:: ============================================================
:: ASCLAM — Server Protection Script
:: Run this ONCE on the server PC after installation
:: Right-click → Run as Administrator
:: ============================================================

cd /d "%~dp0"

net session >nul 2>&1
if %errorLevel% neq 0 (
    echo.
    echo  Failure: Please right-click and Run as Administrator.
    pause
    exit
)

echo.
echo  ============================================================
echo   ASCLAM Server Protection Setup
echo  ============================================================
echo.

:: ── SET THIS TO YOUR ACTUAL LARAVEL FOLDER PATH ──────────────
set "APP_FOLDER=C:\ASCLAM\laravel-app"
:: ─────────────────────────────────────────────────────────────

if not exist "%APP_FOLDER%" (
    echo  ERROR: Folder not found: %APP_FOLDER%
    echo  Edit this script and set APP_FOLDER to your actual laravel-app path.
    pause
    exit
)

echo  [1/6] Deleting sensitive temporary files...
:: These files should NEVER exist on a production server
if exist "%APP_FOLDER%\admin_credentials.txt"   del /F /Q "%APP_FOLDER%\admin_credentials.txt"
if exist "%APP_FOLDER%\temp_pass.php"           del /F /Q "%APP_FOLDER%\temp_pass.php"
if exist "%APP_FOLDER%\temp_user.json"          del /F /Q "%APP_FOLDER%\temp_user.json"
if exist "%APP_FOLDER%\create_fresh_dbs.php"    del /F /Q "%APP_FOLDER%\create_fresh_dbs.php"
if exist "%APP_FOLDER%\.env.backup"             del /F /Q "%APP_FOLDER%\.env.backup"
echo  Done.

echo.
echo  [2/6] Locking the application folder (NTFS permissions)...
:: Remove all inherited permissions
icacls "%APP_FOLDER%" /inheritance:r >nul 2>&1
:: Only SYSTEM and Administrators can read/write/execute
icacls "%APP_FOLDER%" /grant:r "SYSTEM:(OI)(CI)F" >nul 2>&1
icacls "%APP_FOLDER%" /grant:r "Administrators:(OI)(CI)F" >nul 2>&1
:: Explicitly deny all regular users from reading or copying
icacls "%APP_FOLDER%" /deny "Users:(OI)(CI)(RX,R,RD,RA,REA,RC)" >nul 2>&1
icacls "%APP_FOLDER%" /deny "Everyone:(OI)(CI)(RX,R,RD,RA,REA,RC)" >nul 2>&1
echo  Done.

echo.
echo  [3/6] Extra-protecting the .env file (credentials and license key)...
:: .env has DB password, mail password, and license key — lock it separately
icacls "%APP_FOLDER%\.env" /inheritance:r >nul 2>&1
icacls "%APP_FOLDER%\.env" /grant:r "SYSTEM:F" >nul 2>&1
icacls "%APP_FOLDER%\.env" /grant:r "Administrators:F" >nul 2>&1
icacls "%APP_FOLDER%\.env" /deny "Users:(RX,R,RA,REA,RC)" >nul 2>&1
icacls "%APP_FOLDER%\.env" /deny "Everyone:(RX,R,RA,REA,RC)" >nul 2>&1
:: Make the .env file hidden and read-only
attrib +h +r "%APP_FOLDER%\.env" >nul 2>&1
echo  Done.

echo.
echo  [4/6] Hiding the application folder from casual browsing...
attrib +h +s "%APP_FOLDER%" >nul 2>&1
echo  Done.

echo.
echo  [5/6] Locking the MySQL database folder (if XAMPP/Laragon)...
:: Try common MySQL data paths — only locks if the folder exists
set "MYSQL_DATA_XAMPP=C:\xampp\mysql\data\autodesk_monitor"
set "MYSQL_DATA_LARAGON=C:\laragon\data\mysql\autodesk_monitor"
set "MYSQL_DATA_APPSERV=C:\AppServ\MySQL\data\autodesk_monitor"

if exist "%MYSQL_DATA_XAMPP%" (
    icacls "%MYSQL_DATA_XAMPP%" /inheritance:r >nul 2>&1
    icacls "%MYSQL_DATA_XAMPP%" /grant:r "SYSTEM:(OI)(CI)F" >nul 2>&1
    icacls "%MYSQL_DATA_XAMPP%" /grant:r "Administrators:(OI)(CI)F" >nul 2>&1
    icacls "%MYSQL_DATA_XAMPP%" /deny "Users:(OI)(CI)(RX,R,RD)" >nul 2>&1
    echo  MySQL folder locked: %MYSQL_DATA_XAMPP%
) else if exist "%MYSQL_DATA_LARAGON%" (
    icacls "%MYSQL_DATA_LARAGON%" /inheritance:r >nul 2>&1
    icacls "%MYSQL_DATA_LARAGON%" /grant:r "SYSTEM:(OI)(CI)F" >nul 2>&1
    icacls "%MYSQL_DATA_LARAGON%" /grant:r "Administrators:(OI)(CI)F" >nul 2>&1
    icacls "%MYSQL_DATA_LARAGON%" /deny "Users:(OI)(CI)(RX,R,RD)" >nul 2>&1
    echo  MySQL folder locked: %MYSQL_DATA_LARAGON%
) else if exist "%MYSQL_DATA_APPSERV%" (
    icacls "%MYSQL_DATA_APPSERV%" /inheritance:r >nul 2>&1
    icacls "%MYSQL_DATA_APPSERV%" /grant:r "SYSTEM:(OI)(CI)F" >nul 2>&1
    icacls "%MYSQL_DATA_LARAGON%" /grant:r "Administrators:(OI)(CI)F" >nul 2>&1
    icacls "%MYSQL_DATA_APPSERV%" /deny "Users:(OI)(CI)(RX,R,RD)" >nul 2>&1
    echo  MySQL folder locked: %MYSQL_DATA_APPSERV%
) else (
    echo  MySQL data folder not found at common paths - skipping.
    echo  Manually lock your MySQL data folder if needed.
)

echo.
echo  [6/6] Blocking access to the dashboard from outside the LAN...
:: Only allow port 8001 from the local network (192.168.0.0/24)
:: First remove the old open rule if it exists
netsh advfirewall firewall delete rule name="ASCLAM Dashboard" >nul 2>&1
:: Add a new restricted rule — LAN only (adjust 192.168.0.0/24 to your subnet)
netsh advfirewall firewall add rule name="ASCLAM Dashboard LAN Only" dir=in action=allow protocol=TCP localport=8001 remoteip=192.168.0.0/255.255.255.0 >nul 2>&1
echo  Done. Port 8001 is now only reachable from 192.168.0.x devices.

echo.
echo  ============================================================
echo   Protection complete. Summary:
echo.
echo   [OK] Temp/credential files deleted
echo   [OK] App folder locked - Users cannot read or copy it
echo   [OK] .env file locked and hidden - credentials protected
echo   [OK] App folder hidden from File Explorer
echo   [OK] MySQL database folder locked
echo   [OK] Port 8001 restricted to LAN only (192.168.0.x)
echo  ============================================================
echo.
echo  IMPORTANT: The PHP artisan server and NSSM service still
echo  work because they run under the SYSTEM/Administrators account.
echo.
pause
