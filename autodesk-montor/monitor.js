const { exec } = require('child_process');
const os = require('os');

// Configuration
const CHECK_INTERVAL_MS = 5000; // Check every 5 seconds
const SERVER_URL = 'http://localhost:3000/api/heartbeat'; // Replace with your real web server later

// List of Autodesk Process Names to watch for
// You can add 'revit.exe', '3dsmax.exe', 'roamer.exe' (Navisworks) here.
const TARGET_PROCESSES = ['acad.exe', 'revit.exe', '3dsmax.exe']; 

// Get Machine Info
const machineId = os.hostname();
const username = os.userInfo().username;

console.log(`[Monitor Started] Watching for: ${TARGET_PROCESSES.join(', ')}`);
console.log(`[Agent Info] Machine: ${machineId} | User: ${username}`);
console.log('------------------------------------------------');

function checkSoftware() {
    // Windows command to list processes matching our targets would be complex, 
    // so we get all running tasks and filter them in JS.
    exec('tasklist /FO CSV', (error, stdout, stderr) => {
        if (error) {
            console.error(`Error executing tasklist: ${error}`);
            return;
        }

        // Output comes as "Image Name","PID", etc.
        const output = stdout.toString().toLowerCase();
        
        let foundApps = [];

        // Check against our target list
        TARGET_PROCESSES.forEach(app => {
            if (output.includes(app.toLowerCase())) {
                foundApps.push(app);
            }
        });

        if (foundApps.length > 0) {
            const statusUpdate = {
                user: username,
                machine: machineId,
                active_software: foundApps,
                timestamp: new Date().toISOString()
            };

            console.log(`[DETECTED] User is running: ${foundApps.join(', ')}`);
            
            // TODO: Uncomment this later to send data to your web server
            // sendToServer(statusUpdate);
        } else {
            // Optional: Log idle or keep silent
            // console.log("[IDLE] No Autodesk software detected.");
        }
    });
}

function sendToServer(data) {
    // This is where we will eventually send the data to your web dashboard
    fetch(SERVER_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    }).catch(err => console.error("Failed to connect to server:", err.message));
}

// Run the check repeatedly
setInterval(checkSoftware, CHECK_INTERVAL_MS);