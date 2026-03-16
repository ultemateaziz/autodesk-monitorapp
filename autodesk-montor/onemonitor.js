const { exec } = require('child_process');
const os = require('os');
const axios = require('axios'); // REQUIREMENT: Run 'npm install axios'

// --- CONFIGURATION ---
const CHECK_INTERVAL_MS = 3000;

// !!! IMPORTANT: CHANGE THIS IP !!!
// Use '127.0.0.1' for testing on your own PC.
// Use your Server's IP (e.g., '192.168.1.50') for Client PCs.
const SERVER_URL = 'http://127.0.0.1:8000/api/log-activity';

// TARGET PROCESSES (Partial names are better for matching)
const TARGET_PROCESSES = [
    'acad', 'revit', '3dsmax', 'roamer', 'infraworks',
    'recap', 'desktopconnector', 'formit', 'robot',
    'sbd', 'inventor', 'fusion360', 'estmep', 'camduct'
];

const machineId = os.hostname();
const username = os.userInfo().username;

console.log(`[STARTED] Monitor running on ${machineId} (${username})`);
console.log(`[WATCHING] Tracking active focus on AEC software...`);

function checkActiveWindow() {
    // This PowerShell command checks what the user is ACTUALLY looking at
    const psScript = `powershell -command "Add-Type -MemberDefinition '[DllImport(\\"user32.dll\\")] public static extern IntPtr GetForegroundWindow(); [DllImport(\\"user32.dll\\")] public static extern int GetWindowThreadProcessId(IntPtr hWnd, out int lpdwProcessId);' -Name Win32 -Namespace User32; $hwnd = [User32.Win32]::GetForegroundWindow(); [int]$pidOut = 0; [User32.Win32]::GetWindowThreadProcessId($hwnd, [ref]$pidOut); Get-Process -Id $pidOut | Select-Object -ExpandProperty ProcessName"`;

    exec(psScript, (error, stdout, stderr) => {
        if (error) return;

        const activeApp = stdout.trim().toLowerCase();

        // Check if the active window matches our Autodesk list
        const isWorking = TARGET_PROCESSES.some(target => activeApp.includes(target));

        if (isWorking) {
            console.log(`[${new Date().toLocaleTimeString()}] SENT: User is working in ${activeApp}`);

            // --- SENDING DATA TO LARAVEL ---
            axios.post(SERVER_URL, {
                machine_name: machineId,
                user_name: username,
                application: activeApp,
                status: 'Active',
                timestamp: new Date().toISOString()
            })
                .then(() => {
                    // Success (Silent)
                })
                .catch(err => {
                    console.log("Server Error: Check IP Address or Network.");
                });

        } else {
            process.stdout.write("."); // Idle dots
        }
    });
}

setInterval(checkActiveWindow, CHECK_INTERVAL_MS);