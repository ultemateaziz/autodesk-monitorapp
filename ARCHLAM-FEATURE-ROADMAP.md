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
 -# ARCHLAM — Feature Roadmap & Ideas                                                                                                                  
        1 +# ACLM — Application & Feature Presentation                                                                                                   
        2 +### Prompt Source for Google NotebookLM — 13 Slides                                                                                           
        3   
        3 -> **App:** ARCHLAM — Autodesk Software Monitoring & License Management                                                                        
        4 -> **Version:** 0.11 (as of April 2026)                                                                                                        
        5 -> **Purpose:** Track which Autodesk apps (AutoCAD, Revit, 3ds Max, etc.) are in use across an AEC company, manage licenses, and report product
          -ivity to management.                                                                                                                          
        4 +---                                                                                                                                           
        5  
        6 +## PRESENTATION INSTRUCTIONS FOR NOTEBOOKLM                                                                                                   
        7 +Generate a 13-slide professional PowerPoint presentation based on the content below.                                                          
        8 +- Tone: Executive business presentation, confident and clear                                                                                                 9 +- Audience: Management, HR, and IT decision-makers                                                                                            
       10 +- Style: Use real examples with User A, User B, User C as anonymised staff                                                                           
       11 +- Avoid all technology stack references (no code, no frameworks, no servers)                                                                         
       12 +- Each slide should have a headline, 3–5 bullet points, and one example or scenario
       13 +- Use visual suggestion notes where charts or diagrams would help
       14 + 
       15  ---
       16
        9 -## ✅ Features Approved for Current Sprint
       17 +## SLIDE 1 — TITLE SLIDE
       18
       11 -> These 5 features have been approved for implementation. They are built to be **additive only** — no existing functionality is affected.
       19 +**Title:** ACLM — Autodesk License & Activity Monitoring Platform
       20 +**Subtitle:** Visibility, Control, and Accountability Across Every Workstation
       21 +**Presenter Note:** This presentation covers all live features currently operational in the ACLM platform, designed for engineering and design       
          + organisations running Autodesk software.
       22
       23  ---
       24
       15 -### 1. ⚡ Live Real-Time Dashboard
       16 -**Status:** ✅ Implemented
       17 -**Location:** `resources/views/dashboard.blade.php`
       25 +## SLIDE 2 — THE PROBLEM WE SOLVE
       26
       19 -**What it does:**
       20 -- Dashboard auto-refreshes every **30 seconds**
       21 -- A green pulsing dot and countdown timer appear in the top-right of the dashboard header
       22 -- Preserves current URL parameters (date filters, department, period) during refresh
       23 -- No page flicker — smooth reload
       27 +**Headline:** Where Are Your Licenses Going?
       28
       25 -**Why it matters:**
       26 -Without this, managers had to manually press F5 to see who just came online. Now the dashboard stays live throughout the day.
       29 +Engineering firms face three costly blind spots every day:
       30
       28 -**Technical notes:**
       29 -- Implemented via `setInterval` + `window.location.reload()` in JavaScript
       30 -- Does NOT use WebSockets — no server configuration needed
       31 -- Countdown badge shows: `🟢 Refreshing in 28s`
       31 +- **Blind Spot 1 — Ghost Machines:** Computers consuming a license seat but with zero productivity for weeks or months. No one knows they are        
          +running.
       32 +- **Blind Spot 2 — Unused Licenses:** Software assigned to staff who rarely or never open it. The organisation pays full price for zero return       
          +.
       33 +- **Blind Spot 3 — No Accountability:** Managers cannot see who is actively working, who is idle, and who has not opened a billable tool in da       
          +ys.
       34
       35 +**Example:**
       36 +User A is assigned AutoCAD and Revit. Records show Revit has not been opened in 47 days. The seat costs the organisation thousands per year —        
          +with zero usage.
       37 + 
       38 +**Visual Suggestion:** Three icons representing a ghost computer, a sleeping license, and a question mark over a staff photo.
       39 + 
       40  ---
       41
       35 -### 2. 🕐 Idle Time Detection
       36 -**Status:** ✅ Implemented
       37 -**Location:** `autodesk-montor/twomonitor.js`
       42 +## SLIDE 3 — WHAT IS ACLM?
       43
       39 -**What it does:**
       40 -- Uses Windows Win32 `GetLastInputInfo` via PowerShell to check how long the user has been idle
       41 -- If system idle time exceeds **1 hour** (3,600,000 ms), the status sent to server changes from `Active` → `Idle`
       42 -- Dashboard and Users page show a **yellow ⚠ Idle** indicator instead of green 🟢 Online
       43 -- When user comes back and interacts, status automatically returns to `Active`
       44 +**Headline:** One Platform. Complete Visibility.
       45
       45 -**Why it matters:**
       46 -Previously, if AutoCAD was open but the user was at lunch, it still logged "Active" and inflated productivity scores. Now idle time is tracked       
          - accurately.
       46 +ACLM is a real-time monitoring and reporting platform that tracks how Autodesk software is used across every machine in your organisation — si       
          +lently, automatically, and without interrupting any user's work.
       47
       48 -**Threshold:** 1 hour (configurable in `twomonitor.js` → `IDLE_THRESHOLD_MS`)
       48 +**What it monitors:**
       49 +- Which Autodesk application is open and actively in use
       50 +- Which user is at the machine
       51 +- Whether the user is active or idle
       52 +- How many seats are in use across the organisation at any moment
       53 +- Which machines have been silent for days or weeks
       54
       50 -**Technical notes:**
       51 -- PowerShell inline C# using `[DllImport("user32.dll")]` GetLastInputInfo
       52 -- Falls back gracefully if PowerShell call fails (continues normal Active logging)
       53 -- Activity logs now have 3 possible statuses: `Active`, `Idle`, `Open` (background app)
       55 +**What it does NOT do:**
       56 +- It does not capture screenshots
       57 +- It does not record keystrokes or personal data
       58 +- It does not slow down any machine
       59
       60 +**Visual Suggestion:** A clean diagram showing workstations on the left sending silent signals to a central dashboard on the right.
       61 + 
       62  ---
       63
       57 -### 3. ⏰ Working Hours Configuration
       58 -**Status:** ✅ Implemented
       59 -**Location:** `resources/views/settings.blade.php`, `app/Http/Controllers/SettingsController.php`
       64 +## SLIDE 4 — FEATURE: REAL-TIME ACTIVITY MONITORING
       65
       61 -**What it does:**
       62 -- Admin can set **Work Start Time** and **Work End Time** in the Settings page
       63 -- Settings are saved to `storage/app/archlam_settings.json`
       64 -- Dashboard shows a small indicator: `⏰ Within Working Hours` or `🌙 Outside Working Hours`
       65 -- Settings persist across server restarts
       66 +**Headline:** Know Exactly What Is Open, Right Now
       67
       67 -**Why it matters:**
       68 -If someone leaves Revit open overnight, those hours should not count toward productivity. With working hours configured, admins know exactly w       
          -hich hours are "billable" working hours.
       68 +Every few seconds ACLM checks the active foreground application on each monitored machine. It also periodically scans for Autodesk tools runni       
          +ng in the background.
       69
       70 -**Default values:** 08:00 – 18:00 (Monday to Friday)
       70 +**Applications tracked include:**
       71 +- AutoCAD, Civil 3D, Plant 3D
       72 +- Revit
       73 +- Navisworks
       74 +- Inventor
       75 +- 3ds Max, Fusion 360
       76 +- InfraWorks, ReCap Pro, Robot Structural Analysis, and more
       77
       72 -**Future enhancement:** Filter productivity statistics to only count hours within working hours
       78 +**What gets recorded for each session:**
       79 +- Machine name and username
       80 +- Application name and version year
       81 +- Status — Active (user is working) or Idle (no input beyond threshold)
       82 +- Exact timestamp of every recorded event
       83
       84 +**Example Scenario:**
       85 +At 9:14 AM, User B opens AutoCAD on workstation WS-04. ACLM logs this within seconds. At 10:52 AM, User B steps away. After 60 minutes with no       
          + keyboard or mouse input, ACLM marks the session as Idle. The manager sees both states on the dashboard in real time.
       86 + 
       87 +**Visual Suggestion:** A timeline bar showing Active (green) and Idle (amber) segments across a working day.
       88 + 
       89  ---
       90
       76 -### 4. 🖥️ Multi-Monitor / Multi-App Session Tracking
       77 -**Status:** ✅ Implemented
       78 -**Location:** `autodesk-montor/twomonitor.js`
       91 +## SLIDE 5 — FEATURE: CENTRAL DASHBOARD
       92
       80 -**What it does:**
       81 -- In addition to tracking the **active/focused** Autodesk app, twomonitor.js now also detects all **background running** Autodesk processes
       82 -- Background apps are logged with status `Open` (not counted in productivity hours)
       83 -- The twomonitor console now shows both active and background apps:
       84 -  ```
       85 -  [ACTIVE]     AutoCAD 2026   → Logged to server
       86 -  [BACKGROUND] Revit 2025     → Logged to server (status: Open)
       87 -  [BACKGROUND] Navisworks     → Logged to server (status: Open)
       88 -  ```
       89 -- On the Users page, you can see "multiple apps running" for a user
       93 +**Headline:** Your Entire Organisation on One Screen
       94
       91 -**Why it matters:**
       92 -A user may have Revit open (rendering in background) while actively working in AutoCAD. Both licenses are in use. Multi-app tracking captures        
          -this reality without double-counting productivity hours.
       95 +The ACLM dashboard gives managers an instant operational overview without asking anyone a single question.
       96
       94 -**Technical notes:**
       95 -- Active foreground app = status `Active` (counted in productivity)
       96 -- Background Autodesk apps = status `Open` (license is in use, not counted in productivity hours)
       97 -- Background apps are re-checked every **60 seconds** (not every 3 seconds — avoids spam)
       97 +**Dashboard panels include:**
       98 +- **Live Activity Feed** — every active session across all machines updated in real time
       99 +- **Today's Usage Summary** — total active hours logged today across the organisation
      100 +- **Top Applications in Use** — ranked by hours consumed today
      101 +- **Machine Status Cards** — which machines are active, idle, or silent
      102 +- **Idle Alert Flags** — users who have been idle beyond the configured threshold
      103
      104 +**Configurable Idle Threshold:**
      105 +Managers set the idle timeout that suits their organisation — 30 minutes, 60 minutes, or custom. Any session exceeding this is automatically f       
          +lagged as Idle, not Active.
      106 + 
      107 +**Example Scenario:**
      108 +The operations manager opens the dashboard at 2 PM. She immediately sees 12 machines are active, 3 are idle (flagged in amber), and 2 have bee       
          +n completely silent since morning. No phone calls. No emails. Just the dashboard.
      109 + 
      110 +**Visual Suggestion:** A screenshot-style mock of the dashboard with colour-coded machine cards.
      111 + 
      112  ---
      113
      101 -### 5. 📋 Audit Trail Log
      102 -**Status:** ✅ Implemented
      103 -**Location:** `resources/views/audit_trail.blade.php`, `app/Http/Controllers/AuditController.php`
      114 +## SLIDE 6 — FEATURE: USER PRODUCTIVITY PROFILE
      115
      105 -**What it does:**
      106 -- Every admin action is recorded in the `audit_logs` database table
      107 -- Captured actions include:
      108 -  - ✅ License assigned to user
      109 -  - ❌ License removed from user
      110 -  - 🚫 Software suspended
      111 -  - 🔄 Software restored
      112 -  - ⛔ Software permanently removed
      113 -  - 👤 New system user created
      114 -  - ✏️ System user updated
      115 -  - 🗑️ System user deleted
      116 -- Each entry records: **who** did it, **what** they did, **which user** was affected, **IP address**, and **timestamp**
      116 +**Headline:** Every Staff Member Has a Productivity Story
      117
      118 -**Why it matters:**
      119 -If a license goes missing or a user claims they never had their access revoked, admins can look at the exact log entry showing who made the ch       
          -ange and when.
      118 +Each user in the system has a dedicated profile page that shows their complete activity history at a glance.
      119
      121 -**Access:** Admin only. Found at `/audit-trail` in the sidebar under **System**.
      120 +**What the profile shows:**
      121 +- Daily and weekly active hours in each Autodesk application
      122 +- Idle time percentage — how much of their logged session was genuinely productive
      123 +- Application usage breakdown — which tools they use and how often
      124 +- Productivity trend over the last 30 days — improving, declining, or consistent
      125 +- Most recent sessions with timestamps
      126
      127 +**Status indicators per session:**
      128 +- **Active** — user was working (input detected)
      129 +- **Idle** — application was open but no input for the configured threshold period
      130 +- **Open** — application was running in the background while another tool was in focus
      131 + 
      132 +**Example Scenario:**
      133 +User C's profile shows strong AutoCAD usage Monday to Wednesday each week, but very low Revit usage despite holding an active Revit license. T       
          +heir manager uses this data during the monthly review — not to penalise, but to ask whether they need training support or whether the Revit se       
          +at can be reassigned.
      134 + 
      135 +**Visual Suggestion:** A bar chart per day of the week, split by application, with a small idle percentage indicator.
      136 + 
      137  ---
      138
      125 -## 🟡 Next Sprint — Planned Features
      139 +## SLIDE 7 — FEATURE: LEADERBOARD
      140
      127 -### 6. 📊 Project-Based Time Tracking
      128 -Users log which **project** they are working on (e.g., "Block A Tower", "KLCC Renovation"). Reports show hours per project — perfect for clien       
          -t billing.
      141 +**Headline:** Healthy Productivity — Recognised and Visible
      142
      130 -**Effort:** Medium | **Impact:** High
      143 +The ACLM Leaderboard ranks users by total productive hours logged across the organisation for the current period.
      144
      132 ----
      145 +**Leaderboard features:**
      146 +- Top performers ranked by total active (non-idle) hours
      147 +- Department filter — see rankings within a specific team
      148 +- Application filter — see who uses a specific tool the most
      149 +- Weekly and monthly views
      150 +- Medal indicators for top three positions
      151
      134 -### 7. 📱 WhatsApp / Telegram Daily Summary
      135 -Every morning at 9am, Team Leaders receive a WhatsApp or Telegram message summarizing:
      136 -- Who was active yesterday
      137 -- Who was idle >2 hours
      138 -- Top performer of the day
      152 +**Why it matters:**
      153 +The leaderboard is not a surveillance tool — it is a recognition tool. High performers are visible to management. It also highlights users who       
          + may need support, redeployment, or license reallocation.
      154
      140 -**Effort:** Medium | **Impact:** High
      155 +**Example Scenario:**
      156 +This month, User A tops the leaderboard with 187 active hours in AutoCAD across 22 working days — an average of 8.5 productive hours per day.        
          +User B ranks second with 143 hours across Revit and Navisworks. Management uses this to nominate both users for the quarterly high-performer r       
          +ecognition programme.
      157
      158 +**Visual Suggestion:** A podium-style leaderboard with rank numbers, user initials, application icons, and hour counts.
      159 + 
      160  ---
      161
      144 -### 8. 🔔 Smart Alert System
      145 -Automatic email or in-app alerts when:
      146 -- A licensed user hasn't opened their software in 7+ days
      147 -- A machine becomes a "ghost" (3+ days no activity)
      148 -- License utilization drops below 50%
      162 +## SLIDE 8 — FEATURE: DEPARTMENT EFFICIENCY BENCHMARK
      163
      150 -**Effort:** Medium | **Impact:** High
      164 +**Headline:** Which Teams Are Delivering? Which Need Attention?
      165
      152 ----
      166 +ACLM groups users by department and benchmarks their collective productivity — making it easy to compare performance across teams.
      167
      154 -### 9. 💰 License Cost Analytics
      155 -Admin enters cost per license. Dashboard shows:
      156 -- Cost per user per hour
      157 -- Underused licenses with estimated wasted cost
      158 -- ROI per department
      168 +**What the department view shows:**
      169 +- Total active hours per department for the selected period
      170 +- Idle percentage per department — how much of their logged time was non-productive
      171 +- Application distribution — which tools each department relies on most
      172 +- Comparison chart — departments ranked side by side
      173 +- Trend line — efficiency improving or declining over time
      174
      160 -**Effort:** Low | **Impact:** High
      175 +**Threshold benchmarking:**
      176 +Managers set a target efficiency percentage (for example, 70% active out of total logged time). Departments falling below this threshold are h       
          +ighlighted in amber or red.
      177
      178 +**Example Scenario:**
      179 +The Structures department logs 1,240 hours this month. Of those, 890 are Active and 350 are Idle — a 71.8% efficiency rate, just above the 70%       
          + target. The MEP department logs 980 hours but only 590 are Active — 60.2% — flagged below the target. Management schedules a check-in with th       
          +e MEP team leader.
      180 + 
      181 +**Visual Suggestion:** A horizontal bar chart with departments on the Y axis and efficiency percentage on the X axis, with a vertical line mar       
          +king the 70% threshold.
      182 + 
      183  ---
      184
      164 -### 10. 📤 Bulk License Operations
      165 -Select multiple users and assign/revoke licenses in one action instead of one-by-one.
      185 +## SLIDE 9 — FEATURE: GHOST MACHINE DETECTION
      186
      167 -**Effort:** Low | **Impact:** Medium
      187 +**Headline:** Stop Paying for Machines Nobody Uses
      188
      169 ----
      189 +A ghost machine is a workstation that holds an active license but shows zero activity for an extended period. ACLM detects and flags these aut       
          +omatically.
      190
      171 -### 11. 🌙 Overtime / After-Hours Flag
      172 -Automatically flag users working past the configured work end time or on weekends. Dedicated report page for management.
      191 +**What qualifies as a ghost machine:**
      192 +- No Autodesk application opened in the last N days (configurable — default 7 days)
      193 +- Machine was previously active but has gone silent
      194 +- License seat is still allocated and counted against the organisation's total
      195
      174 -**Effort:** Low | **Impact:** Medium
      196 +**What the ghost machine view shows:**
      197 +- Full list of silent machines with last-seen date
      198 +- Days since last activity
      199 +- Which application was last used before going silent
      200 +- Which user was last logged in
      201 +- Dismissal option — manager can mark a machine as acknowledged (e.g. staff on leave)
      202
      203 +**Example Scenario:**
      204 +ACLM flags workstation WS-11 as a ghost machine. Last activity was 19 days ago — User A opened AutoCAD briefly before going on extended leave.       
          + The license seat has been consumed for 19 days with zero return. The IT manager uses ACLM to reclaim the seat and reassign it to a new joiner       
          + while User A is away.
      205 + 
      206 +**Visual Suggestion:** A list of machine cards with red ghost indicators and last-seen timestamps.
      207 + 
      208  ---
      209
      178 -## 🟢 Future / Long-Term Features
      210 +## SLIDE 10 — FEATURE: LICENSE AUDIT & OPTIMISATION
      211
      180 -### 12. 📸 Optional Activity Screenshot
      181 -Every 30 minutes, `twomonitor.js` captures a small screenshot as proof of work.
      182 -> ⚠️ Requires employee consent and HR policy update.
      212 +**Headline:** Are You Getting Value From Every License You Pay For?
      213
      184 -**Effort:** High | **Impact:** Medium
      214 +ACLM cross-references which software licenses are assigned to each user against what they actually open and use — revealing waste clearly and        
          +quantifiably.
      215
      186 ----
      216 +**License Audit view shows:**
      217 +- Every user with an assigned software license
      218 +- Whether that software was opened in the selected period
      219 +- Usage rate percentage — how often the assigned software was actually used
      220 +- Colour-coded ratings: Ghost (never used), Critical (rarely used), Justified (regularly used)
      221
      188 -### 13. 🤖 AI Productivity Insights (Claude API)
      189 -Use the Anthropic Claude API to analyze 30-day patterns and auto-generate written management insights:
      190 -> *"AutoCAD usage in MEP dropped 40% vs last month. Revit licenses in Structural are underutilized — consider reassigning 3 licenses to Archit       
          -ecture department."*
      222 +**License Optimisation view shows:**
      223 +- Users with multiple assigned licenses — how many they actually use
      224 +- Recommended reallocation candidates — licenses that could be freed
      225 +- Potential cost saving based on unused seats
      226
      192 -**Effort:** Medium | **Impact:** Very High
      227 +**Ratings explained:**
      228 +- **Ghost** — license assigned, software never opened in the period
      229 +- **Critical** — software opened fewer than 5 times in the period
      230 +- **Warning** — software used but below expected frequency
      231 +- **Justified** — software used regularly and productively
      232
      233 +**Example Scenario:**
      234 +The audit shows User C holds licenses for AutoCAD, Revit, and Navisworks. In the last 30 days they opened AutoCAD 62 times (Justified), Revit        
          +3 times (Critical), and Navisworks zero times (Ghost). The recommendation: reassign the Navisworks seat immediately and review the Revit seat        
          +at next quarter.
      235 + 
      236 +**Visual Suggestion:** A table with user rows, software columns, and colour-coded cells — green, amber, red, grey.
      237 + 
      238  ---
      239
      196 -### 14. 🌐 Hostinger Cloud Deployment
      197 -Move the app from local server (`192.168.0.x`) to Hostinger cloud with a fixed domain (`https://archlam.yourdomain.com`).
      240 +## SLIDE 11 — FEATURE: AUTOMATED REPORTS
      241
      199 -**Benefits:**
      200 -- Works from any location (remote workers, site visits)
      201 -- No VPN or network configuration needed
      202 -- `twomonitor.js` client config becomes one fixed URL forever
      242 +**Headline:** Reports That Write Themselves — Every Week
      243
      204 -**Effort:** Medium | **Impact:** Very High
      244 +ACLM automatically generates and emails two types of reports on a scheduled basis — no manual effort from management required.
      245
      206 ----
      246 +**Report Type 1 — Individual Weekly Performance Report:**
      247 +Each monitored user receives a personal summary of their own week, including:
      248 +- Total active hours by day
      249 +- Applications used and time in each
      250 +- Idle time percentage
      251 +- Comparison to their own previous week
      252
      208 -### 15. 📱 Mobile Dashboard (PWA)
      209 -Convert the dashboard into a **Progressive Web App** so managers can check live stats from their phone browser without installing anything.
      253 +**Report Type 2 — Team Weekly Summary Report:**
      254 +Department managers receive a consolidated team summary, including:
      255 +- Ranking of all team members by productive hours
      256 +- Department-level efficiency compared to the previous week
      257 +- Flagged concerns — users with unusually low activity
      258 +- Ghost machine alerts if any appeared during the week
      259
      211 -**Effort:** High | **Impact:** Medium
      260 +**Example Scenario:**
      261 +Every Monday at 8 AM, User A receives their personal weekly report by email. This week it shows 43.5 active hours, up from 38.2 the week befor       
          +e — a 14% improvement. Their manager receives the team summary showing the full department trended upward by 9% week-on-week. No meeting requi       
          +red to communicate this.
      262
      263 +**Visual Suggestion:** A mock email preview showing a clean report with a bar chart, a ranking table, and a highlighted improvement percentage       
          +.
      264 + 
      265  ---
      266
      215 -## 📋 Full Feature Priority Matrix
      267 +## SLIDE 12 — FEATURE: LICENSE KEY MANAGEMENT SYSTEM
      268
      217 -| # | Feature | Effort | Impact | Status |
      218 -|---|---------|--------|--------|--------|
      219 -| 1 | Live Real-Time Dashboard | Low | High | ✅ Done |
      220 -| 2 | Idle Time Detection (1 hour) | Low | High | ✅ Done |
      221 -| 3 | Working Hours Configuration | Low | High | ✅ Done |
      222 -| 4 | Multi-App Session Tracking | Medium | Medium | ✅ Done |
      223 -| 5 | Audit Trail Log | Medium | High | ✅ Done |
      224 -| 6 | Project Time Tracking | Medium | High | 🟡 Next |
      225 -| 7 | WhatsApp Daily Summary | Medium | High | 🟡 Next |
      226 -| 8 | Smart Alert System | Medium | High | 🟡 Next |
      227 -| 9 | License Cost Analytics | Low | High | 🟡 Next |
      228 -| 10 | Bulk License Operations | Low | Medium | 🟡 Next |
      229 -| 11 | Overtime / After-Hours Flag | Low | Medium | 🟡 Next |
      230 -| 12 | Activity Screenshot | High | Medium | 🟢 Future |
      231 -| 13 | AI Productivity Insights | Medium | Very High | 🟢 Future |
      232 -| 14 | Hostinger Cloud Deployment | Medium | Very High | 🟢 Future |
      233 -| 15 | Mobile Dashboard (PWA) | High | Medium | 🟢 Future |
      269 +**Headline:** Every Deployment of ACLM Is Controlled and Accountable
      270
      235 ----
      271 +ACLM includes a built-in licensing system that controls how many organisations and machines can use the platform — giving the provider full vi       
          +sibility and control over every active installation.
      272
      237 -## 🔧 Technical Architecture Notes
      273 +**How it works for the organisation:**
      274 +- The organisation receives a unique license key when they subscribe
      275 +- The key is entered once during setup — no further action needed by staff
      276 +- ACLM verifies the license silently at startup — no user ever sees this process
      277 +- If the license expires, the platform alerts the administrator before it stops working
      278
      239 -### Client Agent: twomonitor.js / hazemonitor.exe
      240 -- Runs as a Windows Service via NSSM on each client PC
      241 -- Checks active window every **3 seconds** using PowerShell Win32 API
      242 -- Sends HTTP POST to Laravel API endpoint
      243 -- No authentication required on the client (internal network only)
      279 +**Subscription tiers available:**
      280 +- 7-Day Trial — for evaluation
      281 +- 15-Day Short — for short project deployments
      282 +- 1-Month — monthly rolling subscription
      283 +- 6-Month — semi-annual commitment
      284 +- 1-Year — annual subscription, best value
      285
      245 -### Server: Laravel (PHP)
      246 -- MySQL database
      247 -- REST API endpoint: `POST /api/log-activity`
      248 -- Role-based access: Master Admin, Team Leader, Management
      249 -- Scheduled commands: weekly reports, license checks
      286 +**Seat control:**
      287 +Each license specifies how many machines can run ACLM simultaneously. If a new machine exceeds the purchased seat count it is blocked automati       
          +cally until a seat becomes available.
      288
      251 -### Database Tables
      252 -| Table | Purpose |
      253 -|-------|---------|
      254 -| `activity_logs` | Core tracking data (machine, user, app, status, ip, timestamp) |
      255 -| `user_licenses` | License assignments |
      256 -| `user_profiles` | Display names, departments, emails |
      257 -| `revoked_software` | Suspended / permanently removed access |
      258 -| `monitor_assignments` | Team leader → monitored users mapping |
      259 -| `audit_logs` | Admin action history ✅ NEW |
      260 -| `dismissed_notifications` | Per-user dismissed alerts |
      289 +**Example Scenario:**
      290 +Organisation ABC purchases a 1-Year license with 15 seats. Over 6 months, 14 machines activate successfully. On month 7, a 16th machine attemp       
          +ts to activate. ACLM blocks it and notifies the administrator: "Seat limit reached — 15 of 15 seats in use. Contact your provider to increase        
          +allocation."
      291
      292 +**Visual Suggestion:** A diagram showing a license key flowing to multiple workstations with a seat counter showing 14 of 15 used.
      293 + 
      294  ---
      295
      264 -## 📅 Change Log
      296 +## SLIDE 13 — SUMMARY & WHAT IS COMING NEXT
      297
      266 -| Date | Version | Change |
      267 -|------|---------|--------|
      268 -| 2026-04-12 | 0.10 | Renamed app from "ArchEng Pro Monitor" to "ARCHLAM" |
      269 -| 2026-04-12 | 0.10 | Added IP address capture and display on Users page |
      270 -| 2026-04-14 | 0.11 | Implemented features 1-5 from this roadmap |
      298 +**Headline:** ACLM — What It Delivers Today and Where It Is Heading
      299
      300 +**What ACLM delivers right now:**
      301 +- Real-time monitoring of all Autodesk application usage across every machine
      302 +- Full user productivity profiles with active vs idle breakdown
      303 +- Department efficiency benchmarking with configurable targets
      304 +- Ghost machine detection and alerts
      305 +- License audit — assigned vs actually used, with waste ratings
      306 +- Automated weekly reports delivered by email to users and managers
      307 +- Central license key management with seat control and expiry alerts
      308 + 
      309 +**Measurable outcomes organisations can expect:**
      310 +- Reduction in wasted license spend through reallocation of unused seats
      311 +- Increased manager visibility without increasing administrative overhead
      312 +- Faster identification of underperforming machines or workstations
      313 +- Evidence-based conversations in performance reviews — data, not opinion
      314 + 
      315 +**What is planned for future releases:**
      316 +- Mobile dashboard view for managers on the move
      317 +- Custom report builder — choose your own date range, users, and applications
      318 +- Predictive alerts — flag users trending toward underperformance before it becomes a problem
      319 +- Integration with HR systems for automated onboarding and offboarding of monitored users
      320 +- Branded PDF export with company cover page
      321 + 
      322 +**Closing statement:**
      323 +ACLM transforms an invisible cost — unused software licenses and unproductive workstations — into visible, actionable intelligence. Every seat       
          + justified. Every machine accountable. Every hour counted.
      324 + 
      325  ---
      326
      274 -*Document maintained by: ARCHLAM Development Team*
      275 -*Last updated: 2026-04-14*
      327 +*End of source document — 13 slides*