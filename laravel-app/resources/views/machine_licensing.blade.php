<!DOCTYPE html>
<html lang="en-GB" data-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ACLM | Machine Licensing Hub</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <style>
        .sidebar { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); overflow: hidden; white-space: nowrap; }
        .sidebar.collapsed { width: 90px; padding: 24px 15px; }
        .sidebar.collapsed .logo-text, .sidebar.collapsed .nav-link span,
        .sidebar.collapsed .user-info-text, .sidebar.collapsed .sidebar-footer button span { display: none; }
        .sidebar.collapsed .logo-container { padding: 0; justify-content: center; }
        .sidebar.collapsed .nav-link { justify-content: center; padding: 12px; }
        .sidebar.collapsed .user-profile-sidebar { justify-content: center; padding: 10px; }
        .main-wrapper { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }

        .status-badge {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: 700;
        }
        .status-active  { background: rgba(16, 185, 129, 0.15); color: #10b981; }
        .status-pending { background: rgba(245, 158, 11, 0.15); color: #f59e0b; }
        .status-revoked { background: rgba(239, 68, 68, 0.15);  color: #ef4444; }

        .machine-table { width: 100%; border-collapse: collapse; }
        .machine-table th {
            text-align: left; font-size: 11px; font-weight: 700;
            text-transform: uppercase; letter-spacing: 0.5px;
            color: var(--text-secondary); padding: 12px 16px;
            border-bottom: 1px solid var(--border-color);
        }
        .machine-table td { padding: 14px 16px; border-bottom: 1px solid var(--border-color); font-size: 14px; vertical-align: middle; }
        .machine-table tr:last-child td { border-bottom: none; }
        .machine-table tr:hover td { background: rgba(255,255,255,0.02); }

        .btn-approve { background: rgba(16,185,129,0.1); color: #10b981; border: 1px solid rgba(16,185,129,0.3); padding: 6px 14px; border-radius: 8px; cursor: pointer; font-size: 12px; font-weight: 600; transition: all 0.2s; }
        .btn-approve:hover { background: rgba(16,185,129,0.25); }
        .btn-revoke  { background: rgba(239,68,68,0.1);  color: #ef4444; border: 1px solid rgba(239,68,68,0.3);  padding: 6px 14px; border-radius: 8px; cursor: pointer; font-size: 12px; font-weight: 600; transition: all 0.2s; }
        .btn-revoke:hover { background: rgba(239,68,68,0.25); }
        .btn-delete  { background: rgba(107,114,128,0.1); color: #9ca3af; border: 1px solid rgba(107,114,128,0.2); padding: 6px 10px; border-radius: 8px; cursor: pointer; font-size: 12px; transition: all 0.2s; }
        .btn-delete:hover { color: #ef4444; border-color: rgba(239,68,68,0.3); }

        .stat-card { text-align: center; padding: 24px; }
        .stat-number { font-size: 36px; font-weight: 800; line-height: 1; margin-bottom: 8px; }
        .stat-label  { font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-secondary); }

        .filter-tabs { display: flex; gap: 8px; margin-bottom: 20px; flex-wrap: wrap; }
        .filter-tab { padding: 7px 18px; border-radius: 20px; border: 1px solid var(--border-color); background: transparent; color: var(--text-secondary); font-size: 13px; font-weight: 600; cursor: pointer; transition: all 0.2s; }
        .filter-tab.active, .filter-tab:hover { background: rgba(99,102,241,0.15); color: #818cf8; border-color: rgba(99,102,241,0.4); }

        .mono { font-family: 'Courier New', monospace; font-size: 12px; color: var(--text-secondary); }
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
            <li class="nav-item"><a href="{{ route('dashboard') }}" class="nav-link"><i class="fas fa-th-large"></i><span>Dashboard</span></a></li>
            <li class="nav-item"><a href="{{ route('leaderboard') }}" class="nav-link"><i class="fas fa-trophy"></i><span>Leaderboard</span></a></li>
            <li class="nav-item"><a href="{{ route('users') }}" class="nav-link"><i class="fas fa-users"></i><span>Users</span></a></li>

            <li class="nav-section-title">Analytics & Reports</li>
            <li class="nav-item"><a href="{{ route('license.audit') }}" class="nav-link"><i class="fas fa-user-slash"></i><span>Inactive Users</span></a></li>
            @if (auth()->user()->role !== 'team_leader')
                <li class="nav-item"><a href="{{ route('license.optimization') }}" class="nav-link"><i class="fas fa-lightbulb"></i><span>License Optimization</span></a></li>
                <li class="nav-item"><a href="{{ route('department.efficiency') }}" class="nav-link"><i class="fas fa-chart-pie"></i><span>Efficiency Benchmark</span></a></li>
            @endif
            <li class="nav-item"><a href="{{ route('report.hub') }}" class="nav-link"><i class="fas fa-file-pdf"></i><span>Export Report</span></a></li>

            @if (auth()->user()->role !== 'team_leader')
                <li class="nav-section-title">Infrastructure</li>
                <li class="nav-item"><a href="{{ route('machine.inventory') }}" class="nav-link"><i class="fas fa-desktop"></i><span>Machine Inventory</span></a></li>
                <li class="nav-item"><a href="{{ route('ghost.machines') }}" class="nav-link"><i class="fas fa-ghost"></i><span>Ghost Machines</span></a></li>
                <li class="nav-item"><a href="{{ route('machine.licensing') }}" class="nav-link active"><i class="fas fa-shield-halved"></i><span>Machine Licensing</span></a></li>
            @endif

            <li class="nav-section-title">System</li>
            @if (auth()->check() && auth()->user()->role === 'admin')
                <li class="nav-item"><a href="{{ route('user-management') }}" class="nav-link"><i class="fas fa-user-shield"></i><span>User Management</span></a></li>
            @endif
            <li class="nav-item"><a href="{{ route('settings') }}" class="nav-link"><i class="fas fa-cog"></i><span>Settings</span></a></li>
            @if (auth()->check() && in_array(auth()->user()->role, ['admin', 'management']))
                <li class="nav-item"><a href="{{ route('audit.trail') }}" class="nav-link"><i class="fas fa-history"></i><span>Audit Trail</span></a></li>
            @endif
        </ul>

        <div class="sidebar-footer">
            <div class="user-profile-sidebar">
                <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&background=3b82f6&color=fff" alt="Avatar" class="user-avatar">
                <div class="user-info-text">
                    <span class="user-name">{{ auth()->user()->name }}</span>
                    <span class="user-role">
                        @if (auth()->user()->role === 'admin') IT Manager
                        @elseif(auth()->user()->role === 'management') Management
                        @else Contract Manager
                        @endif
                    </span>
                </div>
            </div>
            @include('partials.license_sidebar_widget')
            <form action="{{ route('logout') }}" method="POST" style="margin-top: 15px;">
                @csrf
                <button type="submit" style="width: 100%; padding: 8px; background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.2); border-radius: 8px; cursor: pointer; font-size: 13px; font-weight: 600; display: flex; align-items: center; justify-content: center; gap: 8px;">
                    <i class="fas fa-sign-out-alt"></i><span>Log Out</span>
                </button>
            </form>
        </div>
    </aside>

    <!-- Main -->
    <div class="main-wrapper">
        <header class="topbar">
            <div style="display: flex; align-items: center; gap: 20px;">
                <button class="action-btn" id="sidebarToggle" title="Toggle Sidebar"><i class="fas fa-bars"></i></button>
                <div class="search-container">
                    <i class="fas fa-search"></i>
                    <input type="text" class="search-input" id="machineSearch" placeholder="Search hostname or machine ID...">
                </div>
            </div>
            <div class="topbar-actions">
                <button class="action-btn theme-toggle-btn" id="themeToggle" title="Toggle Theme">
                    <i class="fas fa-moon"></i><i class="fas fa-sun"></i>
                </button>
            </div>
        </header>

        @include('partials.license_status_banner')

        <main class="content-area">
            <!-- Flash messages -->
            @if (session('success'))
                <div style="background: rgba(16,185,129,0.1); border: 1px solid rgba(16,185,129,0.3); color: #10b981; padding: 14px 18px; border-radius: 12px; margin-bottom: 20px; font-size: 14px; font-weight: 600;">
                    <i class="fas fa-check-circle" style="margin-right: 8px;"></i>{{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div style="background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.3); color: #ef4444; padding: 14px 18px; border-radius: 12px; margin-bottom: 20px; font-size: 14px; font-weight: 600;">
                    <i class="fas fa-exclamation-circle" style="margin-right: 8px;"></i>{{ session('error') }}
                </div>
            @endif
            @if (session('info'))
                <div style="background: rgba(99,102,241,0.1); border: 1px solid rgba(99,102,241,0.3); color: #818cf8; padding: 14px 18px; border-radius: 12px; margin-bottom: 20px; font-size: 14px; font-weight: 600;">
                    <i class="fas fa-info-circle" style="margin-right: 8px;"></i>{{ session('info') }}
                </div>
            @endif

            <header class="page-header" style="margin-bottom: 30px;">
                <div class="page-title">
                    <h1>Machine Licensing Hub</h1>
                    <p>Control which machines are authorised to send monitoring data. Pending machines must be approved.</p>
                </div>
            </header>

            <!-- Stats Row -->
            <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 28px;">
                <div class="card stat-card">
                    <div class="stat-number" style="color: var(--text-primary);">{{ $counts['total'] }}</div>
                    <div class="stat-label">Total Machines</div>
                </div>
                <div class="card stat-card">
                    <div class="stat-number" style="color: #10b981;">{{ $counts['active'] }}</div>
                    <div class="stat-label">Active</div>
                </div>
                <div class="card stat-card">
                    <div class="stat-number" style="color: #f59e0b;">{{ $counts['pending'] }}</div>
                    <div class="stat-label">Pending Approval</div>
                </div>
                <div class="card stat-card">
                    <div class="stat-number" style="color: #ef4444;">{{ $counts['revoked'] }}</div>
                    <div class="stat-label">Revoked</div>
                </div>
            </div>

            <!-- Filter tabs -->
            <div class="filter-tabs">
                <button class="filter-tab active" data-filter="all">All</button>
                <button class="filter-tab" data-filter="pending">Pending</button>
                <button class="filter-tab" data-filter="active">Active</button>
                <button class="filter-tab" data-filter="revoked">Revoked</button>
            </div>

            <!-- Table -->
            <div class="card" style="padding: 0; overflow: hidden;">
                @if($machines->isEmpty())
                    <div style="padding: 60px; text-align: center; color: var(--text-secondary);">
                        <i class="fas fa-shield-halved" style="font-size: 48px; margin-bottom: 16px; opacity: 0.3;"></i>
                        <p>No machines registered yet. Machines register automatically when hazemonitor connects.</p>
                    </div>
                @else
                    <table class="machine-table" id="machineTable">
                        <thead>
                            <tr>
                                <th>Hostname</th>
                                <th>Machine ID</th>
                                <th>IP Address</th>
                                <th>Status</th>
                                <th>Last Seen</th>
                                <th>Registered</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($machines as $machine)
                            <tr data-status="{{ $machine->status }}" class="machine-row">
                                <td>
                                    <div style="font-weight: 600; color: var(--text-primary);">{{ $machine->hostname }}</div>
                                    @if($machine->approved_by)
                                        <div style="font-size: 11px; color: var(--text-muted); margin-top: 2px;">Approved by {{ $machine->approved_by }}</div>
                                    @endif
                                    @if($machine->revoked_by)
                                        <div style="font-size: 11px; color: #ef4444; margin-top: 2px;">Revoked by {{ $machine->revoked_by }}</div>
                                    @endif
                                </td>
                                <td><span class="mono">{{ $machine->machine_id }}</span></td>
                                <td style="color: var(--text-secondary);">{{ $machine->ip_address ?? '—' }}</td>
                                <td>
                                    <span class="status-badge status-{{ $machine->status }}">
                                        <i class="fas fa-{{ $machine->status === 'active' ? 'check-circle' : ($machine->status === 'pending' ? 'clock' : 'ban') }}"></i>
                                        {{ ucfirst($machine->status) }}
                                    </span>
                                </td>
                                <td style="color: var(--text-secondary);">
                                    {{ $machine->last_seen_at ? $machine->last_seen_at->diffForHumans() : 'Never' }}
                                </td>
                                <td style="color: var(--text-secondary); font-size: 13px;">
                                    {{ $machine->registered_at ? $machine->registered_at->format('d M Y') : '—' }}
                                </td>
                                <td>
                                    <div style="display: flex; gap: 6px; flex-wrap: wrap;">
                                        @if($machine->status !== 'active')
                                            <form action="{{ route('machine.licensing.approve', $machine->id) }}" method="POST" style="display: inline;">
                                                @csrf
                                                <button type="submit" class="btn-approve" title="Approve machine">
                                                    <i class="fas fa-check"></i> Approve
                                                </button>
                                            </form>
                                        @endif

                                        @if($machine->status !== 'revoked')
                                            <form action="{{ route('machine.licensing.revoke', $machine->id) }}" method="POST" style="display: inline;">
                                                @csrf
                                                <button type="submit" class="btn-revoke" title="Revoke machine"
                                                    onclick="return confirm('Revoke {{ $machine->hostname }}? Agent will stop reporting within 6 hours.')">
                                                    <i class="fas fa-ban"></i> Revoke
                                                </button>
                                            </form>
                                        @endif

                                        @if($machine->status === 'revoked')
                                            <form action="{{ route('machine.licensing.destroy', $machine->id) }}" method="POST" style="display: inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn-delete" title="Permanently delete"
                                                    onclick="return confirm('Permanently delete {{ $machine->hostname }}?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>

            <!-- How it works -->
            <div class="card" style="margin-top: 24px; padding: 20px;">
                <div style="font-size: 13px; font-weight: 700; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 16px;">
                    <i class="fas fa-circle-info" style="margin-right: 8px;"></i>How Machine Licensing Works
                </div>
                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; font-size: 13px; color: var(--text-secondary); line-height: 1.6;">
                    <div>
                        <div style="font-weight: 700; color: var(--text-primary); margin-bottom: 6px;"><span style="color:#f59e0b;">⏳</span> Pending</div>
                        Machine registered but not approved. Monitoring data is still accepted during the grace period. Approve to activate.
                    </div>
                    <div>
                        <div style="font-weight: 700; color: var(--text-primary); margin-bottom: 6px;"><span style="color:#10b981;">✓</span> Active</div>
                        Machine is fully licensed. Agent sends monitoring data normally. Token validated every 6 hours.
                    </div>
                    <div>
                        <div style="font-weight: 700; color: var(--text-primary); margin-bottom: 6px;"><span style="color:#ef4444;">✗</span> Revoked</div>
                        Agent stops sending data within 6 hours (next validation cycle). Data already collected is preserved.
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Sidebar toggle
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebar = document.querySelector('.sidebar');
        const mainWrapper = document.querySelector('.main-wrapper');
        sidebarToggle.addEventListener('click', () => {
            sidebar.classList.toggle('collapsed');
            mainWrapper.style.marginLeft = sidebar.classList.contains('collapsed') ? '90px' : '';
        });

        // Theme toggle
        const themeToggle = document.getElementById('themeToggle');
        themeToggle.addEventListener('click', () => {
            const newTheme = document.documentElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-theme', newTheme);
        });

        // Filter tabs
        document.querySelectorAll('.filter-tab').forEach(tab => {
            tab.addEventListener('click', () => {
                document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
                tab.classList.add('active');
                const filter = tab.dataset.filter;
                document.querySelectorAll('.machine-row').forEach(row => {
                    row.style.display = (filter === 'all' || row.dataset.status === filter) ? '' : 'none';
                });
            });
        });

        // Search
        document.getElementById('machineSearch').addEventListener('input', function () {
            const q = this.value.toLowerCase();
            document.querySelectorAll('.machine-row').forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(q) ? '' : 'none';
            });
        });
    </script>
</body>
</html>
