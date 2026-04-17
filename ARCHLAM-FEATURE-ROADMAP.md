# ARCHLAM — Feature Roadmap & Ideas

> **App:** ARCHLAM — Autodesk Software Monitoring & License Management
> **Version:** 0.11 (as of April 2026)
> **Purpose:** Track which Autodesk apps (AutoCAD, Revit, 3ds Max, etc.) are in use across an AEC company, manage licenses, and report productivity to management.

---

## ✅ Features Approved for Current Sprint

> These 5 features have been approved for implementation. They are built to be **additive only** — no existing functionality is affected.

---

### 1. ⚡ Live Real-Time Dashboard
**Status:** ✅ Implemented  
**Location:** `resources/views/dashboard.blade.php`

**What it does:**
- Dashboard auto-refreshes every **30 seconds**
- A green pulsing dot and countdown timer appear in the top-right of the dashboard header
- Preserves current URL parameters (date filters, department, period) during refresh
- No page flicker — smooth reload

**Why it matters:**
Without this, managers had to manually press F5 to see who just came online. Now the dashboard stays live throughout the day.

**Technical notes:**
- Implemented via `setInterval` + `window.location.reload()` in JavaScript
- Does NOT use WebSockets — no server configuration needed
- Countdown badge shows: `🟢 Refreshing in 28s`

---

### 2. 🕐 Idle Time Detection
**Status:** ✅ Implemented  
**Location:** `autodesk-montor/twomonitor.js`

**What it does:**
- Uses Windows Win32 `GetLastInputInfo` via PowerShell to check how long the user has been idle
- If system idle time exceeds **1 hour** (3,600,000 ms), the status sent to server changes from `Active` → `Idle`
- Dashboard and Users page show a **yellow ⚠ Idle** indicator instead of green 🟢 Online
- When user comes back and interacts, status automatically returns to `Active`

**Why it matters:**
Previously, if AutoCAD was open but the user was at lunch, it still logged "Active" and inflated productivity scores. Now idle time is tracked accurately.

**Threshold:** 1 hour (configurable in `twomonitor.js` → `IDLE_THRESHOLD_MS`)

**Technical notes:**
- PowerShell inline C# using `[DllImport("user32.dll")]` GetLastInputInfo
- Falls back gracefully if PowerShell call fails (continues normal Active logging)
- Activity logs now have 3 possible statuses: `Active`, `Idle`, `Open` (background app)

---

### 3. ⏰ Working Hours Configuration
**Status:** ✅ Implemented  
**Location:** `resources/views/settings.blade.php`, `app/Http/Controllers/SettingsController.php`

**What it does:**
- Admin can set **Work Start Time** and **Work End Time** in the Settings page
- Settings are saved to `storage/app/archlam_settings.json`
- Dashboard shows a small indicator: `⏰ Within Working Hours` or `🌙 Outside Working Hours`
- Settings persist across server restarts

**Why it matters:**
If someone leaves Revit open overnight, those hours should not count toward productivity. With working hours configured, admins know exactly which hours are "billable" working hours.

**Default values:** 08:00 – 18:00 (Monday to Friday)

**Future enhancement:** Filter productivity statistics to only count hours within working hours

---

### 4. 🖥️ Multi-Monitor / Multi-App Session Tracking
**Status:** ✅ Implemented  
**Location:** `autodesk-montor/twomonitor.js`

**What it does:**
- In addition to tracking the **active/focused** Autodesk app, twomonitor.js now also detects all **background running** Autodesk processes
- Background apps are logged with status `Open` (not counted in productivity hours)
- The twomonitor console now shows both active and background apps:
  ```
  [ACTIVE]     AutoCAD 2026   → Logged to server
  [BACKGROUND] Revit 2025     → Logged to server (status: Open)
  [BACKGROUND] Navisworks     → Logged to server (status: Open)
  ```
- On the Users page, you can see "multiple apps running" for a user

**Why it matters:**
A user may have Revit open (rendering in background) while actively working in AutoCAD. Both licenses are in use. Multi-app tracking captures this reality without double-counting productivity hours.

**Technical notes:**
- Active foreground app = status `Active` (counted in productivity)
- Background Autodesk apps = status `Open` (license is in use, not counted in productivity hours)
- Background apps are re-checked every **60 seconds** (not every 3 seconds — avoids spam)

---

### 5. 📋 Audit Trail Log
**Status:** ✅ Implemented  
**Location:** `resources/views/audit_trail.blade.php`, `app/Http/Controllers/AuditController.php`

**What it does:**
- Every admin action is recorded in the `audit_logs` database table
- Captured actions include:
  - ✅ License assigned to user
  - ❌ License removed from user
  - 🚫 Software suspended
  - 🔄 Software restored
  - ⛔ Software permanently removed
  - 👤 New system user created
  - ✏️ System user updated
  - 🗑️ System user deleted
- Each entry records: **who** did it, **what** they did, **which user** was affected, **IP address**, and **timestamp**

**Why it matters:**
If a license goes missing or a user claims they never had their access revoked, admins can look at the exact log entry showing who made the change and when.

**Access:** Admin only. Found at `/audit-trail` in the sidebar under **System**.

---

## 🟡 Next Sprint — Planned Features

### 6. 📊 Project-Based Time Tracking
Users log which **project** they are working on (e.g., "Block A Tower", "KLCC Renovation"). Reports show hours per project — perfect for client billing.

