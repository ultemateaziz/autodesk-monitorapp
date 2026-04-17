# ASCLAM Monitor — Troubleshooting Log
**Date:** 2026-04-17  
**Session:** Installation & Deployment Troubleshooting

---

## 1. Composer Install Failed on Client PC

**Error:**
```
Failed to download doctrine/inflector from dist: The zip extension and unzip/7z commands are both missing
git was not found in your PATH, skipping source download
```

**Cause:**
- PHP `zip` extension not enabled in `php.ini`
- Git not installed or not in system PATH

**Solution:**
1. Open `C:\xampp\php\php.ini` and enable:
   ```
   extension=zip
   ```
2. Install Git from git-scm.com/download/win — select **"Git from the command line and also from 3rd-party software"**
3. Restart CMD and retry:
   ```cmd
   composer install
   ```

**Quick fix (skip platform checks):**
```cmd
composer install --ignore-platform-reqs --prefer-dist
```

---

## 2. Generate .env File on Fresh Install

**Commands:**
```cmd
copy .env.example .env
php artisan key:generate
php artisan migrate
```

**Set database credentials in `.env`:**
```
DB_DATABASE=your_db_name
DB_USERNAME=root
DB_PASSWORD=
```

---

## 3. Database Seeders

**Run all seeders:**
```cmd
php artisan db:seed
```

**Fresh migrate + seed (new/empty database only):**
```cmd
php artisan migrate:fresh --seed
```

**Individual seeders:**
```cmd
php artisan db:seed --class=DatabaseSeeder
php artisan db:seed --class=UserSeeder
php artisan db:seed --class=ActivitySeeder
```

---

## 4. Default Credentials

### Main App (laravel-app)
| Field | Value |
|---|---|
| Email | admin@admin.com |
| Password | admin123 |

### License Hub (license-manager)
| Field | Value |
|---|---|
| Email | admin@admin.com |
| Password | admin@123 |
| Database | aclm |
| DB User | root |
| DB Password | *(empty)* |

---

## 5. Start Laravel Dashboard

```cmd
php artisan serve --host=0.0.0.0 --port=8001
```

Access from other PCs on network:
```
http://192.168.63.169:8001
```

---

## 6. Build HazeMonitor Agent EXE

**Prerequisite — create `package.json` in `autodesk-montor` folder:**
```cmd
(
echo {
echo   "name": "hazemonitor",
echo   "version": "1.0.0",
echo   "description": "ASCLAM Autodesk Monitor Agent",
echo   "main": "twomonitor.js",
echo   "dependencies": {
echo     "axios": "^1.6.0"
echo   }
echo }
) > package.json
```

**Install dependencies:**
```cmd
npm install
```

**Verify axios is installed:**
```cmd
dir node_modules\axios
```

**Build exe:**
```cmd
pkg twomonitor.js --targets node18-win-x64 --output hazemonitor.exe
```

> Always run `npm install` before building — otherwise axios won't be bundled and the exe will crash with `Cannot find module 'axios'`

---

## 7. Install Agent on Client PC

**Files needed in same folder:**
```
hazemonitor.exe
install.bat
start_silent.vbs
uninstall.bat
```

**Run:**
- Right-click `install.bat` → **Run as Administrator**

---

## 8. install.bat — Access Denied When Copying

**Error:**
```
Access is denied.
0 file(s) copied.
ERROR: Could not find hazemonitor.exe!
```

**Cause:**
Previous failed install left `C:\AutodeskMonitor` with locked/denied permissions.

**Fix — added to install.bat:**
```batch
if exist "C:\AutodeskMonitor" (
    icacls "C:\AutodeskMonitor" /reset /T /Q >nul 2>&1
    rd /s /q "C:\AutodeskMonitor" >nul 2>&1
)
```

This resets permissions and deletes the old folder before reinstalling.

---

## 9. start_silent.vbs — Load Script Failed Access Denied

**Error:**
```
Load script start_silent.vbs failed access denied
```

**Cause:**
`install.bat` was denying read permissions to Everyone before trying to run the VBS with `wscript` — so even the current session couldn't load it.

**Fix — replaced wscript launch with schtasks:**
```batch
:: Before (broken)
wscript "C:\AutodeskMonitor\start_silent.vbs"

:: After (fixed)
schtasks /run /tn "AutodeskMonitorAgent" >nul 2>&1
```

