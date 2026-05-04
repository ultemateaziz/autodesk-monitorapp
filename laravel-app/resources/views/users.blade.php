<!DOCTYPE html>
<html lang="en-GB" data-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ACLM | User Management</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=JetBrains+Mono&display=swap"
        rel="stylesheet">

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

        /* Department Dropdown Styles */
        .dept-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            /* Nice rounded pill shape */
            font-size: 12px;
            font-weight: 600;
            overflow: hidden;
            border: 1px solid transparent;
            /* Takes color from specific classes like dept-mep */
        }

        .dept-badge select {
            background: transparent;
            border: none;
            color: inherit;
            font-size: inherit;
            font-family: inherit;
            cursor: pointer;
            outline: none;
            padding-right: 15px;
            margin: -6px -12px;
            padding: 6px 20px 6px 12px;
            /* Expand hit area to cover badge */
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
        }

        .dept-badge select option {
            background-color: var(--card-bg);
            /* Match popup to theme */
            color: var(--text-primary);
            font-weight: 500;
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
                <a href="{{ route('users') }}" class="nav-link active">
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
                    <input type="text" class="search-input" placeholder="Search users or machine ID...">
                </div>
            </div>

            <div class="topbar-actions">
                <div class="top-actions">
                    <button class="btn-primary" id="openAddUserModal">
                        <i class="fas fa-plus"></i>
                        <span>Add New User</span>
                    </button>
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
                                            <button type="submit" class="btn-clear-notif"
                                                title="Clear Notification">
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
                    <!-- Profile Avatar -->
                    <div class="topbar-user"
                        style="display: flex; align-items: center; gap: 12px; margin-left: 10px; cursor: pointer;">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&background=3b82f6&color=fff"
                            alt="Avatar"
                            style="width: 36px; height: 36px; border-radius: 50%; border: 2px solid var(--accent-color);">
                    </div>
                </div>
            </div>
        </header>

        {{-- License status banner / full block overlay --}}
        @include('partials.license_status_banner')

        <!-- Content Area -->
        <main class="content-area">
            <header class="page-header">
                <div class="page-title">
                    <h1>User Management</h1>
                    <p>Manage enterprise access control and assigned machinery</p>
                </div>
            </header>

            <!-- Metrics Top Row -->
            <div class="metrics-grid">
                <!-- Card 1: Active Now -->
                <div class="metric-card card-floating">
                    <div class="metric-header">
                        <div class="metric-icon icon-blue">
                            <i class="fas fa-user-check"></i>
                        </div>
                    </div>
                    <div class="metric-value">{{ count(array_filter($users, fn($u) => $u->is_online)) }}</div>
                    <div class="metric-label">Active Now</div>
                    <p class="metric-desc">Active in last 5 min</p>
                </div>

                <!-- Card 1b: Idle Now -->
                <div class="metric-card card-floating" style="animation-delay: 0.1s; border-left: 3px solid rgba(245,158,11,0.5);">
                    <div class="metric-header">
                        <div class="metric-icon" style="background: rgba(245,158,11,0.1); color: #f59e0b;">
                            <i class="fas fa-hourglass-half"></i>
                        </div>
                    </div>
                    <div class="metric-value" style="color:#f59e0b;">{{ count(array_filter($users, fn($u) => $u->is_idle)) }}</div>
                    <div class="metric-label">Idle Now</div>
                    <p class="metric-desc">No input &gt; 1 hour</p>
                </div>

                <!-- Card 2: Total Tracked -->
                <div class="metric-card card-floating" style="animation-delay: 0.2s;">
                    <div class="metric-header">
                        <div class="metric-icon icon-green">
                            <i class="fas fa-desktop"></i>
                        </div>
                    </div>
                    <div class="metric-value">{{ count($users) }}</div>
                    <div class="metric-label">Tracked Users</div>
                    <p class="metric-desc">Users with logged activity</p>
                </div>
            </div>

            <!-- Data Table Section -->
            <div class="user-table-wrapper">
                <table class="user-table">
                    <thead>
                        <tr>
                            <th>User Info</th>
                            <th>Team / Dept</th>
                            <th>Today's Activity</th>
                            <th>Machine ID</th>
                            <th>Used Software</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $user)
                            <tr>
                                <td>
                                    <div class="user-info-cell">
                                        <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=3b82f6&color=fff"
                                            alt="Avatar" class="table-avatar">
                                        <div class="user-details">
                                            <span class="name">{{ $user->display_name ?? $user->name }}</span>
                                            <span class="email" style="opacity: 0.7;">{{ $user->last_app }}</span>
                                            @if ($user->display_name)
                                                <span
                                                    style="font-size: 10px; color: var(--text-muted); font-family: monospace;">ID:
                                                    {{ $user->name }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @php
                                        $deptClass = 'dept-arch';
                                        if ($user->department == 'MEP') {
                                            $deptClass = 'dept-mep';
                                        }
                                        if ($user->department == 'Structural') {
                                            $deptClass = 'dept-struct';
                                        }
                                        if ($user->department == 'Infrastructure') {
                                            $deptClass = 'dept-infra';
                                        }
                                        if ($user->department == 'Visualization') {
                                            $deptClass = 'dept-viz';
                                        }
                                    @endphp
                                    @if (auth()->user()->role === 'admin')
                                        <form action="{{ route('user.update-profile') }}" method="POST"
                                            class="dept-form">
                                            @csrf
                                            <input type="hidden" name="user_name" value="{{ $user->name }}">
                                            <div class="dept-badge {{ $deptClass }}">
                                                <select name="department" onchange="this.form.submit()">
                                                    @foreach ($deptList as $d)
                                                        <option value="{{ $d }}"
                                                            {{ $user->department == $d ? 'selected' : '' }}>
                                                            {{ $d }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </form>
                                    @else
                                        <div class="dept-badge {{ $deptClass }}">
                                            {{ $user->department }}
                                        </div>
                                    @endif
                                </td>
                                <td><span class="activity-time"
                                        style="font-weight: 600; font-size: 13px;">{{ $user->total_time_today }}</span>
                                </td>
                                <td>
                                    <span class="machine-id">{{ $user->machine }}</span>
                                    @if ($user->ip_address)
                                        <br><span style="font-size: 11px; color: var(--text-muted, #888); font-family: 'JetBrains Mono', monospace;">
                                            <i class="fas fa-network-wired" style="font-size: 10px;"></i>
                                            {{ $user->ip_address }}
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $revokedList = $revokedMap->get($user->name, []);
                                    @endphp
                                    <div style="display: flex; flex-wrap: wrap; gap: 5px; max-width: 300px;">
                                        @forelse ($user->used_software as $sw)
                                            @php
                                                $revokeType = $revokedMap->get($user->name, [])[$sw] ?? null;
                                                $isSuspended = $revokeType === 'suspended';
                                                $isPermanent = $revokeType === 'permanent';
                                                $isRevoked   = $isSuspended || $isPermanent;
                                            @endphp

                                            @if (!$isPermanent)
                                            {{-- Show software badge for active + suspended only --}}
                                            <span style="display: inline-flex; align-items: center; gap: 4px;
                                                @if ($isSuspended)
                                                    background: rgba(245,158,11,0.1); color: #fbbf24; border: 1px solid rgba(245,158,11,0.3);
                                                @else
                                                    background: rgba(99,102,241,0.12); color: #818cf8; border: 1px solid transparent;
                                                @endif
                                                padding: 3px 8px 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; white-space: nowrap;">

                                                <i class="fas {{ $isSuspended ? 'fa-pause-circle' : 'fa-cube' }}" style="font-size: 9px;"></i>
                                                <span style="{{ $isSuspended ? 'text-decoration: line-through; opacity: 0.7;' : '' }}">{{ $sw }}</span>

                                                @if (auth()->user()->role === 'admin')
                                                    @if ($isSuspended)
                                                        {{-- Restore button --}}
                                                        <form action="{{ route('software.restore') }}" method="POST" style="margin:0;line-height:1;">
                                                            @csrf
                                                            <input type="hidden" name="user_name" value="{{ $user->name }}">
                                                            <input type="hidden" name="software_name" value="{{ $sw }}">
                                                            <button type="submit" title="Restore Access (Undo Suspend)"
                                                                style="background:none;border:none;cursor:pointer;color:#34d399;font-size:10px;padding:0 2px;line-height:1;">
                                                                <i class="fas fa-undo"></i>
                                                            </button>
                                                        </form>
                                                    @else
                                                        {{-- Suspend button --}}
                                                        <form action="{{ route('software.revoke') }}" method="POST" style="margin:0;line-height:1;">
                                                            @csrf
                                                            <input type="hidden" name="user_name" value="{{ $user->name }}">
                                                            <input type="hidden" name="software_name" value="{{ $sw }}">
                                                            <button type="submit" title="Suspend (Temporary — Restorable)"
                                                                style="background:none;border:none;cursor:pointer;color:#fbbf24;font-size:10px;padding:0 2px;line-height:1;">
                                                                <i class="fas fa-pause"></i>
                                                            </button>
                                                        </form>
                                                        {{-- Permanently Remove button --}}
                                                        <form action="{{ route('software.remove-permanent') }}" method="POST" style="margin:0;line-height:1;"
                                                            onsubmit="return confirm('Permanently remove {{ $sw }} from {{ $user->name }}?\n\nThis stops monitoring completely. Cannot be restored easily.')">
                                                            @csrf
                                                            <input type="hidden" name="user_name" value="{{ $user->name }}">
                                                            <input type="hidden" name="software_name" value="{{ $sw }}">
                                                            <button type="submit" title="Remove Permanently (No Restore)"
                                                                style="background:none;border:none;cursor:pointer;color:#f87171;font-size:10px;padding:0 2px;line-height:1;">
                                                                <i class="fas fa-trash-alt"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                @endif
                                            </span>
                                            @endif
                                        @empty
                                            <span style="font-size: 12px; color: var(--text-secondary); opacity: 0.6;">No activity recorded</span>
                                        @endforelse
                                    </div>
                                </td>
                                <td>
                                    <div class="status-cell">
                                        @if ($user->is_online)
                                            <div class="status-dot status-online"></div>
                                            <span>Online</span>
                                        @elseif ($user->is_idle)
                                            <div class="status-dot" style="background:#f59e0b;box-shadow:0 0 0 3px rgba(245,158,11,0.2);animation:idlePulse 2s ease-in-out infinite;"></div>
                                            <span style="color:#f59e0b;">⚠ Idle</span>
                                        @else
                                            <div class="status-dot status-offline"></div>
                                            <span>Offline</span>
                                        @endif
                                    </div>
                                    <div style="font-size: 10px; color: var(--text-muted); opacity: 0.8;">
                                        {{ $user->last_seen }}</div>
                                </td>
                                <td>
                                    <div class="actions-cell" style="display: flex; gap: 8px; opacity: 1;">
                                        @if (auth()->user()->role === 'admin')
                                            <button class="action-icon action-edit open-edit-profile-modal"
                                                data-username="{{ $user->name }}"
                                                data-displayname="{{ $user->display_name ?? $user->name }}"
                                                data-dept="{{ $user->department }}"
                                                data-email="{{ $user->email ?? '' }}" title="Edit Profile"
                                                style="background: none; border: none; cursor: pointer; color: var(--text-primary);">
                                                <i class="fas fa-pencil"></i>
                                            </button>
                                        @endif
                                        <a href="{{ route('profile', $user->name) }}" class="action-icon"
                                            title="View Profile">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <!-- Add User Modal -->
    <div class="modal-overlay" id="addUserModal">
        <div class="modal-container">
            <div class="modal-header">
                <h2>Add New User</h2>
                <p>Configure access control and assign machine IDs for the new staff member.</p>
            </div>

            <form id="addUserForm">
                <div class="form-group">
                    <label class="form-label">Full Name</label>
                    <input type="text" class="form-input" placeholder="e.g. Alex Rivera" required>
                </div>

                <div class="form-group" style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div>
                        <label class="form-label">Email Address</label>
                        <input type="email" class="form-input" placeholder="alex@enterprise.io" required>
                    </div>
                    <div>
                        <label class="form-label">Assigned Machine ID</label>
                        <input type="text" class="form-input" placeholder="M-AR-501" required>
                    </div>
                </div>

                <div class="form-group" style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div>
                        <label class="form-label">Role</label>
                        <select class="form-select">
                            <option value="super-admin">Super Admin</option>
                            <option value="editor" selected>Editor</option>
                            <option value="viewer">Viewer</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Department</label>
                        <select class="form-select">
                            <option value="arch">Architecture</option>
                            <option value="mep">MEP</option>
                            <option value="struct">Structural</option>
                        </select>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn-secondary" id="closeModal">Cancel</button>
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-user-plus"></i>
                        <span>Create User</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Profile Modal -->
    <div class="modal-overlay" id="editProfileModal">
        <div class="modal-container">
            <div class="modal-header" style="display: flex; justify-content: space-between; align-items: center;">
                <div class="title-with-icon" style="display: flex; align-items: center; gap: 12px;">
                    <div class="modal-icon"><i class="fas fa-user-edit"></i></div>
                    <h2 style="margin: 0;">Edit User Profile</h2>
                </div>
                <button class="close-modal" id="closeEditModal"
                    style="background: none; border: none; color: var(--text-muted); cursor: pointer; font-size: 20px; transition: color 0.3s; margin-left: auto;">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form action="{{ route('user.update-profile') }}" method="POST" id="editProfileForm">
                @csrf
                <input type="hidden" name="user_name" id="edit_user_name">
                <div class="modal-body" style="padding: 24px;">
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label
                            style="display: block; font-size: 13px; font-weight: 600; color: var(--text-secondary); margin-bottom: 8px;">Display
                            Name (Alias)</label>
                        <div class="input-with-icon" style="position: relative;">
                            <i class="fas fa-user-tag"
                                style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-muted);"></i>
                            <input type="text" name="display_name" id="edit_display_name"
                                placeholder="Enter friendly name"
                                style="width: 100%; background: var(--bg-color); border: 1px solid var(--border-color); color: var(--text-primary); padding: 12px 16px 12px 40px; border-radius: 12px; outline: none;"
                                required>
                        </div>
                        <p style="font-size: 11px; color: var(--text-secondary); margin-top: 6px; opacity: 0.8;">
                            This name will appear on the dashboard and reports instead of the machine login name.
                        </p>
                    </div>
                    <div class="form-group">
                        <label
                            style="display: block; font-size: 13px; font-weight: 600; color: var(--text-secondary); margin-bottom: 8px;">Assign
                            Team / Department</label>
                        <div class="input-with-icon" style="position: relative;">
                            <i class="fas fa-users"
                                style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-muted);"></i>
                            <select name="department" id="edit_department"
                                style="width: 100%; background: var(--bg-color); border: 1px solid var(--border-color); color: var(--text-primary); padding: 12px 16px 12px 40px; border-radius: 12px; outline: none; appearance: none; cursor: pointer;">
                                <option value="Unassigned">Unassigned</option>
                                @foreach ($deptList as $dept)
                                    <option value="{{ $dept }}">{{ $dept }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-group" style="margin-top: 20px;">
                        <label
                            style="display: block; font-size: 13px; font-weight: 600; color: var(--text-secondary); margin-bottom: 8px;">Email Address</label>
                        <div class="input-with-icon" style="position: relative;">
                            <i class="fas fa-envelope"
                                style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-muted);"></i>
                            <input type="email" name="email" id="edit_email"
                                placeholder="user@company.com"
                                style="width: 100%; background: var(--bg-color); border: 1px solid var(--border-color); color: var(--text-primary); padding: 12px 16px 12px 40px; border-radius: 12px; outline: none;">
                        </div>
                        <p style="font-size: 11px; color: var(--text-secondary); margin-top: 6px; opacity: 0.8;">
                            Used for weekly and individual performance email reports.
                        </p>
                    </div>
                </div>
                <div class="modal-footer"
                    style="padding: 24px; border-top: 1px solid var(--border-color); display: flex; justify-content: flex-end; gap: 12px; background: rgba(0,0,0,0.02);">
                    <button type="button" class="btn-secondary"
                        style="background: var(--bg-color); color: var(--text-primary); border: 1px solid var(--border-color); padding: 12px 24px; border-radius: 12px; font-weight: 600; cursor: pointer;"
                        onclick="document.getElementById('editProfileModal').classList.remove('active')">Cancel</button>
                    <button type="submit" class="btn-primary"
                        style="background: var(--accent-color); color: white; border: none; padding: 12px 24px; border-radius: 12px; font-weight: 600; cursor: pointer;">
                        <i class="fas fa-save" style="margin-right: 8px;"></i>
                        <span>Save Profile</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Theme Logic & Modal Logic -->
    <script>
        const themeToggle = document.getElementById('themeToggle');
        const html = document.documentElement;

        const savedTheme = localStorage.getItem('theme') || 'dark';
        html.setAttribute('data-theme', savedTheme);

        themeToggle.addEventListener('click', () => {
            const currentTheme = html.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
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

        // Modal Logic
        const addUserModal = document.getElementById('addUserModal');
        const openModalBtn = document.getElementById('openAddUserModal');
        const closeModalBtn = document.getElementById('closeModal');
        const addUserForm = document.getElementById('addUserForm');

        openModalBtn.addEventListener('click', () => {
            addUserModal.classList.add('active');
        });

        const hideModal = () => {
            addUserModal.classList.remove('active');
        };

        closeModalBtn.addEventListener('click', hideModal);

        addUserModal.addEventListener('click', (e) => {
            if (e.target === addUserModal) hideModal();
        });

        addUserForm.addEventListener('submit', (e) => {
            e.preventDefault();
            // Simulate creation
            const submitBtn = addUserForm.querySelector('button[type="submit"]');
            submitBtn.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> <span>Creating...</span>';

            setTimeout(() => {
                hideModal();
                submitBtn.innerHTML = '<i class="fas fa-user-plus"></i> <span>Create User</span>';
                addUserForm.reset();
                // Optional: Toast notification could be added here
            }, 1000);
        });

        // Edit Profile Modal Logic
        const editProfileModal = document.getElementById('editProfileModal');
        const editForm = document.getElementById('editProfileForm');
        const editUserNameInput = document.getElementById('edit_user_name');
        const editDisplayNameInput = document.getElementById('edit_display_name');
        const editDeptSelect = document.getElementById('edit_department');
        const editEmailInput = document.getElementById('edit_email');
        const closeEditModalBtn = document.getElementById('closeEditModal');

        document.querySelectorAll('.open-edit-profile-modal').forEach(btn => {
            btn.addEventListener('click', () => {
                const username = btn.getAttribute('data-username');
                const displayname = btn.getAttribute('data-displayname');
                const dept = btn.getAttribute('data-dept');
                const email = btn.getAttribute('data-email');

                editUserNameInput.value = username;
                editDisplayNameInput.value = displayname;
                editDeptSelect.value = dept;
                editEmailInput.value = email;

                editProfileModal.classList.add('active');
            });
        });

        const hideEditModal = () => {
            editProfileModal.classList.remove('active');
        };

        closeEditModalBtn.addEventListener('click', hideEditModal);
        editProfileModal.addEventListener('click', (e) => {
            if (e.target === editProfileModal) hideEditModal();
        });

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
