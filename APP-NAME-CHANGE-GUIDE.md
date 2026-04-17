# ASCLAM — Application Name Change Guide

> Use this document whenever you want to rename the application.
> Only frontend (blade) files need to be changed. Database and functions are not affected.

---

## Current Application Name
**ASCLAM**

---

## How to Change the Name (Step by Step)

### Step 1 — Find all occurrences
Run this command in the laravel-app folder to find every place the name appears:
```bash
grep -r "ASCLAM" resources/views/ --include="*.blade.php" -l
```

### Step 2 — Replace in all files at once
Replace `ASCLAM` with your new name (e.g. `NEWNAME`):
```bash
# On Linux/Mac
find resources/views -name "*.blade.php" -exec sed -i 's/ASCLAM/NEWNAME/g' {} +

# On Windows PowerShell
Get-ChildItem -Path "resources\views" -Recurse -Filter "*.blade.php" | 
ForEach-Object { (Get-Content $_.FullName) -replace "ASCLAM", "NEWNAME" | Set-Content $_.FullName }
```

---

## All Files Containing the Application Name

| # | File Path | Lines Changed | What it controls |
|---|-----------|---------------|-----------------|
| 1 | `resources/views/auth/login.blade.php` | 7, 328 | Login page title + logo |
| 2 | `resources/views/dashboard.blade.php` | 7, 71 | Dashboard title + sidebar logo |
| 3 | `resources/views/department_efficiency.blade.php` | 7, 169 | Department page title + logo |
| 4 | `resources/views/ghost_machines.blade.php` | 7, 135 | Ghost machines title + logo |
| 5 | `resources/views/leaderboard.blade.php` | 7, 273 | Leaderboard title + logo |
| 6 | `resources/views/license_audit.blade.php` | 7, 62 | License audit title + logo |
| 7 | `resources/views/license_optimization.blade.php` | 7, 204 | License optimization title + logo |
| 8 | `resources/views/machine_inventory.blade.php` | 7, 93 | Machine inventory title + logo |
| 9 | `resources/views/profile.blade.php` | 7, 294 | Profile page title + logo |
| 10 | `resources/views/report_hub.blade.php` | 7, 226 | Report hub title + logo |
| 11 | `resources/views/report_pdf.blade.php` | 538, 847 | PDF report header + footer |
| 12 | `resources/views/settings.blade.php` | 7, 155 | Settings page title + logo |
| 13 | `resources/views/users.blade.php` | 7, 108 | Users page title + logo |
| 14 | `resources/views/user_management.blade.php` | 7, 197 | User management title + logo |
| 15 | `resources/views/emails/individual_report.blade.php` | 129, 235, 245, 250 | Email report header + footer |
| 16 | `resources/views/partials/license_status_banner.blade.php` | 41 | Subscription expired banner |
| 17 | `resources/views/emails/weekly_report.blade.php` | 112, 216, 221 | Weekly email header + footer |

---

## Name Change History

| Date | Old Name | New Name | Changed By |
|------|----------|----------|------------|
| 2026-04-12 | ArchEng Pro Monitor | ARCHLAM | Developer |
| 2026-04-17 | ARCHLAM | ASCLAM | Developer |

---

## Important Notes

- ✅ Only frontend `.blade.php` files need to change
- ✅ Database tables, columns, functions are NOT affected
- ✅ Routes, controllers, models do NOT need to change
- ✅ The Node.js monitor agent (twomonitor.js / hazemonitor.exe) does NOT need to change
- ⚠️ Browser tab title changes immediately after save (no rebuild needed)
- ⚠️ Email templates take effect on next email sent

---

## Files That Do NOT Need Changing

| File | Reason |
|------|--------|
| `app/Http/Controllers/*.php` | Backend logic only |
| `database/migrations/*.php` | Database structure only |
| `app/Models/*.php` | Data models only |
| `routes/web.php` | URL routes only |
| `config/app.php` | Laravel config (uses APP_NAME from .env) |
| `autodesk-montor/twomonitor.js` | Monitor agent, no display name |
| `hazemonitor.exe` | Compiled agent, no display name |
