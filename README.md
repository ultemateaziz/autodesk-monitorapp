# ArchEng Pro — System Documentation

> **Version:** 1.0
> **Last Updated:** 2026-03-16
> **Purpose:** Complete technical reference for developers, administrators, and future AI assistants working on this project.

---

## Table of Contents

1. [Project Overview](#1-project-overview)
2. [System Architecture](#2-system-architecture)
3. [Component 1 — Monitor Agent (Client)](#3-component-1--monitor-agent-client)
4. [Component 2 — Laravel Dashboard (Server)](#4-component-2--laravel-dashboard-server)
5. [Component 3 — LicenseHub (License Manager)](#5-component-3--licensehub-license-manager)
6. [Database Schema](#6-database-schema)
7. [API Reference](#7-api-reference)
8. [Installation & Deployment](#8-installation--deployment)
9. [License System Deep Dive](#9-license-system-deep-dive)
10. [Configuration Reference](#10-configuration-reference)
11. [User Roles & Permissions](#11-user-roles--permissions)
12. [Scheduled Tasks](#12-scheduled-tasks)
13. [Monitored Software List](#13-monitored-software-list)
14. [Project File Structure](#14-project-file-structure)
15. [Future Developer Notes](#15-future-developer-notes)

---

## 1. Project Overview

**ArchEng Pro** is a three-tier enterprise software monitoring and license management system built for AEC (Architecture, Engineering & Construction) companies running Autodesk software suites.

### What it does:

| Layer | App | Role |
|---|---|---|
| **Client** | `hazemonitor.exe` | Silently monitors which Autodesk app each employee is using, every 3 seconds |
| **Server** | Laravel Dashboard | Receives usage data, shows analytics, manages users, generates reports |
| **Master** | LicenseHub | Issues and validates subscription keys, enforces hardware locks |

### Why it exists:

Autodesk licenses are expensive. This system answers:
- Who is actually using AutoCAD / Revit / Inventor — and for how long?
- Are we paying for licenses nobody uses?
- Which machines have active subscriptions?
- Is the subscription valid right now?

---

## 2. System Architecture

```
┌──────────────────────────────────────────────────────────────────┐
│                        CLIENT MACHINES                           │
│                                                                  │
│   [PC-01: john.doe]  [PC-02: sara.k]  [PC-03: ahmed.m]          │
│   hazemonitor.exe    hazemonitor.exe   hazemonitor.exe           │
│   (silent, hidden)   (silent, hidden)  (silent, hidden)          │
│         │                  │                  │                  │
│         └──────────────────┴──────────────────┘                  │
│                            │                                     │
│              POST /api/log-activity (every 3s)                   │
└────────────────────────────┼─────────────────────────────────────┘
                             │
                             ▼
┌──────────────────────────────────────────────────────────────────┐
│                    LARAVEL DASHBOARD (SERVER)                    │
│                    http://[server-ip]:8000                       │
│                                                                  │
│   • Receives activity logs from all monitors                    │
│   • Shows admin dashboard: usage, leaderboards, reports         │
│   • Manages users, departments, license assignments             │
│   • Every 5 min → pings LicenseHub to verify own subscription   │
│                                                                  │
│              POST /api/license/pulse (every 5 min)               │
└────────────────────────────┬─────────────────────────────────────┘
                             │
                             ▼
┌──────────────────────────────────────────────────────────────────┐
│                    LICENSEHUB (MASTER SERVER)                    │
│                    http://[license-ip]:8001                      │
│                                                                  │
│   • Issues subscription keys (AEPRO-XXXX-XXXX-XXXX)            │
│   • Validates hardware locks (Windows MachineGUID)              │
│   • Controls tiers: 7D / 15D / 6M / 1Y                         │
│   • Admin can lock/unlock/delete/regenerate keys                │
└──────────────────────────────────────────────────────────────────┘
```

### Data Flow Summary:

```
Employee uses AutoCAD
       ↓
hazemonitor.exe detects focus (PowerShell, every 3s)
       ↓
POST → Laravel Dashboard: { machine, user, app, timestamp }
       ↓
Stored in activity_logs table
       ↓
Admin opens Dashboard → sees real-time analytics
       ↓ (every 5 min, background)
Laravel Dashboard → POST /api/license/pulse → LicenseHub
       ↓
LicenseHub returns: valid / locked / expired
       ↓
Dashboard shows license status bar
```

---

## 3. Component 1 — Monitor Agent (Client)

### Location: `autodesk-montor/`

### Files:

| File | Purpose |
|---|---|
| `twomonitor.js` | Core monitor script — detects active Autodesk window, sends to server |
| `usermonitor.js` | Full-featured version — includes license verification + pulse heartbeat |
| `onemonitor.js` | Older single-monitor variant |
| `hazemonitor.exe` | Compiled version of `twomonitor.js` (pkg/nexe) — deployed to machines |
| `hazeMonitorUser.exe` | Compiled version of `usermonitor.js` — includes license check on boot |
| `install.bat` | Admin installer — copies exe to `C:\AutodeskMonitor\`, registers Task Scheduler |
| `uninstall.bat` | Removes scheduled task and files |
| `start_silent.vbs` | VBScript wrapper — starts exe with no console window (silent) |
| `admin/start.bat` | Checks for `license.json`, triggers activate if missing, then starts monitor |
| `admin/activate.js` | Node.js script — prompts admin for license key, writes `license.json` |
| `admin/activate.html` | Simple HTML UI for license activation step |

### How the monitor works (twomonitor.js):

```javascript
// 1. Every 3 seconds, run PowerShell to get the active window's process name
const psScript = `powershell -command "...GetForegroundWindow()..."`;

// 2. Match against 14 known Autodesk process names
const TARGET_PROCESSES = ['acad', 'revit', '3dsmax', 'roamer', ...]

// 3. If a match is found, send to server
axios.post('http://[SERVER_URL]/api/log-activity', {
    machine_name: os.hostname(),
    user_name: os.userInfo().username,
    application: 'AutoCAD',   // friendly name from mapping
    status: 'Active',
    timestamp: localTimestamp
});
```

### Important: URL Configuration

The server URL is **hardcoded** in `twomonitor.js` before compiling to exe:

```javascript
// Line 8 in twomonitor.js — change this before compiling
const API_URL = 'http://127.0.0.1:8000/api/log-activity';
```

**For production deployment:** Replace `127.0.0.1:8000` with the actual server IP before running `pkg twomonitor.js` to generate the exe.

### Installation on Client Machines (Group Policy):

```
Active Directory Group Policy pushes:
  ├── hazemonitor.exe
  ├── install.bat
  └── start_silent.vbs

install.bat runs automatically:
  1. Copies files to C:\AutodeskMonitor\
  2. Registers Windows Task Scheduler entry: "AutodeskMonitorAgent"
  3. Task fires on every user login
  4. start_silent.vbs launches exe without any visible window
```

### Silent Operation:

The monitor is completely invisible to end users:
- No taskbar icon
- No console window (VBScript wrapper)
- No notification
- Runs in background at all times

---

## 4. Component 2 — Laravel Dashboard (Server)

### Location: `laravel-app/`

### Technology Stack:

- **Framework:** Laravel 11 (PHP 8.2+)
- **Database:** MySQL (`autodesk_monitor` database)
- **Frontend:** Blade templates, Tailwind CSS, Chart.js
- **Auth:** Custom session-based authentication
- **Cache:** Database-backed cache (for license status)

### Key Controllers:

#### `DashboardController.php`
The main analytics engine.

```
Methods:
  index()              → Main dashboard — usage analytics, charts, leaderboards
  users()              → User listing with software usage + revocation status
  licenseAudit()       → Which users have which Autodesk licenses
  leaderboard()        → Top users ranked by software usage time
  machineInventory()   → All machines that have sent data
  ghostMachines()      → Machines inactive for 7+ days
  departmentEfficiency() → Cross-department productivity comparison
  licenseOptimization() → Highlight underused license seats
  revokeSoftware()     → Admin manually marks software as revoked
  restoreSoftware()    → Undo a revocation
```

**Time Calculation Logic:**

Activity is logged every 3 seconds. To calculate usage time:
```
Total activity records × 3 seconds = Total seconds used
Example: 1200 records = 3600 seconds = 1 hour
```

#### `AuthController.php`
Handles login/logout with session-based auth.

Default admin credentials (change on first login):
```
Email:    admin@archengpro.com
Password: (see laravel-app/admin_credentials.txt)
```

#### `ActivityController.php`
Receives data from monitor agents.

```
POST /api/log-activity
Input:  { machine_name, user_name, application, status, timestamp }
Output: { status: "ok" }
```

Stores to `activity_logs` table. No authentication required (internal network only).

### Routes Overview:

```
Public:
  GET  /login         Login form
  POST /login         Process login
  POST /logout        Logout

Protected (require login):
  GET  /              Dashboard analytics
  GET  /users         User list + software status
  GET  /license-audit License assignment audit
  GET  /leaderboard   Productivity ranking
  GET  /export-csv    Download activity data as CSV
  GET  /machine-inventory  All known machines
  GET  /ghost-machines     Inactive machines
  GET  /department-efficiency  Department comparison
  GET  /license-optimization   Underused licenses
  GET  /settings      Application settings

  POST /assign-license      Add license to user
  DELETE /remove-license    Remove license from user
  POST /revoke-software     Flag software as admin-revoked
  POST /restore-software    Undo revocation
  POST /monitor-assignments Update team monitoring scope
```

### License Status (Built-in Scheduler):

The dashboard verifies its own subscription with LicenseHub automatically.

```
Scheduler: routes/console.php
  Schedule::command('license:check')->everyFiveMinutes();

Command: app/Console/Commands/CheckLicense.php
  1. Reads LICENSE_KEY from .env
  2. Reads LICENSE_MANAGER_URL from .env
  3. POST → LicenseHub /api/license/pulse
  4. Stores result in Cache for 10 minutes

Start command (run once, keeps running):
  php artisan schedule:work
```

Cache result structure:
```php
Cache::get('license_status') = [
    'status'    => 'valid' | 'expired' | 'locked' | 'unreachable' | 'not_configured',
    'tier'      => '1Y' | '6M' | '15D' | '7D',
    'days_left' => 287,
    'expires_at'=> '2027-03-16',
    'customer'  => 'Al Habtoor Engineering',
    'checked'   => '2026-03-16 10:30:00',
]
```

### Revoked Software System:

Admins sometimes remove Autodesk licenses externally (e.g., from Autodesk portal) but the system still shows the user as active. The revocation system solves this:

```
Table: revoked_software
  user_name     (e.g., "john.doe")
  software_name (e.g., "AutoCAD")
  revoked_by    (admin username)

In users.blade.php:
  Software shown normally → ✅
  Software marked revoked → strikethrough + red badge + ban icon
  Admin can click X to revoke, or ↩ to restore
```

---

## 5. Component 3 — LicenseHub (License Manager)

### Location: `license-manager/`

### Technology Stack:

- **Framework:** Laravel 11 (PHP 8.2+)
- **Database:** MySQL (separate from dashboard)
- **Frontend:** Custom dark/light theme admin UI
- **Auth:** Session-based (admin only)

### Key Controller: `LicenseController.php`

#### Admin Dashboard Methods:

```
dashboard()     → Metrics: total keys, active, locked machines
index()         → Paginated license list with all details
generateKey()   → Create new license key for a customer
toggleLock()    → Lock or unlock a specific machine activation
destroy()       → Delete license + clear all hardware locks (Option 1 — clean slate)
regenerate()    → Issue new key + delete all old activations (Option 1 — clean slate)
apiReference()  → Show API documentation page
settings()      → Admin settings page
```

#### API Methods (called by monitor agents):

```
apiActivate()   → POST /api/license/activate
apiVerify()     → POST /api/license/verify
apiPulse()      → POST /api/license/pulse
```

### License Key Format:

```
AEPRO-[4 chars]-[4 chars]-[4 chars]
Example: AEPRO-K7X2-M9QP-R3TZ
```

Generated using Laravel's `Str::random(4)` (uppercase alphanumeric).

### License Tiers:

| Tier Code | Duration | Days |
|---|---|---|
| `7D` | 7 Days | 7 |
| `15D` | 15 Days | 15 |
| `6M` | 6 Months | 180 |
| `1Y` | 1 Year | 365 |

Expiry is calculated at activation time: `now() + tier days`.

### Hardware Lock System:

```
1. Admin generates key → license_key created, hardware_id = NULL

2. Customer activates on their machine:
   POST /api/license/activate {
       license_key: "AEPRO-K7X2-M9QP-R3TZ",
       hardware_id: "Windows MachineGUID from registry",
       machine_id:  "WORKSTATION-01"
   }
   → hardware_id gets locked to this key

3. If same key tried on different machine:
   → hardware_id mismatch → rejected → "already_activated"

4. License expires after tier duration:
   → pulse returns "expired"
   → Monitor agent exits

5. Admin generates NEW key for renewal:
   → OLD activations DELETED (Option 1 — clean slate)
   → hardware_id cleared
   → Customer activates new key on same or different machine
   → Fresh lock registered
```

### Admin Routes:

```
GET   /dashboard              → Overview metrics
GET   /licenses               → All license keys (paginated)
POST  /generate-key           → Create new license
POST  /toggle-lock/{id}       → Lock or unlock machine
DELETE /licenses/{id}         → Delete license + activations
POST  /licenses/{id}/regenerate → New key + clear old activations
GET   /api-reference          → API documentation
GET   /settings               → Admin settings
```

---

## 6. Database Schema

### laravel-app Database (`autodesk_monitor`)

#### `activity_logs`
```sql
id            bigint (PK)
machine_name  varchar     -- Windows hostname (e.g., WORKSTATION-01)
user_name     varchar     -- Windows username (e.g., john.doe)
application   varchar     -- Friendly app name (e.g., AutoCAD)
status        varchar     -- Always "Active" (currently)
recorded_at   datetime    -- Local timestamp from client machine
created_at    timestamp
updated_at    timestamp
```
> **Note:** One record = 3 seconds of software focus. Calculate usage = count × 3 seconds.

#### `users`
```sql
id          bigint (PK)
name        varchar
email       varchar (unique)
password    varchar (bcrypt hashed)
role        enum: admin | team_leader | user
department  varchar
created_at  timestamp
updated_at  timestamp
```

#### `user_licenses`
```sql
id           bigint (PK)
user_id      bigint (FK → users.id)
license_type varchar  -- e.g., "AutoCAD", "Revit"
assigned_at  datetime
revoked_at   datetime (nullable)
created_at   timestamp
updated_at   timestamp
```

#### `revoked_software`
```sql
id            bigint (PK)
user_name     varchar  -- matches activity_logs.user_name
software_name varchar  -- e.g., "AutoCAD"
revoked_by    varchar (nullable)  -- admin who revoked it
created_at    timestamp
updated_at    timestamp
UNIQUE (user_name, software_name)
```

#### `monitor_assignments`
```sql
id          bigint (PK)
user_name   varchar  -- Windows username to monitor
assigned_by bigint (FK → users.id)
created_at  timestamp
```

---

### license-manager Database (separate MySQL database)

#### `licenses`
```sql
id             bigint (PK)
license_key    varchar (unique)  -- AEPRO-XXXX-XXXX-XXXX
customer_name  varchar (nullable) -- e.g., "Al Habtoor Engineering"
tier           varchar           -- 7D | 15D | 6M | 1Y
is_active      boolean           -- false = not yet activated
expires_at     datetime (nullable) -- set on first activation
machine_id     varchar (nullable) -- hostname of locked machine
machine_name   varchar (nullable) -- friendly name
hardware_id    varchar (nullable) -- Windows MachineGUID
created_at     timestamp
updated_at     timestamp
```

#### `activations`
```sql
id           bigint (PK)
license_id   bigint (FK → licenses.id)
hardware_id  varchar   -- Windows MachineGUID
machine_id   varchar   -- hostname
machine_name varchar
ip_address   varchar (nullable)
last_pulse   datetime (nullable)  -- last heartbeat timestamp
status       enum: active | locked | expired
created_at   timestamp
updated_at   timestamp
```

---

## 7. API Reference

### Dashboard API (laravel-app)

#### `POST /api/log-activity`
Receives activity data from monitor agents. No authentication required.

**Request:**
```json
{
  "machine_name": "WORKSTATION-01",
  "user_name": "john.doe",
  "application": "AutoCAD",
  "status": "Active",
  "timestamp": "2026-03-16 14:30:45"
}
```

**Response:**
```json
{ "status": "ok" }
```

---

### LicenseHub API (license-manager)

#### `POST /api/license/activate`
Called once when monitor is first launched on a machine.

**Request:**
```json
{
  "license_key": "AEPRO-K7X2-M9QP-R3TZ",
  "hardware_id": "A1B2C3D4-XXXX-XXXX-XXXX-XXXXXXXXXXXX",
  "machine_id": "WORKSTATION-01"
}
```

**Response — Success:**
```json
{
  "status": "activated",
  "message": "License activated successfully",
  "expires_at": "2027-03-16",
  "days_left": 365,
  "tier": "1Y",
  "customer_name": "Al Habtoor Engineering"
}
```

**Response — Already Activated (different machine):**
```json
{
  "status": "already_activated",
  "message": "This key is already activated on another machine"
}
```

---

#### `POST /api/license/verify`
Called at monitor startup to confirm the license is still valid.

**Request:**
```json
{
  "license_key": "AEPRO-K7X2-M9QP-R3TZ",
  "hardware_id": "A1B2C3D4-XXXX-XXXX-XXXX-XXXXXXXXXXXX"
}
```

**Response values for `status`:**

| Status | Meaning | Monitor behaviour |
|---|---|---|
| `valid` | License OK | Monitor starts normally |
| `locked` | Admin locked this machine | Monitor exits with message |
| `expired` | Subscription expired | Monitor exits with message |
| `invalid` | Key not found in database | Monitor exits |
| `not_activated` | Key exists but never activated | Monitor triggers activation flow |

---

#### `POST /api/license/pulse`
Heartbeat — sent every 5 minutes while monitor is running.

**Request:**
```json
{
  "license_key": "AEPRO-K7X2-M9QP-R3TZ",
  "hardware_id": "A1B2C3D4-XXXX-XXXX-XXXX-XXXXXXXXXXXX"
}
```

**Response:**
```json
{
  "status": "ok",
  "days_left": 287,
  "expires_at": "2027-01-01",
  "tier": "1Y",
  "customer_name": "Al Habtoor Engineering"
}
```

If status is `locked` or `expired`, the monitor agent should exit immediately.

---

## 8. Installation & Deployment

### Step 1 — Deploy Monitor Agents (Client Machines)

**Prerequisites:**
- Windows Active Directory / Group Policy
- Node.js installed on client machines (OR use compiled exe)

**Process:**
1. Set the correct server URL in `twomonitor.js` line 8 before compiling:
   ```javascript
   const API_URL = 'http://[YOUR_SERVER_IP]:8000/api/log-activity';
   ```
2. Compile to exe: `pkg twomonitor.js --targets node18-win-x64 --output hazemonitor.exe`
3. Create GPO software deployment or startup script
4. Push these files to all machines:
   - `hazemonitor.exe`
   - `install.bat`
   - `start_silent.vbs`
5. `install.bat` runs automatically and:
   - Copies files to `C:\AutodeskMonitor\`
   - Registers Task Scheduler job `AutodeskMonitorAgent`
   - Job fires on every user login via `start_silent.vbs`

**Result:** Monitor starts silently on every user login, no user interaction needed.

---

### Step 2 — Deploy Laravel Dashboard

**Prerequisites:**
- PHP 8.2+
- Composer
- MySQL
- (Optional) Node.js for asset compilation

**Setup:**
```bash
cd laravel-app
composer install
cp .env.example .env
php artisan key:generate
```

**Configure `.env`:**
```env
APP_URL=http://[YOUR_SERVER_IP]:8000

DB_HOST=127.0.0.1
DB_DATABASE=autodesk_monitor
DB_USERNAME=root
DB_PASSWORD=[your-password]

LICENSE_MANAGER_URL=http://[LICENSE_HUB_IP]:8001
LICENSE_KEY=[key-issued-by-licensehub]
```

**Run migrations:**
```bash
php artisan migrate
php artisan db:seed   # Creates default admin user
```

**Start server:**
```bash
# Development:
php artisan serve --host=0.0.0.0 --port=8000

# Production: Use Nginx/IIS/Apache (see notes below)
```

**Start scheduler (no batch file needed — runs inside Laravel):**
```bash
php artisan schedule:work
```

---

### Step 3 — Deploy LicenseHub

**Prerequisites:** Same as Step 2 (PHP, Composer, MySQL)

**Setup:**
```bash
cd license-manager
composer install
cp .env.example .env
php artisan key:generate
```

**Configure `.env`:**
```env
APP_URL=http://[LICENSE_HUB_IP]:8001
DB_DATABASE=license_manager
DB_USERNAME=root
DB_PASSWORD=[your-password]
```

**Run migrations:**
```bash
php artisan migrate
```

**Start server:**
```bash
php artisan serve --host=0.0.0.0 --port=8001
```

**Log in to LicenseHub admin** and generate a license key for the dashboard deployment. Copy the key into the dashboard's `.env` as `LICENSE_KEY`.

---

### Production Deployment (Cloud / IIS)

For production, replace `php artisan serve` with a proper web server:

**Option A — Windows IIS:**
1. Install PHP Manager for IIS
2. Point IIS site to `laravel-app/public/`
3. Set `web.config` for URL rewriting

**Option B — Linux VPS (Nginx):**
```nginx
server {
    listen 80;
    server_name monitor.yourcompany.com;
    root /var/www/laravel-app/public;

    add_header X-Frame-Options "SAMEORIGIN";
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

**Scheduler on Linux (single cron entry):**
```cron
* * * * * cd /var/www/laravel-app && php artisan schedule:run >> /dev/null 2>&1
```

---

## 9. License System Deep Dive

### The Problem Being Solved

Autodesk licenses can be revoked from the portal externally. When this happens:
- The system still shows the user as having the license
- There's no automatic sync
- Admin needs to manually flag these cases

### Solution: Two-Layer License Tracking

**Layer 1 — Autodesk Licenses (user_licenses table)**
Tracks which software seats were officially assigned.

**Layer 2 — Revoked Software (revoked_software table)**
Admin overlay — marks software as physically revoked even if it still appears in Layer 1.

The dashboard merges both: shows software list with revocation badges overlaid.

---

### Subscription Key Renewal Flow

```
Year 1:
  Admin creates KEY1 → gives to customer
  Customer activates KEY1 on MACHINE-X
  KEY1.hardware_id = MACHINE-X (locked for 365 days)

Year 1 ends (KEY1 expires):
  Admin clicks "Regenerate ↻" on KEY1
  → KEY1 activations DELETED (hardware lock cleared)
  → KEY1 becomes KEY2 (new string)
  → KEY2.hardware_id = NULL (fresh, unlocked)

  Admin gives KEY2 to customer
  Customer enters KEY2 on MACHINE-X (or any machine)
  KEY2.hardware_id = MACHINE-X (fresh lock for 365 days)
```

**Why delete old activations?** Option 1 (clean slate) was chosen to prevent confusion. Old hardware locks from expired keys don't linger and can't interfere with new activations.

---

### Lock/Unlock Machine (Admin Control)

An admin can remotely lock a machine from the `/licenses` page:

```
Admin clicks 🔒 (yellow) on a license row
  → toggleLock() finds the activation record
  → Sets activation.status = 'locked'

Next time monitor sends pulse:
  → apiPulse() returns { "status": "locked" }
  → Monitor exits immediately

Admin clicks 🔓 (green) to unlock:
  → Sets activation.status = 'active'
  → Next pulse returns { "status": "ok" }
  → Monitor resumes
```

---

## 10. Configuration Reference

### `laravel-app/.env` — Key Variables

```env
# Application
APP_URL=http://[server-ip]:8000

# Database (MySQL)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=autodesk_monitor
DB_USERNAME=root
DB_PASSWORD=

# LicenseHub Connection (NEW — added for license scheduling)
LICENSE_MANAGER_URL=http://127.0.0.1:8001  # Change to LicenseHub server IP
LICENSE_KEY=                                # Paste key from LicenseHub here
```

### `license-manager/.env` — Key Variables

```env
# Application
APP_URL=http://[license-ip]:8001

# Database (MySQL — separate database from dashboard)
DB_CONNECTION=mysql
DB_DATABASE=license_manager
DB_USERNAME=root
DB_PASSWORD=
```

### `autodesk-montor/twomonitor.js` — Key Variables

```javascript
// Line 8 — change before compiling to exe
const API_URL = 'http://[SERVER_IP]:8000/api/log-activity';

// Line 6 — how often to check active window (milliseconds)
const CHECK_INTERVAL_MS = 3000;  // 3 seconds
```

### `autodesk-montor/admin/license.json` — Auto-generated on activation

```json
{
  "license_key": "AEPRO-XXXX-XXXX-XXXX",
  "server_url": "http://[licensehub-ip]:8001",
  "machine_id": "WORKSTATION-01",
  "hardware_id": "Windows-MachineGUID-From-Registry"
}
```

---

## 11. User Roles & Permissions

### laravel-app roles:

| Role | Sees | Can Do |
|---|---|---|
| `admin` | All users, all departments | Everything — revoke software, manage licenses, export data |
| `team_leader` | Only their assigned department | View their team's usage, no license management |
| `user` | Only their own profile | View personal usage only |

### LicenseHub roles:

Single admin role — there is no multi-user access to LicenseHub. The admin credentials are set in `.env` and the first migration seeds the admin account.

---

## 12. Scheduled Tasks

### laravel-app scheduler

Defined in `routes/console.php`:

```php
Schedule::command('license:check')->everyFiveMinutes();
```

**How to run (no batch file needed):**
```bash
# Keep this running permanently — it handles all scheduled tasks internally
php artisan schedule:work
```

**What `license:check` does:**
1. Reads `LICENSE_KEY` and `LICENSE_MANAGER_URL` from `.env`
2. Sends POST to `/api/license/pulse` on LicenseHub
3. Saves the response to Laravel cache (10 minute TTL)
4. Dashboard reads cache to show license status

**On Windows — register as service with NSSM:**
```
nssm install LaravelScheduler "php" "C:\path\to\laravel-app\artisan schedule:work"
nssm start LaravelScheduler
```

---

## 13. Monitored Software List

The monitor tracks 14 Autodesk products:

| Process Name | Friendly Name | Product |
|---|---|---|
| `acad` | AutoCAD | AutoCAD / Civil 3D / Plant 3D |
| `revit` | Revit | Revit (Architecture/Structure/MEP) |
| `3dsmax` | 3ds Max | 3ds Max |
| `roamer` | Navisworks | Navisworks Manage/Simulate |
| `infraworks` | InfraWorks | InfraWorks |
| `recap` | ReCap Pro | ReCap Pro |
| `desktopconnector` | Autodesk Docs | Autodesk Docs / Desktop Connector |
| `formit` | FormIt | FormIt 360 |
| `robot` | Robot Structural Analysis | Robot Structural Analysis Pro |
| `sbd` | Structural Bridge Design | Structural Bridge Design |
| `inventor` | Inventor | Inventor |
| `fusion360` | Fusion 360 | Fusion 360 |
| `estmep` | Fabrication ESTmep | Fabrication ESTmep |
| `camduct` | Fabrication CAMduct | Fabrication CAMduct |

**To add a new software:** Edit `TARGET_PROCESSES` and `SOFTWARE_MAPPING` arrays in `twomonitor.js`, then recompile the exe.

---

## 14. Project File Structure

```
autodesk-monitorapp/
│
├── DOCUMENTATION.md                   ← This file
├── plan.md                            ← Original project planning notes
│
├── autodesk-montor/                   ← Client-side monitor agents
│   ├── admin/
│   │   ├── activate.html              ← License activation UI
│   │   ├── activate.js                ← Writes license.json on activation
│   │   ├── start.bat                  ← Checks license.json, starts monitor
│   │   └── usermonitor.js             ← Full monitor with license check
│   ├── hazemonitor.exe                ← Compiled exe (deployed to machines)
│   ├── hazeMonitorUser.exe            ← Compiled exe with license check
│   ├── twomonitor.js                  ← Core monitor script (source of exe)
│   ├── onemonitor.js                  ← Older single variant
│   ├── monitor.js                     ← Dev/testing placeholder
│   ├── install.bat                    ← Group Policy installer
│   ├── uninstall.bat                  ← Removal script
│   ├── start_silent.vbs               ← Silent launcher (no window)
│   └── readme.txt                     ← Original setup notes
│
├── laravel-app/                       ← Main dashboard application
│   ├── app/
│   │   ├── Console/Commands/
│   │   │   └── CheckLicense.php       ← Artisan: php artisan license:check
│   │   ├── Http/Controllers/
│   │   │   ├── DashboardController.php ← Analytics & reports
│   │   │   ├── ActivityController.php  ← Receives monitor data
│   │   │   ├── AuthController.php      ← Login/logout
│   │   │   ├── ProfileController.php   ← User profile
│   │   │   ├── ReportController.php    ← Report generation
│   │   │   ├── SettingsController.php  ← App settings
│   │   │   └── UserController.php      ← User management
│   │   └── Models/
│   │       ├── ActivityLog.php         ← Usage log records
│   │       ├── User.php                ← Admin/team/user accounts
│   │       ├── UserLicense.php         ← License assignments
│   │       ├── RevokedSoftware.php     ← Admin revocation overlay
│   │       └── MonitorAssignment.php   ← Team monitoring scope
│   ├── config/
│   │   └── services.php               ← License manager URL config
│   ├── routes/
│   │   ├── web.php                    ← All dashboard routes
│   │   └── console.php                ← Scheduler (license:check)
│   ├── resources/views/               ← Blade templates
│   ├── database/migrations/           ← DB schema
│   └── .env                           ← Environment config
│
└── license-manager/                   ← LicenseHub admin application
    ├── app/
    │   ├── Http/Controllers/
    │   │   └── LicenseController.php  ← All license logic + API endpoints
    │   └── Models/
    │       ├── License.php            ← License keys
    │       └── Activation.php         ← Hardware activations
    ├── routes/
    │   ├── web.php                    ← Admin dashboard routes
    │   └── api.php                    ← /api/license/* endpoints
    ├── resources/views/
    │   ├── dashboard.blade.php        ← Admin overview
    │   ├── licenses.blade.php         ← License management table
    │   ├── api_reference.blade.php    ← API documentation page
    │   └── settings.blade.php         ← Admin settings
    ├── database/migrations/           ← licenses + activations schema
    └── .env                           ← Environment config
```

---

## 15. Future Developer Notes

### For AI Assistants Reading This

This project was built incrementally through a conversation-driven development process. Here are the key decisions made and why, so you don't undo them:

**1. Why is there a `revoked_software` table instead of deleting from `user_licenses`?**
The `activity_logs` table is raw historical data from the monitor agent. It cannot be edited retroactively. The `revoked_software` table acts as an admin overlay — a flag that says "even though the data shows this software, the admin says it's been physically removed." Never delete from `activity_logs`.

**2. Why does `regenerate()` delete activations instead of keeping them?**
This is "Option 1 — Clean Slate," chosen explicitly. When a license is renewed, old hardware locks are deleted completely. This prevents confusion and allows the customer to re-activate on the same or a different machine with a fresh start.

**3. Why is the scheduler inside Laravel (not a bat file)?**
The user's requirement was to avoid external batch files for the server-side license check. `php artisan schedule:work` keeps the scheduler alive inside the Laravel process itself. One command replaces multiple bat files.

**4. Why are there two compiled executables?**
- `hazemonitor.exe` — compiled from `twomonitor.js` — pure activity monitor, no license code
- `hazeMonitorUser.exe` — compiled from `usermonitor.js` — includes license verify + pulse heartbeat
The separation allows deploying the lightweight version first, then upgrading to the full licensed version when LicenseHub is fully operational.

**5. Why is `API_URL` hardcoded in `twomonitor.js` instead of a config file?**
The exe is deployed silently with no setup UI. A config file would require the admin to edit it manually on each machine, which defeats the purpose of Group Policy silent deployment. The URL is baked in at compile time. Change it in the source before running `pkg`.

**6. Hardware ID source**
The `hardware_id` used for license locking is the **Windows MachineGUID** from the registry at:
```
HKEY_LOCAL_MACHINE\SOFTWARE\Microsoft\Cryptography\MachineGuid
```
This is stable across reboots and user account changes. It only changes if Windows is reinstalled.

---

### Common Tasks for Future Developers

**Add a new monitored software:**
1. Edit `TARGET_PROCESSES` array in `twomonitor.js`
2. Edit `SOFTWARE_MAPPING` object in `twomonitor.js`
3. Recompile: `pkg twomonitor.js --targets node18-win-x64 --output hazemonitor.exe`
4. Redeploy exe via Group Policy

**Change the license check interval:**
```php
// routes/console.php in laravel-app
Schedule::command('license:check')->everyFiveMinutes();
// Change to: ->everyTenMinutes(), ->hourly(), etc.
```

**Add a new license tier:**
```php
// In license-manager's LicenseController.php generateKey()
// In licenses.blade.php tier selector <select>
// Tier codes: 7D, 15D, 6M, 1Y — follow same pattern
```

**Add a new admin user to the dashboard:**
```bash
cd laravel-app
php artisan tinker
User::create(['name'=>'Admin','email'=>'new@admin.com','password'=>bcrypt('pass'),'role'=>'admin']);
```

**Reset a hardware lock (customer's machine reformatted):**
1. Go to LicenseHub `/licenses`
2. Click "Regenerate ↻" on the customer's key
3. All old hardware locks are cleared
4. Customer activates fresh on their new machine

---

### Known Limitations

1. **Single machine per license key** — each key activates on exactly one machine. Multi-seat licenses are not currently supported.
2. **No email notifications** — no alerts when a license is about to expire. This can be added using Laravel's `Mail` + a new scheduled command.
3. **No automatic Autodesk API sync** — license assignments in the dashboard are entered manually. There is no direct connection to Autodesk's own portal API.
4. **HTTP only (localhost)** — local deployments use plain HTTP. For internet-facing deployments, configure SSL (HTTPS) with a proper certificate.
5. **No two-factor authentication** — both admin portals use username/password only.

---

*End of Documentation — ArchEng Pro v1.0*
