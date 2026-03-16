<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ArchEng Pro | Settings</title>
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

        .content{padding:28px;flex:1;max-width:860px;}
        .page-header{margin-bottom:32px;}
        .page-header h1{font-size:22px;font-weight:700;}
        .page-header p{color:var(--muted);font-size:13px;margin-top:4px;}

        .settings-section{background:var(--card);border:1px solid var(--border);border-radius:16px;margin-bottom:20px;overflow:hidden;}
        .settings-section-header{padding:18px 24px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:12px;}
        .settings-section-header i{color:var(--accent);width:18px;text-align:center;}
        .settings-section-header h2{font-size:15px;font-weight:600;}
        .settings-section-header p{font-size:12px;color:var(--muted);margin-top:2px;}

        .settings-row{display:flex;align-items:center;justify-content:space-between;padding:16px 24px;border-bottom:1px solid var(--border);}
        .settings-row:last-child{border-bottom:none;}
        .settings-label{font-size:14px;font-weight:500;}
        .settings-desc{font-size:12px;color:var(--muted);margin-top:3px;}
        .settings-control{flex-shrink:0;margin-left:24px;}

        /* Toggle switch */
        .toggle{position:relative;display:inline-block;width:44px;height:24px;}
        .toggle input{opacity:0;width:0;height:0;}
        .toggle-slider{position:absolute;cursor:pointer;inset:0;background:var(--border);border-radius:24px;transition:0.3s;}
        .toggle-slider:before{position:absolute;content:"";height:18px;width:18px;left:3px;bottom:3px;background:white;border-radius:50%;transition:0.3s;}
        input:checked + .toggle-slider{background:var(--accent);}
        input:checked + .toggle-slider:before{transform:translateX(20px);}

        .form-input{background:var(--surface);border:1px solid var(--border);border-radius:8px;color:var(--text);font-family:'Outfit',sans-serif;font-size:13px;padding:8px 12px;width:220px;outline:none;transition:border-color 0.2s;}
        .form-input:focus{border-color:var(--accent);}
        .form-select{background:var(--surface);border:1px solid var(--border);border-radius:8px;color:var(--text);font-family:'Outfit',sans-serif;font-size:13px;padding:8px 12px;width:160px;outline:none;transition:border-color 0.2s;appearance:none;cursor:pointer;}
        .form-select:focus{border-color:var(--accent);}

        .btn{display:inline-flex;align-items:center;gap:8px;padding:9px 18px;border-radius:9px;font-family:'Outfit',sans-serif;font-size:13px;font-weight:600;cursor:pointer;border:none;transition:all 0.2s;}
        .btn-primary{background:var(--accent);color:white;}
        .btn-primary:hover{opacity:0.9;}
        .btn-danger{background:rgba(239,68,68,0.12);color:#f87171;border:1px solid rgba(239,68,68,0.2);}
        .btn-danger:hover{background:rgba(239,68,68,0.2);}
        .btn-ghost{background:var(--surface);color:var(--muted);border:1px solid var(--border);}
        .btn-ghost:hover{color:var(--text);border-color:var(--accent);}

        .info-row{padding:16px 24px;display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid var(--border);}
        .info-row:last-child{border-bottom:none;}
        .info-val{font-family:'JetBrains Mono',monospace;font-size:12px;color:var(--muted);background:var(--surface);padding:4px 10px;border-radius:6px;border:1px solid var(--border);}

        .badge{display:inline-flex;align-items:center;gap:5px;padding:3px 10px;border-radius:6px;font-size:11px;font-weight:700;}
        .badge-green{background:rgba(16,185,129,0.12);color:#10b981;}

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
        <a href="{{ route('api.reference') }}" class="nav-link"><i class="fas fa-code"></i><span class="nav-label">API Reference</span></a>
    </nav>
    <div class="sidebar-footer">
        <div class="section-title">System</div>
        <a href="{{ route('settings') }}" class="nav-link active"><i class="fas fa-cog"></i><span class="nav-label">Settings</span></a>
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
            <span style="font-size:16px;font-weight:600;">Settings</span>
        </div>
        <div class="topbar-right">
            <button class="icon-btn" id="themeToggle"><i class="fas fa-moon" id="themeIcon"></i></button>
        </div>
    </header>

    <main class="content">

        @if(session('success'))
        <div style="background:rgba(16,185,129,0.1);border:1px solid rgba(16,185,129,0.25);color:#10b981;padding:13px 18px;border-radius:10px;font-size:13px;font-weight:500;display:flex;align-items:center;gap:10px;margin-bottom:24px;">
            <i class="fas fa-circle-check"></i> {{ session('success') }}
        </div>
        @endif

        <div class="page-header">
            <h1>Settings</h1>
            <p>Configure your LicenseHub instance</p>
        </div>

        <!-- Appearance -->
        <div class="settings-section">
            <div class="settings-section-header">
                <i class="fas fa-palette"></i>
                <div>
                    <h2>Appearance</h2>
                    <p>Theme and display preferences</p>
                </div>
            </div>
            <div class="settings-row">
                <div>
                    <div class="settings-label">Dark Mode</div>
                    <div class="settings-desc">Toggle between dark and light interface theme</div>
                </div>
                <div class="settings-control">
                    <label class="toggle">
                        <input type="checkbox" id="themeSwitch">
                        <span class="toggle-slider"></span>
                    </label>
                </div>
            </div>
            <div class="settings-row">
                <div>
                    <div class="settings-label">Items per Page</div>
                    <div class="settings-desc">Number of license keys shown per page in All Licenses</div>
                </div>
                <div class="settings-control">
                    <select class="form-select" disabled title="Coming soon">
                        <option>20</option>
                        <option>50</option>
                        <option>100</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- License Key Settings -->
        <div class="settings-section">
            <div class="settings-section-header">
                <i class="fas fa-key"></i>
                <div>
                    <h2>License Key Format</h2>
                    <p>Current key generation configuration</p>
                </div>
            </div>
            <div class="info-row">
                <div>
                    <div class="settings-label">Key Prefix</div>
                    <div class="settings-desc">All generated keys begin with this prefix</div>
                </div>
                <span class="info-val">AEPRO-</span>
            </div>
            <div class="info-row">
                <div>
                    <div class="settings-label">Key Pattern</div>
                    <div class="settings-desc">Format: PREFIX-XXXX-XXXX-XXXX (4 segments)</div>
                </div>
                <span class="info-val">AEPRO-XXXX-XXXX-XXXX</span>
            </div>
            <div class="info-row">
                <div>
                    <div class="settings-label">Available Tiers</div>
                    <div class="settings-desc">Subscription durations available when generating keys</div>
                </div>
                <div style="display:flex;gap:6px;">
                    <span class="badge badge-green">7D</span>
                    <span class="badge badge-green">15D</span>
                    <span class="badge badge-green">6M</span>
                    <span class="badge badge-green">1Y</span>
                </div>
            </div>
            <div class="info-row">
                <div>
                    <div class="settings-label">Machines per Key</div>
                    <div class="settings-desc">Each license key is locked to one machine</div>
                </div>
                <span class="info-val">1 machine</span>
            </div>
        </div>

        <!-- API Settings -->
        <div class="settings-section">
            <div class="settings-section-header">
                <i class="fas fa-plug"></i>
                <div>
                    <h2>API Configuration</h2>
                    <p>Monitor agent connection settings</p>
                </div>
            </div>
            <div class="info-row">
                <div>
                    <div class="settings-label">Base URL</div>
                    <div class="settings-desc">Configure this in your monitor agent</div>
                </div>
                <code class="info-val" id="baseUrl" style="cursor:pointer;" onclick="copyBaseUrl()" title="Click to copy">{{ url('/api') }}</code>
            </div>
            <div class="info-row">
                <div>
                    <div class="settings-label">Pulse Interval</div>
                    <div class="settings-desc">Recommended heartbeat frequency for monitor agents</div>
                </div>
                <span class="info-val">Every 5 minutes</span>
            </div>
            <div class="info-row">
                <div>
                    <div class="settings-label">API Reference</div>
                    <div class="settings-desc">View full endpoint documentation</div>
                </div>
                <a href="{{ route('api.reference') }}" class="btn btn-ghost" style="text-decoration:none;">
                    <i class="fas fa-book-open"></i> View Docs
                </a>
            </div>
        </div>

        <!-- System Info -->
        <div class="settings-section">
            <div class="settings-section-header">
                <i class="fas fa-circle-info"></i>
                <div>
                    <h2>System Information</h2>
                    <p>Application version and environment details</p>
                </div>
            </div>
            <div class="info-row">
                <div>
                    <div class="settings-label">Application</div>
                    <div class="settings-desc">License management system</div>
                </div>
                <span class="info-val">ArchEng Pro LicenseHub</span>
            </div>
            <div class="info-row">
                <div>
                    <div class="settings-label">Laravel Version</div>
                    <div class="settings-desc">Framework version</div>
                </div>
                <span class="info-val">{{ app()->version() }}</span>
            </div>
            <div class="info-row">
                <div>
                    <div class="settings-label">PHP Version</div>
                    <div class="settings-desc">Server PHP runtime</div>
                </div>
                <span class="info-val">{{ PHP_VERSION }}</span>
            </div>
            <div class="info-row">
                <div>
                    <div class="settings-label">Environment</div>
                    <div class="settings-desc">Current application environment</div>
                </div>
                <span class="info-val">{{ app()->environment() }}</span>
            </div>
            <div class="info-row">
                <div>
                    <div class="settings-label">Server Time</div>
                    <div class="settings-desc">Current server timestamp</div>
                </div>
                <span class="info-val" id="serverTime">{{ now()->format('Y-m-d H:i:s') }}</span>
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

    const html=document.documentElement,themeIcon=document.getElementById('themeIcon'),themeSwitch=document.getElementById('themeSwitch');
    const saved=localStorage.getItem('lm-theme')||'dark';
    html.setAttribute('data-theme',saved);
    themeIcon.className=saved==='dark'?'fas fa-moon':'fas fa-sun';
    themeSwitch.checked=saved==='dark';

    document.getElementById('themeToggle').addEventListener('click',()=>applyTheme(html.getAttribute('data-theme')==='dark'?'light':'dark'));
    themeSwitch.addEventListener('change',()=>applyTheme(themeSwitch.checked?'dark':'light'));

    function applyTheme(t){
        html.setAttribute('data-theme',t);
        localStorage.setItem('lm-theme',t);
        themeIcon.className=t==='dark'?'fas fa-moon':'fas fa-sun';
        themeSwitch.checked=t==='dark';
    }

    function copyBaseUrl(){
        const url=document.getElementById('baseUrl').innerText.trim();
        navigator.clipboard.writeText(url).then(()=>{
            const t=document.getElementById('toast');t.style.display='block';setTimeout(()=>t.style.display='none',2000);
        });
    }
</script>
</body>
</html>
