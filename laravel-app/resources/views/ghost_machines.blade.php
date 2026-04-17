<!DOCTYPE html>
<html lang="en-GB" data-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ASCLAM | Ghost Machine Detection</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <style>
        .sidebar {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: hidden;
            white-space: nowrap;
        }

        .sidebar.collapsed {
            width: 90px;
            padding: 24px 15px;
        }

        .sidebar.collapsed .logo-text,
        .sidebar.collapsed .nav-link span,
        .sidebar.collapsed .user-info-text,
        .sidebar.collapsed .sidebar-footer button span {
            display: none;
        }

        .sidebar.collapsed .logo-container {
            padding: 0;
            justify-content: center;
        }

        .sidebar.collapsed .nav-link {
            justify-content: center;
            padding: 12px;
        }

        .sidebar.collapsed .user-profile-sidebar {
            justify-content: center;
            padding: 10px;
        }

        .main-wrapper {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .ghost-card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 24px;
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .ghost-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: #ef4444;
            opacity: 0.6;
        }

        .ghost-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(239, 68, 68, 0.1);
            border-color: rgba(239, 68, 68, 0.3);
        }

        .ghost-icon {
            font-size: 40px;
            color: #ef4444;
            opacity: 0.1;
            position: absolute;
            right: 20px;
            bottom: 20px;
        }

        .days-badge {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
        }

        .ghost-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 24px;
        }

        .preset-filter-btn {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--border-color);
            color: var(--text-secondary);
            padding: 8px 16px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }

        .preset-filter-btn.active {
            background: #ef4444;
            border-color: #ef4444;
            color: white;
        }

        .preset-filter-btn:hover:not(.active) {
            background: rgba(255, 255, 255, 0.1);
            color: var(--text-primary);
        }
    </style>
</head>

