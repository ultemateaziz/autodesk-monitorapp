<!DOCTYPE html>
<html lang="en-GB" data-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ArchEng Pro | Top Users Leaderboard</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Flatpickr (Modern Calendar) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/dark.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <style>
        /* Sidebar Toggle Styles */
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

        .leaderboard-container {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 24px;
            margin-top: 24px;
        }

        @media (max-width: 1024px) {
            .leaderboard-container {
                grid-template-columns: 1fr;
            }
        }

        .user-rank-card {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 16px;
            background: var(--card-bg);
            border-radius: 16px;
            margin-bottom: 12px;
            transition: transform 0.2s, box-shadow 0.2s;
            border: 1px solid var(--border-color);
        }

        .user-rank-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
            border-color: var(--accent-color);
        }

        .rank-number {
            font-size: 24px;
            font-weight: 700;
            color: var(--text-muted);
            min-width: 40px;
            text-align: center;
        }

        .rank-1 .rank-number {
            color: #f59e0b;
        }

        .rank-2 .rank-number {
            color: #94a3b8;
        }

        .rank-3 .rank-number {
            color: #b45309;
        }

        .user-avatar-large {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            border: 3px solid var(--border-color);
        }

        .rank-1 .user-avatar-large {
            border-color: #f59e0b;
        }

        .user-details {
            flex-grow: 1;
        }

        .user-name-text {
            display: block;
            font-size: 16px;
            font-weight: 600;
            color: var(--text-primary);
        }

        .user-dept-text {
            font-size: 12px;
            color: var(--text-muted);
        }

        .usage-metric {
            text-align: right;
        }

        .usage-value {
            display: block;
            font-size: 18px;
            font-weight: 700;
            color: var(--accent-color);
        }

        .usage-label {
            font-size: 11px;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .leaderboard-header {
            margin-bottom: 32px;
            background: var(--card-bg);
            padding: 24px;
            border-radius: 20px;
            border: 1px solid var(--border-color);
        }

        .filter-select {
            background: var(--bg-color);
            border: 1px solid var(--border-color);
            color: var(--text-primary);
            padding: 10px 16px;
            border-radius: 12px;
            font-family: 'Outfit', sans-serif;
            font-weight: 600;
            outline: none;
            cursor: pointer;
            min-width: 180px;
        }

        .calendar-input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
            background: var(--bg-color);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 2px 12px;
            min-width: 240px;
        }

        .calendar-input-wrapper i {
            color: var(--accent-color);
            margin-right: 10px;
        }

        .calendar-input {
            background: transparent;
            border: none;
            color: var(--text-primary);
            padding: 8px 0;
            width: 100%;
            outline: none;
            font-weight: 600;
            font-family: 'Outfit', sans-serif;
            cursor: pointer;
        }

        .preset-pills {
            display: flex;
            gap: 8px;
            margin-top: 12px;
        }

        .preset-pill {
            padding: 6px 14px;
            border-radius: 20px;
            background: rgba(0, 0, 0, 0.1);
            border: 1px solid var(--border-color);
            color: var(--text-muted);
            font-size: 11px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
        }

        .preset-pill:hover,
        .preset-pill.active {
            background: var(--accent-color);
            border-color: var(--accent-color);
            color: white;
        }

        .view-toggle {
            display: flex;
            background: var(--bg-color);
            padding: 4px;
            border-radius: 12px;
            border: 1px solid var(--border-color);
        }

        .view-btn {
            padding: 8px 16px;
            border-radius: 8px;
            border: none;
            background: transparent;
            color: var(--text-muted);
            font-weight: 600;
            font-size: 12px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .view-btn.active {
            background: var(--accent-color);
            color: white;
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
            <span class="logo-text">ArchEng Pro</span>
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
                <a href="{{ route('leaderboard') }}" class="nav-link active">
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

    <div class="main-wrapper">
        <!-- Topbar -->
        <header class="topbar">
            <div style="display: flex; align-items: center; gap: 20px;">
                <button class="action-btn" id="sidebarToggle" title="Toggle Sidebar">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="search-container">
                    <i class="fas fa-search"></i>
                    <input type="text" class="search-input" placeholder="Search for top performers...">
                </div>
            </div>

            <div class="topbar-actions">
                <button class="action-btn theme-toggle-btn" id="themeToggle" title="Toggle Theme">
                    <i class="fas fa-moon"></i>
                    <i class="fas fa-sun"></i>
                </button>
                <div class="notif-wrapper">
                    <div class="action-btn" id="notifBtn">
                        <i class="fas fa-bell"></i>
                        @if (count($globalNotifications ?? []) > 0)
                            <span class="badge">{{ count($globalNotifications ?? []) }}</span>
                        @endif
                    </div>

                    <div class="notif-dropdown" id="notifDropdown">
                        <div class="notif-header">
                            <h3>Notifications</h3>
                            <span style="font-size: 11px; color: var(--text-secondary);">Inactive > 30 Days</span>
                        </div>

                        <div class="notif-list">
                            @forelse($globalNotifications as $notif)
                                <div class="notif-item">
                                    <div class="notif-icon">
                                        <i class="fas fa-exclamation-triangle"></i>
                                    </div>
                                    <div class="notif-content">
                                        <div class="notif-title">
                                            <strong>{{ $notif->user_name }}</strong> hasn't used
                                            <span>{{ $notif->software_name }}</span>
                                        </div>
                                        <div class="notif-meta">
                                            Last seen: {{ $notif->last_seen }} on {{ $notif->machine }}
                                        </div>
                                    </div>
                                    <form action="{{ route('notification.dismiss') }}" method="POST"
                                        style="margin: 0;">
                                        @csrf
                                        <input type="hidden" name="user_name" value="{{ $notif->user_name }}">
                                        <input type="hidden" name="software_name"
                                            value="{{ $notif->software_name }}">
                                        <button type="submit" class="btn-clear-notif" title="Clear Notification">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </form>
                                </div>
                            @empty
                                <div class="notif-empty">
                                    <i class="fas fa-bell-slash"></i>
                                    <p>No new notifications</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
                <div class="topbar-user">
                    <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&background=3b82f6&color=fff" alt="Avatar"
                        style="width: 36px; height: 36px; border-radius: 50%; border: 2px solid var(--accent-color);">
                </div>
            </div>
        </header>

        <!-- Content Area -->
        <main class="content-area">
            <header class="page-header" style="flex-direction: column; align-items: flex-start; gap: 8px;">
                <div class="page-title">
                    <h1 style="display: flex; align-items: center; gap: 12px;">
                        <i class="fas fa-medal" style="color: #f59e0b;"></i>
                        Top Users Leaderboard
                    </h1>
                    <p>Recognizing the most productive users for <strong>{{ $selectedSoftware }}</strong></p>
                </div>
            </header>

            <!-- Leaderboard Header / Filters -->
            <div class="leaderboard-header">
                <form action="{{ route('leaderboard') }}" method="GET" id="filterForm">
                    <div style="display: flex; gap: 24px; align-items: flex-start; flex-wrap: wrap;">
                        <div class="filter-group-alt">
                            <label
                                style="font-size: 11px; font-weight: 700; color: var(--text-muted); margin-bottom: 8px; display: block; text-transform: uppercase;">Software
                                Product</label>
                            <select name="software" class="filter-select" onchange="this.form.submit()">
                                @foreach ($softwareList as $sw)
                                    <option value="{{ $sw }}"
                                        {{ $selectedSoftware == $sw ? 'selected' : '' }}>{{ $sw }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="filter-group-alt">
                            <label
                                style="font-size: 11px; font-weight: 700; color: var(--text-muted); margin-bottom: 8px; display: block; text-transform: uppercase;">Team
                                / Dept</label>
                            <select name="department" class="filter-select" onchange="this.form.submit()"
                                {{ auth()->user()->role !== 'admin' ? 'disabled' : '' }}>
                                @if (auth()->user()->role === 'admin')
                                    <option value="all" {{ $selectedDept == 'all' ? 'selected' : '' }}>All Teams
                                    </option>
                                    @foreach ($deptList as $d)
                                        <option value="{{ $d }}"
                                            {{ $selectedDept == $d ? 'selected' : '' }}>{{ $d }}</option>
                                    @endforeach
                                @else
                                    <option value="{{ auth()->user()->department }}" selected>
                                        {{ auth()->user()->department }}</option>
                                @endif
                            </select>
                        </div>

                        <div class="filter-group-alt">
                            <label
                                style="font-size: 11px; font-weight: 700; color: var(--text-muted); margin-bottom: 8px; display: block; text-transform: uppercase;">Date
                                Range (Calendar)</label>
                            <div class="calendar-input-wrapper">
                                <i class="fas fa-calendar-alt"></i>
                                <input type="text" id="dateRange" class="calendar-input"
                                    placeholder="Select date range...">
                                <input type="hidden" name="from" id="fromDate" value="{{ $startDate }}">
                                <input type="hidden" name="to" id="toDate" value="{{ $endDate }}">
                            </div>
                        </div>

                        <div class="filter-group-alt">
                            <label
                                style="font-size: 11px; font-weight: 700; color: var(--text-muted); margin-bottom: 8px; display: block; text-transform: uppercase;">View
                                Intensity</label>
                            <div class="view-toggle">
                                <button type="button" onclick="setPeriod('hourly')"
                                    class="view-btn {{ $period == 'hourly' ? 'active' : '' }}">Hourly
                                    Activity</button>
                                <button type="button" onclick="setPeriod('daily')"
                                    class="view-btn {{ $period == 'daily' ? 'active' : '' }}">Daily Growth</button>
                                <input type="hidden" name="period" id="periodInput" value="{{ $period }}">
                            </div>
                        </div>

                        <div class="filter-group-alt" style="margin-left: auto; align-self: flex-end;">
                            <button type="submit" class="btn-primary"
                                style="padding: 12px 24px; border-radius: 12px;">
                                <i class="fas fa-sync-alt"></i> Update Report
                            </button>
                        </div>
                    </div>

                    <div class="preset-pills">
                        <a href="#" onclick="setPreset('today')" class="preset-pill">Today</a>
                        <a href="#" onclick="setPreset('7days')" class="preset-pill">Last 7 Days</a>
                        <a href="#" onclick="setPreset('30days')" class="preset-pill">Last 30 Days</a>
                    </div>
                </form>
            </div>

            <!-- Main Leaderboard Content -->
            <div class="leaderboard-container">
                <!-- Left: Top Users List -->
                <div class="top-performers">
                    <h3 style="margin-bottom: 20px; font-size: 18px; color: var(--text-primary);">Top 5 Producers</h3>

                    @forelse($topUsers as $index => $user)
                        <div class="user-rank-card rank-{{ $index + 1 }}">
                            <div class="rank-number">#{{ $index + 1 }}</div>
                            <img src="{{ $user->avatar }}" alt="{{ $user->name }}" class="user-avatar-large">
                            <div class="user-details">
                                <span class="user-name-text">{{ $user->name }}</span>
                                <span class="user-dept-text">{{ $user->department }}</span>
                            </div>
                            <div class="usage-metric">
                                <span class="usage-value">{{ $user->total_time }}</span>
                                <span class="usage-label">Usage Time</span>
                            </div>
                        </div>
                    @empty
                        <div class="card" style="padding: 40px; text-align: center; color: var(--text-muted);">
                            <i class="fas fa-ghost" style="font-size: 48px; margin-bottom: 16px; opacity: 0.3;"></i>
                            <p>No usage data found for this software in the selected period.</p>
                        </div>
                    @endforelse
                </div>

                <!-- Right: Comparison Chart -->
                <div class="card card-floating" style="padding: 24px; display: flex; flex-direction: column;">
                    <div
                        style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                        <h3 style="font-size: 18px; color: var(--text-primary);">Usage Intensity Comparison</h3>
                        <span style="font-size: 12px; color: var(--text-muted);">Minutes per Period</span>
                    </div>
                    <div style="flex-grow: 1; min-height: 400px;">
                        <canvas id="leaderboardChart"></canvas>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Script Section -->
    <script>
        // Theme Toggle Logic
        const themeToggle = document.getElementById('themeToggle');
        const html = document.documentElement;
        const savedTheme = localStorage.getItem('theme') || 'dark';
        html.setAttribute('data-theme', savedTheme);

        themeToggle.addEventListener('click', () => {
            const currentTheme = html.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            html.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            updateChartsTheme(newTheme);
        });

        // Sidebar Toggle
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebar = document.querySelector('.sidebar');
        if (sidebarToggle && sidebar) {
            sidebarToggle.addEventListener('click', () => {
                sidebar.classList.toggle('collapsed');
                localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
            });

            // Restore sidebar state
            if (localStorage.getItem('sidebarCollapsed') === 'true') {
                sidebar.classList.add('collapsed');
            }
        }

        // Chart Initialization
        let leaderboardChart;

        function initChart() {
            const ctx = document.getElementById('leaderboardChart').getContext('2d');
            const isDark = html.getAttribute('data-theme') === 'dark';
            const gridColor = isDark ? '#334155' : '#e2e8f0';
            const textColor = isDark ? '#94a3b8' : '#64748b';

            leaderboardChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: @json($timelineLabels),
                    datasets: @json($chartDatasets)
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                color: textColor,
                                usePointStyle: true,
                                padding: 20,
                                font: {
                                    family: 'Outfit',
                                    size: 12
                                }
                            }
                        },
                        tooltip: {
                            backgroundColor: isDark ? '#1e293b' : '#ffffff',
                            titleColor: isDark ? '#f1f5f9' : '#0f172a',
                            bodyColor: isDark ? '#94a3b8' : '#64748b',
                            padding: 12,
                            borderColor: isDark ? 'rgba(255,255,255,0.1)' : 'rgba(0,0,0,0.1)',
                            borderWidth: 1,
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': ' + context.parsed.y + ' min';
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                color: textColor,
                                font: {
                                    family: 'Outfit'
                                }
                            }
                        },
                        y: {
                            grid: {
                                color: gridColor,
                                borderDash: [5, 5]
                            },
                            ticks: {
                                color: textColor,
                                font: {
                                    family: 'Outfit'
                                },
                                callback: function(value) {
                                    return value + 'm';
                                }
                            },
                            beginAtZero: true
                        }
                    }
                }
            });
        }

        function updateChartsTheme(theme) {
            const isDark = theme === 'dark';
            const gridColor = isDark ? '#334155' : '#e2e8f0';
            const textColor = isDark ? '#94a3b8' : '#64748b';

            leaderboardChart.options.scales.y.grid.color = gridColor;
            leaderboardChart.options.scales.x.ticks.color = textColor;
            leaderboardChart.options.scales.y.ticks.color = textColor;
            leaderboardChart.options.plugins.legend.labels.color = textColor;
            leaderboardChart.update();
        }

        window.addEventListener('DOMContentLoaded', () => {
            initChart();

            // Flatpickr Range Calendar
            flatpickr("#dateRange", {
                mode: "range",
                dateFormat: "Y-m-d",
                defaultDate: ["{{ $startDate }}", "{{ $endDate }}"],
                onClose: function(selectedDates, dateStr, instance) {
                    if (selectedDates.length === 2) {
                        const from = instance.formatDate(selectedDates[0], "Y-m-d");
                        const to = instance.formatDate(selectedDates[1], "Y-m-d");
                        document.getElementById('fromDate').value = from;
                        document.getElementById('toDate').value = to;
                    }
                }
            });
        });

        function setPeriod(p) {
            document.getElementById('periodInput').value = p;
            document.getElementById('filterForm').submit();
        }

        function setPreset(p) {
            const now = new Date();
            let from, to;

            to = now.toISOString().split('T')[0];

            if (p === 'today') {
                from = to;
            } else if (p === '7days') {
                const d = new Date();
                d.setDate(d.getDate() - 6);
                from = d.toISOString().split('T')[0];
            } else if (p === '30days') {
                const d = new Date();
                d.setDate(d.getDate() - 29);
                from = d.toISOString().split('T')[0];
            }

            document.getElementById('fromDate').value = from;
            document.getElementById('toDate').value = to;
            document.getElementById('filterForm').submit();
        }

        // Notification Toggle (Copied from Dashboard)
        const notifBtn = document.getElementById('notifBtn');
        const notifDropdown = document.getElementById('notifDropdown');

        if (notifBtn && notifDropdown) {
            notifBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                notifDropdown.classList.toggle('show');
            });

            document.addEventListener('click', (e) => {
                if (!notifDropdown.contains(e.target) && !notifBtn.contains(e.target)) {
                    notifDropdown.classList.remove('show');
                }
            });
        }
    </script>
</body>

</html>
