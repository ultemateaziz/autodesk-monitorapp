<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ArchEng Pro | API Reference</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root[data-theme="dark"] {
            --bg:#0f1117;--surface:#161b27;--card:#1c2236;--border:rgba(255,255,255,0.07);
            --text:#e2e8f0;--muted:#64748b;--accent:#6366f1;--accent-glow:rgba(99,102,241,0.25);
            --green:#10b981;--red:#ef4444;--yellow:#f59e0b;--blue:#3b82f6;
        }
        :root[data-theme="light"] {
            --bg:#f1f5f9;--surface:#ffffff;--card:#f8fafc;--border:rgba(0,0,0,0.08);
            --text:#0f172a;--muted:#94a3b8;--accent:#6366f1;--accent-glow:rgba(99,102,241,0.15);
            --green:#10b981;--red:#ef4444;--yellow:#f59e0b;--blue:#3b82f6;
        }
        *{margin:0;padding:0;box-sizing:border-box;}
        body{font-family:'Outfit',sans-serif;background:var(--bg);color:var(--text);min-height:100vh;display:flex;}

        .sidebar{width:260px;background:var(--surface);border-right:1px solid var(--border);display:flex;flex-direction:column;padding:24px 16px;position:fixed;top:0;left:0;bottom:0;z-index:100;transition:all 0.3s ease;}
        .sidebar.collapsed{width:72px;padding:24px 10px;}
        .sidebar.collapsed .logo-text,.sidebar.collapsed .nav-label,.sidebar.collapsed .section-title{display:none;}
        .sidebar.collapsed .nav-link{justify-content:center;padding:12px;}
        .logo{display:flex;align-items:center;gap:12px;margin-bottom:36px;padding:0 8px;}
        .logo-icon{width:40px;height:40px;background:linear-gradient(135deg,#6366f1,#8b5cf6);border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:18px;color:white;flex-shrink:0;}
        .logo-text{font-size:16px;font-weight:700;}.logo-sub{font-size:10px;color:var(--muted);font-weight:400;margin-top:2px;}
        .section-title{font-size:10px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:1px;padding:0 8px;margin:20px 0 6px;}
        .nav-link{display:flex;align-items:center;gap:12px;padding:11px 12px;border-radius:10px;color:var(--muted);text-decoration:none;font-size:14px;font-weight:500;transition:all 0.2s;}
        .nav-link:hover{background:var(--accent-glow);color:var(--accent);}
        .nav-link.active{background:var(--accent-glow);color:var(--accent);}
        .nav-link i{width:18px;text-align:center;flex-shrink:0;}
        .sidebar-footer{margin-top:auto;}

        .main{margin-left:260px;flex:1;display:flex;flex-direction:column;min-height:100vh;transition:margin-left 0.3s ease;}
        .main.expanded{margin-left:72px;}
        .topbar{height:64px;background:var(--surface);border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;padding:0 28px;position:sticky;top:0;z-index:50;}
        .topbar-left{display:flex;align-items:center;gap:16px;}
        .topbar-right{display:flex;align-items:center;gap:12px;}
        .icon-btn{width:38px;height:38px;border-radius:10px;background:var(--card);border:1px solid var(--border);display:flex;align-items:center;justify-content:center;cursor:pointer;color:var(--muted);transition:all 0.2s;font-size:14px;}
        .icon-btn:hover{color:var(--accent);border-color:var(--accent);}

        .content{padding:28px;flex:1;max-width:960px;}
        .page-header{margin-bottom:32px;}
        .page-header h1{font-size:22px;font-weight:700;}
        .page-header p{color:var(--muted);font-size:13px;margin-top:4px;}

        .endpoint-card{background:var(--card);border:1px solid var(--border);border-radius:16px;margin-bottom:20px;overflow:hidden;}
        .endpoint-header{display:flex;align-items:center;gap:14px;padding:18px 24px;border-bottom:1px solid var(--border);cursor:pointer;user-select:none;}
        .endpoint-header:hover{background:rgba(255,255,255,0.02);}
        .method-badge{padding:4px 12px;border-radius:6px;font-family:'JetBrains Mono',monospace;font-size:12px;font-weight:700;flex-shrink:0;}
        .method-post{background:rgba(99,102,241,0.15);color:#818cf8;}
        .method-get{background:rgba(16,185,129,0.15);color:#10b981;}
        .endpoint-url{font-family:'JetBrains Mono',monospace;font-size:13px;font-weight:600;color:var(--text);flex:1;}
        .endpoint-desc{font-size:12px;color:var(--muted);}
        .endpoint-body{padding:24px;}

        .field-table{width:100%;border-collapse:collapse;margin-top:8px;}
        .field-table th{font-size:11px;font-weight:700;text-transform:uppercase;color:var(--muted);padding:8px 12px;border-bottom:1px solid var(--border);text-align:left;}
        .field-table td{padding:10px 12px;font-size:13px;border-bottom:1px solid var(--border);}
        .field-table tr:last-child td{border-bottom:none;}
        .field-name{font-family:'JetBrains Mono',monospace;color:var(--accent);font-size:12px;}
        .field-type{font-family:'JetBrains Mono',monospace;font-size:11px;color:var(--muted);}
        .req-badge{display:inline-block;padding:2px 7px;border-radius:4px;font-size:10px;font-weight:700;}
        .req-yes{background:rgba(239,68,68,0.12);color:#f87171;}
        .req-no{background:rgba(100,116,139,0.12);color:var(--muted);}

        .code-block{background:var(--surface);border:1px solid var(--border);border-radius:10px;padding:16px 18px;font-family:'JetBrains Mono',monospace;font-size:12px;color:#a5b4fc;line-height:1.7;overflow-x:auto;position:relative;}
        .code-block .key{color:#f9a8d4;}.code-block .val{color:#86efac;}.code-block .str{color:#fde68a;}
        .copy-btn{position:absolute;top:10px;right:10px;background:var(--card);border:1px solid var(--border);color:var(--muted);padding:4px 10px;border-radius:6px;font-size:11px;cursor:pointer;font-family:'Outfit',sans-serif;transition:all 0.2s;}
        .copy-btn:hover{color:var(--accent);border-color:var(--accent);}

        .section-label{font-size:12px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:0.5px;margin-bottom:10px;margin-top:20px;}
        .section-label:first-child{margin-top:0;}

        .response-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-top:16px;}
        @media(max-width:700px){.response-grid{grid-template-columns:1fr;}}

        .status-label{display:inline-flex;align-items:center;gap:6px;font-size:11px;font-weight:700;padding:3px 10px;border-radius:6px;margin-bottom:8px;}
        .status-200{background:rgba(16,185,129,0.12);color:#10b981;}
        .status-403{background:rgba(239,68,68,0.12);color:#f87171;}
        .status-404{background:rgba(245,158,11,0.12);color:#fbbf24;}

        .info-box{padding:14px 16px;border-radius:10px;font-size:13px;margin-bottom:24px;}
        .info-box.yellow{background:rgba(245,158,11,0.08);border:1px solid rgba(245,158,11,0.2);color:#fbbf24;}
        .info-box.blue{background:rgba(59,130,246,0.08);border:1px solid rgba(59,130,246,0.2);color:#60a5fa;}

        @media(max-width:900px){
            .sidebar{width:72px;padding:24px 10px;}
            .sidebar .logo-text,.sidebar .nav-label,.sidebar .section-title{display:none;}
            .sidebar .nav-link{justify-content:center;padding:12px;}
            .main{margin-left:72px;}
        }
    </style>
</head>
<body>

<aside class="sidebar" id="sidebar">
    <div class="logo">
        <div class="logo-icon"><i class="fas fa-key"></i></div>
        <div><div class="logo-text">LicenseHub</div><div class="logo-sub">ArchEng Pro</div></div>
    </div>
    <nav>
        <div class="section-title">Management</div>
        <a href="{{ route('dashboard') }}" class="nav-link"><i class="fas fa-th-large"></i><span class="nav-label">Dashboard</span></a>
        <a href="{{ route('license.list') }}" class="nav-link"><i class="fas fa-list-ul"></i><span class="nav-label">All Licenses</span></a>
        <div class="section-title">Developer</div>
        <a href="{{ route('api.reference') }}" class="nav-link active"><i class="fas fa-code"></i><span class="nav-label">API Reference</span></a>
    </nav>
    <div class="sidebar-footer">
        <div class="section-title">System</div>
        <a href="{{ route('settings') }}" class="nav-link"><i class="fas fa-cog"></i><span class="nav-label">Settings</span></a>
        <form method="POST" action="{{ route('logout') }}" style="margin-top:4px;">
            @csrf
            <button type="submit" class="nav-link" style="width:100%;background:none;border:none;cursor:pointer;text-align:left;color:#ef4444;">
                <i class="fas fa-right-from-bracket" style="color:#ef4444;"></i><span class="nav-label">Logout</span>
            </button>
        </form>
    </div>
</aside>

<div class="main" id="main">
    <header class="topbar">
        <div class="topbar-left">
            <button class="icon-btn" id="toggleSidebar"><i class="fas fa-bars"></i></button>
            <span style="font-size:16px;font-weight:600;">API Reference</span>
        </div>
        <div class="topbar-right">
            <button class="icon-btn" id="themeToggle"><i class="fas fa-moon" id="themeIcon"></i></button>
        </div>
    </header>

    <main class="content">
        <div class="page-header">
            <h1>API Reference</h1>
            <p>Endpoints for the monitor agent running on client machines</p>
        </div>

        <div class="info-box yellow">
            <i class="fas fa-triangle-exclamation" style="margin-right:8px;"></i>
            All requests must include <strong>Accept: application/json</strong> and <strong>Content-Type: application/json</strong> headers.
            Base URL: <code style="font-family:'JetBrains Mono',monospace;">http://your-server.com/api</code>
        </div>

        <div class="info-box blue">
            <i class="fas fa-info-circle" style="margin-right:8px;"></i>
            <strong>Integration flow:</strong> On first run → <code style="font-family:'JetBrains Mono',monospace;">/activate</code> →
            On every startup → <code style="font-family:'JetBrains Mono',monospace;">/verify</code> →
            Every 5 minutes while running → <code style="font-family:'JetBrains Mono',monospace;">/pulse</code>
        </div>

        <!-- ACTIVATE -->
        <div class="endpoint-card">
            <div class="endpoint-header">
                <span class="method-badge method-post">POST</span>
                <span class="endpoint-url">/api/license/activate</span>
                <span class="endpoint-desc">First-time activation on a new machine</span>
            </div>
            <div class="endpoint-body">
                <div class="section-label">Request Body</div>
                <table class="field-table">
                    <thead><tr><th>Field</th><th>Type</th><th>Required</th><th>Description</th></tr></thead>
                    <tbody>
                        <tr><td class="field-name">license_key</td><td class="field-type">string</td><td><span class="req-badge req-yes">Required</span></td><td style="color:var(--muted);">License key e.g. <code style="font-family:monospace;">AEPRO-XXXX-XXXX-XXXX</code></td></tr>
                        <tr><td class="field-name">machine_id</td><td class="field-type">string</td><td><span class="req-badge req-yes">Required</span></td><td style="color:var(--muted);">Unique machine identifier (hostname or hardware ID)</td></tr>
                        <tr><td class="field-name">ip_address</td><td class="field-type">string</td><td><span class="req-badge req-no">Optional</span></td><td style="color:var(--muted);">Machine IP address</td></tr>
                    </tbody>
                </table>

                <div class="response-grid">
                    <div>
                        <div class="section-label">Example Request</div>
                        <div class="code-block">
                            <button class="copy-btn" onclick="copyCode(this)">Copy</button>
{<br>
&nbsp;&nbsp;<span class="key">"license_key"</span>: <span class="str">"AEPRO-AB12-CD34-EF56"</span>,<br>
&nbsp;&nbsp;<span class="key">"machine_id"</span>: <span class="str">"PC-ARCH-001"</span>,<br>
&nbsp;&nbsp;<span class="key">"ip_address"</span>: <span class="str">"192.168.1.50"</span><br>
}
                        </div>
                    </div>
                    <div>
                        <div class="section-label">Responses</div>
                        <div class="status-label status-200"><i class="fas fa-circle" style="font-size:7px;"></i> 200 — Activated</div>
                        <div class="code-block" style="margin-bottom:10px;">
{<br>
&nbsp;&nbsp;<span class="key">"status"</span>: <span class="str">"activated"</span>,<br>
&nbsp;&nbsp;<span class="key">"tier"</span>: <span class="str">"1Y"</span>,<br>
&nbsp;&nbsp;<span class="key">"expires_at"</span>: <span class="str">"2027-03-16 10:00:00"</span>,<br>
&nbsp;&nbsp;<span class="key">"days_left"</span>: <span class="val">365</span><br>
}
                        </div>
                        <div class="status-label status-403"><i class="fas fa-circle" style="font-size:7px;"></i> 403 — Already on another machine</div>
                        <div class="code-block">
{<br>
&nbsp;&nbsp;<span class="key">"status"</span>: <span class="str">"invalid"</span>,<br>
&nbsp;&nbsp;<span class="key">"message"</span>: <span class="str">"Key already activated on another machine."</span><br>
}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- VERIFY -->
        <div class="endpoint-card">
            <div class="endpoint-header">
                <span class="method-badge method-post">POST</span>
                <span class="endpoint-url">/api/license/verify</span>
                <span class="endpoint-desc">Check license status on every app startup</span>
            </div>
            <div class="endpoint-body">
                <div class="section-label">Request Body</div>
                <table class="field-table">
                    <thead><tr><th>Field</th><th>Type</th><th>Required</th><th>Description</th></tr></thead>
                    <tbody>
                        <tr><td class="field-name">license_key</td><td class="field-type">string</td><td><span class="req-badge req-yes">Required</span></td><td style="color:var(--muted);">License key</td></tr>
                        <tr><td class="field-name">machine_id</td><td class="field-type">string</td><td><span class="req-badge req-yes">Required</span></td><td style="color:var(--muted);">Must match the machine that activated</td></tr>
                    </tbody>
                </table>

                <div class="response-grid">
                    <div>
                        <div class="section-label">Example Request</div>
                        <div class="code-block">
                            <button class="copy-btn" onclick="copyCode(this)">Copy</button>
{<br>
&nbsp;&nbsp;<span class="key">"license_key"</span>: <span class="str">"AEPRO-AB12-CD34-EF56"</span>,<br>
&nbsp;&nbsp;<span class="key">"machine_id"</span>: <span class="str">"PC-ARCH-001"</span><br>
}
                        </div>
                    </div>
                    <div>
                        <div class="section-label">Responses</div>
                        <div class="status-label status-200"><i class="fas fa-circle" style="font-size:7px;"></i> 200 — Valid</div>
                        <div class="code-block" style="margin-bottom:10px;">
{<br>
&nbsp;&nbsp;<span class="key">"status"</span>: <span class="str">"valid"</span>,<br>
&nbsp;&nbsp;<span class="key">"tier"</span>: <span class="str">"1Y"</span>,<br>
&nbsp;&nbsp;<span class="key">"expires_at"</span>: <span class="str">"2027-03-16 10:00:00"</span>,<br>
&nbsp;&nbsp;<span class="key">"days_left"</span>: <span class="val">300</span><br>
}
                        </div>
                        <div class="status-label status-403"><i class="fas fa-circle" style="font-size:7px;"></i> 403 — Locked / Expired</div>
                        <div class="code-block">
{<br>
&nbsp;&nbsp;<span class="key">"status"</span>: <span class="str">"locked"</span>,<br>
&nbsp;&nbsp;<span class="key">"message"</span>: <span class="str">"Access locked by administrator."</span><br>
}
                        </div>
                    </div>
                </div>

                <div style="margin-top:16px;padding:12px 16px;background:var(--surface);border-radius:10px;border:1px solid var(--border);">
                    <div style="font-size:12px;font-weight:700;color:var(--muted);margin-bottom:8px;">All possible <code style="font-family:monospace;">status</code> values</div>
                    <div style="display:flex;flex-wrap:wrap;gap:8px;">
                        <span style="font-family:'JetBrains Mono',monospace;font-size:12px;background:rgba(16,185,129,0.12);color:#10b981;padding:3px 10px;border-radius:6px;">valid</span>
                        <span style="font-family:'JetBrains Mono',monospace;font-size:12px;background:rgba(239,68,68,0.12);color:#f87171;padding:3px 10px;border-radius:6px;">locked</span>
                        <span style="font-family:'JetBrains Mono',monospace;font-size:12px;background:rgba(245,158,11,0.12);color:#fbbf24;padding:3px 10px;border-radius:6px;">expired</span>
                        <span style="font-family:'JetBrains Mono',monospace;font-size:12px;background:rgba(100,116,139,0.12);color:var(--muted);padding:3px 10px;border-radius:6px;">invalid</span>
                        <span style="font-family:'JetBrains Mono',monospace;font-size:12px;background:rgba(100,116,139,0.12);color:var(--muted);padding:3px 10px;border-radius:6px;">not_activated</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- PULSE -->
        <div class="endpoint-card">
            <div class="endpoint-header">
                <span class="method-badge method-post">POST</span>
                <span class="endpoint-url">/api/license/pulse</span>
                <span class="endpoint-desc">Heartbeat — call every 5 minutes while monitor is running</span>
            </div>
            <div class="endpoint-body">
                <div class="section-label">Request Body</div>
                <table class="field-table">
                    <thead><tr><th>Field</th><th>Type</th><th>Required</th><th>Description</th></tr></thead>
                    <tbody>
                        <tr><td class="field-name">license_key</td><td class="field-type">string</td><td><span class="req-badge req-yes">Required</span></td><td style="color:var(--muted);">License key</td></tr>
                        <tr><td class="field-name">machine_id</td><td class="field-type">string</td><td><span class="req-badge req-yes">Required</span></td><td style="color:var(--muted);">Machine ID</td></tr>
                    </tbody>
                </table>

                <div class="response-grid">
                    <div>
                        <div class="section-label">Example Request</div>
                        <div class="code-block">
                            <button class="copy-btn" onclick="copyCode(this)">Copy</button>
{<br>
&nbsp;&nbsp;<span class="key">"license_key"</span>: <span class="str">"AEPRO-AB12-CD34-EF56"</span>,<br>
&nbsp;&nbsp;<span class="key">"machine_id"</span>: <span class="str">"PC-ARCH-001"</span><br>
}
                        </div>
                    </div>
                    <div>
                        <div class="section-label">Response</div>
                        <div class="status-label status-200"><i class="fas fa-circle" style="font-size:7px;"></i> 200 — OK</div>
                        <div class="code-block" style="margin-bottom:10px;">
{<br>
&nbsp;&nbsp;<span class="key">"status"</span>: <span class="str">"ok"</span>,<br>
&nbsp;&nbsp;<span class="key">"days_left"</span>: <span class="val">300</span><br>
}
                        </div>
                        <div class="status-label status-403"><i class="fas fa-circle" style="font-size:7px;"></i> 403 — Stop the monitor</div>
                        <div class="code-block">
{<br>
&nbsp;&nbsp;<span class="key">"status"</span>: <span class="str">"locked"</span><br>
}
                        </div>
                    </div>
                </div>

                <div style="margin-top:16px;padding:12px 16px;background:rgba(99,102,241,0.06);border:1px solid rgba(99,102,241,0.15);border-radius:10px;font-size:12px;color:#a5b4fc;">
                    <i class="fas fa-lightbulb" style="margin-right:8px;"></i>
                    <strong>Monitor agent logic:</strong> If pulse returns <code style="font-family:monospace;">status !== "ok"</code>, immediately stop the monitoring agent and show a license error to the user.
                </div>
            </div>
        </div>

    </main>
</div>

<div id="toast" style="position:fixed;bottom:28px;right:28px;background:#10b981;color:white;padding:12px 20px;border-radius:10px;font-size:13px;font-weight:600;display:none;z-index:9999;box-shadow:0 4px 20px rgba(0,0,0,0.3);">
    <i class="fas fa-copy" style="margin-right:8px;"></i> Copied!
</div>

<script>
    const sidebar=document.getElementById('sidebar'),mainEl=document.getElementById('main');
    document.getElementById('toggleSidebar').addEventListener('click',()=>{sidebar.classList.toggle('collapsed');mainEl.classList.toggle('expanded');localStorage.setItem('sb',sidebar.classList.contains('collapsed'));});
    if(localStorage.getItem('sb')==='true'){sidebar.classList.add('collapsed');mainEl.classList.add('expanded');}
    const html=document.documentElement,themeIcon=document.getElementById('themeIcon');
    const saved=localStorage.getItem('lm-theme')||'dark';html.setAttribute('data-theme',saved);themeIcon.className=saved==='dark'?'fas fa-moon':'fas fa-sun';
    document.getElementById('themeToggle').addEventListener('click',()=>{const t=html.getAttribute('data-theme')==='dark'?'light':'dark';html.setAttribute('data-theme',t);localStorage.setItem('lm-theme',t);themeIcon.className=t==='dark'?'fas fa-moon':'fas fa-sun';});

    function copyCode(btn) {
        const block = btn.parentElement;
        const text = block.innerText.replace('Copy','').trim();
        navigator.clipboard.writeText(text).then(()=>{
            const t=document.getElementById('toast');t.style.display='block';setTimeout(()=>t.style.display='none',2000);
        });
    }
</script>
</body>
</html>
