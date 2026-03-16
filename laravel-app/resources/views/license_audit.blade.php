<!DOCTYPE html>
<html lang="en" data-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ArchEng Pro | License Audit — Assigned vs Used</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
                <a href="{{ route('license.audit') }}" class="nav-link active">
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

    <!-- Main -->
    <div class="main-wrapper">
        <!-- Topbar -->
        <header class="topbar">
            <div style="display: flex; align-items: center; gap: 20px;">
                <button class="action-btn" id="sidebarToggle" title="Toggle Sidebar">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="search-container">
                    <i class="fas fa-search"></i>
                    <input type="text" class="search-input" placeholder="Search users or machines...">
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

        <!-- Content -->
        <main class="content-area">
            <!-- Page Header -->
            <header class="page-header"
                style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 30px;">
                <div class="page-title">
                    <h1>License Audit — Assigned vs Actual Usage</h1>
                    <p>Compare assigned software licenses against real usage data to find <strong>unused
                            licenses</strong></p>
                </div>
                <div style="display: flex; gap: 12px;">
                    <a href="{{ route('license.audit.export') }}?software={{ $selectedApp }}&from={{ $startDate }}&to={{ $endDate }}&department={{ $selectedDept }}"
                        class="btn-primary" style="text-decoration: none; background: #6366f1;">
                        <i class="fas fa-file-export"></i>
                        <span>Export CSV</span>
                    </a>
                    <a href="{{ route('users') }}" class="btn-primary" style="text-decoration: none;">
                        <i class="fas fa-user-cog"></i>
                        <span>Manage Assignments</span>
                    </a>
                </div>
            </header>

            <!-- Filter Card -->
            <div class="card" style="padding: 28px; margin-bottom: 24px;">
                <form action="{{ route('license.audit') }}" method="GET"
                    style="display: flex; align-items: flex-end; gap: 20px; flex-wrap: wrap;">

                    <!-- Software Dropdown -->
                    <div style="display: flex; flex-direction: column; gap: 6px; flex: 1; min-width: 180px;">
                        <label
                            style="font-size: 12px; font-weight: 600; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 1px;">
                            <i class="fas fa-desktop" style="margin-right: 6px;"></i> Product
                        </label>
                        <select name="software"
                            style="background: var(--bg-color); border: 1px solid var(--border-color); color: var(--text-primary); padding: 12px 16px; border-radius: 12px; font-size: 14px; outline: none; font-family: 'Outfit', sans-serif; cursor: pointer;">
                            <option value="all" {{ $selectedApp === 'all' ? 'selected' : '' }}>All Software
                            </option>
                            @foreach ($softwareList as $sw)
                                <option value="{{ $sw }}" {{ $selectedApp === $sw ? 'selected' : '' }}>
                                    {{ $sw }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Department Dropdown -->
                    <div style="display: flex; flex-direction: column; gap: 6px; flex: 1; min-width: 180px;">
                        <label
                            style="font-size: 12px; font-weight: 600; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 1px;">
                            <i class="fas fa-users" style="margin-right: 6px;"></i> Team / Dept
                        </label>
                        <select name="department"
                            style="background: var(--bg-color); border: 1px solid var(--border-color); color: var(--text-primary); padding: 12px 16px; border-radius: 12px; font-size: 14px; outline: none; font-family: 'Outfit', sans-serif; cursor: pointer;"
                            {{ auth()->user()->role !== 'admin' ? 'disabled' : '' }}>
                            @if (auth()->user()->role === 'admin')
                                <option value="all" {{ $selectedDept === 'all' ? 'selected' : '' }}>All Teams
                                </option>
                                @foreach ($deptList as $dept)
                                    <option value="{{ $dept }}"
                                        {{ $selectedDept === $dept ? 'selected' : '' }}>
                                        {{ $dept }}</option>
                                @endforeach
                            @else
                                <option value="{{ auth()->user()->department }}" selected>
                                    {{ auth()->user()->department }}</option>
                            @endif
                        </select>
                    </div>

                    <!-- Date From -->
                    <div style="display: flex; flex-direction: column; gap: 6px;">
                        <label
                            style="font-size: 12px; font-weight: 600; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 1px;">
                            <i class="fas fa-calendar-alt" style="margin-right: 6px;"></i> From Date
                        </label>
                        <input type="date" name="from" value="{{ $startDate }}"
                            style="background: var(--bg-color); border: 1px solid var(--border-color); color: var(--text-primary); padding: 12px 16px; border-radius: 12px; font-size: 14px; outline: none; font-family: 'Outfit', sans-serif;">
                    </div>

                    <!-- Date To -->
                    <div style="display: flex; flex-direction: column; gap: 6px;">
                        <label
                            style="font-size: 12px; font-weight: 600; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 1px;">
                            <i class="fas fa-calendar-check" style="margin-right: 6px;"></i> To Date
                        </label>
                        <input type="date" name="to" value="{{ $endDate }}"
                            style="background: var(--bg-color); border: 1px solid var(--border-color); color: var(--text-primary); padding: 12px 16px; border-radius: 12px; font-size: 14px; outline: none; font-family: 'Outfit', sans-serif;">
                    </div>

                    <!-- Search Button -->
                    <button type="submit" class="btn-primary"
                        style="padding: 12px 28px; border-radius: 12px; white-space: nowrap;">
                        <i class="fas fa-search"></i>
                        <span>Run Audit</span>
                    </button>
                </form>

                <!-- Activity Presets -->
                <div
                    style="margin-top: 20px; display: flex; align-items: center; gap: 15px; padding-top: 15px; border-top: 1px dashed var(--border-color);">
                    <span
                        style="font-size: 11px; font-weight: 700; color: var(--text-secondary); text-transform: uppercase;">Quick
                        Select: Inactivity Period</span>
                    <div style="display: flex; gap: 8px;">
                        <button type="button" onclick="setPreset(30)" class="preset-btn">30 Days</button>
                        <button type="button" onclick="setPreset(60)" class="preset-btn">60 Days</button>
                        <button type="button" onclick="setPreset(90)" class="preset-btn">90 Days</button>
                    </div>
                    <style>
                        .preset-btn {
                            background: rgba(255, 255, 255, 0.05);
                            border: 1px solid var(--border-color);
                            color: var(--text-secondary);
                            padding: 6px 14px;
                            border-radius: 8px;
                            font-size: 12px;
                            font-weight: 600;
                            cursor: pointer;
                            transition: all 0.2s;
                        }

                        .preset-btn:hover {
                            background: var(--accent-color);
                            border-color: var(--accent-color);
                            color: white;
                        }
                    </style>
                    <script>
                        function setPreset(days) {
                            const toDate = new Date();
                            const fromDate = new Date();
                            fromDate.setDate(toDate.getDate() - days);

                            document.querySelector('input[name="from"]').value = fromDate.toISOString().split('T')[0];
                            document.querySelector('input[name="to"]').value = toDate.toISOString().split('T')[0];
                            document.querySelector('form[action*="license-audit"]').submit();
                        }
                    </script>
                </div>
            </div>

            <!-- Summary Badges -->
            <div class="metrics-grid" style="grid-template-columns: repeat(4, 1fr); margin-bottom: 24px;">
                <div class="metric-card">
                    <div class="metric-header">
                        <div class="metric-icon icon-blue"><i class="fas fa-id-card"></i></div>
                    </div>
                    <div class="metric-value">{{ $totalAssignments }}</div>
                    <div class="metric-label">Total License Assignments</div>
                    <p class="metric-desc">All assigned software-user pairs</p>
                </div>
                <div class="metric-card">
                    <div class="metric-header">
                        <div class="metric-icon icon-green"><i class="fas fa-check-circle"></i></div>
                    </div>
                    <div class="metric-value" style="color: #10b981;">{{ $totalUsed }}</div>
                    <div class="metric-label">Actively Used</div>
                    <p class="metric-desc">User opened the assigned software</p>
                </div>
                <div class="metric-card">
                    <div class="metric-header">
                        <div class="metric-icon" style="background: rgba(239,68,68,0.12); color: #ef4444;"><i
                                class="fas fa-exclamation-triangle"></i></div>
                    </div>
                    <div class="metric-value" style="color: #ef4444;">{{ $totalUnused }}</div>
                    <div class="metric-label">Unused Licenses</div>
                    <p class="metric-desc">Wasting money — no usage detected</p>
                </div>
                <div class="metric-card">
                    <div class="metric-header">
                        <div class="metric-icon icon-purple"><i class="fas fa-user-plus"></i></div>
                    </div>
                    <div class="metric-value">{{ $usersWithNoAssignments }}</div>
                    <div class="metric-label">Users Without Assignments</div>
                    <p class="metric-desc">Need license assignment on Users page</p>
                </div>
            </div>

            <!-- Results Table -->
            <div class="user-table-wrapper">
                <div
                    style="padding: 20px 28px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <div style="font-size: 17px; font-weight: 700;">
                            Assigned License Usage Comparison
                        </div>
                        <div style="font-size: 13px; color: var(--text-secondary); margin-top: 4px;">
                            Period: {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} →
                            {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}
                            @if ($selectedApp !== 'all')
                                | Software: <strong>{{ $selectedApp }}</strong>
                            @endif
                        </div>
                    </div>
                    @if ($totalUnused > 0)
                        <span
                            style="background: rgba(239,68,68,0.1); color: #ef4444; padding: 6px 14px; border-radius: 20px; font-size: 13px; font-weight: 600;">
                            ⚠️ {{ $totalUnused }} Unused License{{ $totalUnused > 1 ? 's' : '' }} Found
                        </span>
                    @else
                        <span
                            style="background: rgba(16,185,129,0.1); color: #10b981; padding: 6px 14px; border-radius: 20px; font-size: 13px; font-weight: 600;">
                            All assigned licenses are being used
                        </span>
                    @endif
                </div>

                @if ($totalAssignments === 0)
                    <div style="padding: 60px; text-align: center; color: var(--text-secondary);">
                        <i class="fas fa-clipboard-list"
                            style="font-size: 48px; color: var(--accent-color); margin-bottom: 16px; display: block;"></i>
                        <div
                            style="font-size: 18px; font-weight: 600; color: var(--text-primary); margin-bottom: 8px;">
                            No License Assignments Found</div>
                        <p>Go to the <strong>Users</strong> page to assign software licenses to users first.</p>
                        <a href="{{ route('users') }}" class="btn-primary"
                            style="text-decoration: none; display: inline-flex; margin-top: 16px;">
                            <i class="fas fa-user-cog"></i>
                            <span>Go to Users Page</span>
                        </a>
                    </div>
                @else
                    <table class="user-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>User Name</th>
                                <th>Team</th>
                                <th>Machine</th>
                                <th>Product</th>
                                <th>Inactivity</th>
                                <th>Status</th>
                                <th>Last Seen</th>
                                <th>Urgency</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($auditResults as $idx => $result)
                                <tr style="{{ !$result->is_used ? 'background: rgba(239,68,68,0.03);' : '' }}">
                                    <td style="color: var(--text-secondary); font-size: 13px;">{{ $idx + 1 }}
                                    </td>
                                    <td>
                                        <div class="user-info-cell">
                                            <img src="https://ui-avatars.com/api/?name={{ urlencode($result->user_name) }}&background={{ $result->is_used ? '10b981' : 'ef4444' }}&color=fff&size=80"
                                                alt="{{ $result->user_name }}" class="table-avatar">
                                            <div class="user-details">
                                                <span class="name">{{ $result->user_name }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @php
                                            $deptClass = 'dept-arch';
                                            if ($result->department == 'MEP') {
                                                $deptClass = 'dept-mep';
                                            }
                                            if ($result->department == 'Structural') {
                                                $deptClass = 'dept-struct';
                                            }
                                            if ($result->department == 'Infrastructure') {
                                                $deptClass = 'dept-infra';
                                            }
                                            if ($result->department == 'Visualization') {
                                                $deptClass = 'dept-viz';
                                            }
                                            if ($result->department == 'Unassigned') {
                                                $deptClass = '';
                                            }
                                        @endphp
                                        <span class="badge-dept {{ $deptClass }}"
                                            style="font-size: 11px; padding: 4px 10px;">{{ $result->department }}</span>
                                    </td>
                                    <td>
                                        <span class="machine-id">{{ $result->machine }}</span>
                                    </td>
                                    <td>
                                        <span
                                            style="background: rgba(139,92,246,0.1); color: #a78bfa; padding: 4px 12px; border-radius: 8px; font-size: 12px; font-weight: 600;">
                                            {{ $result->software }}
                                        </span>
                                    </td>
                                    <td>
                                        <div
                                            style="font-weight: 700; color: {{ $result->days_inactive > 30 ? '#ef4444' : 'var(--text-primary)' }};">
                                            {{ $result->days_inactive >= 999 ? 'N/A' : $result->days_inactive . ' days' }}
                                        </div>
                                    </td>
                                    <td>
                                        @if ($result->is_used)
                                            <span
                                                style="background: rgba(16,185,129,0.1); color: #10b981; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 700;">
                                                ACTUALLY USED
                                            </span>
                                        @else
                                            <span
                                                style="background: rgba(239,68,68,0.1); color: #ef4444; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 700;">
                                                NOT USED
                                            </span>
                                        @endif
                                    </td>
                                    <td style="font-size: 13px; color: var(--text-secondary);">
                                        {{ $result->last_seen }}
                                    </td>
                                    <td>
                                        @php
                                            $urgencyColor = '#10b981';
                                            if ($result->urgency === 'Critical') {
                                                $urgencyColor = '#ef4444';
                                            } elseif ($result->urgency === 'Warning') {
                                                $urgencyColor = '#f59e0b';
                                            }
                                        @endphp
                                        <span
                                            style="display: inline-flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 700; color: {{ $urgencyColor }};">
                                            <i class="fas fa-circle" style="font-size: 8px;"></i>
                                            {{ $result->urgency }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="actions-cell" style="opacity: 1; display: flex; gap: 8px;">
                                            <a href="{{ route('profile', $result->user_name) }}"
                                                class="action-icon action-edit" title="View Full Profile">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('users') }}?search={{ $result->user_name }}"
                                                class="action-icon"
                                                style="background: rgba(99, 102, 241, 0.1); color: #6366f1;"
                                                title="Manage Licenses">
                                                <i class="fas fa-id-card"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>

        </main>
    </div>

    <script>
        const themeToggle = document.getElementById('themeToggle');
        const html = document.documentElement;
        const savedTheme = localStorage.getItem('theme') || 'dark';
        html.setAttribute('data-theme', savedTheme);

        themeToggle.addEventListener('click', () => {
            const newTheme = html.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
            html.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
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
