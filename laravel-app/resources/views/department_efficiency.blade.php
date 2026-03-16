<!DOCTYPE html>
<html lang="en" data-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ArchEng Pro | Department Efficiency Benchmark</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

        .efficiency-card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            padding: 24px;
            transition: all 0.3s ease;
            position: relative;
        }

        .efficiency-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.2);
            border-color: var(--accent-color);
        }

        .value-display {
            font-size: 32px;
            font-weight: 800;
            color: var(--text-primary);
            margin: 10px 0;
            display: flex;
            align-items: baseline;
            gap: 6px;
        }

        .value-unit {
            font-size: 14px;
            color: var(--text-secondary);
            font-weight: 500;
        }

        .dept-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            margin-bottom: 15px;
        }

        .icon-arch {
            background: rgba(59, 130, 246, 0.1);
            color: #3b82f6;
        }

        .icon-mep {
            background: rgba(16, 185, 129, 0.1);
            color: #10b981;
        }

        .icon-struc {
            background: rgba(139, 92, 246, 0.1);
            color: #8b5cf6;
        }

        .icon-infra {
            background: rgba(245, 158, 11, 0.1);
            color: #f59e0b;
        }

        .icon-viz {
            background: rgba(236, 72, 153, 0.1);
            color: #ec4899;
        }

        .benchmark-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            gap: 24px;
            margin-bottom: 30px;
        }

        .chart-container {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            padding: 30px;
            height: 450px;
        }

        .period-selector {
            display: flex;
            gap: 10px;
            background: var(--bg-color);
            padding: 4px;
            border-radius: 12px;
            border: 1px solid var(--border-color);
        }

        .period-btn {
            padding: 8px 16px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            background: transparent;
            color: var(--text-secondary);
            transition: all 0.2s;
        }

        .period-btn.active {
            background: var(--card-bg);
            color: var(--text-primary);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>

<body>

    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="logo-container">
            <div class="logo-icon"><i class="fas fa-compass-drafting"></i></div>
            <span class="logo-text">ArchEng Pro</span>
        </div>

        <ul class="nav-menu">
            <li class="nav-section-title">Main Monitoring</li>
            <li class="nav-item">
                <a href="{{ route('dashboard') }}" class="nav-link">
                    <i class="fas fa-th-large"></i><span>Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('leaderboard') }}" class="nav-link">
                    <i class="fas fa-trophy"></i><span>Leaderboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('users') }}" class="nav-link">
                    <i class="fas fa-users"></i><span>Users</span>
                </a>
            </li>

            <li class="nav-section-title">Analytics & Reports</li>
            <li class="nav-item">
                <a href="{{ route('license.audit') }}" class="nav-link">
                    <i class="fas fa-user-slash"></i><span>Inactive Users</span>
                </a>
            </li>
            @if (auth()->user()->role !== 'team_leader')
                <li class="nav-item">
                    <a href="{{ route('license.optimization') }}" class="nav-link">
                        <i class="fas fa-lightbulb"></i><span>License Optimization</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('department.efficiency') }}" class="nav-link active">
                        <i class="fas fa-chart-pie"></i><span>Efficiency Benchmark</span>
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
                        <i class="fas fa-desktop"></i><span>Machine Inventory</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('ghost.machines') }}" class="nav-link">
                        <i class="fas fa-ghost"></i><span>Ghost Machines</span>
                    </a>
                </li>
            @endif

            <li class="nav-section-title">System</li>
            @if (auth()->check() && auth()->user()->role === 'admin')
                <li class="nav-item">
                    <a href="{{ route('user-management') }}" class="nav-link">
                        <i class="fas fa-user-shield"></i><span>User Management</span>
                    </a>
                </li>
            @endif
            <li class="nav-item">
                <a href="{{ route('settings') }}" class="nav-link">
                    <i class="fas fa-cog"></i><span>Settings</span>
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
                    <i class="fas fa-sign-out-alt"></i><span>Log Out</span>
                </button>
            </form>
        </div>
    </aside>

    <!-- Main -->
    <div class="main-wrapper">
        <header class="topbar">
            <div style="display: flex; align-items: center; gap: 20px;">
                <button class="action-btn" id="sidebarToggle" title="Toggle Sidebar"><i
                        class="fas fa-bars"></i></button>
                <div class="period-selector">
                    <button onclick="window.location.href='?days=7'"
                        class="period-btn {{ $days == 7 ? 'active' : '' }}">Last 7 Days</button>
                    <button onclick="window.location.href='?days=30'"
                        class="period-btn {{ $days == 30 ? 'active' : '' }}">Last 30 Days</button>
                    <button onclick="window.location.href='?days=90'"
                        class="period-btn {{ $days == 90 ? 'active' : '' }}">Last 90 Days</button>
                </div>
            </div>
            <div class="topbar-actions">
                <button class="action-btn theme-toggle-btn" id="themeToggle" title="Toggle Theme">
                    <i class="fas fa-moon"></i><i class="fas fa-sun"></i>
                </button>
            </div>
        </header>

        <main class="content-area">
            <header class="page-header" style="margin-bottom: 30px;">
                <div class="page-title">
                    <h1>Department Efficiency Benchmark</h1>
                    <p>Average daily software usage hours per user across different teams</p>
                </div>
            </header>

            <div class="benchmark-grid">
                @foreach ($benchmarks as $b)
                    <div class="efficiency-card">
                        @php
                            $iconClass = 'icon-arch';
                            $icon = 'fa-building';
                            if (str_contains($b['department'], 'MEP')) {
                                $iconClass = 'icon-mep';
                                $icon = 'fa-bolt';
                            } elseif (str_contains($b['department'], 'Struc')) {
                                $iconClass = 'icon-struc';
                                $icon = 'fa-hard-hat';
                            } elseif (str_contains($b['department'], 'Infra')) {
                                $iconClass = 'icon-infra';
                                $icon = 'fa-road';
                            } elseif (str_contains($b['department'], 'Viz')) {
                                $iconClass = 'icon-viz';
                                $icon = 'fa-palette';
                            }
                        @endphp
                        <div class="dept-icon {{ $iconClass }}"><i class="fas {{ $icon }}"></i></div>
                        <div
                            style="font-size: 14px; font-weight: 600; color: var(--text-secondary); text-transform: uppercase;">
                            {{ $b['department'] }}</div>
                        <div class="value-display">
                            {{ $b['avg_hours'] }}
                            <span class="value-unit">h / day</span>
                        </div>
                        <div style="font-size: 12px; color: var(--text-muted);">
                            Based on {{ $b['user_count'] }} users · {{ $b['total_hours'] }} total hrs
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="chart-container">
                <canvas id="efficiencyChart"></canvas>
            </div>
        </main>
    </div>

    <script>
        const ctx = document.getElementById('efficiencyChart').getContext('2d');
        const labels = {!! $chartLabels !!};
        const data = {!! $chartData !!};

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Avg Hours / User / Day',
                    data: data,
                    backgroundColor: [
                        'rgba(59, 130, 246, 0.6)',
                        'rgba(16, 185, 129, 0.6)',
                        'rgba(139, 92, 246, 0.6)',
                        'rgba(245, 158, 11, 0.6)',
                        'rgba(236, 72, 153, 0.6)'
                    ],
                    borderColor: [
                        '#3b82f6', '#10b981', '#8b5cf6', '#f59e0b', '#ec4899'
                    ],
                    borderWidth: 2,
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: '#1e293b',
                        titleFont: {
                            family: 'Outfit',
                            size: 14
                        },
                        bodyFont: {
                            family: 'Outfit',
                            size: 13
                        },
                        padding: 12,
                        cornerRadius: 10
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(255, 255, 255, 0.05)'
                        },
                        ticks: {
                            color: '#94a3b8',
                            font: {
                                family: 'Outfit'
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            color: '#94a3b8',
                            font: {
                                family: 'Outfit'
                            }
                        }
                    }
                }
            }
        });

        // Sidebar toggle
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebar = document.querySelector('.sidebar');
        sidebarToggle.addEventListener('click', () => {
            sidebar.classList.toggle('collapsed');
        });

        // Theme toggle
        const themeToggle = document.getElementById('themeToggle');
        const html = document.documentElement;
        themeToggle.addEventListener('click', () => {
            const newTheme = html.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
            html.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
        });
    </script>
</body>

</html>
