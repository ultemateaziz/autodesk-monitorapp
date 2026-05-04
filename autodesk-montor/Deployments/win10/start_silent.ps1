# ACLAM Monitor Launcher & Watchdog
# Replaces start_silent.vbs — avoids WSH error 800704C7 on Windows 11

$exe = "C:\AutodeskMonitor\hazemonitor.exe"

# Launch if not already running
if (-not (Get-Process -Name "hazemonitor" -ErrorAction SilentlyContinue)) {
    Start-Process -FilePath $exe -WindowStyle Hidden
}

# Watchdog loop — checks every 60 seconds and restarts if crashed
while ($true) {
    Start-Sleep -Seconds 60
    if (-not (Get-Process -Name "hazemonitor" -ErrorAction SilentlyContinue)) {
        Start-Process -FilePath $exe -WindowStyle Hidden
    }
}