**Effort:** Medium | **Impact:** High

---

### 7. 📱 WhatsApp / Telegram Daily Summary
Every morning at 9am, Team Leaders receive a WhatsApp or Telegram message summarizing:
- Who was active yesterday
- Who was idle >2 hours
- Top performer of the day

**Effort:** Medium | **Impact:** High

---

### 8. 🔔 Smart Alert System
Automatic email or in-app alerts when:
- A licensed user hasn't opened their software in 7+ days
- A machine becomes a "ghost" (3+ days no activity)
- License utilization drops below 50%

**Effort:** Medium | **Impact:** High

---

### 9. 💰 License Cost Analytics
Admin enters cost per license. Dashboard shows:
- Cost per user per hour
- Underused licenses with estimated wasted cost
- ROI per department

**Effort:** Low | **Impact:** High

---

### 10. 📤 Bulk License Operations
Select multiple users and assign/revoke licenses in one action instead of one-by-one.

**Effort:** Low | **Impact:** Medium

---

### 11. 🌙 Overtime / After-Hours Flag
Automatically flag users working past the configured work end time or on weekends. Dedicated report page for management.

**Effort:** Low | **Impact:** Medium

---

## 🟢 Future / Long-Term Features

### 12. 📸 Optional Activity Screenshot
Every 30 minutes, `twomonitor.js` captures a small screenshot as proof of work.
> ⚠️ Requires employee consent and HR policy update.

**Effort:** High | **Impact:** Medium

---

### 13. 🤖 AI Productivity Insights (Claude API)
Use the Anthropic Claude API to analyze 30-day patterns and auto-generate written management insights:
> *"AutoCAD usage in MEP dropped 40% vs last month. Revit licenses in Structural are underutilized — consider reassigning 3 licenses to Architecture department."*

**Effort:** Medium | **Impact:** Very High

---

### 14. 🌐 Hostinger Cloud Deployment
Move the app from local server (`192.168.0.x`) to Hostinger cloud with a fixed domain (`https://archlam.yourdomain.com`).

**Benefits:**
- Works from any location (remote workers, site visits)
- No VPN or network configuration needed
- `twomonitor.js` client config becomes one fixed URL forever

**Effort:** Medium | **Impact:** Very High

---

### 15. 📱 Mobile Dashboard (PWA)
Convert the dashboard into a **Progressive Web App** so managers can check live stats from their phone browser without installing anything.

**Effort:** High | **Impact:** Medium

---

## 📋 Full Feature Priority Matrix

| # | Feature | Effort | Impact | Status |
|---|---------|--------|--------|--------|
| 1 | Live Real-Time Dashboard | Low | High | ✅ Done |
| 2 | Idle Time Detection (1 hour) | Low | High | ✅ Done |
| 3 | Working Hours Configuration | Low | High | ✅ Done |
| 4 | Multi-App Session Tracking | Medium | Medium | ✅ Done |
| 5 | Audit Trail Log | Medium | High | ✅ Done |
| 6 | Project Time Tracking | Medium | High | 🟡 Next |
| 7 | WhatsApp Daily Summary | Medium | High | 🟡 Next |
| 8 | Smart Alert System | Medium | High | 🟡 Next |
| 9 | License Cost Analytics | Low | High | 🟡 Next |
| 10 | Bulk License Operations | Low | Medium | 🟡 Next |
| 11 | Overtime / After-Hours Flag | Low | Medium | 🟡 Next |
| 12 | Activity Screenshot | High | Medium | 🟢 Future |
| 13 | AI Productivity Insights | Medium | Very High | 🟢 Future |
| 14 | Hostinger Cloud Deployment | Medium | Very High | 🟢 Future |
| 15 | Mobile Dashboard (PWA) | High | Medium | 🟢 Future |

---

## 🔧 Technical Architecture Notes

### Client Agent: twomonitor.js / hazemonitor.exe
- Runs as a Windows Service via NSSM on each client PC
- Checks active window every **3 seconds** using PowerShell Win32 API
- Sends HTTP POST to Laravel API endpoint
- No authentication required on the client (internal network only)

### Server: Laravel (PHP)
- MySQL database
- REST API endpoint: `POST /api/log-activity`
- Role-based access: Master Admin, Team Leader, Management
- Scheduled commands: weekly reports, license checks

### Database Tables
| Table | Purpose |
|-------|---------|
| `activity_logs` | Core tracking data (machine, user, app, status, ip, timestamp) |
| `user_licenses` | License assignments |
| `user_profiles` | Display names, departments, emails |
| `revoked_software` | Suspended / permanently removed access |
| `monitor_assignments` | Team leader → monitored users mapping |
| `audit_logs` | Admin action history ✅ NEW |
| `dismissed_notifications` | Per-user dismissed alerts |

---

## 📅 Change Log

| Date | Version | Change |
|------|---------|--------|
| 2026-04-12 | 0.10 | Renamed app from "ArchEng Pro Monitor" to "ARCHLAM" |
| 2026-04-12 | 0.10 | Added IP address capture and display on Users page |
| 2026-04-14 | 0.11 | Implemented features 1-5 from this roadmap |

---

*Document maintained by: ARCHLAM Development Team*
*Last updated: 2026-04-14*
