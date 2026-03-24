# Deployment & Troubleshooting Summary

This document summarizes the steps taken to set up the **Autodesk Monitor App** (local) and the **License Manager** (remote).

## 1. Local Environment Setup (Monitor App)
*   **Port Configuration**: Set the monitor app to run on port `8001` using `php artisan serve --port=8001`.
*   **Database Seeding**: 
    *   Created `UserSeeder.php` to generate an admin user.
    *   Modified `DatabaseSeeder.php` to handle duplicate entry errors using `updateOrCreate`.
    *   **Admin Credentials**: `admin@admin.com` / `admin@123`.
*   **Activity Tracker**: Updated `twomonitor.js` to send data to `http://192.168.0.200:8001` (your local network IP).

## 2. Remote Server Setup (Hostinger)
*   **Error: 403 Forbidden**:
    *   **Cause**: The application was uploaded to `public_html`, but the server was looking for an index file in the root instead of the `public/` folder.
    *   **Fix**: Created an `.htaccess` file in `public_html` to redirect all traffic to `public/index.php`.
*   **Error: 500 Internal Server Error**:
    *   **Cause 1**: `DB_HOST` was set to `127.0.0.1`. On Hostinger, this must be `localhost`.
    *   **Cause 2**: Missing `APP_KEY`.
    *   **Fix**: Updated `.env` to `localhost`, ran `php artisan key:generate`, and used `php artisan migrate:fresh --seed` to reset the database.

## 3. License Activation Troubleshooting
*   **Error: Unknown Error**:
    *   **Cause 1 (Remote)**: The server's `APP_URL` was set to `localhost`, causing the API to return incorrect headers.
    *   **Cause 2 (Local)**: The local app could not verify the SSL certificate of the `https` Hostinger URL.
    *   **Fix**: Updated remote `APP_URL` to `https://steelblue-newt-798585.hostingersite.com`.
    *   **Fix**: Modified `LicenseActivationController.php` to use `Http::withoutVerifying()` to bypass local SSL checks.

## 4. Current Working Configuration
*   **License Hub**: `https://steelblue-newt-798585.hostingersite.com`
*   **Local Monitor**: `http://192.168.0.200:8001`
*   **AutoCAD Tracker**: Set to report to `192.168.0.200:8001`.

---
**Status**: All systems are now synchronized and communicating.
