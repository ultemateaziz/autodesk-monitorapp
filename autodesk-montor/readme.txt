Here is a complete **README** document for the client-side operation. You can save this as `README.txt` or `README.md` in your project folder for future reference.

---

# HazeMonitor - Client-Side Installation Guide

**Description:**
This tool monitors Autodesk AEC Collection software usage on a Windows machine. It runs silently in the background, starts automatically upon user login, and tracks "Active Work" (clicks/focus) vs. "Idle" time.

---

### **1. Prerequisites**

Before creating the executable, ensure the developer machine has:

* **Node.js** installed (v16 or higher).
* **Pkg** tool installed globally:
```bash
npm install -g pkg

```



---

### **2. Project Files**

You need to create these 4 specific files in your folder.

#### **File A: `monitor.js` (The Source Code)**

*This script uses PowerShell to detect the active foreground window.*

```javascript
const { exec } = require('child_process');
const os = require('os');

// --- CONFIGURATION ---
const CHECK_INTERVAL_MS = 3000; 

// TARGET PROCESSES (Partial names to match)
const TARGET_PROCESSES = [
    'acad', 'revit', '3dsmax', 'roamer', 'infraworks', 
    'recap', 'desktopconnector', 'formit', 'robot', 
    'sbd', 'inventor', 'fusion360', 'estmep', 'camduct'
];

const machineId = os.hostname();
const username = os.userInfo().username;

console.log(`[STARTED] Smart Monitor on ${machineId} (${username})`);
console.log(`[WATCHING] Tracking active focus on AEC software...`);

function checkActiveWindow() {
    // PowerShell command to get the Process Name of the currently ACTIVE window
    const psScript = `powershell -command "Add-Type -MemberDefinition '[DllImport(\\"user32.dll\\")] public static extern IntPtr GetForegroundWindow(); [DllImport(\\"user32.dll\\")] public static extern int GetWindowThreadProcessId(IntPtr hWnd, out int lpdwProcessId);' -Name Win32 -Namespace User32; $hwnd = [User32.Win32]::GetForegroundWindow(); [int]$pidOut = 0; [User32.Win32]::GetWindowThreadProcessId($hwnd, [ref]$pidOut); Get-Process -Id $pidOut | Select-Object -ExpandProperty ProcessName"`;

    exec(psScript, (error, stdout, stderr) => {
        if (error) return; // Ignore errors

        const activeApp = stdout.trim().toLowerCase();
        const isWorking = TARGET_PROCESSES.some(target => activeApp.includes(target));

        if (isWorking) {
            console.log(`[${new Date().toLocaleTimeString()}] WORKING: User is active in ${activeApp.toUpperCase()}`);
            // FUTURE: Send data to Laravel API here
        } else {
            process.stdout.write("."); // Idle indicator
        }
    });
}

setInterval(checkActiveWindow, CHECK_INTERVAL_MS);

```

#### **File B: `start_silent.vbs` (The Hider)**

*This script launches the EXE invisibly so no black window appears.*

```vbscript
Set WshShell = CreateObject("WScript.Shell") 
' Points to the hidden installed location
WshShell.Run chr(34) & "C:\AutodeskMonitor\hazemonitor.exe" & chr(34), 0
Set WshShell = Nothing

```

#### **File C: `install.bat` (The Installer)**

*Run this as Administrator to install the system.*

```batch
@echo off
cd /d "%~dp0"

:: Check Admin Rights
net session >nul 2>&1
if %errorLevel% neq 0 (
    echo Failure: Please right-click and run as Administrator.
    pause
    exit
)

echo Installing HazeMonitor...

:: 1. Create Hidden Folder
if not exist "C:\AutodeskMonitor" mkdir "C:\AutodeskMonitor"

:: 2. Copy Files
copy /Y "hazemonitor.exe" "C:\AutodeskMonitor\"
copy /Y "start_silent.vbs" "C:\AutodeskMonitor\"

:: 3. Create Scheduled Task (Auto-Start on Logon)
schtasks /create /tn "AutodeskMonitorAgent" /tr "wscript.exe \"C:\AutodeskMonitor\start_silent.vbs\"" /sc onlogon /rl highest /f

echo SUCCESS! Monitor installed. Starting now...
wscript "C:\AutodeskMonitor\start_silent.vbs"
pause

```

#### **File D: `uninstall.bat` (The Remover)**

*Run this as Administrator to permanently remove the system.*

```batch
@echo off
cd /d "%~dp0"

net session >nul 2>&1
if %errorLevel% neq 0 (
    echo Failure: Please right-click and run as Administrator.
    pause
    exit
)

echo REMOVING AUTODESK MONITOR...

:: 1. Kill Processes
taskkill /F /IM hazemonitor.exe >nul 2>&1
taskkill /F /IM wscript.exe >nul 2>&1

:: 2. Remove Schedule
schtasks /delete /tn "AutodeskMonitorAgent" /f >nul 2>&1

:: 3. Delete Files
rmdir /S /Q "C:\AutodeskMonitor"

echo SUCCESS! Application removed.
pause

```

---

### **3. Build Instructions (Developer Only)**

To turn the JavaScript code into the executable file (`hazemonitor.exe`):

1. Open your terminal in the project folder.
2. Run the package command:
```bash
pkg monitor.js --targets node18-win-x64 --output hazemonitor.exe

```


3. Ensure `hazemonitor.exe` appears in your folder.

---

### **4. Deployment Instructions (For User Machines)**

1. Copy these **3 files** to the target user's computer (via USB or Network):
* `hazemonitor.exe`
* `start_silent.vbs`
* `install.bat`


2. Right-click `install.bat` and select **"Run as Administrator"**.
3. Wait for the "SUCCESS" message.
4. The tool is now running and will auto-restart with Windows.

---

### **5. Verification**

To check if it is working:

1. Open **Task Manager** (`Ctrl+Shift+Esc`).
2. Go to the **Details** tab.
3. Find **`hazemonitor.exe`**.
* *If present:* It is working correctly.
* *If missing:* Restart the computer or run `install.bat` again.