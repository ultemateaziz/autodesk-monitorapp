====================================================
 HazeMonitor v2 - Deployment Package
====================================================

FILES IN THIS FOLDER:
---------------------
  hazemonitor.exe    ← Main monitor app (copy from parent folder)
  nssm.exe           ← Download from https://nssm.cc/download
  install.bat        ← Run this to install (as Admin)
  uninstall.bat      ← Run this to remove (as Admin)
  silent_install.vbs ← For GPO / silent deployment

----------------------------------------------------
BEFORE YOU START — REQUIRED STEPS:
----------------------------------------------------
1. Copy hazemonitor.exe from the parent folder into THIS folder
2. Download nssm.exe from https://nssm.cc/download
   → Pick the version matching your Windows: win32 or win64
   → Copy nssm.exe into THIS folder

----------------------------------------------------
MANUAL INSTALL (Single PC):
----------------------------------------------------
1. Right-click install.bat → Run as Administrator
2. Done. Service starts automatically on every reboot.

----------------------------------------------------
SILENT INSTALL (Group Policy / Remote):
----------------------------------------------------
1. Put this entire folder on a shared network drive
   e.g. \\SERVER\Software\HazeMonitor\

2. In Group Policy Management:
   Computer Configuration
     → Windows Settings
       → Scripts (Startup/Shutdown)
         → Startup → Add:
           Script: \\SERVER\Software\HazeMonitor\install.bat

3. Every PC installs silently on next reboot.

----------------------------------------------------
UNINSTALL:
----------------------------------------------------
Right-click uninstall.bat → Run as Administrator

----------------------------------------------------
WHAT THIS INSTALLS:
----------------------------------------------------
- Copies files to C:\AutodeskMonitor\
- Installs "HazeMonitor" as a Windows Service via NSSM
- Service starts on boot, no user login needed
- Logs saved to C:\AutodeskMonitor\monitor.log
- Error logs at C:\AutodeskMonitor\error.log

====================================================
