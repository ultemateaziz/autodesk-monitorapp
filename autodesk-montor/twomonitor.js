const { exec } = require('child_process');
const os = require('os');
const axios = require('axios'); // Import the library to send data

// --- CONFIGURATION ---
const CHECK_INTERVAL_MS = 3000;
// This is the URL we tested successfully with curl
const API_URL = 'http://192.168.0.200:8001/api/log-activity';

// FINAL PROGRAMMED LIST (Process names without '.exe' for matching)
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

const machineId = os.hostname();
const username = os.userInfo().username;

console.log(`[STARTED] Smart Monitor on ${machineId} (${username})`);
console.log(`[WATCHING] Tracking active focus on AEC software...`);
console.log('------------------------------------------------');

function checkActiveWindow() {
    // PowerShell command to get Process Name AND Window Title of the currently ACTIVE window
    const psScript = `powershell -command "Add-Type -MemberDefinition '[DllImport(\\"user32.dll\\")] public static extern IntPtr GetForegroundWindow(); [DllImport(\\"user32.dll\\")] public static extern int GetWindowThreadProcessId(IntPtr hWnd, out int lpdwProcessId);' -Name Win32 -Namespace User32; $hwnd = [User32.Win32]::GetForegroundWindow(); [int]$pidOut = 0; [User32.Win32]::GetWindowThreadProcessId($hwnd, [ref]$pidOut); $p = Get-Process -Id $pidOut; Write-Output ($p.ProcessName + '|' + $p.MainWindowTitle)"`;

    exec(psScript, (error, stdout, stderr) => {
        if (error) {
            return; // Ignore errors
        }

        // Split output into process name and window title
        const parts = stdout.trim().split('|');
        const activeApp = (parts[0] || '').toLowerCase();
        const windowTitle = parts[1] || '';

        // Mapping process names to Friendly Software Names
        const SOFTWARE_MAPPING = {
            'acad': 'AutoCAD',
            'revit': 'Revit',
            '3dsmax': '3ds Max',
            'roamer': 'Navisworks',
            'infraworks': 'InfraWorks',
            'recap': 'ReCap Pro',
            'desktopconnector': 'Autodesk Docs',
            'formit': 'FormIt',
            'robot': 'Robot Structural Analysis',
            'sbd': 'Structural Bridge Design',
            'inventor': 'Inventor',
            'fusion360': 'Fusion 360',
            'estmep': 'Fabrication ESTmep',
            'camduct': 'Fabrication CAMduct'
        };

        // Find which target matches the active app (e.g., if activeApp is "11892 acad", it finds "acad")
        const foundTarget = TARGET_PROCESSES.find(target => activeApp.includes(target));

        if (foundTarget) {
            // MATCH FOUND: Get the clean name (e.g., "AutoCAD")
            // Extract version year from window title (e.g., "Autodesk AutoCAD 2026 - Drawing1.dwg" → "2026")
            const yearMatch = windowTitle.match(/\b(20[2-3]\d)\b/);
            const version = yearMatch ? ` ${yearMatch[1]}` : '';
            const cleanSoftwareName = SOFTWARE_MAPPING[foundTarget] + version; // e.g. "AutoCAD 2026"
            // Send UTC timestamp — Laravel converts to Asia/Dubai for display
            const timestamp = new Date().toISOString().slice(0, 19).replace('T', ' ');

            const payload = {
                machine_name: machineId,
                user_name: username,
                application: cleanSoftwareName, // This ensures ONLY "AutoCAD" is sent
                status: 'Active',
                timestamp: timestamp
            };

            // SEND TO DATABASE
            axios.post(API_URL, payload)
                .then(response => {
                    console.log(`[SAVED] ${timestamp} - Logged: ${cleanSoftwareName}`);
                })
                .catch(err => {
                    console.error(`[ERROR] Could not save to database: ${err.message}`);
                });

        } else {
            // User is looking at something else (Chrome, etc.)
            process.stdout.write(".");
        }
    });
}

// Run the check every 3 seconds
setInterval(checkActiveWindow, CHECK_INTERVAL_MS);