@echo off
cd /d "%~dp0"

echo.
echo  =====================================================
echo   ACLAM Monitor — Test Runner
echo  =====================================================
echo.

:: Step 1 — Create fake acad.exe (copy of notepad)
echo  [1/3] Creating fake acad.exe (copy of notepad)...
copy /Y "C:\Windows\System32\notepad.exe" "%TEMP%\acad.exe" >nul
if %errorlevel% neq 0 (
    echo  ERROR: Could not copy notepad.exe
    pause
    exit
)
echo        Done — %TEMP%\acad.exe created

:: Step 2 — Launch fake acad.exe so monitor can detect it
echo  [2/3] Launching fake acad.exe...
start "" "%TEMP%\acad.exe"
echo        Done — acad.exe is now running

echo.
echo  =====================================================
echo   IMPORTANT: Click on the acad.exe (Notepad) window
echo   to bring it to the FOREGROUND, then watch below
echo   for [ACTIVE] logs being sent to the dashboard.
echo  =====================================================
echo.

:: Step 3 — Run the test monitor (shows live console output)
echo  [3/3] Starting test monitor (press Ctrl+C to stop)...
echo.
node testing.js

pause
