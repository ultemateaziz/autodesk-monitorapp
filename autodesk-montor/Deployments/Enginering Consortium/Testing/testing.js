const { exec } = require('child_process');
const os = require('os');
const axios = require('axios');

// ─── TESTING CONFIG ───────────────────────────────────────────────────────────
// Change API_URL to your server IP before running
const CHECK_INTERVAL_MS    = 2000;     // 2s — faster than production so you see results quickly
const BG_CHECK_INTERVAL_MS = 10000;   // 10s — background scan (production is 60s)
const API_URL              = 'http://192.168.63.169:8001/api/log-activity';
const SETTINGS_API_URL     = 'http://192.168.63.169:8001/api/idle-threshold';

// Only watching for acad — this matches the fake acad.exe (renamed notepad)
const TARGET_PROCESSES = [
    'acad',   // Fake AutoCAD — rename notepad.exe to acad.exe to trigger this
];

const SOFTWARE_MAPPING = {
    'acad': 'AutoCAD',
};

let IDLE_THRESHOLD_MS = 3600000;

const machineId = os.hostname();
const username  = os.userInfo().username;

console.log('');
console.log('╔══════════════════════════════════════════════════╗');
console.log('║         ACLAM MONITOR — TEST MODE                ║');
console.log('╠══════════════════════════════════════════════════╣');
console.log(`║  Machine : ${machineId.padEnd(38)}║`);
console.log(`║  User    : ${username.padEnd(38)}║`);
console.log(`║  Server  : ${API_URL.padEnd(38)}║`);
console.log('╠══════════════════════════════════════════════════╣');
console.log('║  HOW TO TRIGGER A LOG:                           ║');
console.log('║  1. Run run_test.bat (already done if you used   ║');
console.log('║     it) — it opens acad.exe (fake AutoCAD)       ║');
console.log('║  2. Click on the acad.exe window to make it the  ║');
console.log('║     foreground (active) window                   ║');
console.log('║  3. Watch for [ACTIVE ✓] lines below             ║');
console.log('║  4. Check your ACLAM dashboard                   ║');
console.log('╚══════════════════════════════════════════════════╝');
console.log('');

// Fetch idle threshold
axios.get(SETTINGS_API_URL)
    .then(res => {
        const fetched = res.data && res.data.idle_threshold_ms;
        if (fetched && fetched > 0) IDLE_THRESHOLD_MS = fetched;
        console.log(`[CONFIG]  Idle threshold: ${IDLE_THRESHOLD_MS / 60000} minutes`);
    })
    .catch(() => {
        console.log(`[CONFIG]  Server not reached for idle threshold — using default 60 min`);
    });

console.log(`[WATCH]   Scanning every ${CHECK_INTERVAL_MS / 1000}s for: ${TARGET_PROCESSES.join(', ')}`);
console.log('');

// ─── IDLE TIME ────────────────────────────────────────────────────────────────
function getSystemIdleTimeMs(callback) {
    const psIdleScript = `powershell -command "
Add-Type -TypeDefinition @'
using System;
using System.Runtime.InteropServices;
public class IdleTimer {
    [DllImport(\\"user32.dll\\")]
    static extern bool GetLastInputInfo(ref LASTINPUTINFO plii);
    [StructLayout(LayoutKind.Sequential)]
    struct LASTINPUTINFO { public uint cbSize; public uint dwTime; }
    public static uint GetIdleMs() {
        LASTINPUTINFO li = new LASTINPUTINFO();
        li.cbSize = (uint)System.Runtime.InteropServices.Marshal.SizeOf(li);
        GetLastInputInfo(ref li);
        return (uint)System.Environment.TickCount - li.dwTime;
    }
}
'@ -Language CSharp
[IdleTimer]::GetIdleMs()"`;

    exec(psIdleScript, (err, stdout) => {
        if (err) return callback(0);
        const ms = parseInt(stdout.trim(), 10);
        callback(isNaN(ms) ? 0 : ms);
    });
}