The scheduled task runs as SYSTEM which bypasses the deny rules.

---

## 10. Windows Defender Quarantining hazemonitor.exe

**Symptom:**
Install succeeds but `C:\AutodeskMonitor` folder is empty after install.

**Cause:**
Windows Defender detects the silent background exe and quarantines it automatically.

**Fix — add exclusions before installing:**
```cmd
powershell -Command "Add-MpPreference -ExclusionPath 'C:\AutodeskMonitor'"
powershell -Command "Add-MpPreference -ExclusionPath 'D:\Important\User'"
```

Then reinstall.

---

## 11. Verify Agent is Running

```cmd
tasklist | findstr hazemonitor
```

**Expected output:**
```
hazemonitor.exe   1234   Console   1   45,000 K
```

**Check scheduled task status:**
```cmd
schtasks /query /tn "AutodeskMonitorAgent"
```

**Run task manually:**
```cmd
schtasks /run /tn "AutodeskMonitorAgent"
```

---

## 12. Agent Running But No Data on Dashboard

**Check 1 — Test API is reachable from client:**
```cmd
curl http://192.168.63.169:8001/api/log-activity
```
Expected: `The GET method is not supported` → means API is reachable ✅

**Check 2 — Open Windows Firewall port 8001 on server PC:**
```cmd
netsh advfirewall firewall add rule name="ASCLAM Port 8001" dir=in action=allow protocol=TCP localport=8001
```

**Check 3 — Confirm server IP in twomonitor.js:**
```javascript
const API_URL          = 'http://192.168.63.169:8001/api/log-activity';
const SETTINGS_API_URL = 'http://192.168.63.169:8001/api/idle-threshold';
```

---

## 13. Agent Shows Only Dots — AutoCAD Not Detected

**Symptom:**
Agent running but only shows `.........` — no ACTIVE or IDLE logs.

**Cause:**
AutoCAD window is not the **active foreground window** — dots mean a non-Autodesk app is focused.

**Fix:**
Click on AutoCAD window to bring it to focus. Within 3 seconds you should see:
```
[ACTIVE ✓] 2026-04-17 14:30:00 - AutoCAD 2024
```

**Test PowerShell foreground detection manually:**
```cmd
powershell -command "Add-Type -MemberDefinition '[DllImport(\"user32.dll\")] public static extern IntPtr GetForegroundWindow(); [DllImport(\"user32.dll\")] public static extern int GetWindowThreadProcessId(IntPtr hWnd, out int lpdwProcessId);' -Name Win32 -Namespace User32; $hwnd = [User32.Win32]::GetForegroundWindow(); [int]$pidOut = 0; [User32.Win32]::GetWindowThreadProcessId($hwnd, [ref]$pidOut); $p = Get-Process -Id $pidOut; Write-Output ($p.ProcessName + '|' + $p.MainWindowTitle)"
```

Expected output:
```
acad|Drawing1.dwg
```

---

## 14. Cannot find module 'axios' Error

**Error:**
```
Error: Cannot find module 'axios'
Require stack:
- C:\snapshot\autodesk-montor\twomonitor.js
```

**Cause:**
Exe was built before `npm install` — axios was not bundled inside the exe.

**Fix:**
On master PC run `npm install` first then rebuild:
```cmd
npm install
pkg twomonitor.js --targets node18-win-x64 --output hazemonitor.exe
```

---

## 15. VirtualBox Testing Setup

To test on a Win 10 VM before deploying to real client machines:

1. Set VM network adapter to **Bridged Adapter**
2. Verify ping from VM:
   ```cmd
   ping 192.168.63.169
   ```
3. Copy 4 files to VM: `hazemonitor.exe`, `install.bat`, `start_silent.vbs`, `uninstall.bat`
4. Run `install.bat` as Administrator
5. Test agent detection

---

## Folder Permissions Reference

| Who | Access |
|---|---|
| Administrators | Full control |
| SYSTEM | Full control |
| Regular Users | Full control |

---

## Key File Locations

| File | Location |
|---|---|
| Monitor Agent | `C:\AutodeskMonitor\hazemonitor.exe` |
| VBS Launcher | `C:\AutodeskMonitor\start_silent.vbs` |
| Scheduled Task | `AutodeskMonitorAgent` |
| Laravel Dashboard | `laravel-app/` |
| License Hub | `license-manager/` |
| Agent Source | `autodesk-montor/twomonitor.js` |
