<!DOCTYPE html>
<html lang="en-GB" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ArchEng Pro | LicenseHub</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root[data-theme="dark"] {
            --bg: #080c14;
            --surface: #0f1520;
            --card: #131a27;
            --card2: #162030;
            --border: rgba(255,255,255,0.06);
            --text: #e2e8f0;
            --muted: #4e6080;
            --accent: #6366f1;
            --accent2: #8b5cf6;
            --accent-glow: rgba(99,102,241,0.2);
            --green: #10b981;
            --red: #ef4444;
            --yellow: #f59e0b;
            --blue: #3b82f6;
            --cyan: #06b6d4;
        }
        :root[data-theme="light"] {
            --bg: #f0f4ff;
            --surface: #ffffff;
            --card: #f8faff;
            --card2: #eef2ff;
            --border: rgba(0,0,0,0.07);
            --text: #0f172a;
            --muted: #94a3b8;
            --accent: #6366f1;
            --accent2: #8b5cf6;
            --accent-glow: rgba(99,102,241,0.1);
            --green: #10b981;
            --red: #ef4444;
            --yellow: #f59e0b;
            --blue: #3b82f6;
            --cyan: #06b6d4;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Outfit', sans-serif; background: var(--bg); color: var(--text); min-height: 100vh; display: flex; }

        /* ── Sidebar ── */
        .sidebar {
            width: 255px; background: var(--surface);
            border-right: 1px solid var(--border);
            display: flex; flex-direction: column;
            padding: 22px 14px; position: fixed;
            top: 0; left: 0; bottom: 0; z-index: 100;
            transition: all 0.3s ease;
        }
        .sidebar.collapsed { width: 68px; padding: 22px 10px; }
        .sidebar.collapsed .logo-text,.sidebar.collapsed .nav-label,.sidebar.collapsed .section-title { display: none; }
        .sidebar.collapsed .nav-link { justify-content: center; padding: 12px; }

        .logo { display: flex; align-items: center; gap: 12px; margin-bottom: 32px; padding: 0 6px; }
        .logo-icon {
            width: 38px; height: 38px; flex-shrink: 0;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            border-radius: 10px; display: flex; align-items: center; justify-content: center;
            font-size: 16px; color: white;
            box-shadow: 0 4px 14px rgba(99,102,241,0.4);
        }
        .logo-text { font-size: 15px; font-weight: 800; letter-spacing: -0.3px; }
        .logo-sub  { font-size: 10px; color: var(--muted); margin-top: 1px; }

        .section-title { font-size: 10px; font-weight: 700; color: var(--muted); text-transform: uppercase; letter-spacing: 1.2px; padding: 0 8px; margin: 18px 0 5px; }
        .nav-link {
            display: flex; align-items: center; gap: 11px;
            padding: 10px 12px; border-radius: 10px;
            color: var(--muted); text-decoration: none;
            font-size: 13.5px; font-weight: 500;
            transition: all 0.2s; cursor: pointer;
            border: none; background: none; width: 100%;
        }
        .nav-link:hover { background: var(--accent-glow); color: var(--accent); }
        .nav-link.active { background: var(--accent-glow); color: var(--accent); font-weight: 600; }
        .nav-link i { width: 17px; text-align: center; flex-shrink: 0; font-size: 13px; }
        .nav-label { flex: 1; }

        .sidebar-footer { margin-top: auto; }
        .sidebar-footer .nav-link { color: #ef4444; }
        .sidebar-footer .nav-link:hover { background: rgba(239,68,68,0.1); }

        /* ── Main ── */
        .main { margin-left: 255px; flex: 1; display: flex; flex-direction: column; min-height: 100vh; transition: margin-left 0.3s; }
        .main.expanded { margin-left: 68px; }

        /* ── Topbar ── */
        .topbar {
            height: 62px; background: var(--surface);
            border-bottom: 1px solid var(--border);
            display: flex; align-items: center; justify-content: space-between;
            padding: 0 26px; position: sticky; top: 0; z-index: 50;
            backdrop-filter: blur(10px);
        }
        .topbar-left { display: flex; align-items: center; gap: 14px; }
        .topbar-right { display: flex; align-items: center; gap: 10px; }
        .icon-btn {
            width: 36px; height: 36px; border-radius: 9px;
            background: var(--card); border: 1px solid var(--border);
            display: flex; align-items: center; justify-content: center;
            cursor: pointer; color: var(--muted);
            transition: all 0.2s; font-size: 13px;
        }
        .icon-btn:hover { color: var(--accent); border-color: var(--accent); }
        .page-title-header { font-size: 15px; font-weight: 600; }

        /* ── Content ── */
        .content { padding: 26px; flex: 1; }

        /* ── Flash ── */
        .flash {
            padding: 13px 18px; border-radius: 11px; margin-bottom: 20px;
            display: flex; align-items: center; gap: 11px;
            font-size: 13.5px; font-weight: 500;
            animation: slideDown 0.3s ease;
        }
        .flash-success { background: rgba(16,185,129,0.1); color: #10b981; border: 1px solid rgba(16,185,129,0.2); }
        .flash-error   { background: rgba(239,68,68,0.1);  color: #ef4444; border: 1px solid rgba(239,68,68,0.2); }
        @keyframes slideDown { from { opacity:0;transform:translateY(-8px); } to { opacity:1;transform:translateY(0); } }

        /* ── Page Header ── */
        .page-hero {
            display: flex; align-items: center; justify-content: space-between;
            margin-bottom: 26px;
        }
        .page-hero h1 { font-size: 24px; font-weight: 800; letter-spacing: -0.5px; }
        .page-hero p { color: var(--muted); font-size: 13px; margin-top: 3px; }
        .hero-badge {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 8px 16px; border-radius: 30px;
            background: var(--accent-glow); border: 1px solid rgba(99,102,241,0.3);
            color: var(--accent); font-size: 12px; font-weight: 700;
        }
        .hero-badge .dot { width: 7px; height: 7px; border-radius: 50%; background: var(--green); animation: pulse 2s infinite; }
        @keyframes pulse { 0%,100%{opacity:1;transform:scale(1);} 50%{opacity:0.6;transform:scale(1.3);} }

        /* ── Metric Cards ── */
        .metrics { display: grid; grid-template-columns: repeat(5, 1fr); gap: 14px; margin-bottom: 24px; }
        .metric {
            background: var(--card); border: 1px solid var(--border);
            border-radius: 16px; padding: 20px 18px;
            position: relative; overflow: hidden;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .metric:hover { transform: translateY(-2px); box-shadow: 0 8px 30px rgba(0,0,0,0.15); }
        .metric-top { display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 14px; }
        .metric-icon {
            width: 40px; height: 40px; border-radius: 11px;
            display: flex; align-items: center; justify-content: center; font-size: 16px;
        }
        .metric.blue   .metric-icon { background: rgba(59,130,246,0.15);  color: #60a5fa; }
        .metric.green  .metric-icon { background: rgba(16,185,129,0.15);  color: #34d399; }
        .metric.red    .metric-icon { background: rgba(239,68,68,0.15);   color: #f87171; }
        .metric.yellow .metric-icon { background: rgba(245,158,11,0.15);  color: #fbbf24; }
        .metric.purple .metric-icon { background: rgba(99,102,241,0.15);  color: #818cf8; }

        .metric-trend {
            font-size: 10px; font-weight: 700; padding: 3px 8px;
            border-radius: 20px; display: flex; align-items: center; gap: 4px;
        }
        .trend-up   { background: rgba(16,185,129,0.12); color: #10b981; }
        .trend-down { background: rgba(239,68,68,0.12);  color: #ef4444; }
        .trend-warn { background: rgba(245,158,11,0.12); color: #f59e0b; }

        .metric-val   { font-size: 32px; font-weight: 800; font-family: 'JetBrains Mono', monospace; line-height: 1; }
        .metric-label { font-size: 12px; color: var(--muted); margin-top: 5px; font-weight: 500; }
        .metric-bar   { height: 3px; border-radius: 2px; margin-top: 14px; background: var(--border); }
        .metric-bar-fill { height: 100%; border-radius: 2px; }
        .metric.blue   .metric-bar-fill { background: #3b82f6; }
        .metric.green  .metric-bar-fill { background: #10b981; }
        .metric.red    .metric-bar-fill { background: #ef4444; }
        .metric.yellow .metric-bar-fill { background: #f59e0b; }
        .metric.purple .metric-bar-fill { background: #6366f1; }

        /* ── Grid layouts ── */
        .grid-3 { display: grid; grid-template-columns: 1.1fr 1fr 1fr; gap: 18px; margin-bottom: 20px; }
        .grid-2 { display: grid; grid-template-columns: 420px 1fr; gap: 18px; margin-bottom: 20px; }

        /* ── Card ── */
        .card {
            background: var(--card); border: 1px solid var(--border);
            border-radius: 16px; padding: 22px;
        }
        .card-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 18px; }
        .card-title  { font-size: 13.5px; font-weight: 700; display: flex; align-items: center; gap: 9px; }
        .card-title i { color: var(--accent); }
        .card-sub    { font-size: 11px; color: var(--muted); }

        /* ── Generate Form ── */
        .form-label { font-size: 11px; font-weight: 700; color: var(--muted); text-transform: uppercase; letter-spacing: 0.6px; display: block; margin-bottom: 7px; }
        .form-input {
            width: 100%; background: var(--surface);
            border: 1px solid var(--border); color: var(--text);
            padding: 10px 13px; border-radius: 10px;
            font-size: 13.5px; font-family: 'Outfit', sans-serif;
            outline: none; transition: border-color 0.2s;
        }
        .form-input:focus { border-color: var(--accent); }
        .form-group { margin-bottom: 15px; }

        /* Tier grid — now 3 columns to fit 1M */
        .tier-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; margin-bottom: 4px; }
        .tier-btn {
            padding: 12px 8px; border-radius: 11px;
            border: 1.5px solid var(--border);
            background: var(--surface); cursor: pointer;
            text-align: center; transition: all 0.2s;
            color: var(--muted); font-size: 11px; font-weight: 600;
            font-family: 'Outfit', sans-serif;
        }
        .tier-btn:hover { border-color: var(--accent); color: var(--accent); background: var(--accent-glow); }
        .tier-btn.selected { border-color: var(--accent); color: var(--accent); background: var(--accent-glow); }
        .tier-btn .tier-name { font-size: 14px; font-weight: 800; color: var(--text); display: block; margin-bottom: 2px; }
        .tier-btn.selected .tier-name { color: var(--accent); }
        .tier-btn .tier-sub  { font-size: 10px; opacity: 0.8; }

        .btn-generate {
            width: 100%; padding: 13px;
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            color: white; border: none; border-radius: 12px;
            font-size: 14px; font-weight: 700; cursor: pointer;
            display: flex; align-items: center; justify-content: center; gap: 8px;
            transition: all 0.2s; margin-top: 8px; font-family: 'Outfit', sans-serif;
            box-shadow: 0 4px 20px rgba(99,102,241,0.35);
        }
        .btn-generate:hover { transform: translateY(-1px); box-shadow: 0 6px 25px rgba(99,102,241,0.45); }

        /* ── Key highlight ── */
        .key-highlight {
            margin-top: 14px;
            background: linear-gradient(135deg, rgba(99,102,241,0.12), rgba(139,92,246,0.08));
            border: 1px solid rgba(99,102,241,0.25);
            border-radius: 12px; padding: 14px 16px;
        }
        .key-highlight-label { font-size: 10px; color: var(--muted); font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 6px; }
        .key-mono {
            font-family: 'JetBrains Mono', monospace;
            font-size: 14px; font-weight: 600; color: var(--accent);
            letter-spacing: 2px; cursor: pointer;
            transition: opacity 0.2s;
        }
        .key-mono:hover { opacity: 0.7; }

        /* ── Donut Chart ── */
        .chart-wrap { display: flex; align-items: center; gap: 20px; }
        .chart-canvas { max-width: 140px; max-height: 140px; }
        .chart-legend { display: flex; flex-direction: column; gap: 8px; flex: 1; }
        .legend-item { display: flex; align-items: center; gap: 9px; font-size: 12.5px; }
        .legend-dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }
        .legend-val { font-weight: 700; font-family: 'JetBrains Mono', monospace; margin-left: auto; font-size: 13px; }

        /* ── Expiry Warning ── */
        .expiry-alert {
            display: flex; align-items: center; gap: 12px;
            padding: 13px 16px; border-radius: 11px; margin-bottom: 10px;
            font-size: 13px;
        }
        .expiry-alert.warn { background: rgba(245,158,11,0.1); border: 1px solid rgba(245,158,11,0.2); color: #fbbf24; }
        .expiry-alert.danger { background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.2); color: #f87171; }

        /* ── Recent Licenses ── */
        .recent-item {
            display: flex; align-items: center; gap: 12px;
            padding: 11px 0; border-bottom: 1px solid var(--border);
        }
        .recent-item:last-child { border-bottom: none; padding-bottom: 0; }
        .recent-avatar {
            width: 36px; height: 36px; border-radius: 10px;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            display: flex; align-items: center; justify-content: center;
            font-size: 13px; font-weight: 700; color: white; flex-shrink: 0;
        }
        .recent-info { flex: 1; min-width: 0; }
        .recent-name { font-size: 13px; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .recent-key  { font-size: 11px; color: var(--muted); font-family: 'JetBrains Mono', monospace; }
        .recent-meta { text-align: right; flex-shrink: 0; }
        .recent-date { font-size: 11px; color: var(--muted); }

        /* ── Badges ── */
        .badge {
            display: inline-flex; align-items: center; gap: 4px;
            padding: 3px 9px; border-radius: 20px;
            font-size: 10.5px; font-weight: 700; white-space: nowrap;
        }
        .badge-active   { background: rgba(16,185,129,0.12); color: #10b981; }
        .badge-inactive { background: rgba(100,116,139,0.12); color: var(--muted); }
        .badge-locked   { background: rgba(239,68,68,0.12);   color: #ef4444; }
        .badge-expired  { background: rgba(245,158,11,0.12);  color: #f59e0b; }
        .badge-7D  { background: rgba(59,130,246,0.12);  color: #60a5fa; }
        .badge-15D { background: rgba(99,102,241,0.12);  color: #818cf8; }
        .badge-1M  { background: rgba(6,182,212,0.12);   color: #22d3ee; }
        .badge-6M  { background: rgba(16,185,129,0.12);  color: #34d399; }
        .badge-1Y  { background: rgba(245,158,11,0.12);  color: #fbbf24; }

        /* ── API Quick Ref ── */
        .api-row {
            display: flex; align-items: center; gap: 12px;
            padding: 10px 14px; border-radius: 10px;
            background: var(--surface); border: 1px solid var(--border);
            margin-bottom: 8px; font-family: 'JetBrains Mono', monospace; font-size: 12px;
        }
        .api-method { padding: 3px 8px; border-radius: 5px; font-size: 10px; font-weight: 700; flex-shrink: 0; }
        .post { background: rgba(99,102,241,0.15); color: #818cf8; }
        .api-path { color: var(--accent); flex: 1; }
        .api-desc { color: var(--muted); font-size: 11px; font-family: 'Outfit', sans-serif; }

        /* ── Toast ── */
        #toast {
            position: fixed; bottom: 24px; right: 24px;
            background: linear-gradient(135deg, #10b981, #059669);
            color: white; padding: 12px 20px;
            border-radius: 12px; font-size: 13px; font-weight: 600;
            display: none; z-index: 9999;
            box-shadow: 0 8px 30px rgba(16,185,129,0.4);
            animation: slideUp 0.3s ease;
        }
        @keyframes slideUp { from { opacity:0;transform:translateY(10px); } to { opacity:1;transform:translateY(0); } }

        @media (max-width: 1200px) {
            .metrics { grid-template-columns: repeat(3, 1fr); }
            .grid-3  { grid-template-columns: 1fr 1fr; }
        }
        @media (max-width: 900px) {
            .metrics { grid-template-columns: repeat(2, 1fr); }
            .grid-2,.grid-3 { grid-template-columns: 1fr; }
            .sidebar { width: 68px; padding: 22px 10px; }
            .sidebar .logo-text,.sidebar .nav-label,.sidebar .section-title { display: none; }
            .sidebar .nav-link { justify-content: center; padding: 12px; }
            .main { margin-left: 68px; }
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
            <i class="fas fa-th-large"></i><span class="nav-label">Dashboard</span>
        </a>
        <a href="{{ route('license.list') }}" class="nav-link">
            <i class="fas fa-list-ul"></i><span class="nav-label">All Licenses</span>
        </a>
        <div class="section-title">Developer</div>
        <a href="{{ route('api.reference') }}" class="nav-link">
            <i class="fas fa-code"></i><span class="nav-label">API Reference</span>
        </a>
    </nav>
    <div class="sidebar-footer">
        <div class="section-title">System</div>
        <a href="{{ route('settings') }}" class="nav-link">
            <i class="fas fa-cog"></i><span class="nav-label">Settings</span>
        </a>
        <form method="POST" action="{{ route('logout') }}" style="margin-top:4px;">
            @csrf
            <button type="submit" class="nav-link" style="color:#ef4444;">
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
            <span class="page-title-header">License Dashboard</span>
        </div>
        <div class="topbar-right">
            <button class="icon-btn" id="themeToggle" title="Toggle Theme">
                <i class="fas fa-moon" id="themeIcon"></i>
            </button>
            <div style="width:34px;height:34px;border-radius:50%;background:linear-gradient(135deg,#6366f1,#8b5cf6);display:flex;align-items:center;justify-content:center;font-size:13px;color:white;box-shadow:0 3px 12px rgba(99,102,241,0.4);">
                <i class="fas fa-user-shield"></i>
            </div>
        </div>
    </header>

    <main class="content">

        <!-- Page Hero -->
        <div class="page-hero">
            <div>
                <h1>License Manager</h1>
                <p>Generate, monitor and control all ArchEng Pro subscription keys</p>
            </div>
            <div class="hero-badge">
                <div class="dot"></div>
                System Operational
            </div>
        </div>

        <!-- Flash Messages -->
        @if (session('success'))
        <div class="flash flash-success">
            <i class="fas fa-check-circle"></i>
            {!! session('success') !!}
        </div>
        @endif
        @if (session('error'))
        <div class="flash flash-error">
            <i class="fas fa-exclamation-circle"></i>
            {{ session('error') }}
        </div>
        @endif

        <!-- Metric Cards -->
        <div class="metrics">
            <div class="metric blue">
                <div class="metric-top">
                    <div class="metric-icon"><i class="fas fa-key"></i></div>
                    <span class="metric-trend trend-up"><i class="fas fa-arrow-up"></i> Total</span>
                </div>
                <div class="metric-val">{{ $totalLicenses }}</div>
                <div class="metric-label">Keys Generated</div>
                <div class="metric-bar"><div class="metric-bar-fill" style="width:100%"></div></div>
            </div>

            <div class="metric green">
                <div class="metric-top">
                    <div class="metric-icon"><i class="fas fa-circle-check"></i></div>
                    <span class="metric-trend trend-up"><i class="fas fa-check"></i> Active</span>
                </div>
                <div class="metric-val">{{ $activeLicenses }}</div>
                <div class="metric-label">Active Subscriptions</div>
                <div class="metric-bar"><div class="metric-bar-fill" style="width:{{ $totalLicenses ? round($activeLicenses/$totalLicenses*100) : 0 }}%"></div></div>
            </div>

            <div class="metric red">
                <div class="metric-top">
                    <div class="metric-icon"><i class="fas fa-lock"></i></div>
                    <span class="metric-trend trend-down"><i class="fas fa-ban"></i> Locked</span>
                </div>
                <div class="metric-val">{{ $lockedLicenses }}</div>
                <div class="metric-label">Locked Machines</div>
                <div class="metric-bar"><div class="metric-bar-fill" style="width:{{ $totalLicenses ? round($lockedLicenses/$totalLicenses*100) : 0 }}%"></div></div>
            </div>

            <div class="metric yellow">
                <div class="metric-top">
                    <div class="metric-icon"><i class="fas fa-triangle-exclamation"></i></div>
                    <span class="metric-trend trend-warn"><i class="fas fa-clock"></i> Soon</span>
                </div>
                <div class="metric-val">{{ $expiringSoon }}</div>
                <div class="metric-label">Expiring in 7 Days</div>
                <div class="metric-bar"><div class="metric-bar-fill" style="width:{{ $totalLicenses ? round($expiringSoon/$totalLicenses*100) : 0 }}%"></div></div>
            </div>

            <div class="metric purple">
                <div class="metric-top">
                    <div class="metric-icon"><i class="fas fa-xmark-circle"></i></div>
                    <span class="metric-trend trend-down"><i class="fas fa-times"></i> Expired</span>
                </div>
                <div class="metric-val">{{ $expiredLicenses }}</div>
                <div class="metric-label">Expired Keys</div>
                <div class="metric-bar"><div class="metric-bar-fill" style="width:{{ $totalLicenses ? round($expiredLicenses/$totalLicenses*100) : 0 }}%"></div></div>
            </div>
        </div>

        <!-- Expiry Warnings -->
        @if ($expiringSoon > 0)
        <div class="expiry-alert warn">
            <i class="fas fa-triangle-exclamation fa-lg"></i>
            <div>
                <strong>{{ $expiringSoon }} license{{ $expiringSoon > 1 ? 's' : '' }}</strong> expiring within 7 days.
                <a href="{{ route('license.list') }}" style="color:inherit;font-weight:700;margin-left:8px;">View & Renew →</a>
            </div>
        </div>
        @endif
        @if ($expiredLicenses > 0)
        <div class="expiry-alert danger" style="margin-bottom:20px;">
            <i class="fas fa-circle-xmark fa-lg"></i>
            <div>
                <strong>{{ $expiredLicenses }} license{{ $expiredLicenses > 1 ? 's' : '' }}</strong> already expired and may be blocking customers.
                <a href="{{ route('license.list') }}" style="color:inherit;font-weight:700;margin-left:8px;">Manage Now →</a>
            </div>
        </div>
        @endif

        <!-- Row 1: Generate Form + Tier Chart + Recent -->
        <div class="grid-3">

            <!-- Generate Key Form -->
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
                            <i class="fas fa-building" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--muted);font-size:12px;"></i>
                            <input type="text" name="customer_name" class="form-input"
                                placeholder="e.g. Al Habtoor Engineering"
                                style="padding-left:34px;" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Subscription Duration</label>
                        <div class="tier-grid">
                            <button type="button" class="tier-btn" data-tier="7D" onclick="selectTier(this)">
                                <span class="tier-name">7D</span>
                                <span class="tier-sub">Trial</span>
                            </button>
                            <button type="button" class="tier-btn" data-tier="15D" onclick="selectTier(this)">
                                <span class="tier-name">15D</span>
                                <span class="tier-sub">Short</span>
                            </button>
                            <button type="button" class="tier-btn" data-tier="1M" onclick="selectTier(this)">
                                <span class="tier-name">1M</span>
                                <span class="tier-sub">Monthly</span>
                            </button>
                            <button type="button" class="tier-btn" data-tier="6M" onclick="selectTier(this)">
                                <span class="tier-name">6M</span>
                                <span class="tier-sub">Semi</span>
                            </button>
                            <button type="button" class="tier-btn selected" data-tier="1Y" onclick="selectTier(this)" style="grid-column:span 2;">
                                <span class="tier-name">1 Year ★</span>
                                <span class="tier-sub">Annual — Best Value</span>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn-generate">
                        <i class="fas fa-key"></i> Generate License Key
                    </button>
                </form>

                @if (session('success') && str_contains(session('success'), 'AEPRO-'))
                    @php preg_match('/AEPRO-[A-Z0-9]+-[A-Z0-9]+-[A-Z0-9]+/', session('success'), $m); @endphp
                    @if (!empty($m))
                    <div class="key-highlight">
                        <div class="key-highlight-label"><i class="fas fa-copy" style="margin-right:4px;"></i> Click to copy</div>
                        <div class="key-mono" onclick="copyKey('{{ $m[0] }}')">{{ $m[0] }}</div>
                    </div>
                    @endif
                @endif
            </div>

            <!-- Tier Distribution Donut -->
            <div class="card">
                <div class="card-header">
                    <div class="card-title"><i class="fas fa-chart-pie"></i> Tier Distribution</div>
                    <span class="card-sub">{{ $totalLicenses }} total keys</span>
                </div>
                @if ($totalLicenses > 0)
                <div class="chart-wrap">
                    <canvas id="tierChart" class="chart-canvas" width="140" height="140"></canvas>
                    <div class="chart-legend">
                        @foreach ([
                            '7D'  => ['#60a5fa', 'Trial (7D)'],
                            '15D' => ['#818cf8', 'Short (15D)'],
                            '1M'  => ['#22d3ee', 'Monthly (1M)'],
                            '6M'  => ['#34d399', 'Semi (6M)'],
                            '1Y'  => ['#fbbf24', 'Annual (1Y)'],
                        ] as $tier => [$color, $label])
                            @if (($tierBreakdown[$tier] ?? 0) > 0)
                            <div class="legend-item">
                                <div class="legend-dot" style="background:{{ $color }};"></div>
                                <span style="color:var(--muted);font-size:12px;">{{ $label }}</span>
                                <span class="legend-val" style="color:{{ $color }};">{{ $tierBreakdown[$tier] ?? 0 }}</span>
                            </div>
                            @endif
                        @endforeach
                    </div>
                </div>
                @else
                <div style="text-align:center;padding:40px 0;color:var(--muted);">
                    <i class="fas fa-chart-pie fa-2x" style="opacity:0.3;margin-bottom:10px;display:block;"></i>
                    No licenses yet
                </div>
                @endif
            </div>

            <!-- Recent Licenses -->
            <div class="card">
                <div class="card-header">
                    <div class="card-title"><i class="fas fa-clock-rotate-left"></i> Recently Generated</div>
                    <a href="{{ route('license.list') }}" style="font-size:11px;color:var(--accent);text-decoration:none;font-weight:600;">View all →</a>
                </div>
                @forelse ($recentLicenses as $lic)
                <div class="recent-item">
                    <div class="recent-avatar">{{ strtoupper(substr($lic->customer_name ?? 'U', 0, 1)) }}</div>
                    <div class="recent-info">
                        <div class="recent-name">{{ $lic->customer_name ?? 'Unknown' }}</div>
                        <div class="recent-key">{{ $lic->license_key }}</div>
                    </div>
                    <div class="recent-meta">
                        <span class="badge badge-{{ $lic->tier }}">{{ $lic->tier }}</span>
                        <div class="recent-date" style="margin-top:4px;">{{ $lic->created_at->format('d/m/Y') }}</div>
                    </div>
                </div>
                @empty
                <div style="text-align:center;padding:30px 0;color:var(--muted);font-size:13px;">
                    <i class="fas fa-key fa-2x" style="opacity:0.2;margin-bottom:8px;display:block;"></i>
                    No licenses generated yet
                </div>
                @endforelse
            </div>
        </div>

        <!-- API Quick Reference -->
        <div class="card">
            <div class="card-header">
                <div class="card-title"><i class="fas fa-code"></i> API Quick Reference</div>
                <a href="{{ route('api.reference') }}" style="font-size:11px;color:var(--accent);text-decoration:none;font-weight:600;">Full Docs →</a>
            </div>
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:10px;">
                <div class="api-row">
                    <span class="api-method post">POST</span>
                    <span class="api-path">/api/license/activate</span>
                    <span class="api-desc">First-time machine activation</span>
                </div>
                <div class="api-row">
                    <span class="api-method post">POST</span>
                    <span class="api-path">/api/license/verify</span>
                    <span class="api-desc">Verify on startup</span>
                </div>
                <div class="api-row">
                    <span class="api-method post">POST</span>
                    <span class="api-path">/api/license/pulse</span>
                    <span class="api-desc">Heartbeat every 5 min</span>
                </div>
            </div>
            <div style="margin-top:12px;padding:11px 14px;background:rgba(245,158,11,0.07);border:1px solid rgba(245,158,11,0.15);border-radius:9px;font-size:12px;color:#fbbf24;">
                <i class="fas fa-triangle-exclamation" style="margin-right:6px;"></i>
                All API calls require <strong>Accept: application/json</strong> header. Responses include <code>status</code> (valid / locked / expired / invalid).
            </div>
        </div>

    </main>
</div>

<!-- Toast -->
<div id="toast"><i class="fas fa-copy" style="margin-right:8px;"></i> Copied to clipboard!</div>

<script>
    // Sidebar toggle
    const sidebar = document.getElementById('sidebar');
    const mainEl  = document.getElementById('main');
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

    // Copy key
    function copyKey(key) {
        navigator.clipboard.writeText(key).then(() => {
            const t = document.getElementById('toast');
            t.style.display = 'block';
            setTimeout(() => t.style.display = 'none', 2000);
        });
    }

    // Auto-dismiss flash
    setTimeout(() => {
        document.querySelectorAll('.flash').forEach(f => {
            f.style.transition = 'opacity 0.5s';
            f.style.opacity = '0';
        });
    }, 5000);

    // Tier Donut Chart
    @if ($totalLicenses > 0)
    const tierData = {
        labels: ['7D', '15D', '1M', '6M', '1Y'],
        datasets: [{
            data: [
                {{ $tierBreakdown['7D']  ?? 0 }},
                {{ $tierBreakdown['15D'] ?? 0 }},
                {{ $tierBreakdown['1M']  ?? 0 }},
                {{ $tierBreakdown['6M']  ?? 0 }},
                {{ $tierBreakdown['1Y']  ?? 0 }},
            ],
            backgroundColor: ['#60a5fa','#818cf8','#22d3ee','#34d399','#fbbf24'],
            borderColor: 'transparent',
            borderWidth: 0,
            hoverOffset: 6,
        }]
    };
    new Chart(document.getElementById('tierChart'), {
        type: 'doughnut',
        data: tierData,
        options: {
            cutout: '68%',
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => ` ${ctx.label}: ${ctx.raw} key${ctx.raw !== 1 ? 's' : ''}`
                    }
                }
            }
        }
    });
    @endif
</script>
</body>
</html>
