<!DOCTYPE html>
<html lang="en-GB" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ACLM | Audit Trail</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <style>
        .sidebar { transition: all 0.3s cubic-bezier(0.4,0,0.2,1); overflow:hidden; white-space:nowrap; }
        .sidebar.collapsed { width: 90px; padding: 24px 15px; }
        .sidebar.collapsed .logo-text, .sidebar.collapsed .nav-link span,
        .sidebar.collapsed .user-info-text, .sidebar.collapsed .sidebar-footer button span { display: none; }
        .sidebar.collapsed .logo-container { padding:0; justify-content:center; }
        .sidebar.collapsed .nav-link { justify-content:center; padding:12px; }
        .sidebar.collapsed .user-profile-sidebar { justify-content:center; padding:10px; }
        .main-wrapper { transition: all 0.3s cubic-bezier(0.4,0,0.2,1); }

        .audit-table-wrapper {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            overflow: hidden;
            margin-top: 24px;
        }
        .audit-filters {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            align-items: flex-end;
            padding: 20px 24px;
            border-bottom: 1px solid var(--border-color);
            background: var(--card-bg);
        }
        .audit-filter-group { display: flex; flex-direction: column; gap: 5px; }
        .audit-filter-group label {
            font-size: 11px; font-weight: 700; text-transform: uppercase;
            letter-spacing: 0.5px; color: var(--text-secondary);
        }
        .audit-filter-group input,
        .audit-filter-group select {
            padding: 8px 12px;
            background: var(--bg-color);
            border: 1px solid var(--border-color);
            border-radius: 10px;
            color: var(--text-primary);
            font-size: 13px;
            font-family: 'Outfit', sans-serif;
            min-width: 160px;
        }
        .audit-filter-group input:focus,
        .audit-filter-group select:focus { outline: none; border-color: var(--accent-color); }

        .audit-table { width: 100%; border-collapse: collapse; }
        .audit-table th {
            padding: 14px 20px;
            text-align: left;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            color: var(--text-secondary);
            border-bottom: 1px solid var(--border-color);
            background: var(--card-bg);
        }
        .audit-table td {
            padding: 14px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.04);
            font-size: 13px;
            color: var(--text-primary);
            vertical-align: middle;
        }
        .audit-table tr:last-child td { border-bottom: none; }
        .audit-table tr:hover td { background: rgba(255,255,255,0.02); }

        .action-badge {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 4px 10px; border-radius: 8px;
            font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.4px;
            white-space: nowrap;
        }
        .badge-license_assigned    { background: rgba(16,185,129,0.12); color: #10b981; }
        .badge-license_removed     { background: rgba(239,68,68,0.12);  color: #ef4444; }
        .badge-software_suspended  { background: rgba(245,158,11,0.12); color: #f59e0b; }
        .badge-software_restored   { background: rgba(99,102,241,0.12); color: #6366f1; }
        .badge-software_permanent_removal { background: rgba(220,38,38,0.15); color: #dc2626; }
        .badge-user_created        { background: rgba(59,130,246,0.12); color: #3b82f6; }
        .badge-user_updated        { background: rgba(107,114,128,0.12); color: #9ca3af; }
        .badge-user_deleted        { background: rgba(239,68,68,0.15); color: #ef4444; }

        .empty-state {
            padding: 64px 20px; text-align: center; color: var(--text-secondary);
        }
        .empty-state i { font-size: 48px; opacity: 0.3; margin-bottom: 16px; display: block; }
        .empty-state p { font-size: 14px; }

        .pagination-wrap { padding: 16px 20px; display: flex; justify-content: flex-end; }
        .pagination-wrap .pagination { display: flex; gap: 6px; list-style: none; padding: 0; margin: 0; }
        .pagination-wrap .page-item .page-link {
            padding: 7px 13px; border: 1px solid var(--border-color);
            border-radius: 8px; font-size: 13px; color: var(--text-secondary);
            text-decoration: none; background: var(--card-bg); transition: all 0.2s;
        }
        .pagination-wrap .page-item.active .page-link,
        .pagination-wrap .page-item .page-link:hover {
            background: var(--accent-color); color: #fff; border-color: var(--accent-color);
        }
        .pagination-wrap .page-item.disabled .page-link { opacity: 0.4; pointer-events: none; }
    </style>
</head>
<body>

    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="logo-container">
            <div class="logo-icon"><i class="fas fa-compass-drafting"></i></div>
            <span class="logo-text">ACLM</span>
        </div>
        <ul class="nav-menu">
            <li class="nav-section-title">Main Monitoring</li>
            <li class="nav-item">
                <a href="{{ route('dashboard') }}" class="nav-link">
                    <i class="fas fa-th-large"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('leaderboard') }}" class="nav-link">
                    <i class="fas fa-trophy"></i>
                    <span>Leaderboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('users') }}" class="nav-link">
                    <i class="fas fa-users"></i>
                    <span>Users</span>
                </a>
            </li>

            <li class="nav-section-title">Analytics & Reports</li>
            <li class="nav-item">
                <a href="{{ route('license.audit') }}" class="nav-link">
                    <i class="fas fa-user-slash"></i>
                    <span>Inactive Users</span>
                </a>
            </li>
            @if (auth()->user()->role !== 'team_leader')
                <li class="nav-item">
                    <a href="{{ route('license.optimization') }}" class="nav-link">
                        <i class="fas fa-lightbulb"></i>
                        <span>License Optimization</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('department.efficiency') }}" class="nav-link">
                        <i class="fas fa-chart-pie"></i>
                        <span>Efficiency Benchmark</span>
                    </a>
                </li>
            @endif
            <li class="nav-item">
                <a href="{{ route('report.hub') }}" class="nav-link">
                    <i class="fas fa-file-pdf"></i>
                    <span>Export Report</span>
                </a>
            </li>

            @if (auth()->user()->role !== 'team_leader')
                <li class="nav-section-title">Infrastructure</li>
                <li class="nav-item">
                    <a href="{{ route('machine.inventory') }}" class="nav-link">
                        <i class="fas fa-desktop"></i>
                        <span>Machine Inventory</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('ghost.machines') }}" class="nav-link">
                        <i class="fas fa-ghost"></i>
                        <span>Ghost Machines</span>
                    </a>
                </li>
            @endif

            <li class="nav-section-title">System</li>
            @if (auth()->check() && auth()->user()->role === 'admin')
                <li class="nav-item">
                    <a href="{{ route('user-management') }}" class="nav-link">
                        <i class="fas fa-user-shield"></i>
                        <span>User Management</span>
                    </a>
                </li>
            @endif
            <li class="nav-item">
                <a href="{{ route('settings') }}" class="nav-link">
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
                </a>
            </li>
            @if (auth()->check() && in_array(auth()->user()->role, ['admin', 'management']))
                <li class="nav-item">
                    <a href="{{ route('audit.trail') }}" class="nav-link active">
                        <i class="fas fa-history"></i>
                        <span>Audit Trail</span>
                    </a>
                </li>
            @endif
        </ul>
        <div class="sidebar-footer">
            <div class="user-profile-sidebar">
                <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&background=3b82f6&color=fff"
                    alt="Avatar" class="user-avatar">
                <div class="user-info-text">
                    <span class="user-name">{{ auth()->user()->name }}</span>
                    <span class="user-role">
                        @if (auth()->user()->role === 'admin') IT Manager
                        @elseif(auth()->user()->role === 'management') Management
                        @else Contract Manager @endif
                    </span>
                </div>
            </div>
            @include('partials.license_sidebar_widget')

            <form action="{{ route('logout') }}" method="POST" style="margin-top: 15px;">
                @csrf
                <button type="submit"
                    style="width:100%;padding:8px;background:rgba(239,68,68,0.1);color:#ef4444;border:1px solid rgba(239,68,68,0.2);border-radius:8px;cursor:pointer;font-size:13px;font-weight:600;display:flex;align-items:center;justify-content:center;gap:8px;">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Log Out</span>
                </button>
            </form>
        </div>
    </aside>

    <!-- Main -->
    <div class="main-wrapper">
        <header class="topbar">
            <div style="display:flex;align-items:center;gap:20px;">
                <button class="action-btn" id="sidebarToggle" title="Toggle Sidebar">
                    <i class="fas fa-bars"></i>
                </button>
                <div>
                    <h2 style="font-size:18px;font-weight:700;margin:0;">Audit Trail</h2>
                    <p style="font-size:12px;color:var(--text-secondary);margin:0;">Full history of all admin actions</p>
                </div>
            </div>
            <div class="topbar-actions">
                <button class="action-btn theme-toggle-btn" id="themeToggle" title="Toggle Theme">
                    <i class="fas fa-moon"></i>
                    <i class="fas fa-sun"></i>
                </button>
            </div>
        </header>

        @include('partials.license_status_banner')

        <main class="content-area">
            <header class="page-header">
                <div class="page-title">
                    <h1>Audit Trail</h1>
                    <p>Every admin action recorded — who did what, and when</p>
                </div>
            </header>

            <!-- Filters -->
            <div class="audit-table-wrapper">
                <form method="GET" action="{{ route('audit.trail') }}" class="audit-filters">
                    <div class="audit-filter-group">
                        <label>Action Type</label>
                        <select name="action">
                            <option value="">All Actions</option>
                            @foreach ($actionTypes as $type)
                                <option value="{{ $type }}" {{ request('action') === $type ? 'selected' : '' }}>
                                    {{ ucwords(str_replace('_', ' ', $type)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="audit-filter-group">
                        <label>Target User</label>
                        <input type="text" name="user" value="{{ request('user') }}" placeholder="Search user…">
                    </div>
                    <div class="audit-filter-group">
                        <label>From Date</label>
                        <input type="date" name="from" value="{{ request('from') }}">
                    </div>
                    <div class="audit-filter-group">
                        <label>To Date</label>
                        <input type="date" name="to" value="{{ request('to') }}">
                    </div>
                    <div class="audit-filter-group" style="justify-content:flex-end;">
                        <label>&nbsp;</label>
                        <button type="submit"
                            style="padding:8px 20px;background:var(--accent-color);color:white;border:none;border-radius:10px;cursor:pointer;font-size:13px;font-weight:600;font-family:'Outfit',sans-serif;">
                            <i class="fas fa-filter" style="margin-right:5px;"></i> Filter
                        </button>
                    </div>
                    @if (request()->hasAny(['action','user','from','to']))
                        <div class="audit-filter-group" style="justify-content:flex-end;">
                            <label>&nbsp;</label>
                            <a href="{{ route('audit.trail') }}"
                                style="padding:8px 16px;background:rgba(239,68,68,0.1);color:#ef4444;border:1px solid rgba(239,68,68,0.2);border-radius:10px;font-size:13px;font-weight:600;text-decoration:none;">
                                <i class="fas fa-times" style="margin-right:4px;"></i> Clear
                            </a>
                        </div>
                    @endif
                </form>

                @if ($logs->isEmpty())
                    <div class="empty-state">
                        <i class="fas fa-history"></i>
                        <p>No audit entries yet. Admin actions will appear here automatically.</p>
                    </div>
                @else
                    <table class="audit-table">
                        <thead>
                            <tr>
                                <th>Date & Time</th>
                                <th>Performed By</th>
                                <th>Action</th>
                                <th>Target User</th>
                                <th>Description</th>
                                <th>IP Address</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($logs as $log)
                                <tr>
                                    <td style="white-space:nowrap; color: var(--text-secondary); font-size:12px;">
                                        <div style="font-weight:600; color: var(--text-primary); font-size:13px;">
                                            {{ $log->created_at->format('d M Y') }}
                                        </div>
                                        {{ $log->created_at->format('H:i:s') }}
                                        <div style="font-size:11px; margin-top:2px; opacity:0.6;">
                                            {{ $log->created_at->diffForHumans() }}
                                        </div>
                                    </td>
                                    <td>
                                        <div style="display:flex;align-items:center;gap:8px;">
                                            <img src="https://ui-avatars.com/api/?name={{ urlencode($log->performed_by) }}&background=3b82f6&color=fff&size=28"
                                                style="width:28px;height:28px;border-radius:50%;" alt="">
                                            <span style="font-weight:600;">{{ $log->performed_by }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="action-badge badge-{{ $log->action }}">
                                            @php
                                                $icons = [
                                                    'license_assigned'           => 'fa-plus-circle',
                                                    'license_removed'            => 'fa-minus-circle',
                                                    'software_suspended'         => 'fa-ban',
                                                    'software_restored'          => 'fa-undo',
                                                    'software_permanent_removal' => 'fa-trash',
                                                    'user_created'               => 'fa-user-plus',
                                                    'user_updated'               => 'fa-user-edit',
                                                    'user_deleted'               => 'fa-user-times',
                                                ];
                                                $icon = $icons[$log->action] ?? 'fa-circle';
                                            @endphp
                                            <i class="fas {{ $icon }}"></i>
                                            {{ ucwords(str_replace('_', ' ', $log->action)) }}
                                        </span>
                                    </td>
                                    <td style="font-weight:600;">
                                        {{ $log->target_user ?? '—' }}
                                    </td>
                                    <td style="color: var(--text-secondary); max-width: 320px;">
                                        {{ $log->description }}
                                    </td>
                                    <td style="font-family:'JetBrains Mono',monospace; font-size:12px; color: var(--text-secondary);">
                                        {{ $log->ip_address ?? '—' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    @if ($logs->hasPages())
                        <div class="pagination-wrap">
                            {{ $logs->links() }}
                        </div>
                    @endif
                @endif
            </div>
        </main>
    </div>

    <script>
        // Theme
        const savedTheme = localStorage.getItem('theme') || 'dark';
        document.documentElement.setAttribute('data-theme', savedTheme);
        document.getElementById('themeToggle').addEventListener('click', () => {
            const t = document.documentElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-theme', t);
            localStorage.setItem('theme', t);
        });

        // Sidebar Toggle
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebar = document.querySelector('.sidebar');
        if (sidebarToggle && sidebar) {
            sidebarToggle.addEventListener('click', () => {
                sidebar.classList.toggle('collapsed');
                localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
            });
            if (localStorage.getItem('sidebarCollapsed') === 'true') sidebar.classList.add('collapsed');
        }
    </script>
</body>
</html>
