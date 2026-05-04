# ACLAM — Pre-Installation Requirements
**Application:** ACLAM (Autodesk License & Activity Monitor)  
**Document Version:** 1.0  
**Date:** 2026-04-26  
**Purpose:** Submit this document to the client before installation begins. Lists all software and network conditions required on the server machine and each client PC.

---

## PART A — SERVER MACHINE REQUIREMENTS
*(One dedicated Windows PC that runs the dashboard)*

| # | Software | Version Required | Why It Is Needed | Where to Get |
|---|---|---|---|---|
| 1 | **Windows OS** | Windows 10 / 11 (64-bit) | The server and all services run on Windows | Built-in |
| 2 | **Laragon** | Latest (Full version) | All-in-one bundle that installs Apache, MySQL, and PHP together on Windows. It manages the web server and database engine in one tool. Much easier than installing each separately. | laragon.org |
| 3 | **Apache** *(inside Laragon)* | 2.4.x | The web server. It receives browser requests and hands them to PHP/Laravel to process. Without it the dashboard cannot be opened in a browser. | Included in Laragon |
| 4 | **PHP** | **8.2.x minimum** | ACLAM's backend is built in Laravel which runs on PHP. The version must be 8.2 or higher — older versions will fail to run the application. | Included in Laragon |
| 5 | **MySQL** *(inside Laragon)* | 8.0.x | The database that stores all activity logs, users, license data, and settings. Every time a user opens AutoCAD the agent sends a record — MySQL stores it. Without the database the entire application has no data. | Included in Laragon |
| 6 | **Composer** | 2.x | PHP's dependency manager. It downloads and installs all the PHP libraries that Laravel needs (PDF generation, authentication, API helpers). Run once during setup via `composer install`. | getcomposer.org |
| 7 | **Node.js** | **18 LTS (v18.20.x)** | Used to build the `hazemonitor.exe` agent from source. Also used by Laravel's front-end build process (`npm run build`). Must be version 18 — other versions may break the pkg compiler. | nodejs.org |
| 8 | **Git** | Latest | Required by Composer internally to download certain PHP packages. If Git is missing, `composer install` will fail with a "zip extension missing" error. | git-scm.com |
| 9 | **NSSM** | 2.24 | Non-Sucking Service Manager. Registers the Laravel server as a Windows background service so the dashboard stays online automatically after every PC restart — no terminal window needs to stay open. | nssm.cc *(also provided by us)* |
| 10 | **Notepad++** | Latest | Used to edit configuration files such as `.env` (which holds the server IP, database name, and mail settings). Windows Notepad cannot properly display these files — Notepad++ shows correct formatting and line endings. | notepad-plus-plus.org |
| 11 | **Windows Firewall — Port 8001** | Built-in | Port 8001 must be opened so client PCs on the same network can reach the dashboard and send activity data. Command: `netsh advfirewall firewall add rule name="ACLAM" dir=in action=allow protocol=TCP localport=8001` | Built-in (we run this during setup) |
| 12 | **PowerShell** | 5.1+ | Used by NSSM and some Laravel setup commands. Already built into Windows 10/11 — no install needed. | Built-in |

---

## PART B — CLIENT PC REQUIREMENTS
*(Every designer/user machine that runs Autodesk software)*

| # | Requirement | Why It Is Needed |
|---|---|---|
| 1 | **Windows 10 or Windows 11 (64-bit)** | The `hazemonitor.exe` agent is compiled for Windows 64-bit only. |
| 2 | **Administrator rights at time of install** | `install.bat` needs Administrator rights to create folders, copy files, and register the Scheduled Task. After installation is complete, no admin rights are needed for daily use. |
| 3 | **PowerShell 5.1+** | The agent calls PowerShell internally to detect the active window and measure keyboard/mouse idle time. Already built into Windows 10/11. |
| 4 | **Same LAN network as the server** | The agent sends data to the server's local IP address (e.g. `192.168.0.200:8001`). The client PC must be on the same office network (Wi-Fi or Ethernet). It will not work over the internet. |
| 5 | **Windows Defender exclusion on `C:\AutodeskMonitor`** | Windows Defender may quarantine `hazemonitor.exe` because it is a background silent process without a code signature. The exclusion is added automatically by our installer before copying the file. |
| 6 | **No additional software required** | The agent is a fully self-contained `.exe` file. Node.js and all dependencies are compiled inside it. The user does not need to install anything manually. |

