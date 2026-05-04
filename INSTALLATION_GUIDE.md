# ACLAM — Full Installation Guide
**Version:** ACLAM v2  
**Stack:** Laravel (Server) + HazeMonitor Agent (Client PCs)

---

## Overview — How The System Works

```
[Client PC 1]  hazemonitor.exe  ──┐
[Client PC 2]  hazemonitor.exe  ──┼──►  Server PC (192.168.x.x:8001)  ◄──  Admin Browser
[Client PC 3]  hazemonitor.exe  ──┘         Laravel + MySQL
```

- **Server PC** = One dedicated Windows machine running the Laravel dashboard + MySQL
- **Client PC** = Every designer/user machine — has the silent `hazemonitor.exe` installed
- All client PCs must be on the **same local network (LAN)** as the server

---

## PART 1 — SERVER SETUP
*(Do this once on the dedicated server machine)*

---

### Step 1 — Find the Server PC's IP Address

Open **Command Prompt** on the server machine and run:

```cmd
ipconfig
```

**What to look for:**

```
Wireless LAN adapter Wi-Fi:
   IPv4 Address. . . . . . . : 192.168.0.200   ← THIS IS YOUR SERVER IP
   Subnet Mask . . . . . . . : 255.255.255.0
   Default Gateway . . . . . : 192.168.0.1

  — OR —

Ethernet adapter Ethernet:
   IPv4 Address. . . . . . . : 192.168.1.105   ← THIS IS YOUR SERVER IP
```

> ⚠️ Write this IP down. You will need it in Step 5 and in every client PC install.

---

### Step 2 — Check Required Software is Installed

Open **Command Prompt** and run these one by one:

```cmd
php --version
```
Expected output:
```
PHP 8.2.x (cli)
```

```cmd
mysql --version
```
Expected output:
```
mysql  Ver 8.x.x
```

```cmd
composer --version
```
Expected output:
```
Composer version 2.x.x
```

> If any of these show "not recognized", install **XAMPP** or **Laragon** first, then re-run.

---

### Step 3 — Copy the Project to the Server

Place the entire `laravel-app` folder somewhere permanent, for example:

```
C:\ACLAM\laravel-app\
```

Open **Command Prompt** and navigate into it:

```cmd
cd C:\ACLAM\laravel-app
```

Install PHP dependencies:

```cmd
composer install --no-dev --optimize-autoloader
```

Expected output (last lines):
```
Generating optimized autoload files
> Illuminate\Foundation\ComposerScripts::postAutoloadDump
Package manifest generated successfully.
```

---

### Step 4 — Create the Database

Open **Command Prompt** and log into MySQL:

```cmd
mysql -u root -p
```
*(Press Enter when asked for password if you have no root password)*

Inside MySQL, run:

```sql
CREATE DATABASE autodesk_monitor CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;
```

Expected output:
```
Query OK, 1 row affected (0.01 sec)
```

---

### Step 5 — Configure the .env File

Inside `C:\ACLAM\laravel-app\`, open the `.env` file in Notepad and update these lines:

```env
APP_NAME=ACLAM
APP_ENV=production
APP_DEBUG=false
APP_URL=http://YOUR_SERVER_IP:8001

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=autodesk_monitor
DB_USERNAME=root
DB_PASSWORD=

MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-gmail-app-password
MAIL_FROM_ADDRESS="monitor@yourcompany.com"
MAIL_FROM_NAME="ACLAM Monitor"

HR_EMAIL=hr@yourcompany.com
```

> Replace `YOUR_SERVER_IP` with the IP you found in Step 1 (e.g. `192.168.0.200`)

---

### Step 6 — Run Database Migrations

In **Command Prompt** inside `C:\ACLAM\laravel-app\`:

```cmd
php artisan migrate --force
```

Expected output:
```
  INFO  Running migrations.

  2019_12_14_000001_create_personal_access_tokens_table .... 23ms DONE
  2024_01_01_000001_create_users_table ..................... 18ms DONE
  2024_01_01_000002_create_activity_logs_table ............. 12ms DONE
  2024_01_01_000003_create_user_profiles_table ............. 10ms DONE
  ...
```

> If you see `DONE` on all lines — the database is ready.

---

### Step 7 — Generate App Key

```cmd
php artisan key:generate
```

Expected output:
```
INFO  Application key set successfully.
```

---

### Step 8 — Create the First Admin Account

```cmd
php artisan tinker
```

Once inside the Tinker shell, paste this (replace with real details):

```php
\App\Models\User::create([
    'name'     => 'Admin',
    'email'    => 'admin@yourcompany.com',
    'password' => bcrypt('YourStrongPassword123'),
    'role'     => 'admin',
]);
```

Expected output:
```
= App\Models\User {#xxxx
    name: "Admin",
    email: "admin@yourcompany.com",
    role: "admin",
    ...
  }
