<!DOCTYPE html>
<html lang="en-GB" data-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ASCLAM | Report Hub</title>
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

        /* Hub layout */
        .hub-grid {
            display: grid;
            grid-template-columns: 1fr 1.4fr;
            gap: 30px;
            margin-top: 30px;
        }

        .config-card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 24px;
            padding: 36px;
        }

        .config-card h2 {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 6px;
            color: var(--text-primary);
        }

        .config-card p.sub {
            font-size: 13px;
            color: var(--text-secondary);
            margin-bottom: 28px;
        }

        .form-group-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 16px;
        }

        .field-label {
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            color: var(--text-secondary);
            margin-bottom: 8px;
            display: block;
        }

        .field-input {
            width: 100%;
            background: var(--bg-color);
            border: 1px solid var(--border-color);
            color: var(--text-primary);
            padding: 12px 16px;
            border-radius: 12px;
            font-size: 14px;
            font-family: 'Outfit', sans-serif;
            outline: none;
            transition: border-color 0.2s;
            box-sizing: border-box;
        }

        .field-input:focus {
            border-color: #3b82f6;
        }

        .generate-btn {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #3b82f6, #6366f1);
            color: white;
            border: none;
            border-radius: 14px;
            font-size: 15px;
            font-weight: 700;
            font-family: 'Outfit', sans-serif;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-top: 24px;
            transition: all 0.3s;
            box-shadow: 0 6px 20px rgba(59, 130, 246, 0.35);
        }

        .generate-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 28px rgba(59, 130, 246, 0.5);
        }

        /* Preview panel */
        .preview-panel {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 24px;
            padding: 36px;
        }

        .preview-panel h2 {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 6px;
            color: var(--text-primary);
        }

        .preview-panel p.sub {
            font-size: 13px;
            color: var(--text-secondary);
            margin-bottom: 24px;
        }

        .section-item {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 14px 18px;
            border-radius: 14px;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--border-color);
            margin-bottom: 10px;
            transition: all 0.2s;
        }

        .section-item:hover {
            border-color: #3b82f6;
            background: rgba(59, 130, 246, 0.05);
        }

        .section-item-icon {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            flex-shrink: 0;
        }

        .section-item h4 {
            font-size: 14px;
            font-weight: 600;
            color: var(--text-primary);
            margin: 0;
        }

        .section-item p {
            font-size: 12px;
            color: var(--text-secondary);
            margin: 2px 0 0 0;
        }

        .tip-box {
            background: rgba(59, 130, 246, 0.07);
            border: 1px solid rgba(59, 130, 246, 0.2);
            border-radius: 14px;
            padding: 16px 20px;
            margin-top: 18px;
            font-size: 13px;
            color: #93c5fd;
            display: flex;
            gap: 12px;
            align-items: flex-start;
        }

        .tip-box i {
            margin-top: 2px;
            flex-shrink: 0;
        }
    </style>
</head>

