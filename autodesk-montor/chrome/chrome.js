const { exec } = require('child_process');
const os = require('os');
const axios = require('axios');

// --- CONFIGURATION ---
const CHECK_INTERVAL_MS = 3000;
const API_URL = 'http://192.168.0.200:8000/api/log-activity';

// --- TEST PROCESSES (simulates Autodesk monitoring using common apps) ---
const TARGET_PROCESSES = [
    'chrome',       // Google Chrome
    'msedge',       // Microsoft Edge
    'firefox',      // Mozilla Firefox
    'notepad',      // Notepad
    'code',         // VS Code
    'winword',      // Microsoft Word
    'excel',        // Microsoft Excel
    'powerpnt',     // Microsoft PowerPoint
    'mspaint',      // Paint
    'calc',         // Calculator
];

const SOFTWARE_MAPPING = {
    'chrome'    : 'Google Chrome',
    'msedge'    : 'Microsoft Edge',
    'firefox'   : 'Mozilla Firefox',
    'notepad'   : 'Notepad',
    'code'      : 'VS Code',
    'winword'   : 'Microsoft Word',
    'excel'     : 'Microsoft Excel',
    'powerpnt'  : 'Microsoft PowerPoint',
    'mspaint'   : 'Paint',
    'calc'      : 'Calculator',
};

const machineId = os.hostname();
const username  = os.userInfo().username;

console.log('================================================');
console.log(' ArchEng Pro — TEST MONITOR (Chrome Mode)');
console.log('================================================');
console.log(` Machine : ${machineId}`);
console.log(` User    : ${username}`);
console.log(` Server  : ${API_URL}`);
console.log(` Interval: every ${CHECK_INTERVAL_MS / 1000}s`);
console.log('================================================');
console.log('[WATCHING] Tracking: Chrome, Edge, Firefox, Notepad, VS Code...');
console.log('');

function checkActiveWindow() {
    const psScript = `powershell -command "Add-Type -MemberDefinition '[DllImport(\\"user32.dll\\")] public static extern IntPtr GetForegroundWindow(); [DllImport(\\"user32.dll\\")] public static extern int GetWindowThreadProcessId(IntPtr hWnd, out int lpdwProcessId);' -Name Win32 -Namespace User32; $hwnd = [User32.Win32]::GetForegroundWindow(); [int]$pidOut = 0; [User32.Win32]::GetWindowThreadProcessId($hwnd, [ref]$pidOut); Get-Process -Id $pidOut | Select-Object -ExpandProperty ProcessName"`;

    exec(psScript, (error, stdout, stderr) => {
        if (error) return;

        const activeApp = stdout.trim().toLowerCase();
        const foundTarget = TARGET_PROCESSES.find(target => activeApp.includes(target));

        if (foundTarget) {
            const cleanSoftwareName = SOFTWARE_MAPPING[foundTarget];
            const tzOffset   = (new Date()).getTimezoneOffset() * 60000;
            const timestamp  = (new Date(Date.now() - tzOffset)).toISOString().slice(0, 19).replace('T', ' ');

            const payload = {
                machine_name : machineId,
                user_name    : username,
                application  : cleanSoftwareName,
                status       : 'Active',
                timestamp    : timestamp,
            };

            axios.post(API_URL, payload)
                .then(() => {
                    console.log(`[SAVED] ${timestamp} — ${cleanSoftwareName}`);
                })
                .catch(err => {
                    console.error(`[ERROR] Could not reach server: ${err.message}`);
                    console.error(`        Check API_URL = ${API_URL}`);
                });

        } else {
            process.stdout.write('.');
        }
    });
}

// Start monitoring
setInterval(checkActiveWindow, CHECK_INTERVAL_MS);
