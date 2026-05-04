@echo off
title ArchEng Pro — Monitor Agent

:: Check if license.json exists
IF NOT EXIST "%~dp0license.json" (
    echo.
    echo  [SETUP] No license found. Opening Activation Tool...
    echo.
    node "%~dp0activate.js"
    pause
    exit /b
)

echo.
echo  ArchEng Pro — Starting Monitor Agent...
echo.
node "%~dp0usermonitor.js"
pause