```

Type `exit` to leave Tinker:
```
exit
```

---

### Step 9 — Set Storage Permissions

```cmd
php artisan storage:link
```

Expected output:
```
INFO  The [public/storage] link has been connected to [storage/app/public].
```

---

### Step 10 — Start the Dashboard Server

```cmd
php artisan serve --host=0.0.0.0 --port=8001
```

Expected output:
```
   INFO  Server running on [http://0.0.0.0:8001].

   Press Ctrl+C to stop the server
```

> ✅ The server is now running. Open a browser and go to:
> `http://YOUR_SERVER_IP:8001`
> You should see the ACLAM login page.

---

### Step 11 — Activate the License

1. Open browser → `http://YOUR_SERVER_IP:8001`
2. Log in with the admin account you created
3. If prompted for a **License Key**, enter:
   ```
   AEPRO-K2RN-26DD-GRIR
   ```
4. Click **Activate**

---

### Step 12 — Run the Server Permanently (Auto-Start)

To keep the server running after you close the terminal, use NSSM (included in `nssm-2.24.zip`).

Extract `nssm-2.24.zip` → copy `nssm.exe` to `C:\Windows\System32\`

Open **Command Prompt as Administrator** and run:

```cmd
nssm install ACLAM-Server
```

A GUI window opens — fill in:
- **Path:** `C:\php\php.exe`  *(or wherever PHP is installed — check with `where php`)*
- **Startup directory:** `C:\ACLAM\laravel-app`
- **Arguments:** `artisan serve --host=0.0.0.0 --port=8001`

Click **Install Service**, then:

```cmd
nssm start ACLAM-Server
```

Expected output:
```
ACLAM-Server: START: The operation completed successfully.
```

> Now the dashboard starts automatically every time the server PC boots.

---

### Step 13 — Allow Port 8001 Through Windows Firewall

Open **Command Prompt as Administrator**:

```cmd
netsh advfirewall firewall add rule name="ACLAM Dashboard" dir=in action=allow protocol=TCP localport=8001
```

Expected output:
```
Ok.
```

---

## PART 2 — CLIENT PC SETUP
*(Do this on every designer/user machine)*

---

### Step 14 — Prepare the Agent Files

On the **server machine**, make sure the `autodesk-montor` folder contains:

```
autodesk-montor\
├── hazemonitor.exe      ← the compiled monitor agent
├── start_silent.vbs     ← the silent launcher
├── install.bat          ← the installer
└── uninstall.bat        ← the remover
```

> ⚠️ Before going to any client PC, verify `hazemonitor.exe` is pointing to the correct server IP.
> Open `twomonitor.js` and check line 8:
> ```javascript
> const API_URL = 'http://192.168.0.200:8001/api/log-activity';
> ```
> The IP must match the server IP from Step 1. If you changed it, rebuild the exe:
> ```cmd
> npm install -g pkg
> pkg twomonitor.js --targets node18-win-x64 --output hazemonitor.exe
> ```

---

### Step 15 — Find the Client PC's IP (For Verification)

On the **client PC**, open **Command Prompt**:

```cmd
ipconfig
```

Note the IPv4 address — it should be on the same network as the server (e.g. `192.168.0.xxx`).

Verify the client can reach the server by pinging it:

```cmd
ping 192.168.0.200
```

Expected output:
```
Reply from 192.168.0.200: bytes=32 time=1ms TTL=128
Reply from 192.168.0.200: bytes=32 time=1ms TTL=128
```

> If you see `Request timed out` — the client cannot reach the server. Check WiFi/LAN and firewall.

---

### Step 16 — Install the Monitor Agent on the Client PC

Copy these 3 files to the client PC (via USB drive or shared network folder):

```
hazemonitor.exe
start_silent.vbs
install.bat
```

On the **client PC**:
1. Right-click `install.bat`
2. Select **"Run as Administrator"**

Expected output in the black window:
```
[Admin Rights Confirmed]
---------------------------------------------------
Installing Autodesk Monitor (Silent & Protected)...
---------------------------------------------------
Copying monitor.exe...
        1 file(s) copied.
Copying start_silent.vbs...
        1 file(s) copied.
SUCCESS! The monitor is installed.
It will start automatically next time you restart.
---------------------------------------------------
Starting it now for the first time...
```

> The window will close automatically. The agent is now running silently.

---

### Step 17 — Verify the Agent is Running (Client PC)

On the **client PC**, open **Task Manager**:

```
Press: Ctrl + Shift + Esc
→ Click "Details" tab
→ Look for: hazemonitor.exe
```

If `hazemonitor.exe` is in the list — ✅ the agent is running.

---

### Step 18 — Verify Data is Arriving (Server Dashboard)

1. On the client PC, open **AutoCAD, Revit, or any Autodesk app**
2. Wait **30–60 seconds**
3. On the **server browser**, go to: `http://YOUR_SERVER_IP:8001`
4. Log in → go to **Dashboard** or **Users**
5. The client PC username should appear as **Online** with a green dot

---

## PART 3 — QUICK REFERENCE COMMANDS

### Server Commands (Run inside `C:\ACLAM\laravel-app`)

| What to do | Command |
|---|---|
| Start the server | `php artisan serve --host=0.0.0.0 --port=8001` |
| Run migrations | `php artisan migrate --force` |
| Clear all caches | `php artisan optimize:clear` |
| Rebuild cache | `php artisan optimize` |
| Check scheduled jobs | `php artisan schedule:run` |
| Open Tinker shell | `php artisan tinker` |
| Check artisan commands | `php artisan list` |

### Reset a Forgotten Admin Password (Tinker)

```cmd
php artisan tinker
```

```php
$user = \App\Models\User::where('email', 'admin@yourcompany.com')->first();
$user->password = bcrypt('NewPassword123');
$user->save();
exit
```

### Check if Port 8001 is Listening

```cmd
netstat -ano | findstr :8001
```

Expected output:
```
TCP    0.0.0.0:8001    0.0.0.0:0    LISTENING    1234
```

### Find What Process is Using Port 8001

```cmd
netstat -ano | findstr :8001
```
Note the PID (last number), then:
```cmd
tasklist | findstr 1234
```

### Uninstall Monitor Agent (Client PC) — Run as Admin

```cmd
C:\AutodeskMonitor\uninstall.bat
```
Or double-click `uninstall.bat` → **Run as Administrator**

---

## PART 4 — TROUBLESHOOTING

| Problem | What to check | Command |
|---|---|---|
| Dashboard won't open | Server running? | `netstat -ano \| findstr :8001` |
| Login fails | Admin account exists? | `php artisan tinker` → check User |
| No users appearing | Agent installed? | Task Manager → `hazemonitor.exe` |
| Agent not sending | Server IP correct? | Check `twomonitor.js` line 8 |
| Client can't reach server | Same network? Firewall? | `ping 192.168.0.200` on client |
| Database error on start | Migrations run? | `php artisan migrate --force` |
| Port already in use | Another process on 8001? | `netstat -ano \| findstr :8001` |
| Scheduled emails not sending | Mail config in `.env` set? | Check `MAIL_*` values in `.env` |

---

## PART 5 — SOFTWARE PROTECTION

### 5A — Server (Laravel Dashboard) Protection

After completing the server setup in Part 1, run the protection script **once**:

```cmd
Right-click  protect_server.bat  →  Run as Administrator
```

**What it does — 6 steps automatically:**

| Step | What is protected | How |
|---|---|---|
| 1 | Deletes `admin_credentials.txt`, `temp_pass.php`, `temp_user.json` | These files expose passwords — gone permanently |
| 2 | Locks the entire `laravel-app` folder | NTFS DENY on Users + Everyone — Access Denied if copied |
| 3 | Extra-locks the `.env` file | Hidden + read-only + NTFS DENY — credentials and license key are sealed |
| 4 | Hides the app folder | `attrib +h +s` — invisible in File Explorer |
| 5 | Locks the MySQL database folder | NTFS DENY — raw `.ibd` database files cannot be copied |
| 6 | Restricts port 8001 to LAN only | Firewall rule: only `192.168.0.x` devices can open the dashboard |

> The `php artisan serve` and NSSM service still work because they run under the **SYSTEM** or **Administrators** account, which keeps full access.

---

### Verify Server Protection is Active

Open **Command Prompt as Administrator** on the server and run:

```cmd
icacls "C:\ACLAM\laravel-app"
```

Expected output:
```
C:\ACLAM\laravel-app SYSTEM:(OI)(CI)(F)
                       Administrators:(OI)(CI)(F)
                       Everyone:(OI)(CI)(DENY)(R,RD,RA,REA,RC,RX)
                       Users:(OI)(CI)(DENY)(R,RD,RA,REA,RC,RX)
```

Check the `.env` file specifically:

```cmd
icacls "C:\ACLAM\laravel-app\.env"
```

Expected output:
```
C:\ACLAM\laravel-app\.env SYSTEM:(F)
                            Administrators:(F)
                            Everyone:(DENY)(R,RA,REA,RC,RX)
                            Users:(DENY)(R,RA,REA,RC,RX)
```

If you see `DENY` entries — credentials are fully protected. ✅

---

### What Happens if Someone Tries to Steal the Server Code

| What they try | What happens |
|---|---|
| Open `C:\ACLAM` in File Explorer | Folder is hidden — invisible |
| Copy the `laravel-app` folder | **Access is denied** |
| Open or read the `.env` file | **Access is denied** — passwords stay sealed |
| Copy the MySQL database files directly | **Access is denied** |
| Open the dashboard from outside the office | Port 8001 blocked — only LAN IPs can connect |
| Take the `.env` and connect to DB remotely | DB is on `127.0.0.1` (localhost only) — unreachable from outside |
| Use the license key on another server | License is validated against your LicenseHub — will be rejected |

---

### 5B — Client PC (hazemonitor.exe) Protection

### How the Client Agent is Protected from Being Stolen

The `install.bat` automatically applies 4 layers of protection during installation:

#### Layer 1 — Folder Hidden from File Explorer
```cmd
attrib +h +s "C:\AutodeskMonitor"
```
- The folder is invisible in File Explorer and `dir` listings
- A normal user will never know the folder exists

#### Layer 2 — File Permissions Locked (NTFS)
```cmd
icacls "C:\AutodeskMonitor" /inheritance:r
icacls "C:\AutodeskMonitor" /grant:r "SYSTEM:(OI)(CI)F"
icacls "C:\AutodeskMonitor" /deny "Users:(OI)(CI)(RX,R,RD,RA,REA,RC)"
icacls "C:\AutodeskMonitor" /deny "Everyone:(OI)(CI)(RX,R,RD,RA,REA,RC)"
```
- Even if someone finds the folder (e.g. via `dir /a`), they get **Access Denied** when trying to open or copy it
- Only the SYSTEM account can read and run the files

#### Layer 3 — Runs as SYSTEM Account
```cmd
schtasks ... /ru SYSTEM /rl highest
```
- The agent runs under the Windows SYSTEM account — not as the logged-in user
- The user cannot attach a debugger or inspect the running process

#### Layer 4 — Compiled Binary (Not Raw Script)
- `hazemonitor.exe` is compiled with `pkg` — the JavaScript source is not visible
- It cannot be opened in Notepad or a text editor to read the code

---

### What Happens if Someone Tries to Copy It

| What they try | What happens |
|---|---|
| Open `C:\AutodeskMonitor` in File Explorer | Folder is invisible — they cannot find it |
| Run `dir C:\AutodeskMonitor` in CMD | Shows nothing (hidden + system) |
| Run `dir /a C:\AutodeskMonitor` to show hidden | **Access is denied** |
| Copy `hazemonitor.exe` via CMD | **Access is denied** |
| Copy via File Explorer | **Access is denied** |
| Open the exe in a text editor | Binary — unreadable source |
| Run the stolen exe on another network | Connects to YOUR server IP only — useless to them |

---

### Even if They Somehow Get the .exe — It's Useless

The `hazemonitor.exe` has your server IP hardcoded inside it:
```javascript
const API_URL = 'http://192.168.0.200:8001/api/log-activity';
```
If someone takes the exe and runs it on a different network, it tries to connect to `192.168.0.200` — which is **your** private LAN IP. It will fail to connect and send data nowhere. The exe is worthless outside your network.

---

### Manual Verification — Check Permissions are Applied (On Client PC)

After running `install.bat`, verify the lock worked:

```cmd
icacls "C:\AutodeskMonitor"
```

Expected output:
```
C:\AutodeskMonitor SYSTEM:(OI)(CI)(F)
                   Administrators:(OI)(CI)(F)
                   Everyone:(OI)(CI)(DENY)(R,RD,RA,REA,RC,RX)
                   Users:(OI)(CI)(DENY)(R,RD,RA,REA,RC,RX)
```

If you see `DENY` entries for Users and Everyone — the protection is active. ✅

---

## PART 6 — NAME CHANGE HISTORY

| Date | Old Name | New Name |
|---|---|---|
| 2026-04-12 | ArchEng Pro Monitor | ARCHLAM |
| 2026-04-17 | ARCHLAM | ACLAM |

---

## PART 6 — SYSTEM INFORMATION

| Item | Value |
|---|---|
| Dashboard Port | 8001 |
| API Endpoint | `/api/log-activity` |
| Idle Threshold API | `/api/idle-threshold` |
| Monitor Install Path | `C:\AutodeskMonitor\` |
| Monitor Exe Name | `hazemonitor.exe` |
| Scheduled Task Name | `AutodeskMonitorAgent` |
| Database Name | `autodesk_monitor` |
| Default Admin Role | `admin` |
| Heartbeat Interval | Every 3 seconds |
| Background Scan | Every 60 seconds |