<body>
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="logo-container">
            <div class="logo-icon"><i class="fas fa-compass-drafting"></i></div>
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
                <a href="{{ route('report.hub') }}" class="nav-link active">
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
                    <h2 style="font-size:18px;font-weight:700;margin:0;">Export Report</h2>
                    <p style="font-size:12px;color:var(--text-secondary);margin:0;">Generate a visual PDF/presentation
                        report</p>
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
            <header class="page-header" style="margin-bottom: 0;">
                <div class="page-title">
                    <h1>Report Hub</h1>
                    <p>Configure your report parameters and generate a printable PDF or screen presentation.</p>
                </div>
            </header>

            @if (session('error'))
                <div
                    style="background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.3);border-radius:12px;padding:14px 20px;margin-top:20px;color:#ef4444;font-size:14px;">
                    <i class="fas fa-exclamation-triangle" style="margin-right:8px;"></i>{{ session('error') }}
                </div>
            @endif

            <div class="hub-grid">
                <!-- Config Form -->
                <div class="config-card">
                    <h2><i class="fas fa-sliders-h" style="color:#3b82f6;margin-right:10px;"></i>Report Configuration
                    </h2>
                    <p class="sub">Choose the date range and filters for your report.</p>

                    <form action="{{ route('report.generate') }}" method="GET" target="_blank">
                        <div class="form-group-row">
                            <div>
                                <label class="field-label">Time Period</label>
                                <select name="days" class="field-input">
                                    <option value="7">Last 7 Days</option>
                                    <option value="30" selected>Last 30 Days</option>
                                    <option value="60">Last 60 Days</option>
                                    <option value="90">Last 90 Days</option>
                                </select>
                            </div>
                            <div>
                                <label class="field-label">Department Filter</label>
                                @if (auth()->user()->role === 'team_leader')
                                    <input type="text" class="field-input"
                                        value="{{ auth()->user()->department ?: 'Unassigned' }}" readonly
                                        style="background:rgba(255,255,255,0.05);cursor:not-allowed;">
                                    <input type="hidden" name="department"
                                        value="{{ auth()->user()->department ?: 'unassigned' }}">
                                @else
                                    <select name="department" class="field-input">
                                        <option value="all">All Departments</option>
                                        <option value="Architecture">Architecture</option>
                                        <option value="MEP">MEP</option>
                                        <option value="Structural">Structural</option>
                                        <option value="Infrastructure">Infrastructure</option>
                                        <option value="Visualization">Visualization</option>
                                    </select>
                                @endif
                            </div>
                        </div>

                        <div style="margin-top: 8px;">
                            <label class="field-label">Report Title (Optional)</label>
                            <input type="text" name="title" class="field-input"
                                placeholder="e.g. Q1 Software Utilization Report">
                        </div>

                        <div style="margin-top:16px;">
                            <label class="field-label" style="margin-bottom:12px;display:block;">Include
                                Sections</label>
                            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                                @foreach ([['kpi', 'KPI Summary', 'fa-chart-bar'], ['app_usage', 'App Usage', 'fa-layer-group'], ['dept', 'Dept. Efficiency', 'fa-building'], ['top_users', 'Top Performers', 'fa-medal'], ['license', 'License Health', 'fa-key'], ['ghost', 'Ghost Machines', 'fa-ghost'], ['trend', 'Usage Trend Chart', 'fa-chart-line']] as [$val, $label, $icon])
                                    <label
                                        style="display:flex;align-items:center;gap:10px;padding:10px 14px;border-radius:10px;border:1px solid var(--border-color);cursor:pointer;font-size:13px;font-weight:600;color:var(--text-primary);background:rgba(255,255,255,0.02);">
                                        <input type="checkbox" name="sections[]" value="{{ $val }}" checked
                                            style="accent-color:#3b82f6;width:16px;height:16px;">
                                        <i class="fas {{ $icon }}"
                                            style="color:#3b82f6;width:16px;text-align:center;"></i>
                                        {{ $label }}
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <button type="submit" class="generate-btn">
                            <i class="fas fa-file-pdf"></i>
                            Generate Report
                        </button>
                    </form>
                </div>

                <!-- Preview Guide -->
                <div class="preview-panel">
                    <h2><i class="fas fa-eye" style="color:#6366f1;margin-right:10px;"></i>What's Included</h2>
                    <p class="sub">Your report will be generated as a full-page visual document, ready to print or
                        save as PDF.</p>

                    @foreach ([['fa-chart-bar', 'rgba(59,130,246,0.1)', '#3b82f6', 'KPI Summary', 'Online users, total hours worked, active users, unique apps used.'], ['fa-chart-line', 'rgba(16,185,129,0.1)', '#10b981', 'Usage Trend', 'Day-by-day total usage hours chart for the selected period.'], ['fa-layer-group', 'rgba(139,92,246,0.1)', '#8b5cf6', 'Application Usage', 'Horizontal bar chart of top Autodesk apps by hours used.'], ['fa-building', 'rgba(245,158,11,0.1)', '#f59e0b', 'Dept. Efficiency', 'Average daily hours per user per department, ranked.'], ['fa-medal', 'rgba(236,72,153,0.1)', '#ec4899', 'Top Performers', 'Top 10 users ranked by total software usage hours.'], ['fa-key', 'rgba(99,102,241,0.1)', '#818cf8', 'License Health', 'Breakdown by rating: Justified, Warning, Critical, Ghost.'], ['fa-ghost', 'rgba(239,68,68,0.1)', '#ef4444', 'Ghost Machines', 'Machines with no heartbeat in the last 30 days.']] as [$icon, $bg, $color, $title, $desc])
                        <div class="section-item">
                            <div class="section-item-icon"
                                style="background:{{ $bg }};color:{{ $color }};">
                                <i class="fas {{ $icon }}"></i>
                            </div>
                            <div>
                                <h4>{{ $title }}</h4>
                                <p>{{ $desc }}</p>
                            </div>
                        </div>
                    @endforeach

                    <div class="tip-box">
                        <i class="fas fa-info-circle"></i>
                        <div>
                            <strong>How to save as PDF:</strong> After the report opens, press <kbd
                                style="background:rgba(255,255,255,0.1);padding:2px 7px;border-radius:5px;font-family:monospace;">Ctrl+P</kbd>
                            (or <kbd
                                style="background:rgba(255,255,255,0.1);padding:2px 7px;border-radius:5px;font-family:monospace;">⌘+P</kbd>
                            on Mac), then choose <em>"Save as PDF"</em> as the destination. Use
                            <strong>Landscape</strong> orientation for best results.
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Theme
        const themeToggle = document.getElementById('themeToggle');
        const html = document.documentElement;
        themeToggle.addEventListener('click', () => {
            const t = html.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
            html.setAttribute('data-theme', t);
            localStorage.setItem('theme', t);
        });
        // Sidebar
        document.getElementById('sidebarToggle').addEventListener('click', () => {
            document.querySelector('.sidebar').classList.toggle('collapsed');
        });
    </script>
</body>

</html>
