/**
 * ArchEng Pro — Smart Monitor Agent
 * Verifies subscription license on startup, then monitors active Autodesk usage.
 */

const { exec } = require('child_process');
const os       = require('os');
const fs       = require('fs');
const path     = require('path');
const axios    = require('axios');

// ── CONFIG ─────────────────────────────────────────────────────
const CHECK_INTERVAL_MS = 3000;           // Activity check: every 3 s
const PULSE_INTERVAL_MS = 5 * 60 * 1000; // License pulse:  every 5 min
const CONFIG_FILE       = path.join(__dirname, 'license.json');

// Activity log API (main laravel-app)
const ACTIVITY_URL = 'http://192.168.1.55:8000/api/log-activity';

// ── AUTODESK TARGETS ───────────────────────────────────────────
const TARGET_PROCESSES = [
    'acad', 'revit', '3dsmax', 'roamer', 'infraworks', 'recap',
    'desktopconnector', 'formit', 'robot', 'sbd', 'inventor',
    'fusion360', 'estmep', 'camduct'
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

// ── LICENSE HELPERS ────────────────────────────────────────────
function loadConfig() {
    if (!fs.existsSync(CONFIG_FILE)) {
        console.error('[LICENSE] No license.json found.');
        console.error('[LICENSE] Run activate.exe first to activate your subscription.');
        process.exit(1);
    }
    try {
        const cfg = JSON.parse(fs.readFileSync(CONFIG_FILE, 'utf8'));
        if (!cfg.license_key || !cfg.server_url) {
            console.error('[LICENSE] license.json is incomplete. Run activate.exe again.');
            process.exit(1);
        }
        return cfg;
    } catch (e) {
        console.error('[LICENSE] Could not read license.json: ' + e.message);
        process.exit(1);
    }
}

async function verifyLicense(cfg) {
    const res = await axios.post(
        `${cfg.server_url}/api/license/verify`,
        { license_key: cfg.license_key, machine_id: machineId },
        { headers: { 'Accept': 'application/json', 'Content-Type': 'application/json' }, timeout: 15000 }
    );
    return res.data;
}

async function sendPulse(cfg) {
    try {
        const res = await axios.post(
            `${cfg.server_url}/api/license/pulse`,
            { license_key: cfg.license_key, machine_id: machineId },
            { headers: { 'Accept': 'application/json', 'Content-Type': 'application/json' }, timeout: 10000 }
        );
        if (res.data.status !== 'ok') {
            console.error(`\n[LICENSE] Subscription ${res.data.status}. Stopping monitor.`);
            process.exit(1);
        }
        if (res.data.days_left !== null && res.data.days_left <= 7) {
            console.log(`\n[LICENSE WARNING] Subscription expires in ${res.data.days_left} day(s)!`);
        }
    } catch (e) {
        console.error(`[PULSE] Could not reach license server: ${e.message}`);
        // Non-fatal — temporary network issue; will retry next interval
    }
}

// ── ACTIVITY MONITOR ───────────────────────────────────────────
function checkActiveWindow() {
    const psScript = `powershell -command "Add-Type -MemberDefinition '[DllImport(\\"user32.dll\\")] public static extern IntPtr GetForegroundWindow(); [DllImport(\\"user32.dll\\")] public static extern int GetWindowThreadProcessId(IntPtr hWnd, out int lpdwProcessId);' -Name Win32 -Namespace User32; $hwnd = [User32.Win32]::GetForegroundWindow(); [int]$pidOut = 0; [User32.Win32]::GetWindowThreadProcessId($hwnd, [ref]$pidOut); Get-Process -Id $pidOut | Select-Object -ExpandProperty ProcessName"`;

    exec(psScript, (error, stdout) => {
        if (error) return;

        const activeApp   = stdout.trim().toLowerCase();
        const foundTarget = TARGET_PROCESSES.find(t => activeApp.includes(t));

        if (foundTarget) {
            const cleanName = SOFTWARE_MAPPING[foundTarget];
            const tzOffset  = (new Date()).getTimezoneOffset() * 60000;
            const timestamp = (new Date(Date.now() - tzOffset)).toISOString().slice(0, 19).replace('T', ' ');

            axios.post(ACTIVITY_URL, {
                machine_name: machineId,
                user_name:    username,
                application:  cleanName,
                status:       'Active',
                timestamp:    timestamp
            }).then(() => {
                console.log(`[SAVED] ${timestamp} — ${cleanName}`);
            }).catch(err => {
                console.error(`[ERROR] Could not log activity: ${err.message}`);
            });
        } else {
            process.stdout.write('.');
        }
    });
}

// ── STARTUP ────────────────────────────────────────────────────
async function start() {
    console.log('============================================');
    console.log('   ArchEng Pro — Smart Monitor Agent        ');
    console.log('============================================');
    console.log(`  Machine : ${machineId}`);
    console.log(`  User    : ${username}`);
    console.log('--------------------------------------------');

    // 1. Load license config
    const cfg = loadConfig();

    // 2. Verify license with server
    console.log('[LICENSE] Verifying subscription...');
    let licInfo;
    try {
        licInfo = await verifyLicense(cfg);
    } catch (err) {
        if (err.response) {
            licInfo = err.response.data;
        } else {
            console.error(`[LICENSE ERROR] Cannot reach license server: ${err.message}`);
            console.error('[LICENSE ERROR] Check your network and server URL in license.json.');
            process.exit(1);
        }
    }

    // 3. Act on license status
    if (licInfo.status === 'valid') {
        console.log('[LICENSE] Status    : VALID');
        console.log(`[LICENSE] Plan      : ${licInfo.tier}`);
        console.log(`[LICENSE] Expires   : ${licInfo.expires_at}`);
        console.log(`[LICENSE] Days Left : ${licInfo.days_left !== null ? licInfo.days_left + ' days' : 'Unlimited'}`);
        if (licInfo.days_left !== null && licInfo.days_left <= 7) {
            console.log(`[LICENSE WARNING] Subscription expires in ${licInfo.days_left} day(s). Please renew!`);
        }
    } else if (licInfo.status === 'expired') {
        console.error('[LICENSE ERROR] Subscription EXPIRED.');
        console.error('[LICENSE ERROR] Contact your administrator to renew.');
        process.exit(1);
    } else if (licInfo.status === 'locked') {
        console.error('[LICENSE ERROR] Access LOCKED by administrator.');
        console.error('[LICENSE ERROR] Contact your IT admin for assistance.');
        process.exit(1);
    } else if (licInfo.status === 'not_activated') {
        console.error('[LICENSE ERROR] Machine not activated.');
        console.error('[LICENSE ERROR] Run activate.exe to activate your subscription.');
        process.exit(1);
    } else {
        console.error(`[LICENSE ERROR] ${licInfo.message || 'License check failed.'}`);
        process.exit(1);
    }

    // 4. Start monitoring
    console.log('--------------------------------------------');
    console.log('[STARTED] Monitoring active Autodesk software...');
    console.log('--------------------------------------------');

    setInterval(checkActiveWindow, CHECK_INTERVAL_MS);
    setInterval(() => sendPulse(cfg), PULSE_INTERVAL_MS);
}

start();