---

## PART C — NETWORK REQUIREMENTS

| Requirement | Detail |
|---|---|
| **Server must have a static (reserved) local IP** | The server IP (e.g. `192.168.0.200`) must not change after installation. If the IP changes, all client agents will stop reporting until the exe is rebuilt with the new IP. Ask the client's IT team to reserve the server IP in the router (DHCP reservation). |
| **Port 8001 open on server firewall** | Required for both dashboard browser access and agent data reporting. |
| **All PCs on the same LAN subnet** | For example all on `192.168.0.x`. Agents cannot reach the server across different subnets without additional network routing. |
| **No internet connection required** | The entire system runs on the local office network. No cloud service, no external server, no subscription calls. |

---

## PART D — VERSION SUMMARY TABLE
*(Quick reference for the client's IT team)*

| Software | Minimum Version | How It Arrives |
|---|---|---|
| Windows | 10 / 11 (64-bit) | Client & Server — already present |
| PHP | 8.2.x | Inside Laragon |
| MySQL | 8.0.x | Inside Laragon |
| Apache | 2.4.x | Inside Laragon |
| Laravel | 12.x | Inside the app folder we provide |
| Composer | 2.x | Separate install |
| Node.js | 18 LTS | Separate install |
| Git | Any recent | Separate install |
| NSSM | 2.24 | We provide with the app |
| Notepad++ | Any recent | Separate install |

---

## PART E — WHAT OUR TEAM PROVIDES
*(The client does NOT need to prepare these — we supply them ready to use)*

| Item | Description |
|---|---|
| `laravel-app/` folder | The complete ACLAM dashboard application, pre-configured and ready to deploy |
| `hazemonitor.exe` | The compiled monitoring agent, already pointing to the correct server IP |
| `install.bat` | Silent installer for client PCs — right-click → Run as Administrator |
| `start_silent.vbs` | Auto-start launcher — deployed automatically by install.bat |
| `uninstall.bat` | Clean removal script for client PCs |
| `nssm.exe` | Service manager to keep the server running after PC restarts |
| `server_setup.bat` | Auto-installer for all server software (Laragon, Composer, Node.js, Notepad++) |
| License Key | Provided separately before go-live |

---

## PART F — COMMON INSTALLATION FAILURES TO PREVENT

These are the most frequent reasons an installation fails. Resolve these before starting.

| Risk | How to Prevent |
|---|---|
| **Git not installed** | Install Git before running `composer install`. Without it, Composer cannot download some packages and fails with "zip extension missing". |
| **Server IP changes** | Reserve the server IP in the router before installation. If the IP ever changes after install, all 50 client agents will stop sending data. |
| **PHP zip extension disabled** | In `C:\laragon\bin\php\php8.2.x\php.ini`, confirm the line `extension=zip` is not commented out. |
| **Windows Defender quarantine** | The Defender exclusion for `C:\AutodeskMonitor` must be added before copying the exe. Our installer handles this automatically. |
| **UAC blocks install.bat on client PCs** | The installer must be right-clicked and run as Administrator. For silent Group Policy deployment, use a GPO Startup Script (runs as SYSTEM — no UAC prompt). |
| **Port 8001 blocked** | After server setup, run the firewall command in Part A (#11) or agents will connect but receive no response. |

---

*Document prepared by the ACLAM deployment team.*  
*For questions contact the installation team before proceeding.*
