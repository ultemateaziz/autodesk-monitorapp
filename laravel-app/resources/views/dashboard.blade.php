<!DOCTYPE html>
<html lang="en-GB" data-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ArchEng Pro | Application Usage Dashboard</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
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
    </style>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                <a href="{{ route('dashboard') }}" class="nav-link active">
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

    <!-- Main Wrapper -->
    <div class="main-wrapper">
        <header class="topbar">
            <div style="display: flex; align-items: center; gap: 20px;">
                <button class="action-btn" id="sidebarToggle" title="Toggle Sidebar">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="search-container">
                    <i class="fas fa-search"></i>
                    <input type="text" class="search-input" placeholder="Search projects, users, or data...">
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
                <div class="action-btn">
                    <i class="fas fa-question-circle"></i>
                </div>
                <!-- User Profile in Topbar -->
                <div class="topbar-user"
                    style="display: flex; align-items: center; gap: 12px; margin-left: 10px; cursor: pointer;">
                    <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&background=3b82f6&color=fff" alt="Avatar"
                        style="width: 36px; height: 36px; border-radius: 50%; border: 2px solid var(--accent-color);">
                </div>
            </div>
        </header>

        {{-- License status banner / full block overlay --}}
        @include('partials.license_status_banner')

        <!-- Content Area -->
        <main class="content-area">
            <header class="page-header"
                style="display: flex; justify-content: space-between; align-items: flex-start;">
                <div class="page-title">
                    <h1>Application Usage Productivity Dashboard</h1>
                    <p>Real-time application usage intensity across the organization</p>
                </div>
                <div class="header-actions">
                    <a href="{{ route('dashboard.export') }}?from={{ $startDate }}&to={{ $endDate }}"
                        class="btn-primary"
                        style="background: #10b981; text-decoration: none; box-shadow: 0 4px 14px 0 rgba(16, 185, 129, 0.4); display: flex; align-items: center; gap: 10px; padding: 12px 24px; border-radius: 14px;">
                        <i class="fas fa-file-csv" style="font-size: 18px;"></i>
                        <span style="font-weight: 600;">Export Organization Report</span>
                    </a>
                </div>
            </header>

            <!-- Charts Section -->
            <div class="dashboard-grid">
                <!-- Productivity Multi-line Chart -->
                <div class="card card-floating">
                    <div class="card-header" style="flex-direction: column; align-items: stretch; gap: 16px;">
                        <div class="header-main"
                            style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
                            <span class="card-title">Overall Organization Productivity</span>
                            <form action="{{ route('dashboard') }}" method="GET" class="dashboard-filter-form"
                                style="display: flex; gap: 8px;">
                                <input type="hidden" name="period" value="{{ $period }}">
                                <div class="date-picker-mini">
                                    <span class="date-tag">DEPT</span>
                                    <select name="department" class="mini-input"
                                        style="padding: 0 8px; border: none; background: transparent; font-size: 11px; font-weight: 600; color: var(--text-primary); cursor: pointer;"
                                        {{ auth()->user()->role !== 'admin' ? 'disabled' : '' }}>
                                        @if (auth()->user()->role === 'admin')
                                            <option value="all">All Teams</option>
                                            @foreach ($deptList as $d)
                                                <option value="{{ $d }}"
                                                    {{ $selectedDept == $d ? 'selected' : '' }}>{{ $d }}
                                                </option>
                                            @endforeach
                                        @else
                                            <option value="{{ auth()->user()->department }}" selected>
                                                {{ auth()->user()->department }}</option>
                                        @endif
                                    </select>
                                </div>
                                <div class="date-picker-mini">
                                    <span class="date-tag">FROM</span>
                                    <input type="date" name="from" value="{{ $startDate }}"
                                        class="mini-input">
                                </div>
                                <div class="date-picker-mini">
                                    <span class="date-tag">TO</span>
                                    <input type="date" name="to" value="{{ $endDate }}"
                                        class="mini-input">
                                </div>
                                <button type="submit" class="mini-submit"><i class="fas fa-sync-alt"></i></button>
                            </form>
                        </div>
                        <div class="filter-group"
                            style="margin: 0; align-self: flex-start; background: rgba(0,0,0,0.05);">
                            <a href="{{ route('dashboard') }}?period=hourly&from={{ $startDate }}&to={{ $endDate }}&department={{ $selectedDept }}"
                                class="filter-btn {{ $period == 'hourly' ? 'active' : '' }}">Hourly
                                Activity</a>
                            <a href="{{ route('dashboard') }}?period=daily&from={{ $startDate }}&to={{ $endDate }}&department={{ $selectedDept }}"
                                class="filter-btn {{ $period == 'daily' ? 'active' : '' }}">Daily Intensity</a>
                            <a href="{{ route('dashboard') }}?period=weekly&from={{ $startDate }}&to={{ $endDate }}&department={{ $selectedDept }}"
                                class="filter-btn {{ $period == 'weekly' ? 'active' : '' }}">Weekly Average</a>
                            <a href="{{ route('dashboard') }}?period=monthly&from={{ $startDate }}&to={{ $endDate }}&department={{ $selectedDept }}"
                                class="filter-btn {{ $period == 'monthly' ? 'active' : '' }}">Monthly
                                Average</a>
                        </div>
                    </div>
                    <div class="productivity-chart-container">
                        <canvas id="productivityChart"></canvas>
                    </div>
                </div>

                <!-- Market Share Donut Chart -->
                <div class="card card-floating">
                    <div class="card-header">
                        <span class="card-title">Application Market Share</span>
                    </div>
                    <div class="market-chart-container">
                        <canvas id="marketChart"></canvas>
                        <div class="chart-center-text">
                            <span class="center-val">100%</span>
                            <span class="center-label">Total Usage</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Top Performers Section -->
            <div class="card card-floating" style="margin-bottom: 24px;">
                <div class="card-header" style="justify-content: space-between; align-items: center;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <i class="fas fa-trophy" style="color: #f59e0b; font-size: 20px;"></i>
                        <span class="card-title">Top Productivity Performers</span>
                    </div>
                    <a href="{{ route('leaderboard') }}" class="btn-text"
                        style="color: var(--accent-color); font-weight: 600; text-decoration: none; font-size: 13px;">View
                        Full Leaderboard <i class="fas fa-arrow-right"></i></a>
                </div>
                <div class="leaderboard-summary-grid"
                    style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 16px; padding: 16px;">
                    @foreach ($topUsers as $index => $user)
                        <div class="performer-mini-card"
                            style="background: rgba(0,0,0,0.03); border-radius: 12px; padding: 16px; display: flex; align-items: center; gap: 12px; border: 1px solid var(--border-color);">
                            <div class="rank-badge"
                                style="width: 28px; height: 28px; background: {{ $index == 0 ? '#f59e0b' : ($index == 1 ? '#94a3b8' : ($index == 2 ? '#b45309' : 'rgba(0,0,0,0.1)')) }}; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 700;">
                                {{ $index + 1 }}
                            </div>
                            <img src="{{ $user->avatar }}" alt="{{ $user->name }}"
                                style="width: 40px; height: 40px; border-radius: 50%; border: 2px solid var(--border-color);">
                            <div style="flex-grow: 1; min-width: 0;">
                                <div
                                    style="font-weight: 600; font-size: 14px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; color: var(--text-primary);">
                                    {{ $user->name }}</div>
                                <div style="font-size: 11px; color: var(--text-muted);">{{ $user->department }}</div>
                            </div>
                            <div style="text-align: right;">
                                <div style="font-weight: 700; color: var(--accent-color); font-size: 14px;">
                                    {{ $user->time_formatted }}</div>
                                <div style="font-size: 9px; color: var(--text-muted); text-transform: uppercase;">
                                    Productivity</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Metrics Grid -->
            <div class="metrics-grid">
                <!-- Metric 1: Online Now -->
                <div class="metric-card card-floating" style="animation-delay: 0.2s;">
                    <div class="metric-header">
                        <div class="metric-icon icon-blue">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="trend-badge trend-up">
                            <i class="fas fa-circle" style="color: #10b981; font-size: 8px; margin-right: 5px;"></i>
                            <span>Live</span>
                        </div>
                    </div>
                    <div class="metric-value">{{ $onlineUsers }}</div>
                    <div class="metric-label">Users Online Now</div>
                    <p class="metric-desc">Active in last 60 seconds</p>
                </div>

                <!-- Metric 2: Total Time Today -->
                <div class="metric-card card-floating" style="animation-delay: 0.4s;">
                    <div class="metric-header">
                        <div class="metric-icon icon-green">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                    <div class="metric-value">{{ $totalTime }}</div>
                    <div class="metric-label">Total Work Today</div>
                    <p class="metric-desc">Based on 3s heartbeats</p>
                </div>

                <!-- Metric 3 -->
                <div class="metric-card card-floating" style="animation-delay: 0.6s;">
                    <div class="metric-header">
                        <div class="metric-icon icon-purple">
                            <i class="fas fa-id-card"></i>
                        </div>
                    </div>
                    <div class="metric-value">{{ count($marketShare) }}</div>
                    <div class="metric-label">Apps Tracked</div>
                    <p class="metric-desc">Unique Autodesk processes</p>
                </div>

                <!-- Metric 4 -->
                <div class="metric-card card-floating" style="animation-delay: 0.8s;">
                    <div class="metric-header">
                        <div class="metric-icon icon-orange">
                            <i class="fas fa-desktop"></i>
                        </div>
                    </div>
                    <div class="metric-value">Active</div>
                    <div class="metric-label">System Monitoring</div>
                    <p class="metric-desc">Healthy connection</p>
                </div>
            </div>
        </main>
    </div>

    <!-- Initialization Script -->
    <script>
        // Theme Toggle Logic
        const themeToggle = document.getElementById('themeToggle');
        const html = document.documentElement;

        // Load preferred theme
        const savedTheme = localStorage.getItem('theme') || 'dark';
        html.setAttribute('data-theme', savedTheme);

        themeToggle.addEventListener('click', () => {
            const currentTheme = html.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';

            html.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);

            // Update charts after theme change
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

        // Charts Initialization
        let productivityChart, marketChart;

        function initCharts() {
            const ctxProductivity = document.getElementById('productivityChart').getContext('2d');
            const ctxMarket = document.getElementById('marketChart').getContext('2d');

            const isDark = html.getAttribute('data-theme') === 'dark';
            const gridColor = isDark ? '#334155' : '#e2e8f0';
            const textColor = isDark ? '#94a3b8' : '#64748b';

            // Productivity Chart
            productivityChart = new Chart(ctxProductivity, {
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
                            display: true,
                            position: 'top',
                            align: 'start',
                            labels: {
                                color: textColor,
                                usePointStyle: true,
                                pointStyle: 'circle',
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
                            borderColor: isDark ? 'rgba(255,255,255,0.1)' : 'rgba(0,0,0,0.1)',
                            borderWidth: 1,
                            padding: 12,
                            displayColors: true,
                            usePointStyle: true,
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': ' + context.parsed.y + ' min/hr';
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
                                    family: 'Outfit',
                                    size: 10
                                },
                                stepSize: 5,
                                callback: function(value) {
                                    return value + ' min';
                                }
                            },
                            min: 0,
                            max: 60
                        }
                    }
                }
            });

            // Market Share Chart
            const marketData = @json($marketShare);
            const marketLabels = marketData.map(item => item.application);
            const marketMinutes = marketData.map(item => item.minutes);
            const marketFormattedTimes = marketData.map(item => item.formatted_time);

            marketChart = new Chart(ctxMarket, {
                type: 'doughnut',
                data: {
                    labels: marketLabels.length > 0 ? marketLabels : ['No Data'],
                    datasets: [{
                        data: marketMinutes.length > 0 ? marketMinutes : [1],
                        backgroundColor: [
                            '#ef4444',
                            '#3b82f6',
                            '#10b981',
                            '#f59e0b',
                            '#8b5cf6',
                            '#ec4899',
                            '#94a3b8'
                        ],
                        borderWidth: 0,
                        hoverOffset: 15,
                        cutout: '80%'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                color: textColor,
                                usePointStyle: true,
                                pointStyle: 'circle',
                                padding: 20,
                                font: {
                                    family: 'Outfit',
                                    size: 11
                                }
                            }
                        },
                        tooltip: {
                            backgroundColor: isDark ? '#1e293b' : '#ffffff',
                            titleColor: isDark ? '#f1f5f9' : '#0f172a',
                            bodyColor: isDark ? '#94a3b8' : '#64748b',
                            borderColor: isDark ? 'rgba(255,255,255,0.1)' : 'rgba(0,0,0,0.1)',
                            borderWidth: 1,
                            padding: 12,
                            displayColors: true,
                            usePointStyle: true,
                            callbacks: {
                                label: function(context) {
                                    const appName = context.label;
                                    const timeStr = marketFormattedTimes[context.dataIndex];
                                    return appName + ': ' + timeStr;
                                }
                            }
                        }
                    }
                }
            });
        }

        function updateChartsTheme(theme) {
            const isDark = theme === 'dark';
            const gridColor = isDark ? '#334155' : '#e2e8f0';
            const textColor = isDark ? '#94a3b8' : '#64748b';

            // Update Productivity Chart
            productivityChart.options.scales.y.grid.color = gridColor;
            productivityChart.options.scales.x.ticks.color = textColor;
            productivityChart.options.scales.y.ticks.color = textColor;
            productivityChart.options.plugins.legend.labels.color = textColor;
            productivityChart.options.plugins.tooltip.backgroundColor = isDark ? '#1e293b' : '#ffffff';
            productivityChart.options.plugins.tooltip.titleColor = isDark ? '#f1f5f9' : '#0f172a';
            productivityChart.options.plugins.tooltip.bodyColor = textColor;
            productivityChart.update();

            // Update Market Chart
            marketChart.options.plugins.legend.labels.color = textColor;
            marketChart.update();
        }

        // Init on load
        window.addEventListener('DOMContentLoaded', initCharts);

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
    </script>
</body>

</html>
