<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ArchEng Pro | License Manager</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root[data-theme="dark"] {
            --bg: #0f1117;
            --surface: #161b27;
            --card: #1c2236;
            --border: rgba(255,255,255,0.07);
            --text: #e2e8f0;
            --muted: #64748b;
            --accent: #6366f1;
            --accent-glow: rgba(99,102,241,0.25);
            --green: #10b981;
            --red: #ef4444;
            --yellow: #f59e0b;
            --blue: #3b82f6;
        }
        :root[data-theme="light"] {
            --bg: #f1f5f9;
            --surface: #ffffff;
            --card: #f8fafc;
            --border: rgba(0,0,0,0.08);
            --text: #0f172a;
            --muted: #94a3b8;
            --accent: #6366f1;
            --accent-glow: rgba(99,102,241,0.15);
            --green: #10b981;
            --red: #ef4444;
            --yellow: #f59e0b;
            --blue: #3b82f6;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Outfit', sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            display: flex;
        }

        /* ── Sidebar ─────────────────────────────────────────── */
        .sidebar {
            width: 260px;
            background: var(--surface);
            border-right: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            padding: 24px 16px;
            position: fixed;
            top: 0; left: 0; bottom: 0;
            z-index: 100;
            transition: all 0.3s ease;
        }
        .sidebar.collapsed { width: 72px; padding: 24px 10px; }
        .sidebar.collapsed .logo-text,
        .sidebar.collapsed .nav-label,
        .sidebar.collapsed .section-title { display: none; }
        .sidebar.collapsed .nav-link { justify-content: center; padding: 12px; }

        .logo { display: flex; align-items: center; gap: 12px; margin-bottom: 36px; padding: 0 8px; }
        .logo-icon {
            width: 40px; height: 40px;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 18px; color: white; flex-shrink: 0;
        }
        .logo-text { font-size: 16px; font-weight: 700; color: var(--text); }
        .logo-sub { font-size: 10px; color: var(--muted); font-weight: 400; margin-top: 2px; }

        .section-title {
            font-size: 10px; font-weight: 700; color: var(--muted);
            text-transform: uppercase; letter-spacing: 1px;
            padding: 0 8px; margin: 20px 0 6px;
        }
        .nav-link {
            display: flex; align-items: center; gap: 12px;
            padding: 11px 12px; border-radius: 10px;
            color: var(--muted); text-decoration: none;
            font-size: 14px; font-weight: 500;
            transition: all 0.2s; cursor: pointer; border: none;
            background: none; width: 100%;
        }
        .nav-link:hover { background: var(--accent-glow); color: var(--accent); }
        .nav-link.active { background: var(--accent-glow); color: var(--accent); }
        .nav-link i { width: 18px; text-align: center; flex-shrink: 0; }
        .nav-label { flex: 1; }

        .sidebar-footer { margin-top: auto; }
        .sidebar-footer .nav-link { color: #ef4444; }
        .sidebar-footer .nav-link:hover { background: rgba(239,68,68,0.1); color: #ef4444; }

        /* ── Main ─────────────────────────────────────────────── */
        .main { margin-left: 260px; flex: 1; display: flex; flex-direction: column; min-height: 100vh; transition: margin-left 0.3s ease; }
        .main.expanded { margin-left: 72px; }

        /* ── Topbar ───────────────────────────────────────────── */
        .topbar {
            height: 64px;
            background: var(--surface);
            border-bottom: 1px solid var(--border);
            display: flex; align-items: center; justify-content: space-between;
            padding: 0 28px;
            position: sticky; top: 0; z-index: 50;
        }
        .topbar-left { display: flex; align-items: center; gap: 16px; }
        .topbar-right { display: flex; align-items: center; gap: 12px; }
        .icon-btn {
            width: 38px; height: 38px; border-radius: 10px;
            background: var(--card); border: 1px solid var(--border);
            display: flex; align-items: center; justify-content: center;
            cursor: pointer; color: var(--muted);
            transition: all 0.2s; font-size: 14px;
        }
        .icon-btn:hover { color: var(--accent); border-color: var(--accent); }
        .page-title-header { font-size: 16px; font-weight: 600; color: var(--text); }

        /* ── Content ──────────────────────────────────────────── */
        .content { padding: 28px; flex: 1; }
        .page-header { margin-bottom: 24px; }
        .page-header h1 { font-size: 22px; font-weight: 700; }
        .page-header p { color: var(--muted); font-size: 13px; margin-top: 4px; }

        /* ── Flash ────────────────────────────────────────────── */
        .flash {
            padding: 14px 20px; border-radius: 12px; margin-bottom: 20px;
            display: flex; align-items: center; gap: 12px;
            font-size: 14px; font-weight: 500;
            animation: slideDown 0.3s ease;
        }
        .flash-success { background: rgba(16,185,129,0.12); color: #10b981; border: 1px solid rgba(16,185,129,0.25); }
        .flash-error   { background: rgba(239,68,68,0.12);  color: #ef4444;  border: 1px solid rgba(239,68,68,0.25); }
        @keyframes slideDown { from { opacity: 0; transform: translateY(-8px); } to { opacity: 1; transform: translateY(0); } }

        /* ── Metrics Grid ─────────────────────────────────────── */
        .metrics { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 16px; margin-bottom: 28px; }
        .metric {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 16px; padding: 20px;
            position: relative; overflow: hidden;
        }
        .metric::before {
            content: ''; position: absolute; top: 0; right: 0;
            width: 80px; height: 80px; border-radius: 50%;
            transform: translate(20px, -20px);
        }
        .metric.blue::before  { background: rgba(59,130,246,0.08); }
        .metric.green::before { background: rgba(16,185,129,0.08); }
        .metric.red::before   { background: rgba(239,68,68,0.08); }
        .metric.purple::before{ background: rgba(99,102,241,0.08); }

        .metric-icon {
            width: 38px; height: 38px; border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 16px; margin-bottom: 14px;
        }
        .metric.blue   .metric-icon { background: rgba(59,130,246,0.15);  color: var(--blue); }
        .metric.green  .metric-icon { background: rgba(16,185,129,0.15);  color: var(--green); }
        .metric.red    .metric-icon { background: rgba(239,68,68,0.15);   color: var(--red); }
        .metric.purple .metric-icon { background: rgba(99,102,241,0.15);  color: var(--accent); }

        .metric-val  { font-size: 28px; font-weight: 700; font-family: 'JetBrains Mono', monospace; }
        .metric-label{ font-size: 12px; color: var(--muted); margin-top: 4px; }

        /* ── Two-col layout ───────────────────────────────────── */
        .two-col { display: grid; grid-template-columns: 380px 1fr; gap: 20px; margin-bottom: 28px; }

        /* ── Card ─────────────────────────────────────────────── */
        .card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 16px; padding: 24px;
        }
        .card-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; }
        .card-title  { font-size: 14px; font-weight: 700; display: flex; align-items: center; gap: 10px; }
        .card-title i{ color: var(--accent); }

        /* ── Generate Form ────────────────────────────────────── */
        .form-label { font-size: 12px; font-weight: 600; color: var(--muted); text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 8px; }
        .form-select, .form-input {
            width: 100%; background: var(--surface);
            border: 1px solid var(--border);
            color: var(--text); padding: 11px 14px;
            border-radius: 10px; font-size: 14px;
            font-family: 'Outfit', sans-serif;
            outline: none; transition: border-color 0.2s;
            -webkit-appearance: none; appearance: none;
        }
        .form-select:focus, .form-input:focus { border-color: var(--accent); }
        .form-group { margin-bottom: 16px; }

        .tier-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
        .tier-btn {
            padding: 14px; border-radius: 12px;
            border: 1.5px solid var(--border);
            background: var(--surface); cursor: pointer;
            text-align: center; transition: all 0.2s;
            color: var(--muted); font-size: 12px; font-weight: 600;
        }
        .tier-btn:hover { border-color: var(--accent); color: var(--accent); background: var(--accent-glow); }
        .tier-btn.selected { border-color: var(--accent); color: var(--accent); background: var(--accent-glow); }
        .tier-btn .tier-name { font-size: 15px; font-weight: 700; color: var(--text); display: block; margin-bottom: 4px; }
        .tier-btn.selected .tier-name { color: var(--accent); }
        .tier-val { display: none; }

        .btn-primary {
            width: 100%; padding: 13px;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            color: white; border: none; border-radius: 12px;
            font-size: 14px; font-weight: 700; cursor: pointer;
            display: flex; align-items: center; justify-content: center; gap: 8px;
            transition: opacity 0.2s; margin-top: 8px;
            font-family: 'Outfit', sans-serif;
        }
        .btn-primary:hover { opacity: 0.9; }

        /* ── Table ────────────────────────────────────────────── */
        .table-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        thead th {
            font-size: 11px; font-weight: 700; text-transform: uppercase;
            letter-spacing: 0.5px; color: var(--muted);
            padding: 10px 16px; border-bottom: 1px solid var(--border);
            text-align: left; white-space: nowrap;
        }
        tbody tr { transition: background 0.15s; }
        tbody tr:hover { background: rgba(255,255,255,0.02); }
        tbody td { padding: 14px 16px; font-size: 13px; border-bottom: 1px solid var(--border); vertical-align: middle; }
        tbody tr:last-child td { border-bottom: none; }

        .key-mono {
            font-family: 'JetBrains Mono', monospace;
            font-size: 12px; font-weight: 600; color: var(--accent);
            background: var(--accent-glow); padding: 4px 10px;
            border-radius: 6px; letter-spacing: 1px;
            cursor: pointer; transition: opacity 0.2s;
        }
        .key-mono:hover { opacity: 0.7; }

        .badge {
            display: inline-flex; align-items: center; gap: 5px;
            padding: 3px 10px; border-radius: 20px;
            font-size: 11px; font-weight: 700;
        }
        .badge-active   { background: rgba(16,185,129,0.12); color: #10b981; }
        .badge-inactive { background: rgba(100,116,139,0.12); color: var(--muted); }
        .badge-locked   { background: rgba(239,68,68,0.12);   color: #ef4444; }
        .badge-expired  { background: rgba(245,158,11,0.12);  color: #f59e0b; }
        .badge-7D    { background: rgba(59,130,246,0.12);  color: #60a5fa; }
        .badge-15D   { background: rgba(99,102,241,0.12); color: #818cf8; }
        .badge-6M    { background: rgba(16,185,129,0.12); color: #34d399; }
        .badge-1Y    { background: rgba(245,158,11,0.12); color: #fbbf24; }

        .lock-btn {
            padding: 5px 12px; border-radius: 8px; font-size: 11px; font-weight: 700;
            cursor: pointer; border: none; font-family: 'Outfit', sans-serif;
            display: inline-flex; align-items: center; gap: 5px; transition: all 0.2s;
        }
        .lock-btn.lock-active { background: rgba(239,68,68,0.12); color: #ef4444; }
        .lock-btn.lock-active:hover { background: rgba(239,68,68,0.2); }
        .lock-btn.lock-locked { background: rgba(16,185,129,0.12); color: #10b981; }
        .lock-btn.lock-locked:hover { background: rgba(16,185,129,0.2); }

        /* ── API Box ──────────────────────────────────────────── */
        .api-box {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 10px; padding: 14px 16px;
            font-family: 'JetBrains Mono', monospace;
            font-size: 12px; color: var(--accent);
            word-break: break-all; margin-bottom: 10px;
        }
        .api-method {
            display: inline-block;
            padding: 2px 8px; border-radius: 5px;
            font-size: 10px; font-weight: 700; margin-right: 8px;
        }
        .api-get  { background: rgba(16,185,129,0.15); color: #10b981; }
        .api-post { background: rgba(99,102,241,0.15); color: #818cf8; }

        /* ── Responsive ───────────────────────────────────────── */
        @media (max-width: 900px) {
            .two-col { grid-template-columns: 1fr; }
            .sidebar { width: 72px; padding: 24px 10px; }
            .sidebar .logo-text, .sidebar .nav-label, .sidebar .section-title { display: none; }
            .sidebar .nav-link { justify-content: center; padding: 12px; }
            .main { margin-left: 72px; }
        }
    </style>
</head>
<body>

<!-- Sidebar -->
<aside class="sidebar" id="sidebar">
    <div class="logo">
        <div class="logo-icon"><i class="fas fa-key"></i></div>
        <div>
            <div class="logo-text">LicenseHub</div>
            <div class="logo-sub">ArchEng Pro</div>
        </div>
    </div>

    <nav>
        <div class="section-title">Management</div>
        <a href="{{ route('dashboard') }}" class="nav-link active">
            <i class="fas fa-th-large"></i>
            <span class="nav-label">Dashboard</span>
        </a>
        <a href="{{ route('license.list') }}" class="nav-link">
            <i class="fas fa-list-ul"></i>
            <span class="nav-label">All Licenses</span>
        </a>

        <div class="section-title">Developer</div>
        <a href="{{ route('api.reference') }}" class="nav-link">
            <i class="fas fa-code"></i>
            <span class="nav-label">API Reference</span>
        </a>
    </nav>

    <div class="sidebar-footer">
        <div class="section-title">System</div>
        <a href="{{ route('settings') }}" class="nav-link">
            <i class="fas fa-cog"></i>
            <span class="nav-label">Settings</span>
        </a>
        <form method="POST" action="{{ route('logout') }}" style="margin-top:4px;">
            @csrf
            <button type="submit" class="nav-link" style="width:100%;background:none;border:none;cursor:pointer;text-align:left;color:#ef4444;">
                <i class="fas fa-right-from-bracket" style="color:#ef4444;"></i>
                <span class="nav-label">Logout</span>
            </button>
        </form>
    </div>
</aside>

<!-- Main -->
<div class="main" id="main">
    <!-- Topbar -->
    <header class="topbar">
        <div class="topbar-left">
            <button class="icon-btn" id="toggleSidebar"><i class="fas fa-bars"></i></button>
            <span class="page-title-header">License Manager</span>
        </div>
        <div class="topbar-right">
            <button class="icon-btn" id="themeToggle" title="Toggle Theme">
                <i class="fas fa-moon" id="themeIcon"></i>
            </button>
            <div style="width:34px;height:34px;border-radius:50%;background:linear-gradient(135deg,#6366f1,#8b5cf6);display:flex;align-items:center;justify-content:center;font-size:14px;color:white;">
                <i class="fas fa-user-shield"></i>
            </div>
        </div>
    </header>

    <!-- Content -->
    <main class="content">
        <div class="page-header">
            <h1>License Dashboard</h1>
            <p>Generate, monitor and manage all subscription keys for ArchEng Pro</p>
        </div>

        <!-- Flash Messages -->
        @if (session('success'))
            <div class="flash flash-success">
                <i class="fas fa-check-circle"></i>
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="flash flash-error">
                <i class="fas fa-exclamation-circle"></i>
                {{ session('error') }}
            </div>
        @endif

        <!-- Metrics -->
        <div class="metrics">
            <div class="metric blue">
                <div class="metric-icon"><i class="fas fa-key"></i></div>
                <div class="metric-val">{{ $totalLicenses }}</div>
                <div class="metric-label">Total Keys Generated</div>
            </div>
            <div class="metric green">
                <div class="metric-icon"><i class="fas fa-check-circle"></i></div>
                <div class="metric-val">{{ $activeLicenses }}</div>
                <div class="metric-label">Active Subscriptions</div>
            </div>
            <div class="metric red">
                <div class="metric-icon"><i class="fas fa-lock"></i></div>
                <div class="metric-val">{{ $lockedLicenses }}</div>
                <div class="metric-label">Locked Machines</div>
            </div>
            <div class="metric purple">
                <div class="metric-icon"><i class="fas fa-list-ul"></i></div>
                <div class="metric-val"><a href="{{ route('license.list') }}" style="color:inherit;text-decoration:none;">View All</a></div>
                <div class="metric-label">Manage Licenses</div>
            </div>
        </div>

        <!-- Generate Key Card -->
        <div style="max-width: 480px; margin-bottom: 28px;">
            <div class="card">
                <div class="card-header">
                    <div class="card-title"><i class="fas fa-wand-magic-sparkles"></i> Generate License Key</div>
                </div>

                <form action="{{ route('license.generate') }}" method="POST" id="genForm">
                    @csrf
                    <input type="hidden" name="tier" id="tierInput" value="1Y">

                    <div class="form-group">
                        <label class="form-label">Customer / Company Name</label>
                        <div style="position:relative;">
                            <i class="fas fa-building" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--muted);font-size:13px;"></i>
                            <input type="text" name="customer_name" class="form-input"
                                placeholder="e.g. Al Habtoor Engineering"
                                style="padding-left:36px;"
                                required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Subscription Duration</label>
                        <div class="tier-grid">
                            <button type="button" class="tier-btn" data-tier="7D" onclick="selectTier(this)">
                                <span class="tier-name">7 Days</span>
                                <span>Trial</span>
                            </button>
                            <button type="button" class="tier-btn" data-tier="15D" onclick="selectTier(this)">
                                <span class="tier-name">15 Days</span>
                                <span>Short-term</span>
                            </button>
                            <button type="button" class="tier-btn" data-tier="6M" onclick="selectTier(this)">
                                <span class="tier-name">6 Months</span>
                                <span>Semi-Annual</span>
                            </button>
                            <button type="button" class="tier-btn selected" data-tier="1Y" onclick="selectTier(this)">
                                <span class="tier-name">1 Year</span>
                                <span>Annual ★</span>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn-primary">
                        <i class="fas fa-key"></i>
                        Generate License Key
                    </button>
                </form>

                <!-- Last generated key highlight -->
                @if (session('success') && str_contains(session('success'), 'AEPRO-'))
                    @php preg_match('/AEPRO-[A-Z0-9]+-[A-Z0-9]+-[A-Z0-9]+/', session('success'), $m); @endphp
                    @if (!empty($m))
                        <div style="margin-top: 16px; background: var(--accent-glow); border: 1px solid rgba(99,102,241,0.3); border-radius: 12px; padding: 14px 16px;">
                            <div style="font-size: 11px; color: var(--muted); margin-bottom: 6px;">Generated Key — click to copy</div>
                            <div class="key-mono" onclick="copyKey('{{ $m[0] }}')" style="font-size: 15px; letter-spacing: 2px;">{{ $m[0] }}</div>
                        </div>
                    @endif
                @endif
            </div>
        </div>

        <!-- API Reference -->
        <div class="card" id="api-ref">
            <div class="card-header">
                <div class="card-title"><i class="fas fa-code"></i> API Reference</div>
                <span style="font-size: 11px; color: var(--muted); font-family: 'JetBrains Mono', monospace;">For monitor agent integration</span>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); gap: 16px;">
                <div>
                    <div style="font-size: 12px; font-weight: 700; color: var(--muted); margin-bottom: 8px;">Activate License (first use)</div>
                    <div class="api-box">
                        <span class="api-method api-post">POST</span>/api/license/activate<br>
                        <span style="color:var(--muted);">{ "license_key": "AEPRO-XXXX-XXXX-XXXX", "machine_id": "PC-001", "ip_address": "192.168.x.x" }</span>
                    </div>
                </div>
                <div>
                    <div style="font-size: 12px; font-weight: 700; color: var(--muted); margin-bottom: 8px;">Verify License Status</div>
                    <div class="api-box">
                        <span class="api-method api-post">POST</span>/api/license/verify<br>
                        <span style="color:var(--muted);">{ "license_key": "AEPRO-XXXX-XXXX-XXXX", "machine_id": "PC-001" }</span>
                    </div>
                </div>
                <div>
                    <div style="font-size: 12px; font-weight: 700; color: var(--muted); margin-bottom: 8px;">Heartbeat / Pulse</div>
                    <div class="api-box">
                        <span class="api-method api-post">POST</span>/api/license/pulse<br>
                        <span style="color:var(--muted);">{ "license_key": "AEPRO-XXXX-XXXX-XXXX", "machine_id": "PC-001" }</span>
                    </div>
                </div>
            </div>

            <div style="margin-top: 16px; padding: 14px 16px; background: rgba(245,158,11,0.08); border: 1px solid rgba(245,158,11,0.2); border-radius: 10px; font-size: 12px; color: #fbbf24;">
                <i class="fas fa-triangle-exclamation" style="margin-right: 6px;"></i>
                All API calls must include <strong>Accept: application/json</strong> header.
                Responses include <code>status</code> (valid/invalid/locked/expired) and <code>expires_at</code>.
            </div>
        </div>

    </main>
</div>

<!-- Toast notification -->
<div id="toast" style="position:fixed;bottom:28px;right:28px;background:#10b981;color:white;padding:12px 20px;border-radius:10px;font-size:13px;font-weight:600;display:none;z-index:9999;box-shadow:0 4px 20px rgba(0,0,0,0.3);">
    <i class="fas fa-copy" style="margin-right:8px;"></i> Copied to clipboard!
</div>

<script>
    // Sidebar toggle
    const sidebar = document.getElementById('sidebar');
    const mainEl = document.getElementById('main');
    document.getElementById('toggleSidebar').addEventListener('click', () => {
        sidebar.classList.toggle('collapsed');
        mainEl.classList.toggle('expanded');
        localStorage.setItem('sb', sidebar.classList.contains('collapsed'));
    });
    if (localStorage.getItem('sb') === 'true') {
        sidebar.classList.add('collapsed');
        mainEl.classList.add('expanded');
    }

    // Theme toggle
    const html = document.documentElement;
    const themeIcon = document.getElementById('themeIcon');
    const saved = localStorage.getItem('lm-theme') || 'dark';
    html.setAttribute('data-theme', saved);
    themeIcon.className = saved === 'dark' ? 'fas fa-moon' : 'fas fa-sun';

    document.getElementById('themeToggle').addEventListener('click', () => {
        const t = html.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
        html.setAttribute('data-theme', t);
        localStorage.setItem('lm-theme', t);
        themeIcon.className = t === 'dark' ? 'fas fa-moon' : 'fas fa-sun';
    });

    // Tier selection
    function selectTier(btn) {
        document.querySelectorAll('.tier-btn').forEach(b => b.classList.remove('selected'));
        btn.classList.add('selected');
        document.getElementById('tierInput').value = btn.getAttribute('data-tier');
    }

    // Copy key to clipboard
    function copyKey(key) {
        navigator.clipboard.writeText(key).then(() => {
            const t = document.getElementById('toast');
            t.style.display = 'block';
            setTimeout(() => t.style.display = 'none', 2000);
        });
    }

    // Auto-dismiss flash
    setTimeout(() => {
        document.querySelectorAll('.flash').forEach(f => f.style.opacity = '0');
    }, 4000);
</script>
</body>
</html>
