const { exec } = require('child_process');
const os = require('os');
const axios = require('axios');
const crypto = require('crypto');

// --- CONFIGURATION ---
const CHECK_INTERVAL_MS    = 3000;      // Foreground check: every 3 seconds
const BG_CHECK_INTERVAL_MS = 30000;    // Background apps check: every 30 seconds
const API_URL              = 'http://192.168.56.1:8001/api/log-activity';
const SETTINGS_API_URL     = 'http://192.168.56.1:8001/api/idle-threshold';
const AGENT_REGISTER_URL   = 'http://192.168.56.1:8001/api/agent/register';
const AGENT_VALIDATE_URL   = 'http://192.168.56.1:8001/api/agent/validate';
const LICENSE_CHECK_INTERVAL_MS = 6 * 60 * 60 * 1000; // Re-validate every 6 hours

// Idle threshold — fetched from server at startup, falls back to 60 minutes
let IDLE_THRESHOLD_MS = 3600000;

// Target process names (without .exe)
const TARGET_PROCESSES = [
    'acad',             // AutoCAD / Civil 3D / Plant 3D
    'revit',            // Revit
    '3dsmax',           // 3ds Max
    'roamer',           // Navisworks
    'infraworks',       // InfraWorks
    'recap',            // ReCap Pro
    'desktopconnector', // Autodesk Docs
    'formit',           // FormIt
    'robot',            // Robot Structural Analysis
    'sbd',              // Structural Bridge Design
    'inventor',         // Inventor
    'fusion360',        // Fusion 360
    'estmep',           // Fabrication ESTmep
    'camduct'           // Fabrication CAMduct
];

const SOFTWARE_MAPPING = {
    'acad':             'AutoCAD',
    'revit':            'Revit',
    '3dsmax':           '3ds Max',
    'roamer':           'Navisworks',
    'infraworks':       'InfraWorks',
    'recap':            'ReCap Pro',
    'desktopconnector': 'Autodesk Docs',
    'formit':           'FormIt',
    'robot':            'Robot Structural Analysis',
    'sbd':              'Structural Bridge Design',
    'inventor':         'Inventor',
    'fusion360':        'Fusion 360',
    'estmep':           'Fabrication ESTmep',
    'camduct':          'Fabrication CAMduct'
};

const machineId = os.hostname();
const username  = os.userInfo().username;

console.log(`[STARTED] ARCHLAM Monitor on ${machineId} (${username})`);
console.log(`[MULTI]   Background app scan: every ${BG_CHECK_INTERVAL_MS / 1000}s`);
console.log('------------------------------------------------');

// Fetch idle threshold from server — overrides the default if reachable
axios.get(SETTINGS_API_URL)
    .then(res => {
        const fetched = res.data && res.data.idle_threshold_ms;
        if (fetched && fetched > 0) {
            IDLE_THRESHOLD_MS = fetched;
        }
        console.log(`[IDLE]    Threshold loaded from server: ${IDLE_THRESHOLD_MS / 60000} minutes`);
    })
    .catch(() => {
        console.log(`[IDLE]    Server unreachable — using default: ${IDLE_THRESHOLD_MS / 60000} minutes`);
    });

// ─── FEATURE 2: IDLE TIME DETECTION ──────────────────────────────────────────
// Uses Windows Win32 GetLastInputInfo to get ms since last keyboard/mouse input
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
        if (err) return callback(0); // Fail safe: treat as not idle
        const ms = parseInt(stdout.trim(), 10);
        callback(isNaN(ms) ? 0 : ms);
    });
}

