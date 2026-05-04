<!DOCTYPE html>
<html lang="en-GB" data-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ACLM | License Optimization</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
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

        /* Rating Color System */
        .rating-ghost {
            --rc: #64748b;
            --rbg: rgba(100, 116, 139, 0.12);
            --rborder: rgba(100, 116, 139, 0.3);
        }

        .rating-critical {
            --rc: #ef4444;
            --rbg: rgba(239, 68, 68, 0.08);
            --rborder: rgba(239, 68, 68, 0.3);
        }

        .rating-warning {
            --rc: #f59e0b;
            --rbg: rgba(245, 158, 11, 0.08);
            --rborder: rgba(245, 158, 11, 0.3);
        }

        .rating-justified {
            --rc: #10b981;
            --rbg: rgba(16, 185, 129, 0.08);
            --rborder: rgba(16, 185, 129, 0.3);
        }

        .opt-card {
            background: var(--card-bg);
            border: 1px solid var(--rborder, var(--border-color));
            border-radius: 18px;
            padding: 24px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .opt-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: var(--rc, var(--border-color));
        }

        .opt-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 28px rgba(0, 0, 0, 0.2);
        }

        .rating-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 14px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            background: var(--rbg);
            color: var(--rc);
        }

        .app-tag-used {
            display: inline-block;
            background: rgba(99, 102, 241, 0.1);
            color: #818cf8;
            padding: 3px 10px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 600;
            margin: 3px 3px 0 0;
        }

        .app-tag-unused {
            display: inline-block;
            background: rgba(100, 116, 139, 0.08);
            color: #64748b;
            padding: 3px 10px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 500;
            margin: 3px 3px 0 0;
            text-decoration: line-through;
        }

        .recommendation-box {
            margin-top: 16px;
            padding: 12px 16px;
            border-radius: 12px;
            background: var(--rbg);
            border: 1px dashed var(--rborder);
            font-size: 13px;
            color: var(--rc);
            font-weight: 600;
        }

        .period-btn {
            padding: 8px 18px;
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
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.12);
        }

        .legend-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 6px;
        }

        .opt-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(420px, 1fr));
            gap: 22px;
        }

        .usage-bar-track {
            background: rgba(255, 255, 255, 0.06);
            height: 6px;
            border-radius: 99px;
            margin-top: 6px;
            overflow: hidden;
        }

        .usage-bar-fill {
            height: 100%;
            border-radius: 99px;
            background: var(--rc);
            transition: width 0.6s ease;
        }
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
                    <a href="{{ route('license.optimization') }}" class="nav-link active">
                        <i class="fas fa-lightbulb"></i><span>License Optimization</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('department.efficiency') }}" class="nav-link">
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
            @include('partials.license_sidebar_widget')

            <form action="{{ route('logout') }}" method="POST" style="margin-top:15px;">
                @csrf
                <button type="submit"
                    style="width:100%;padding:8px;background:rgba(239,68,68,0.1);color:#ef4444;border:1px solid rgba(239,68,68,0.2);border-radius:8px;cursor:pointer;font-size:13px;font-weight:600;display:flex;align-items:center;justify-content:center;gap:8px;">
                    <i class="fas fa-sign-out-alt"></i><span>Log Out</span>
                </button>
            </form>
        </div>
    </aside>

    <!-- Main -->
    <div class="main-wrapper">
        <header class="topbar">
            <div style="display:flex;align-items:center;gap:20px;">
                <button class="action-btn" id="sidebarToggle"><i class="fas fa-bars"></i></button>
                <!-- Period selector -->
                <div
                    style="display:flex;gap:6px;background:var(--bg-color);padding:4px;border-radius:12px;border:1px solid var(--border-color);">
                    <button onclick="window.location.href='?days=30'"
                        class="period-btn {{ $days == 30 ? 'active' : '' }}">30 Days</button>
                    <button onclick="window.location.href='?days=60'"
                        class="period-btn {{ $days == 60 ? 'active' : '' }}">60 Days</button>
                    <button onclick="window.location.href='?days=90'"
                        class="period-btn {{ $days == 90 ? 'active' : '' }}">90 Days</button>
                </div>
            </div>
            <div class="topbar-actions">
                <button class="action-btn theme-toggle-btn" id="themeToggle"><i class="fas fa-moon"></i><i
                        class="fas fa-sun"></i></button>
            </div>
        </header>

        @include('partials.license_status_banner')

        <main class="content-area">
            <!-- Header -->
            <header class="page-header" style="margin-bottom:24px;">
                <div class="page-title">
                    <h1>License Optimization</h1>
                    <p>Identify users with multiple assigned software but limited actual usage — and recommend a leaner
                        license set</p>
                </div>
            </header>

            <!-- Legend -->
            <div class="card"
                style="padding:18px 24px;margin-bottom:28px;display:flex;align-items:center;gap:30px;flex-wrap:wrap;">
                <span
                    style="font-size:12px;font-weight:700;color:var(--text-secondary);text-transform:uppercase;">Optimization
                    Signal:</span>
                <span style="font-size:13px;display:flex;align-items:center;"><span class="legend-dot"
                        style="background:#64748b;"></span> <strong style="color:#64748b;">No Usage</strong> &nbsp;—
                    User opened zero apps in the period</span>
                <span style="font-size:13px;display:flex;align-items:center;"><span class="legend-dot"
                        style="background:#ef4444;"></span> <strong style="color:#ef4444;">Critical</strong> &nbsp;—
                    Only 1–2 apps used</span>
                <span style="font-size:13px;display:flex;align-items:center;"><span class="legend-dot"
                        style="background:#f59e0b;"></span> <strong style="color:#f59e0b;">Review</strong> &nbsp;— 3–4
                    apps used</span>
                <span style="font-size:13px;display:flex;align-items:center;"><span class="legend-dot"
                        style="background:#10b981;"></span> <strong style="color:#10b981;">Justified</strong> &nbsp;—
                    5+ apps used, bundle is cost-effective</span>
            </div>

            <!-- Summary Stats -->
            @php
                $ghostCount = collect($results)->where('rating', 'ghost')->count();
                $criticalCount = collect($results)->where('rating', 'critical')->count();
                $warningCount = collect($results)->where('rating', 'warning')->count();
                $justifiedCount = collect($results)->where('rating', 'justified')->count();
                $totalUsers = count($results);
            @endphp
            <div class="metrics-grid" style="grid-template-columns:repeat(4,1fr);margin-bottom:28px;">
                <div class="metric-card">
                    <div class="metric-header">
                        <div class="metric-icon" style="background:rgba(100,116,139,0.1);color:#64748b;"><i
                                class="fas fa-user-slash"></i></div>
                    </div>
                    <div class="metric-value" style="color:#64748b;">{{ $ghostCount }}</div>
                    <div class="metric-label">No Usage</div>
                    <p class="metric-desc">Users with zero activity</p>
                </div>
                <div class="metric-card">
                    <div class="metric-header">
                        <div class="metric-icon icon-red" style="background:rgba(239,68,68,0.1);color:#ef4444;"><i
                                class="fas fa-exclamation-circle"></i></div>
                    </div>
                    <div class="metric-value" style="color:#ef4444;">{{ $criticalCount }}</div>
                    <div class="metric-label">Critical Waste</div>
                    <p class="metric-desc">Only 1–2 apps used</p>
                </div>
                <div class="metric-card">
                    <div class="metric-header">
                        <div class="metric-icon" style="background:rgba(245,158,11,0.1);color:#f59e0b;"><i
                                class="fas fa-exclamation-triangle"></i></div>
                    </div>
                    <div class="metric-value" style="color:#f59e0b;">{{ $warningCount }}</div>
                    <div class="metric-label">Review Needed</div>
                    <p class="metric-desc">3–4 apps used</p>
                </div>
                <div class="metric-card">
                    <div class="metric-header">
                        <div class="metric-icon" style="background:rgba(16,185,129,0.1);color:#10b981;"><i
                                class="fas fa-check-circle"></i></div>
                    </div>
                    <div class="metric-value" style="color:#10b981;">{{ $justifiedCount }}</div>
                    <div class="metric-label">Justified</div>
                    <p class="metric-desc">5+ apps used</p>
                </div>
            </div>

            <!-- Cards -->
            @if (empty($results))
                <div class="card" style="padding:60px;text-align:center;color:var(--text-secondary);">
                    <i class="fas fa-box-open" style="font-size:48px;margin-bottom:16px;opacity:0.3;"></i>
                    <p>No users with 2+ software assignments found. Assign licenses from the Users section first.</p>
                </div>
            @else
                <div class="opt-grid">
                    @foreach ($results as $r)
                        @php
                            $rClass = 'rating-' . $r['rating'];
                            $rLabel = match ($r['rating']) {
                                'ghost' => 'No Usage',
                                'critical' => 'Critical Waste',
                                'warning' => 'Review',
                                'justified' => 'Justified',
                            };
                            $rIcon = match ($r['rating']) {
                                'ghost' => 'fa-ghost',
                                'critical' => 'fa-exclamation-circle',
                                'warning' => 'fa-exclamation-triangle',
                                'justified' => 'fa-check-circle',
                            };
                            $barPct =
                                $r['assigned_count'] > 0 ? round(($r['used_count'] / $r['assigned_count']) * 100) : 0;
                        @endphp
                        <div class="opt-card {{ $rClass }}">
                            <!-- Header row -->
                            <div
                                style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:12px;">
                                <div>
                                    <div style="font-size:17px;font-weight:700;color:var(--text-primary);">
                                        {{ $r['user_name'] }}</div>
                                    <div style="font-size:12px;color:var(--text-secondary);margin-top:2px;">
                                        <span
                                            style="background:rgba(255,255,255,0.05);padding:2px 6px;border-radius:4px;font-size:10px;text-transform:uppercase;font-weight:700;margin-right:8px;border:1px solid rgba(255,255,255,0.1);">
                                            {{ $r['is_bundle'] ? 'Collection/Bundle' : 'Standalone' }}
                                        </span>
                                        <i class="fas fa-sitemap"
                                            style="margin-right:4px;"></i>{{ $r['department'] }}
                                    </div>
                                </div>
                                <span class="rating-badge">
                                    <i class="fas {{ $rIcon }}"></i> {{ $rLabel }}
                                </span>
                            </div>

                            <!-- Assignment list (Small) -->
                            <div
                                style="font-size:11px;color:var(--text-muted);margin-bottom:12px;background:rgba(0,0,0,0.1);padding:6px 10px;border-radius:8px;">
                                <span style="font-weight:700;text-transform:uppercase;">Assigned:</span>
                                {{ implode(', ', $r['assigned_list']) }}
                            </div>

                            <!-- Usage bar -->
                            <div
                                style="display:flex;justify-content:space-between;font-size:12px;color:var(--text-secondary);">
                                <span>Apps used: <strong style="color:var(--text-primary);">{{ $r['used_count'] }} of
                                        {{ $r['assigned_count'] }}</strong></span>
                                <span>{{ $barPct }}% utilised</span>
                            </div>
                            <div class="usage-bar-track">
                                <div class="usage-bar-fill" style="width:{{ $barPct }}%;"></div>
                            </div>

                            <!-- Apps used -->
                            @if (!empty($r['used_apps']))
                                <div style="margin-top:14px;">
                                    <div
                                        style="font-size:11px;font-weight:700;color:var(--text-secondary);text-transform:uppercase;margin-bottom:6px;">
                                        Actually Used</div>
                                    @foreach ($r['used_apps'] as $app)
                                        <span class="app-tag-used">{{ $app }}</span>
                                    @endforeach
                                </div>
                            @endif

                            <!-- Recommendation -->
                            @if ($r['rating'] === 'ghost')
                                <div class="recommendation-box">
                                    <i class="fas fa-ban" style="margin-right:8px;"></i>
                                    No activity detected in {{ $days }} days. Consider removing all assigned
                                    licenses.
                                </div>
                            @elseif($r['rating'] === 'critical' && !empty($r['used_apps']))
                                <div class="recommendation-box">
                                    <i class="fas fa-lightbulb" style="margin-right:8px;"></i>
                                    Replace with individual license(s) only:
                                    <strong>{{ implode(' + ', $r['used_apps']) }}</strong>
                                </div>
                            @elseif($r['rating'] === 'warning' && !empty($r['used_apps']))
                                <div class="recommendation-box">
                                    <i class="fas fa-search" style="margin-right:8px;"></i>
                                    Review: {{ count($r['unused_apps']) }} assigned app(s) were never opened in
                                    {{ $days }} days.
                                </div>
                            @elseif($r['rating'] === 'justified')
                                <div class="recommendation-box">
                                    <i class="fas fa-check" style="margin-right:8px;"></i>
                                    Bundle is well-utilised. No change recommended.
                                </div>
                            @endif

                            <!-- Profile link -->
                            <div style="margin-top:16px;text-align:right;">
                                <a href="{{ route('profile', $r['user_name']) }}"
                                    style="font-size:12px;font-weight:600;color:var(--accent-color);text-decoration:none;">
                                    <i class="fas fa-user-circle" style="margin-right:4px;"></i> View Profile
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

        </main>
    </div>

    <script>
        document.getElementById('sidebarToggle').addEventListener('click', () => {
            document.querySelector('.sidebar').classList.toggle('collapsed');
        });
        document.getElementById('themeToggle').addEventListener('click', () => {
            const html = document.documentElement;
            const t = html.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
            html.setAttribute('data-theme', t);
            localStorage.setItem('theme', t);
        });
    </script>
</body>

</html>