// ─── FOREGROUND WINDOW CHECK ──────────────────────────────────────────────────
function checkActiveWindow() {
    const psScript = `powershell -command "Add-Type -MemberDefinition '[DllImport(\\"user32.dll\\")] public static extern IntPtr GetForegroundWindow(); [DllImport(\\"user32.dll\\")] public static extern int GetWindowThreadProcessId(IntPtr hWnd, out int lpdwProcessId);' -Name Win32 -Namespace User32; $hwnd = [User32.Win32]::GetForegroundWindow(); [int]$pidOut = 0; [User32.Win32]::GetWindowThreadProcessId($hwnd, [ref]$pidOut); $p = Get-Process -Id $pidOut; Write-Output ($p.ProcessName + '|' + $p.MainWindowTitle)"`;

    exec(psScript, (error, stdout) => {
        if (error) return;

        const parts       = stdout.trim().split('|');
        const activeApp   = (parts[0] || '').toLowerCase();
        const windowTitle = parts[1] || '';

        const foundTarget = TARGET_PROCESSES.find(t => activeApp.includes(t));

        if (!foundTarget) {
            process.stdout.write(`[SCAN]    Foreground: ${activeApp || '?'} — not a target\r`);
            return;
        }

        console.log('');
        console.log(`[FOUND]   Target process detected: ${activeApp} ("${windowTitle}")`);

        getSystemIdleTimeMs((idleMs) => {
            const isIdle   = idleMs >= IDLE_THRESHOLD_MS;
            const idleMins = Math.floor(idleMs / 60000);
            const appName  = SOFTWARE_MAPPING[foundTarget] || foundTarget;
            const status   = isIdle ? 'Idle' : 'Active';

            const _now = new Date();
            const timestamp = _now.getFullYear() + '-' +
                String(_now.getMonth() + 1).padStart(2, '0') + '-' +
                String(_now.getDate()).padStart(2, '0') + ' ' +
                String(_now.getHours()).padStart(2, '0') + ':' +
                String(_now.getMinutes()).padStart(2, '0') + ':' +
                String(_now.getSeconds()).padStart(2, '0');

            const payload = {
                machine_name: machineId,
                user_name:    username,
                application:  appName,
                status:       status,
                timestamp:    timestamp
            };

            console.log(`[POST]    Sending → ${JSON.stringify(payload)}`);

            axios.post(API_URL, payload)
                .then(() => {
                    if (isIdle) {
                        console.log(`[IDLE ⚠]  ${timestamp} - ${appName} (idle ${idleMins}m) — logged OK`);
                    } else {
                        console.log(`[ACTIVE ✓] ${timestamp} - ${appName} — logged OK ✓`);
                    }
                    console.log(`[DASH]    Check your dashboard now!`);
                    console.log('');
                })
                .catch(err => {
                    console.error(`[ERROR]   POST failed: ${err.message}`);
                    console.error(`[ERROR]   Is the server running? Check API_URL = ${API_URL}`);
                    console.log('');
                });
        });
    });
}

// ─── BACKGROUND SCAN ──────────────────────────────────────────────────────────
function scanBackgroundApps() {
    const psAllProc = `powershell -command "Get-Process | Where-Object { $_.MainWindowTitle -ne '' } | Select-Object -Property Name,MainWindowTitle | ConvertTo-Csv -NoTypeInformation"`;

    exec(psAllProc, (err, stdout) => {
        if (err) return;

        const lines = stdout.trim().split('\n').slice(1);
        const found = [];

        for (const line of lines) {
            const parts = line.replace(/"/g, '').split(',');
            const procName = (parts[0] || '').toLowerCase().trim();
            const foundTarget = TARGET_PROCESSES.find(t => procName.includes(t));
            if (foundTarget) found.push(procName);
        }

        if (found.length > 0) {
            console.log(`[BG SCAN] Found target(s) in background: ${found.join(', ')}`);
        } else {
            console.log(`[BG SCAN] No target processes running in background`);
        }
    });
}

// ─── START ────────────────────────────────────────────────────────────────────
setInterval(checkActiveWindow,  CHECK_INTERVAL_MS);
setInterval(scanBackgroundApps, BG_CHECK_INTERVAL_MS);
