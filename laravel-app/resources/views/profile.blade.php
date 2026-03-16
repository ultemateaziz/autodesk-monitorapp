<!DOCTYPE html>
<html lang="en" data-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ArchEng Pro | User Productivity Insight</title>

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

    <style>
        :root {
            /* Light Theme (Default) */
            --bg-deep-navy: #f8fafc;
            --bg-card-navy: #ffffff;
            --accent-blue: #2563eb;
            --accent-glow: rgba(37, 99, 235, 0.1);
            --border-navy: rgba(0, 0, 0, 0.05);
            --text-primary: #0f172a;
            --text-secondary: #64748b;
            --card-gradient: linear-gradient(135deg, #ffffff 0%, #f1f5f9 100%);
            --indicator-invert: 0;
        }

        [data-theme="dark"] {
            /* Dark Theme Override */
            --bg-deep-navy: #0a0e1a;
            --bg-card-navy: #12182b;
            --accent-blue: #3b82f6;
            --accent-glow: rgba(59, 130, 246, 0.5);
            --border-navy: rgba(255, 255, 255, 0.05);
            --text-primary: #f8fafc;
            --text-secondary: #94a3b8;
            --card-gradient: linear-gradient(135deg, #12182b 0%, #0c1120 100%);
            --indicator-invert: 1;
        }

        body {
            background-color: var(--bg-deep-navy);
            color: var(--text-primary);
            font-family: 'Outfit', sans-serif;
            margin: 0;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .clock-container {
            text-align: right;
            background: var(--accent-glow);
            padding: 10px 24px;
            border-radius: 20px;
            border: 1px solid var(--border-navy);
            backdrop-filter: blur(20px);
            min-width: 170px;
            /* Prevent layout shifting as time updates */
        }

        #digitalClock {
            font-size: 24px;
            font-weight: 700;
            color: var(--text-primary);
            display: block;
            letter-spacing: -0.5px;
            font-variant-numeric: tabular-nums;
            /* Ensures numbers don't push each other */
        }

        #dashboardDate {
            font-size: 13px;
            color: var(--text-secondary);
            font-weight: 500;
        }

        .mixed-widget-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 24px;
            margin-top: 24px;
        }

        .widget-panel,
        .card-floating {
            background: var(--bg-card-navy);
            border-radius: 28px;
            padding: 32px;
            border: 1px solid var(--border-navy);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease, background-color 0.3s ease, border-color 0.3s ease;
        }

        [data-theme="dark"] .widget-panel,
        [data-theme="dark"] .card-floating {
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
        }

        .widget-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 24px;
        }

        .widget-header i {
            font-size: 20px;
            color: var(--accent-blue);
            filter: drop-shadow(0 0 8px var(--accent-glow));
        }

        .widget-title {
            font-size: 18px;
            font-weight: 600;
            letter-spacing: -0.3px;
        }

        /* Modern Date Picker & Controls */
        .chart-controls {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .date-range-group {
            display: flex;
            align-items: center;
            background: rgba(125, 125, 125, 0.05);
            border: 1px solid var(--border-navy);
            padding: 8px 16px;
            border-radius: 14px;
            gap: 12px;
            color: var(--text-primary);
            font-size: 13px;
            font-weight: 500;
        }

        .btn-live-tracking {
            background: var(--bg-card-navy);
            color: var(--text-primary);
            border: 1px solid var(--border-navy);
            padding: 9px 18px;
            border-radius: 14px;
            font-size: 13px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-live-tracking:hover {
            background: var(--bg-deep-navy);
            border-color: var(--accent-blue);
        }

        .live-dot {
            width: 6px;
            height: 6px;
            background: #ef4444;
            border-radius: 50%;
            box-shadow: 0 0 8px #ef4444;
        }

        .date-picker-input {
            background: transparent;
            border: none;
            color: var(--text-primary);
            font-size: 13px;
            font-weight: 500;
            outline: none;
            font-family: 'Outfit', sans-serif;
            width: 110px;
        }

        .date-picker-input::-webkit-calendar-picker-indicator {
            filter: invert(var(--indicator-invert));
            cursor: pointer;
            opacity: 0.6;
            transition: opacity 0.2s;
        }

        .date-picker-input::-webkit-calendar-picker-indicator:hover {
            opacity: 1;
        }

        /* Hero Chart Styling */
        .daily-activity-card {
            background: var(--card-gradient);
            position: relative;
            overflow: hidden;
        }

        .daily-activity-card::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle at center, var(--accent-glow) 0%, transparent 70%);
            pointer-events: none;
        }

        .chart-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 32px;
        }

        .chart-legend {
            display: flex;
            gap: 20px;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
            color: var(--text-secondary);
        }

        .legend-color {
            width: 8px;
            height: 8px;
            border-radius: 50%;
        }

        /* Timeline Logs */
        .mini-log-table {
            width: 100%;
            border-spacing: 0;
        }

        .mini-log-table td {
            padding: 16px 0;
            border-bottom: 1px solid var(--border-navy);
            vertical-align: middle;
        }

        .log-indicator {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 12px;
            box-shadow: 0 0 10px currentColor;
        }

        .log-name {
            font-weight: 500;
            color: var(--text-primary);
        }

        .log-time {
            font-size: 12px;
            color: var(--text-secondary);
            text-align: right;
        }

        .chart-min-height {
            height: 280px;
            width: 100%;
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
        </div>
    </aside>

    <!-- Main Wrapper -->
    <div class="main-wrapper">
        <!-- Topbar -->
        <header class="topbar">
            <div class="search-container">
                <i class="fas fa-search"></i>
                <input type="text" class="search-input" placeholder="Search team members...">
            </div>

            <div class="topbar-actions">
                <button class="btn-primary" style="background-color: #3b82f6;">
                    <i class="fas fa-download"></i>
                    <span>Export Report</span>
                </button>
                <button class="action-btn theme-toggle-btn" id="themeToggle" title="Toggle Theme">
                    <i class="fas fa-moon"></i>
                    <i class="fas fa-sun"></i>
                </button>
                <div class="notif-wrapper">
                    <div class="action-btn" id="notifBtn">
                        <i class="fas fa-bell"></i>
                        @if (count($globalNotifications) > 0)
                            <span class="badge">{{ count($globalNotifications) }}</span>
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
            </div>
        </header>

        <!-- Content Area -->
        <main class="content-area">
            <header class="dashboard-header">
                <div class="page-title">
                    <h1 style="font-size: 24px;">User Productivity Insight</h1>
                    <p style="font-size: 14px; opacity: 0.7;">Reports > Individual Performance</p>
                </div>

                <div class="clock-container">
                    <span id="digitalClock">--:--:--</span>
                    <span id="dashboardDate">-----------</span>
                </div>
            </header>

            <!-- Profile Header Card -->
            @php
                // Force fallback to userName if displayName is completely empty or just spaces
                $finalName = !empty(trim($displayName)) ? trim($displayName) : $userName;
            @endphp
            <div class="profile-header-card card-floating">
                <img src="https://ui-avatars.com/api/?name={{ urlencode($finalName) }}&background=3b82f6&color=fff&size=200"
                    alt="{{ $finalName }}" class="profile-avatar-large">

                <div class="profile-info-main">
                    <h2 style="font-size: 42px; font-weight: 800; letter-spacing: -1px; margin-bottom: 8px;">
                        {{ $finalName }}</h2>
                    <p class="subtitle" style="font-size: 16px; opacity: 0.8; margin-bottom: 16px;">
                        Machine: <strong style="color: var(--text-primary);">{{ $machineName }}</strong> |
                        ID: <strong style="color: var(--text-primary);">{{ $userName }}</strong>
                    </p>
                    <div class="status-badges">
                        <span class="badge-status active">{{ $department }}</span>
                        <span class="badge-status"
                            style="background: rgba(16, 185, 129, 0.1); color: #10b981;">Active</span>
                    </div>
                </div>
            </div>

            <!-- Overall Database Summary (Lifetime) -->
            <div style="margin-bottom: 24px;">
                <h3
                    style="font-size: 14px; color: var(--text-secondary); margin-bottom: 12px; text-transform: uppercase; letter-spacing: 1px; display: flex; align-items: center; gap: 8px;">
                    <i class="fas fa-database" style="font-size: 12px;"></i> Lifetime Records (Database Overall)
                </h3>
                <div class="metrics-grid" style="grid-template-columns: repeat(2, 1fr);">
                    <div class="metric-card card-floating"
                        style="background: var(--card-gradient); border: 1px solid var(--border-navy);">
                        <div class="metric-header">
                            <span class="metric-label">Lifetime Working Hours</span>
                            <i class="fas fa-business-time" style="color: #f59e0b;"></i>
                        </div>
                        <div class="metric-value">{{ $overallTotalHours }}</div>
                        <p class="metric-desc">Total hours recorded since registration</p>
                    </div>

                    <div class="metric-card card-floating"
                        style="background: var(--card-gradient); border: 1px solid var(--border-navy);">
                        <div class="metric-header">
                            <span class="metric-label">Favorite Application</span>
                            <i class="fas fa-crown" style="color: #ef4444;"></i>
                        </div>
                        <div class="metric-value">{{ $overallTopApp }}</div>
                        <p class="metric-desc">Most used tool across all logged sessions</p>
                    </div>
                </div>
            </div>

            <!-- Filtered Key Metrics Row -->
            <h3
                style="font-size: 14px; color: var(--text-secondary); margin-bottom: 12px; text-transform: uppercase; letter-spacing: 1px; display: flex; align-items: center; gap: 8px;">
                <i class="fas fa-filter" style="font-size: 12px;"></i> Filtered Analytics (Based on Selection)
            </h3>
            <div class="metrics-grid" style="margin-bottom: 24px;">
                <div class="metric-card card-floating">
                    <div class="metric-header">
                        <span class="metric-label">Total Hours Logged</span>
                        <i class="fas fa-clock" style="color: var(--accent-color);"></i>
                    </div>
                    <div class="metric-value">{{ $totalHours }}</div>
                    <p class="metric-desc">Activity in selected range</p>
                </div>

                <div class="metric-card card-floating" style="animation-delay: 0.2s;">
                    <div class="metric-header">
                        <span class="metric-label">Primary Software</span>
                        <i class="fas fa-cube" style="color: #10b981;"></i>
                    </div>
                    <div class="metric-value">{{ $primarySoftware }}</div>
                    <p class="metric-desc">Used for {{ $primarySoftwarePercent }}% of period</p>
                </div>

                <div class="metric-card card-floating" style="animation-delay: 0.4s;">
                    <div class="metric-header">
                        <span class="metric-label">Productivity Score</span>
                        <i class="fas fa-chart-line" style="color: #8b5cf6;"></i>
                    </div>
                    <div class="metric-value">{{ $productivityScore }}%</div>
                    <div class="trend-badge {{ $trend >= 0 ? 'trend-up' : 'trend-down' }}">
                        <i class="fas fa-arrow-{{ $trend >= 0 ? 'up' : 'down' }}"></i>
                        <span>{{ abs($trend) }}% vs last month</span>
                    </div>
                </div>
            </div>

            <!-- Daily Activity Timeline (Hero Graph) -->
            <div class="daily-activity-card card-floating" style="margin-bottom: 24px;">
                <div class="chart-header">
                    <div class="chart-info">
                        <h1 class="widget-title"
                            style="font-size: 24px; margin-bottom: 8px; display: flex; align-items: center;">
                            Daily Activity Timeline
                            <span
                                style="background: rgba(37,99,235,0.1); color: var(--accent-color); padding: 4px 12px; border-radius: 20px; font-size: 16px; margin-left: 14px; border: 1px solid rgba(37,99,235,0.2);">
                                <i class="fas fa-clock"
                                    style="margin-right: 6px; font-size: 14px;"></i>{{ $totalHours }}
                            </span>
                        </h1>
                        <p style="font-size: 14px; color: var(--text-secondary); opacity: 0.8;">Engagement across
                            platforms for selected date range</p>
                    </div>

                    <div class="chart-controls">
                        <form action="{{ url()->current() }}" method="GET"
                            style="display: flex; align-items: center; gap: 12px;">
                            <div class="date-range-group">
                                <span
                                    style="font-size: 11px; opacity: 0.7; color: var(--text-secondary); margin-right: 4px;">FROM</span>
                                <input type="date" name="from" class="date-picker-input"
                                    value="{{ $startDate }}">
                            </div>
                            <div class="date-range-group">
                                <span
                                    style="font-size: 11px; opacity: 0.7; color: var(--text-secondary); margin-right: 4px;">TO</span>
                                <input type="date" name="to" class="date-picker-input"
                                    value="{{ $endDate }}">
                            </div>
                            <button type="submit" class="btn-live-tracking"
                                style="padding: 8px 16px; min-width: auto; background: var(--accent-blue); color: white;">
                                <i class="fas fa-filter"></i> Apply
                            </button>

                            @php
                                $isToday = $startDate == date('Y-m-d') && $endDate == date('Y-m-d');
                            @endphp

                            <a href="{{ url()->current() }}?from={{ date('Y-m-d') }}&to={{ date('Y-m-d') }}"
                                class="btn-live-tracking {{ $isToday ? 'active' : '' }}"
                                style="text-decoration: none; display: flex; align-items: center; gap: 8px; {{ $isToday ? 'background: rgba(34, 197, 94, 0.2); border-color: #22c55e;' : '' }}">
                                <span class="live-dot"
                                    style="{{ $isToday ? 'background: #22c55e; box-shadow: 0 0 10px #22c55e;' : '' }}"></span>
                                <span style="{{ $isToday ? 'color: #22c55e; font-weight: 600;' : '' }}">Live
                                    Tracking</span>
                            </a>
                        </form>
                    </div>
                </div>

                <div style="height: 380px; width: 100%; position: relative; margin-top: 20px;">
                    <canvas id="activityChart"></canvas>
                </div>
            </div>

            <!-- Mixed Widget Grid (Bottom Section) -->
            <div class="mixed-widget-grid">
                <!-- Column 1: Recent Activity Logs -->
                <div class="widget-panel card-floating" style="animation-delay: 0.2s;">
                    <div class="widget-header">
                        <i class="fas fa-history"></i>
                        <h3 class="widget-title">Activity Feed</h3>
                    </div>
                    <table class="mini-log-table">
                        @foreach ($recentLogs as $log)
                            <tr>
                                <td>
                                    <span class="log-indicator" style="color: #3b82f6;"></span>
                                    <span class="log-name">{{ $log->application }}</span>
                                    <div style="font-size: 10px; opacity: 0.6;">{{ $log->status }}</div>
                                </td>
                                <td class="log-time">{{ $log->recorded_at->diffForHumans() }}</td>
                            </tr>
                        @endforeach
                    </table>
                </div>

                <!-- Column 2: Software Usage (Donut) -->
                <div class="widget-panel card-floating" style="animation-delay: 0.4s;">
                    <div class="widget-header">
                        <i class="fas fa-chart-pie"></i>
                        <h3 class="widget-title">Software Usage</h3>
                    </div>
                    <div class="chart-min-height">
                        <canvas id="softwareDonut"></canvas>
                    </div>
                </div>

                <!-- Column 3: Daily Productivity (Bars) -->
                <div class="widget-panel card-floating" style="animation-delay: 0.6s;">
                    <div class="widget-header">
                        <i class="fas fa-bolt"></i>
                        <h3 class="widget-title">Performance Trend</h3>
                    </div>
                    <div class="chart-min-height">
                        <canvas id="productivityBarChart"></canvas>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Initialization Script -->
    <script>
        // Digital Clock logic
        function updateClock() {
            const now = new Date();
            const timeOptions = {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: true
            };
            const dateOptions = {
                weekday: 'short',
                month: 'short',
                day: 'numeric',
                year: 'numeric'
            };

            document.getElementById('digitalClock').textContent = now.toLocaleTimeString('en-US', timeOptions);
            document.getElementById('dashboardDate').textContent = now.toLocaleDateString('en-US', dateOptions);
        }
        setInterval(updateClock, 1000);
        updateClock();

        const html = document.documentElement;
        const themeToggle = document.getElementById('themeToggle');
        const savedTheme = localStorage.getItem('theme') || 'dark';
        html.setAttribute('data-theme', savedTheme);

        // Theme toggle click handler
        themeToggle.addEventListener('click', () => {
            const currentTheme = html.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            html.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            updateChartsTheme(newTheme);
        });

        // Chart references for theme updates
        let activityChart, donutChart, barChart;

        function updateChartsTheme(theme) {
            const isDark = theme === 'dark';
            const textColor = isDark ? '#94a3b8' : '#64748b';
            const gridColor = isDark ? '#334155' : '#e2e8f0';

            if (activityChart) {
                activityChart.options.scales.y.grid.color = gridColor;
                activityChart.options.scales.x.ticks.color = textColor;
                activityChart.options.scales.y.ticks.color = textColor;
                activityChart.options.plugins.legend.labels.color = textColor;
                activityChart.update();
            }
            if (donutChart) {
                donutChart.options.plugins.legend.labels.color = textColor;
                donutChart.update();
            }
            if (barChart) {
                barChart.options.scales.y.grid.color = gridColor;
                barChart.options.scales.x.ticks.color = textColor;
                barChart.options.scales.y.ticks.color = textColor;
                barChart.options.plugins.legend.labels.color = textColor;
                barChart.update();
            }
        }

        function initCharts() {
            const ctxActivity = document.getElementById('activityChart').getContext('2d');
            const ctxDonut = document.getElementById('softwareDonut').getContext('2d');
            const ctxBar = document.getElementById('productivityBarChart').getContext('2d');

            const isDark = html.getAttribute('data-theme') === 'dark';
            const textColor = isDark ? '#94a3b8' : '#64748b';
            const gridColor = isDark ? '#334155' : '#e2e8f0';

            // Activity Area Chart (Hero)
            const gradientLine = ctxActivity.createLinearGradient(0, 0, 0, 300);
            gradientLine.addColorStop(0, 'rgba(59, 130, 246, 0.4)');
            gradientLine.addColorStop(1, 'rgba(59, 130, 246, 0)');

            activityChart = new Chart(ctxActivity, {
                type: 'line',
                data: {
                    labels: @json($timelineLabels),
                    datasets: @json($timelineDatasets)
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                            align: 'end',
                            labels: {
                                color: textColor,
                                boxWidth: 10,
                                usePointStyle: true,
                                padding: 15,
                                font: {
                                    family: 'Outfit',
                                    size: 11
                                }
                            }
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false,
                            backgroundColor: '#12182b',
                            titleFont: {
                                family: 'Outfit',
                                size: 13
                            },
                            bodyFont: {
                                family: 'Outfit',
                                size: 12
                            },
                            borderColor: 'rgba(255,255,255,0.1)',
                            borderWidth: 1,
                            callbacks: {
                                label: function(context) {
                                    const totalMins = Math.round(context.parsed.y);
                                    const h = Math.floor(totalMins / 60);
                                    const m = totalMins % 60;
                                    return context.dataset.label + ': ' + h + 'h ' + m + 'm';
                                }
                            }
                        }
                    },
                    animations: {
                        y: {
                            easing: 'easeInOutQuart',
                            duration: 1000,
                            from: 500
                        },
                        tension: {
                            duration: 1000,
                            easing: 'linear',
                            from: 1,
                            to: 0.4
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
                                    family: 'Outfit',
                                    size: 11
                                }
                            }
                        },
                        y: {
                            stacked: true,
                            grid: {
                                color: gridColor,
                                borderDash: [6, 6]
                            },
                            ticks: {
                                color: textColor,
                                font: {
                                    family: 'Outfit',
                                    size: 10
                                },
                                stepSize: 5,
                                callback: function(value) {
                                    const h = Math.floor(value / 60);
                                    const m = value % 60;
                                    return h + 'h ' + m + 'm';
                                }
                            },
                            beginAtZero: true,
                            max: 60
                        }
                    }
                }
            });

            // Software Usage Donut
            const donutLabels = @json($donutLabels);
            const donutMinutes = @json($donutMinutes);
            const donutFormattedTimes = @json($donutFormattedTimes);

            donutChart = new Chart(ctxDonut, {
                type: 'doughnut',
                data: {
                    labels: donutLabels.length > 0 ? donutLabels : ['No Data'],
                    datasets: [{
                        data: donutMinutes.length > 0 ? donutMinutes : [1],
                        backgroundColor: ['#3b82f6', '#60a5fa', '#8b5cf6', '#a855f7', '#6366f1', '#1e293b'],
                        borderWidth: 0,
                        hoverOffset: 10,
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '82%',
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                color: textColor,
                                usePointStyle: true,
                                padding: 20,
                                font: {
                                    family: 'Outfit',
                                    size: 10
                                }
                            }
                        },
                        tooltip: {
                            backgroundColor: '#12182b',
                            titleFont: {
                                family: 'Outfit',
                                size: 13
                            },
                            bodyFont: {
                                family: 'Outfit',
                                size: 12
                            },
                            borderColor: 'rgba(255,255,255,0.1)',
                            borderWidth: 1,
                            callbacks: {
                                label: function(context) {
                                    const appName = context.label;
                                    const timeStr = donutFormattedTimes[context.dataIndex];
                                    return appName + ': ' + timeStr;
                                }
                            }
                        }
                    }
                }
            });

            // Performance Trend Bar Chart (Last 7 days)
            barChart = new Chart(ctxBar, {
                type: 'bar',
                data: {
                    labels: @json($sevenDaysLabels),
                    datasets: [{
                        label: 'Efficiency',
                        data: @json($sevenDaysData),
                        backgroundColor: '#3b82f6',
                        borderRadius: 12,
                        barThickness: 8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
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
                                    family: 'Outfit',
                                    size: 10
                                }
                            }
                        },
                        y: {
                            grid: {
                                display: 'rgba(255, 255, 255, 0.05)'
                            },
                            ticks: {
                                display: false
                            },
                            min: 0,
                            max: 100
                        }
                    }
                }
            });
        }

        window.addEventListener('DOMContentLoaded', () => {
            initCharts();

            // Auto-refresh if in Live Tracking mode (Today)
            const urlParams = new URLSearchParams(window.location.search);
            const from = urlParams.get('from');
            const to = urlParams.get('to');
            const today = new Date().toISOString().split('T')[0];

            if (from === today && to === today) {
                console.log('Live Tracking Active: Refreshing in 30s...');
                setTimeout(() => {
                    window.location.reload();
                }, 30000); // 30 seconds
            }

            // Notification Toggle
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
        });
    </script>
</body>

</html>
