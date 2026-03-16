<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ArchEng Pro | All Licenses</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root[data-theme="dark"] {
            --bg: #0f1117; --surface: #161b27; --card: #1c2236;
            --border: rgba(255,255,255,0.07); --text: #e2e8f0; --muted: #64748b;
            --accent: #6366f1; --accent-glow: rgba(99,102,241,0.25);
            --green: #10b981; --red: #ef4444; --yellow: #f59e0b; --blue: #3b82f6;
        }
        :root[data-theme="light"] {
            --bg: #f1f5f9; --surface: #ffffff; --card: #f8fafc;
            --border: rgba(0,0,0,0.08); --text: #0f172a; --muted: #94a3b8;
            --accent: #6366f1; --accent-glow: rgba(99,102,241,0.15);
            --green: #10b981; --red: #ef4444; --yellow: #f59e0b; --blue: #3b82f6;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Outfit', sans-serif; background: var(--bg); color: var(--text); min-height: 100vh; display: flex; }

        .sidebar { width: 260px; background: var(--surface); border-right: 1px solid var(--border); display: flex; flex-direction: column; padding: 24px 16px; position: fixed; top: 0; left: 0; bottom: 0; z-index: 100; transition: all 0.3s ease; }
        .sidebar.collapsed { width: 72px; padding: 24px 10px; }
        .sidebar.collapsed .logo-text, .sidebar.collapsed .nav-label, .sidebar.collapsed .section-title { display: none; }
        .sidebar.collapsed .nav-link { justify-content: center; padding: 12px; }
        .logo { display: flex; align-items: center; gap: 12px; margin-bottom: 36px; padding: 0 8px; }
        .logo-icon { width: 40px; height: 40px; background: linear-gradient(135deg, #6366f1, #8b5cf6); border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 18px; color: white; flex-shrink: 0; }
        .logo-text { font-size: 16px; font-weight: 700; }
        .logo-sub  { font-size: 10px; color: var(--muted); font-weight: 400; margin-top: 2px; }
        .section-title { font-size: 10px; font-weight: 700; color: var(--muted); text-transform: uppercase; letter-spacing: 1px; padding: 0 8px; margin: 20px 0 6px; }
        .nav-link { display: flex; align-items: center; gap: 12px; padding: 11px 12px; border-radius: 10px; color: var(--muted); text-decoration: none; font-size: 14px; font-weight: 500; transition: all 0.2s; }
        .nav-link:hover { background: var(--accent-glow); color: var(--accent); }
        .nav-link.active { background: var(--accent-glow); color: var(--accent); }
        .nav-link i { width: 18px; text-align: center; flex-shrink: 0; }
        .sidebar-footer { margin-top: auto; }

        .main { margin-left: 260px; flex: 1; display: flex; flex-direction: column; min-height: 100vh; transition: margin-left 0.3s ease; }
        .main.expanded { margin-left: 72px; }

        .topbar { height: 64px; background: var(--surface); border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; padding: 0 28px; position: sticky; top: 0; z-index: 50; }
        .topbar-left { display: flex; align-items: center; gap: 16px; }
        .topbar-right { display: flex; align-items: center; gap: 12px; }
        .icon-btn { width: 38px; height: 38px; border-radius: 10px; background: var(--card); border: 1px solid var(--border); display: flex; align-items: center; justify-content: center; cursor: pointer; color: var(--muted); transition: all 0.2s; font-size: 14px; }
        .icon-btn:hover { color: var(--accent); border-color: var(--accent); }
        .page-title-header { font-size: 16px; font-weight: 600; }

        .content { padding: 28px; flex: 1; }
        .page-header { margin-bottom: 24px; display: flex; align-items: center; justify-content: space-between; }
        .page-header h1 { font-size: 22px; font-weight: 700; }
        .page-header p { color: var(--muted); font-size: 13px; margin-top: 4px; }

        .search-bar { display: flex; align-items: center; gap: 10px; background: var(--card); border: 1px solid var(--border); border-radius: 10px; padding: 9px 14px; }
        .search-bar i { color: var(--muted); font-size: 13px; }
        .search-bar input { background: none; border: none; outline: none; color: var(--text); font-size: 14px; font-family: 'Outfit', sans-serif; width: 220px; }

        .card { background: var(--card); border: 1px solid var(--border); border-radius: 16px; overflow: hidden; }
        .table-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        thead th { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: var(--muted); padding: 12px 16px; border-bottom: 1px solid var(--border); text-align: left; white-space: nowrap; }
        tbody tr { transition: background 0.15s; }
        tbody tr:hover { background: rgba(255,255,255,0.02); }
        tbody td { padding: 14px 16px; font-size: 13px; border-bottom: 1px solid var(--border); vertical-align: middle; }
        tbody tr:last-child td { border-bottom: none; }

        .key-mono { font-family: 'JetBrains Mono', monospace; font-size: 12px; font-weight: 600; color: var(--accent); background: var(--accent-glow); padding: 4px 10px; border-radius: 6px; letter-spacing: 1px; cursor: pointer; transition: opacity 0.2s; display: inline-block; }
        .key-mono:hover { opacity: 0.7; }

        .badge { display: inline-flex; align-items: center; gap: 5px; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; }
        .badge-active   { background: rgba(16,185,129,0.12); color: #10b981; }
        .badge-inactive { background: rgba(100,116,139,0.12); color: var(--muted); }
        .badge-locked   { background: rgba(239,68,68,0.12);   color: #ef4444; }
        .badge-expired  { background: rgba(245,158,11,0.12);  color: #f59e0b; }
        .badge-7D  { background: rgba(59,130,246,0.12);  color: #60a5fa; }
        .badge-15D { background: rgba(99,102,241,0.12);  color: #818cf8; }
        .badge-6M  { background: rgba(16,185,129,0.12);  color: #34d399; }
        .badge-1Y  { background: rgba(245,158,11,0.12);  color: #fbbf24; }

        .expiry-warn { color: #f87171; font-size: 12px; }
        .expiry-ok   { color: var(--muted); font-size: 12px; }

        .empty { text-align: center; padding: 60px 0; color: var(--muted); }
        .empty i { font-size: 40px; opacity: 0.3; margin-bottom: 14px; display: block; }

        .pagination { display: flex; align-items: center; justify-content: center; gap: 8px; padding: 20px; }
        .pagination a, .pagination span { padding: 7px 13px; border-radius: 8px; font-size: 13px; font-weight: 600; text-decoration: none; border: 1px solid var(--border); background: var(--surface); color: var(--muted); transition: all 0.2s; }
        .pagination a:hover { border-color: var(--accent); color: var(--accent); }
        .pagination .active-page { background: var(--accent-glow); border-color: var(--accent); color: var(--accent); }

        @media (max-width: 900px) {
            .sidebar { width: 72px; padding: 24px 10px; }
            .sidebar .logo-text, .sidebar .nav-label, .sidebar .section-title { display: none; }
            .sidebar .nav-link { justify-content: center; padding: 12px; }
            .main { margin-left: 72px; }
        }
    </style>
</head>
<body>

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
        <a href="{{ route('dashboard') }}" class="nav-link">
            <i class="fas fa-th-large"></i><span class="nav-label">Dashboard</span>
        </a>
        <a href="{{ route('license.list') }}" class="nav-link active">
            <i class="fas fa-list-ul"></i><span class="nav-label">All Licenses</span>
        </a>
        <div class="section-title">Developer</div>
        <a href="{{ route('api.reference') }}" class="nav-link">
            <i class="fas fa-code"></i><span class="nav-label">API Reference</span>
        </a>
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
            <span class="page-title-header">All Licenses</span>
        </div>
        <div class="topbar-right">
            <div class="search-bar">
                <i class="fas fa-search"></i>
                <input type="text" id="searchInput" placeholder="Search key, machine, tier…">
            </div>
            <button class="icon-btn" id="themeToggle" title="Toggle Theme">
                <i class="fas fa-moon" id="themeIcon"></i>
            </button>
        </div>
    </header>

    <main class="content">
        <div class="page-header">
            <div>
                <h1>License Keys</h1>
                <p>{{ $licenses->total() }} keys total — manage activations and subscription tiers</p>
            </div>
            <a href="{{ route('dashboard') }}" style="display:inline-flex;align-items:center;gap:8px;padding:10px 20px;background:linear-gradient(135deg,#6366f1,#8b5cf6);color:white;border-radius:10px;text-decoration:none;font-size:13px;font-weight:700;">
                <i class="fas fa-plus"></i> Generate New Key
            </a>
        </div>

        @if (session('success'))
            <div style="padding:14px 20px;border-radius:12px;margin-bottom:20px;display:flex;align-items:center;gap:12px;font-size:14px;font-weight:500;background:rgba(16,185,129,0.12);color:#10b981;border:1px solid rgba(16,185,129,0.25);">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif

        <div class="card">
            @if ($licenses->isEmpty())
                <div class="empty">
                    <i class="fas fa-key"></i>
                    <p>No license keys generated yet.</p>
                </div>
            @else
                <div class="table-wrap">
                    <table id="licenseTable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Customer</th>
                                <th>License Key</th>
                                <th>Tier</th>
                                <th>Status</th>
                                <th>Machine ID</th>
                                <th>Expires</th>
                                <th>Days Left</th>
                                <th>Activations</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($licenses as $lic)
                                @php
                                    $daysLeft = $lic->expires_at ? now()->diffInDays($lic->expires_at, false) : null;
                                    $isExpired = $lic->expires_at && now()->isAfter($lic->expires_at);
                                @endphp
                                <tr>
                                    <td style="color:var(--muted);font-size:12px;">{{ $lic->id }}</td>
                                    <td>
                                        <div style="display:flex;align-items:center;gap:10px;">
                                            <div style="width:32px;height:32px;border-radius:8px;background:linear-gradient(135deg,#6366f1,#8b5cf6);display:flex;align-items:center;justify-content:center;font-size:13px;color:white;font-weight:700;flex-shrink:0;">
                                                {{ strtoupper(substr($lic->customer_name ?? '?', 0, 1)) }}
                                            </div>
                                            <span style="font-size:13px;font-weight:600;color:var(--text);">
                                                {{ $lic->customer_name ?? '—' }}
                                            </span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="key-mono" onclick="copyKey('{{ $lic->license_key }}')" title="Click to copy">
                                            {{ $lic->license_key }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $lic->tier }}">{{ $lic->tier }}</span>
                                    </td>
                                    <td>
                                        @if ($isExpired)
                                            <span class="badge badge-expired"><i class="fas fa-clock" style="font-size:8px;"></i> Expired</span>
                                        @elseif ($lic->is_active)
                                            <span class="badge badge-active"><i class="fas fa-circle" style="font-size:6px;"></i> Active</span>
                                        @else
                                            <span class="badge badge-inactive"><i class="fas fa-circle" style="font-size:6px;"></i> Unused</span>
                                        @endif
                                    </td>
                                    <td style="font-family:'JetBrains Mono',monospace;font-size:11px;color:var(--muted);">
                                        {{ $lic->machine_id ?? '—' }}
                                    </td>
                                    <td>
                                        @if ($lic->expires_at)
                                            <span class="{{ $isExpired ? 'expiry-warn' : 'expiry-ok' }}">
                                                {{ $lic->expires_at->format('d M Y') }}
                                            </span>
                                        @else
                                            <span style="color:var(--muted);">Not activated</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($daysLeft !== null)
                                            <span class="{{ $daysLeft < 15 ? 'expiry-warn' : 'expiry-ok' }}" style="font-family:'JetBrains Mono',monospace;font-weight:700;">
                                                {{ $isExpired ? '—' : $daysLeft . 'd' }}
                                            </span>
                                        @else
                                            <span style="color:var(--muted);">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span style="font-family:'JetBrains Mono',monospace;font-size:12px;color:var(--accent);">
                                            {{ $lic->activations_count }}
                                        </span>
                                    </td>
                                    <td style="color:var(--muted);font-size:12px;">
                                        {{ $lic->created_at->format('d M Y') }}
                                    </td>
                                    <td>
                                        <div style="display:flex;align-items:center;gap:6px;">
                                            {{-- Lock / Unlock (only if activated) --}}
                                            @php $act = $lic->activations->first(); @endphp
                                            @if ($act)
                                                <form action="{{ route('license.toggle-lock', $act->id) }}" method="POST" style="margin:0;">
                                                    @csrf
                                                    <button type="submit"
                                                        title="{{ $act->status === 'locked' ? 'Unlock Machine' : 'Lock Machine' }}"
                                                        style="width:30px;height:30px;border-radius:8px;border:none;cursor:pointer;font-size:12px;display:flex;align-items:center;justify-content:center;transition:all 0.2s;
                                                        {{ $act->status === 'locked'
                                                            ? 'background:rgba(16,185,129,0.12);color:#34d399;'
                                                            : 'background:rgba(245,158,11,0.12);color:#fbbf24;' }}"
                                                        onmouseover="this.style.opacity='0.7'"
                                                        onmouseout="this.style.opacity='1'">
                                                        <i class="fas {{ $act->status === 'locked' ? 'fa-unlock' : 'fa-lock' }}"></i>
                                                    </button>
                                                </form>
                                            @else
                                                <span style="width:30px;height:30px;display:inline-flex;align-items:center;justify-content:center;color:var(--muted);font-size:11px;" title="Not activated yet">
                                                    <i class="fas fa-minus"></i>
                                                </span>
                                            @endif
                                            {{-- Regenerate --}}
                                            <form action="{{ route('license.regenerate', $lic->id) }}" method="POST"
                                                onsubmit="return confirm('Regenerate key for this license? The machine will need to re-activate with the new key.')">
                                                @csrf
                                                <button type="submit" title="Regenerate Key"
                                                    style="width:30px;height:30px;border-radius:8px;border:none;background:rgba(99,102,241,0.12);color:#818cf8;cursor:pointer;font-size:12px;display:flex;align-items:center;justify-content:center;transition:all 0.2s;"
                                                    onmouseover="this.style.background='rgba(99,102,241,0.25)'"
                                                    onmouseout="this.style.background='rgba(99,102,241,0.12)'">
                                                    <i class="fas fa-rotate-right"></i>
                                                </button>
                                            </form>
                                            {{-- Delete --}}
                                            <form action="{{ route('license.destroy', $lic->id) }}" method="POST"
                                                onsubmit="return confirm('Delete this license key permanently? This cannot be undone.')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" title="Delete License"
                                                    style="width:30px;height:30px;border-radius:8px;border:none;background:rgba(239,68,68,0.1);color:#f87171;cursor:pointer;font-size:12px;display:flex;align-items:center;justify-content:center;transition:all 0.2s;"
                                                    onmouseover="this.style.background='rgba(239,68,68,0.25)'"
                                                    onmouseout="this.style.background='rgba(239,68,68,0.1)'">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if ($licenses->hasPages())
                    <div class="pagination">
                        @if ($licenses->onFirstPage())
                            <span>&laquo;</span>
                        @else
                            <a href="{{ $licenses->previousPageUrl() }}">&laquo;</a>
                        @endif

                        @foreach ($licenses->getUrlRange(1, $licenses->lastPage()) as $page => $url)
                            @if ($page == $licenses->currentPage())
                                <span class="active-page">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}">{{ $page }}</a>
                            @endif
                        @endforeach

                        @if ($licenses->hasMorePages())
                            <a href="{{ $licenses->nextPageUrl() }}">&raquo;</a>
                        @else
                            <span>&raquo;</span>
                        @endif
                    </div>
                @endif
            @endif
        </div>
    </main>
</div>

<div id="toast" style="position:fixed;bottom:28px;right:28px;background:#10b981;color:white;padding:12px 20px;border-radius:10px;font-size:13px;font-weight:600;display:none;z-index:9999;box-shadow:0 4px 20px rgba(0,0,0,0.3);">
    <i class="fas fa-copy" style="margin-right:8px;"></i> Copied to clipboard!
</div>

<script>
    const sidebar = document.getElementById('sidebar');
    const mainEl  = document.getElementById('main');
    document.getElementById('toggleSidebar').addEventListener('click', () => {
        sidebar.classList.toggle('collapsed');
        mainEl.classList.toggle('expanded');
        localStorage.setItem('sb', sidebar.classList.contains('collapsed'));
    });
    if (localStorage.getItem('sb') === 'true') { sidebar.classList.add('collapsed'); mainEl.classList.add('expanded'); }

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

    function copyKey(key) {
        navigator.clipboard.writeText(key).then(() => {
            const t = document.getElementById('toast');
            t.style.display = 'block';
            setTimeout(() => t.style.display = 'none', 2000);
        });
    }

    // Client-side search filter
    document.getElementById('searchInput').addEventListener('input', function () {
        const q = this.value.toLowerCase();
        document.querySelectorAll('#licenseTable tbody tr').forEach(row => {
            row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
        });
    });
</script>
</body>
</html>