<body>

    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="logo-container">
            <div class="logo-icon">
                <i class="fas fa-compass-drafting"></i>
            </div>
            <span class="logo-text">ASCLAM</span>
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
                    <a href="{{ route('ghost.machines') }}" class="nav-link active">
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
                    <a href="{{ route('audit.trail') }}" class="nav-link">
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
                        @if (auth()->user()->role === 'admin')
                            IT Manager
                        @elseif(auth()->user()->role === 'management')
                            Management
                        @else
                            Contract Manager
                        @endif
                    </span>
                </div>
            </div>

            <form action="{{ route('logout') }}" method="POST" style="margin-top: 15px;">
                @csrf
                <button type="submit"
                    style="width: 100%; padding: 8px; background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.2); border-radius: 8px; cursor: pointer; font-size: 13px; font-weight: 600; display: flex; align-items: center; justify-content: center; gap: 8px;">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Log Out</span>
                </button>
            </form>
        </div>
    </aside>

    <!-- Main -->
    <div class="main-wrapper">
        <header class="topbar">
            <div style="display: flex; align-items: center; gap: 20px;">
                <button class="action-btn" id="sidebarToggle" title="Toggle Sidebar">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="search-container">
                    <i class="fas fa-search"></i>
                    <input type="text" class="search-input" placeholder="Search ghost machines...">
                </div>
            </div>
            <div class="topbar-actions">
                <button class="action-btn theme-toggle-btn" id="themeToggle" title="Toggle Theme">
                    <i class="fas fa-moon"></i>
                    <i class="fas fa-sun"></i>
                </button>
            </div>
        </header>

        <main class="content-area">
            <header class="page-header" style="margin-bottom: 30px;">
                <div class="page-title">
                    <h1>Ghost Machine Detection</h1>
                    <p>Workstations that haven't sent heartbeats recently. Possible agent crashes or decomissions.</p>
                </div>
            </header>

            <!-- Filter -->
            <div class="card"
                style="padding: 24px; margin-bottom: 24px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px;">
                <div style="display: flex; align-items: center; gap: 15px;">
                    <span
                        style="font-size: 12px; font-weight: 700; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 0.5px;">Inactivity
                        Threshold:</span>
                    <div style="display: flex; gap: 8px;">
                        <button
                            onclick="window.location.href='{{ route('ghost.machines') }}?days=7&department={{ $selectedDept }}'"
                            class="preset-filter-btn {{ $inactiveDays == 7 ? 'active' : '' }}">7 Days</button>
                        <button
                            onclick="window.location.href='{{ route('ghost.machines') }}?days=14&department={{ $selectedDept }}'"
                            class="preset-filter-btn {{ $inactiveDays == 14 ? 'active' : '' }}">14 Days</button>
                        <button
                            onclick="window.location.href='{{ route('ghost.machines') }}?days=30&department={{ $selectedDept }}'"
                            class="preset-filter-btn {{ $inactiveDays == 30 ? 'active' : '' }}">30 Days</button>
                        <button
                            onclick="window.location.href='{{ route('ghost.machines') }}?days=45&department={{ $selectedDept }}'"
                            class="preset-filter-btn {{ $inactiveDays == 45 ? 'active' : '' }}">45 Days</button>
                    </div>
                </div>

                <form action="{{ route('ghost.machines') }}" method="GET"
                    style="display: flex; align-items: center; gap: 10px;">
                    <input type="hidden" name="days" value="{{ $inactiveDays }}">
                    <select name="department" onchange="this.form.submit()"
                        style="background: var(--bg-color); border: 1px solid var(--border-color); color: var(--text-primary); padding: 10px 16px; border-radius: 12px; font-size: 14px; outline: none; cursor: pointer;">
                        <option value="all" {{ $selectedDept == 'all' ? 'selected' : '' }}>All Teams</option>
                        @foreach ($deptList as $d)
                            <option value="{{ $d }}" {{ $selectedDept == $d ? 'selected' : '' }}>
                                {{ $d }}</option>
                        @endforeach
                    </select>
                </form>
            </div>

            <div class="ghost-grid">
                @forelse($ghostMachines as $machine)
                    <div class="ghost-card">
                        <div
                            style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                            <div style="font-size: 18px; font-weight: 700; color: var(--text-primary);">
                                {{ $machine['name'] }}
                            </div>
                            <span class="days-badge">{{ $machine['days_inactive'] }} Days Out</span>
                        </div>

                        <div style="margin-bottom: 20px;">
                            <div style="font-size: 13px; color: var(--text-secondary); margin-bottom: 6px;">
                                <i class="fas fa-user" style="margin-right: 8px; width: 14px;"></i> Last Seen User:
                                <strong>{{ $machine['last_user'] }}</strong>
                            </div>
                            <div style="font-size: 13px; color: var(--text-secondary);">
                                <i class="fas fa-clock" style="margin-right: 8px; width: 14px;"></i> Last Active:
                                <strong>{{ $machine['last_seen'] }}</strong>
                            </div>
                        </div>

                        <div style="font-size: 11px; color: var(--text-muted); font-style: italic;">
                            {{ $machine['last_seen_date'] }}
                        </div>

                        <i class="fas fa-ghost ghost-icon"></i>
                    </div>
                @empty
                    <div class="card"
                        style="grid-column: 1 / -1; padding: 60px; text-align: center; color: var(--text-secondary);">
                        <i class="fas fa-shield-alt"
                            style="font-size: 48px; margin-bottom: 16px; color: #10b981; opacity: 0.5;"></i>
                        <h3 style="color: var(--text-primary); margin-bottom: 8px;">No Ghost Machines Found</h3>
                        <p>All monitored workstations have checked in within the last {{ $inactiveDays }} days.</p>
                    </div>
                @endforelse
            </div>
        </main>
    </div>

    <script>
        // Theme toggle
        const themeToggle = document.getElementById('themeToggle');
        const html = document.documentElement;
        themeToggle.addEventListener('click', () => {
            const newTheme = html.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
            html.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
        });

        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebar = document.querySelector('.sidebar');
        sidebarToggle.addEventListener('click', () => {
            sidebar.classList.toggle('collapsed');
        });

        // Search filter
        const searchInput = document.querySelector('.search-input');
        if (searchInput) {
            searchInput.addEventListener('input', function(e) {
                const term = e.target.value.toLowerCase();
                const cards = document.querySelectorAll('.ghost-card');
                cards.forEach(card => {
                    const text = card.textContent.toLowerCase();
                    card.style.display = text.includes(term) ? '' : 'none';
                });
            });
        }
    </script>
</body>

</html>