// ─── FEATURE 4: MULTI-APP TRACKING ───────────────────────────────────────────
// Gets ALL running Autodesk processes (not just the foreground one)
function getBackgroundAutodeskApps(activeProcessName, callback) {
    const psAllProc = `powershell -command "Get-Process | Where-Object { $_.MainWindowTitle -ne '' } | Select-Object -Property Name,MainWindowTitle | ConvertTo-Csv -NoTypeInformation"`;

    exec(psAllProc, (err, stdout) => {
        if (err) return callback([]);

        const lines = stdout.trim().split('\n').slice(1); // Skip CSV header
        const backgroundApps = [];

        for (const line of lines) {
            const parts = line.replace(/"/g, '').split(',');
            const procName  = (parts[0] || '').toLowerCase().trim();
            const winTitle  = (parts.slice(1).join(',') || '').trim();

            const foundTarget = TARGET_PROCESSES.find(t => procName.includes(t));
            if (!foundTarget) continue;
            if (procName.includes(activeProcessName)) continue; // Skip the active one

            const yearMatch = winTitle.match(/\b(20[2-3]\d)\b/);
            const version   = yearMatch ? ` ${yearMatch[1]}` : '';
            backgroundApps.push({
                process: foundTarget,
                name:    SOFTWARE_MAPPING[foundTarget] + version,
                title:   winTitle
            });
        }

        // Deduplicate by software name
        const seen = new Set();
        const unique = backgroundApps.filter(a => {
            if (seen.has(a.name)) return false;
            seen.add(a.name);
            return true;
        });

        callback(unique);
    });
}

// ─── FEATURE 1 (existing): FOREGROUND ACTIVE WINDOW CHECK ────────────────────
function checkActiveWindow() {
    const psScript = `powershell -command "try { if (-not ([System.Management.Automation.PSTypeName]'WinHelper.FgWin').Type) { Add-Type -TypeDefinition 'using System; using System.Runtime.InteropServices; namespace WinHelper { public class FgWin { [DllImport(\\"user32.dll\\")] public static extern IntPtr GetForegroundWindow(); [DllImport(\\"user32.dll\\")] public static extern int GetWindowThreadProcessId(IntPtr h, out int pid); } }' } ; $h=[WinHelper.FgWin]::GetForegroundWindow(); [int]$p=0; [WinHelper.FgWin]::GetWindowThreadProcessId($h,[ref]$p)|Out-Null; $proc=Get-Process -Id $p -ErrorAction SilentlyContinue; if($proc){ Write-Output($proc.ProcessName+'|'+$proc.MainWindowTitle) } } catch {}"`;

    exec(psScript, (error, stdout) => {
        if (error) return;

        const parts      = stdout.trim().split('|');
        const activeApp  = (parts[0] || '').toLowerCase();
        const windowTitle = parts[1] || '';

        const foundTarget = TARGET_PROCESSES.find(t => activeApp.includes(t));
        if (!foundTarget) {
            process.stdout.write('.');
            return;
        }

        // ─── Check idle time before sending ───────────────────────────────────
        getSystemIdleTimeMs((idleMs) => {
            const isIdle   = idleMs >= IDLE_THRESHOLD_MS;
            const idleMins = Math.floor(idleMs / 60000);

            const yearMatch = windowTitle.match(/\b(20[2-3]\d)\b/);
            const version   = yearMatch ? ` ${yearMatch[1]}` : '';
            const appName   = SOFTWARE_MAPPING[foundTarget] + version;
            const status    = isIdle ? 'Idle' : 'Active';
            const timestamp = (() => { const d = new Date(); return new Date(d.getTime() - d.getTimezoneOffset() * 60000).toISOString().slice(0, 19).replace('T', ' '); })();

            const payload = {
                machine_name: machineId,
                user_name:    username,
                application:  appName,
                status:       status,
                timestamp:    timestamp
            };

            if (!machineAuthorized) {
                console.log(`[LICENSE] Machine not authorized — skipping log: ${appName}`);
                return;
            }

            axios.post(API_URL, payload)
                .then(() => {
                    if (isIdle) {
                        console.log(`[IDLE ⚠]  ${timestamp} - ${appName} (idle ${idleMins}m)`);
                    } else {
                        console.log(`[ACTIVE ✓] ${timestamp} - ${appName}`);
                    }
                })
                .catch(err => {
                    console.error(`[ERROR]   Could not save: ${err.message}`);
                });
        });
    });
}

// ─── FEATURE 4: BACKGROUND APP SCAN (every 60 seconds) ───────────────────────
function scanBackgroundApps() {
    // Get foreground app name first to exclude it
    const psActive = `powershell -command "Add-Type -MemberDefinition '[DllImport(\\"user32.dll\\")] public static extern IntPtr GetForegroundWindow(); [DllImport(\\"user32.dll\\")] public static extern int GetWindowThreadProcessId(IntPtr hWnd, out int lpdwProcessId);' -Name Win32 -Namespace User32; $hwnd = [User32.Win32]::GetForegroundWindow(); [int]$pidOut = 0; [User32.Win32]::GetWindowThreadProcessId($hwnd, [ref]$pidOut); $p = Get-Process -Id $pidOut; Write-Output $p.ProcessName"`;

    exec(psActive, (err, stdout) => {
        const activeProcessName = (stdout || '').trim().toLowerCase();

        getBackgroundAutodeskApps(activeProcessName, (bgApps) => {
            if (bgApps.length === 0) return;
            if (!machineAuthorized) {
                console.log('[LICENSE] Machine not authorized — skipping background scan.');
                return;
            }

            const timestamp = (() => { const d = new Date(); return new Date(d.getTime() - d.getTimezoneOffset() * 60000).toISOString().slice(0, 19).replace('T', ' '); })();
            console.log(`[MULTI]   Background apps detected: ${bgApps.map(a => a.name).join(', ')}`);

            bgApps.forEach(app => {
                const payload = {
                    machine_name: machineId,
                    user_name:    username,
                    application:  app.name,
                    status:       'Open',   // Background: license in use, not counted in productivity
                    timestamp:    timestamp
                };

                axios.post(API_URL, payload)
                    .then(() => {
                        console.log(`[OPEN ⬜]  ${timestamp} - ${app.name} (background)`);
                    })
                    .catch(err => {
                        console.error(`[ERROR]   Background log failed: ${err.message}`);
                    });
            });
        });
    });
}

// ─── START MONITORING ─────────────────────────────────────────────────────────
setInterval(checkActiveWindow, CHECK_INTERVAL_MS);       // Every 3s — foreground app
setInterval(scanBackgroundApps, BG_CHECK_INTERVAL_MS);   // Every 60s — background apps

// ─── MACHINE LICENSING ────────────────────────────────────────────────────────
// Generates a stable fingerprint from hardware identifiers.
// If MAC unavailable, falls back to hostname-only hash (still unique per machine).
function getMachineFingerprint() {
    try {
        const interfaces = os.networkInterfaces();
        const mac = Object.values(interfaces)
            .flat()
            .find(i => i && !i.internal && i.mac && i.mac !== '00:00:00:00:00:00')?.mac || 'nomac';
        const raw = `${os.hostname()}|${os.cpus()[0]?.model || 'unknown'}|${mac}`;
        return crypto.createHash('sha256').update(raw).digest('hex').slice(0, 32);
    } catch (_) {
        return crypto.createHash('sha256').update(os.hostname()).digest('hex').slice(0, 32);
    }
}

const MACHINE_FINGERPRINT = getMachineFingerprint();
let agentToken       = null;  // Stored in memory; re-fetched on restart
let machineAuthorized = true; // Optimistic default — block only if explicitly revoked

// Registers this machine with the license hub on startup.
// If pending, monitoring continues (grace period). If revoked, monitoring halts.
async function registerWithLicenseHub() {
    try {
        const res = await axios.post(AGENT_REGISTER_URL, {
            machine_id:  MACHINE_FINGERPRINT,
            hostname:    machineId,
            license_key: null, // set this if deploying with a known license key
        }, { timeout: 10000 });

        const { status, agent_token } = res.data;

        if (status === 'revoked') {
            console.log('[LICENSE] Machine revoked. Monitoring halted. Contact administrator.');
            machineAuthorized = false;
            return;
        }

        if (status === 'active' && agent_token) {
            agentToken = agent_token;
            console.log('[LICENSE] Machine active and licensed.');
        } else {
            console.log(`[LICENSE] Machine status: ${status}. Monitoring continues in grace period.`);
        }
    } catch (err) {
        console.log(`[LICENSE] Could not reach license hub — continuing offline: ${err.message}`);
        // Offline: keep machineAuthorized = true (fail-open for network blips)
    }
}

// Periodically re-validates authorization. Stops monitoring if revoked remotely.
async function validateLicenseStatus() {
    if (!agentToken) {
        // No token yet — re-attempt registration
        await registerWithLicenseHub();
        return;
    }

    try {
        const res = await axios.post(AGENT_VALIDATE_URL, {}, {
            headers: { 'X-Agent-Token': agentToken },
            timeout: 10000,
        });

        if (!res.data.authorized) {
            console.log(`[LICENSE] Authorization lost (${res.data.status}). Monitoring halted.`);
            machineAuthorized = false;
        } else if (!machineAuthorized) {
            console.log('[LICENSE] Authorization restored. Monitoring resumed.');
            machineAuthorized = true;
        }
    } catch (err) {
        console.log(`[LICENSE] Validation check failed — keeping current state: ${err.message}`);
        // Network error: keep current machineAuthorized state (don't punish blips)
    }
}

// Boot: register on startup, re-validate every 6 hours
registerWithLicenseHub();
setInterval(validateLicenseStatus, LICENSE_CHECK_INTERVAL_MS);
