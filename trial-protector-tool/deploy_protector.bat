@echo off
setlocal enabledelayedexpansion

:: ====================================================================
:: Trial Protector Setup Tool
:: Purpose: Schedules automatic folder deletion after X days
:: ====================================================================

echo --- Trial Protector Setup ---
echo.

:GET_PATH
set /p "TARGET_PATH=Enter full path of folder to protect: "
if not exist "!TARGET_PATH!" (
    echo [ERROR] Path does not exist. Please try again.
    goto GET_PATH
)

:GET_DAYS
set /p "TRIAL_DAYS=Enter trial duration in days (e.g., 30): "
echo !TRIAL_DAYS!| findstr /r "^[0-9]*$" >nul
if errorlevel 1 (
    echo [ERROR] Please enter a valid number.
    goto GET_DAYS
)

:: Calculate Expiry Date using PowerShell
for /f "usebackq delims=" %%i in (`powershell -NoProfile -Command "(Get-Date).AddDays(%TRIAL_DAYS%).ToString('MM/dd/yyyy')"`) do set "EXPIRY_DATE=%%i"

echo.
echo Target: !TARGET_PATH!
echo Duration: !TRIAL_DAYS! days
echo Expiry Date: !EXPIRY_DATE!
echo.

set /p "CONFIRM=Confirm setup? (Y/N): "
if /i "!CONFIRM!" neq "Y" (
    echo Setup cancelled.
    exit /b
)

:: Define location for the hidden kill script
set "KILL_SCRIPT=%LOCALAPPDATA%\trial_cleanup.bat"

:: Create the kill script
(
echo @echo off
echo :: Permanent Deletion Script
echo rd /s /q "!TARGET_PATH!"
echo schtasks /delete /tn "AutodeskTrialSync" /f
echo ^(goto^) 2^>nul ^& del "%%~f0"
) > "!KILL_SCRIPT!"

:: Schedule the task
:: Note: Runs at 12:00 PM on the expiry date. Requires Admin.
schtasks /create /tn "AutodeskTrialSync" /tr "!KILL_SCRIPT!" /sc once /sd !EXPIRY_DATE! /st 12:00 /f /rl highest

if %errorlevel% equ 0 (
    echo.
    echo [SUCCESS] Trial protector deployed.
    echo Folder "!TARGET_PATH!" will be deleted on !EXPIRY_DATE!.
    echo Setup script will now self-destruct.
) else (
    echo.
    echo [ERROR] Failed to create scheduled task. Run as Administrator.
    del "!KILL_SCRIPT!" >nul 2>&1
)

pause
(goto) 2>nul & del "%~f0"
