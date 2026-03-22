/**
 * ArchEng Pro — License Activation Tool
 * Starts a local HTTP server and opens the activation page in the default browser.
 * On success writes license.json next to this file.
 */

const http     = require('http');
const fs       = require('fs');
const path     = require('path');
const os       = require('os');
const { exec } = require('child_process');
const axios    = require('axios');

const PORT        = 3737;
const CONFIG_FILE = path.join(__dirname, 'license.json');
const HTML_FILE   = path.join(__dirname, 'activate.html');

// ── Helper: read current config ────────────────────────────────
function readConfig() {
    try {
        if (fs.existsSync(CONFIG_FILE)) {
            return JSON.parse(fs.readFileSync(CONFIG_FILE, 'utf8'));
        }
    } catch (_) {}
    return { license_key: '', server_url: 'http://192.168.1.55:8100' };
}

// ── HTTP Server ────────────────────────────────────────────────
const server = http.createServer(async (req, res) => {

    // Serve the activation page
    if (req.method === 'GET' && req.url === '/') {
        const cfg = readConfig();
        let html = fs.readFileSync(HTML_FILE, 'utf8');
        html = html
            .replace('{{EXISTING_KEY}}', cfg.license_key || '')
            .replace('{{EXISTING_SERVER}}', cfg.server_url || 'http://192.168.1.55:8100')
            .replace('{{MACHINE_ID}}', os.hostname());
        res.writeHead(200, { 'Content-Type': 'text/html; charset=utf-8' });
        return res.end(html);
    }

    // Favicon — ignore
    if (req.url === '/favicon.ico') {
        res.writeHead(204); return res.end();
    }

    // ── POST /activate ─────────────────────────────────────────
    if (req.method === 'POST' && req.url === '/activate') {
        let body = '';
        req.on('data', chunk => body += chunk);
        req.on('end', async () => {
            try {
                const { license_key, server_url } = JSON.parse(body);
                const machineId = os.hostname();

                const apiRes = await axios.post(
                    `${server_url}/api/license/activate`,
                    { license_key, machine_id: machineId },
                    { headers: { 'Accept': 'application/json', 'Content-Type': 'application/json' }, timeout: 12000 }
                );

                // Save config
                fs.writeFileSync(CONFIG_FILE, JSON.stringify(
                    { license_key, server_url, machine_id: machineId },
                    null, 2
                ));

                res.writeHead(200, { 'Content-Type': 'application/json' });
                res.end(JSON.stringify({ success: true, data: apiRes.data }));
            } catch (err) {
                const status  = err.response?.status  || 500;
                const errData = err.response?.data    || { status: 'error', message: err.message };
                res.writeHead(status, { 'Content-Type': 'application/json' });
                res.end(JSON.stringify({ success: false, data: errData }));
            }
        });
        return;
    }

    // ── POST /verify ───────────────────────────────────────────
    if (req.method === 'POST' && req.url === '/verify') {
        let body = '';
        req.on('data', chunk => body += chunk);
        req.on('end', async () => {
            try {
                const { license_key, server_url } = JSON.parse(body);
                const machineId = os.hostname();

                const apiRes = await axios.post(
                    `${server_url}/api/license/verify`,
                    { license_key, machine_id: machineId },
                    { headers: { 'Accept': 'application/json', 'Content-Type': 'application/json' }, timeout: 12000 }
                );

                res.writeHead(200, { 'Content-Type': 'application/json' });
                res.end(JSON.stringify({ success: true, data: apiRes.data }));
            } catch (err) {
                const status  = err.response?.status  || 500;
                const errData = err.response?.data    || { status: 'error', message: err.message };
                res.writeHead(status, { 'Content-Type': 'application/json' });
                res.end(JSON.stringify({ success: false, data: errData }));
            }
        });
        return;
    }

    // ── POST /shutdown ─────────────────────────────────────────
    if (req.method === 'POST' && req.url === '/shutdown') {
        res.writeHead(200, { 'Content-Type': 'application/json' });
        res.end(JSON.stringify({ ok: true }));
        console.log('[ACTIVATE] Activation complete. Closing tool.');
        setTimeout(() => { server.close(); process.exit(0); }, 600);
        return;
    }

    res.writeHead(404); res.end('Not found');
});

server.listen(PORT, '127.0.0.1', () => {
    console.log('============================================');
    console.log('   ArchEng Pro — License Activation Tool   ');
    console.log('============================================');
    console.log(`  Machine ID : ${os.hostname()}`);
    console.log(`  Local URL  : http://localhost:${PORT}`);
    console.log('  Opening browser...');
    console.log('  (Close this window after activation)');
    console.log('============================================');
    exec(`start http://localhost:${PORT}`);
});

server.on('error', err => {
    if (err.code === 'EADDRINUSE') {
        console.error(`[ERROR] Port ${PORT} is already in use. Close any existing activation window and try again.`);
    } else {
        console.error(`[ERROR] ${err.message}`);
    }
    process.exit(1);
});
