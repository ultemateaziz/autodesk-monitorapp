<!DOCTYPE html>
<html lang="en" data-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ArchEng Pro | User Management</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">

    <style>
        .user-table-card {
            background: var(--card-bg);
            border-radius: 20px;
            padding: 24px;
            border: 1px solid var(--border-color);
            margin-top: 24px;
        }

        .user-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 8px;
        }

        .user-table th {
            text-align: left;
            padding: 12px 16px;
            color: var(--text-muted);
            font-size: 13px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .user-table tr.user-row {
            background: rgba(255, 255, 255, 0.02);
            transition: all 0.2s;
        }

        .user-table tr.user-row:hover {
            background: rgba(255, 255, 255, 0.05);
            transform: scale(1.002);
        }

        .user-table td {
            padding: 16px;
            vertical-align: middle;
            border-top: 1px solid transparent;
            border-bottom: 1px solid transparent;
        }

        .user-table td:first-child {
            border-left: 1px solid transparent;
            border-top-left-radius: 12px;
            border-bottom-left-radius: 12px;
        }

        .user-table td:last-child {
            border-right: 1px solid transparent;
            border-top-right-radius: 12px;
            border-bottom-right-radius: 12px;
        }

        .role-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .role-management {
            background: rgba(16, 185, 129, 0.1);
            color: #10b981;
        }

        .role-admin {
            background: rgba(59, 130, 246, 0.1);
            color: #3b82f6;
        }

        .role-leader {
            background: rgba(16, 185, 129, 0.1);
            color: #10b981;
        }

        .btn-icon {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid var(--border-color);
            background: transparent;
            color: var(--text-muted);
            cursor: pointer;
            transition: all 0.2s;
            margin-left: 4px;
        }

        /* Modal styles are now inherited from dashboard.css */
        .btn-icon:hover {
            color: var(--accent-color);
            border-color: var(--accent-color);
            background: rgba(59, 130, 246, 0.05);
        }

        .btn-icon.delete:hover {
            color: #ef4444;
            border-color: #ef4444;
            background: rgba(239, 68, 68, 0.05);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: var(--text-muted);
            margin-bottom: 8px;
        }

        .form-input {
            width: 100%;
            background: var(--bg-color);
            border: 1px solid var(--border-color);
            padding: 12px 16px;
            border-radius: 12px;
            color: var(--text-primary);
            font-family: 'Outfit', sans-serif;
            outline: none;
            transition: all 0.2s;
        }

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
                    <a href="{{ route('user-management') }}" class="nav-link active">
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
        <header class="topbar">
            <div style="display: flex; align-items: center; gap: 20px;">
                <button class="action-btn" id="sidebarToggle" title="Toggle Sidebar">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="search-container">
                    <i class="fas fa-search"></i>
                    <input type="text" class="search-input" placeholder="Search system users by name or email...">
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
            <header class="page-header">
                <div class="page-title">
                    <h1>User Management</h1>
                    <p>Manage system administrators and professional team leaders</p>
                </div>
                <button class="btn-primary" id="addUserBtn" onclick="openCreateModal()"
                    style="background: #3b82f6; box-shadow: 0 4px 14px rgba(59, 130, 246, 0.4);">
                    <i class="fas fa-plus"></i>
                    <span>Add User</span>
                </button>
            </header>

            @if (session('success'))
                <div class="alert"
                    style="background: rgba(16, 185, 129, 0.1); color: #10b981; padding: 16px; border-radius: 12px; margin-top: 20px; border: 1px solid rgba(16, 185, 129, 0.2);">
                    <i class="fas fa-check-circle" style="margin-right: 8px;"></i> {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="alert"
                    style="background: rgba(239, 68, 68, 0.1); color: #ef4444; padding: 16px; border-radius: 12px; margin-top: 20px; border: 1px solid rgba(239, 68, 68, 0.2);">
                    <i class="fas fa-exclamation-circle" style="margin-right: 8px;"></i> {{ session('error') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="alert"
                    style="background: rgba(239, 68, 68, 0.1); color: #ef4444; padding: 16px; border-radius: 12px; margin-top: 20px; border: 1px solid rgba(239, 68, 68, 0.2);">
                    <ul style="margin: 0; padding-left: 20px;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="user-table-card">
                <table class="user-table">
                    <thead>
                        <tr>
                            <th>User Info</th>
                            <th>Role</th>
                            <th>Occupation</th>
                            <th>Department</th>
                            <th>Created</th>
                            <th style="text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $user)
                            <tr class="user-row">
                                <td>
                                    <div style="display: flex; align-items: center; gap: 12px;">
                                        <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=3b82f6&color=fff"
                                            style="width: 40px; height: 40px; border-radius: 12px; border: 2px solid var(--border-color);">
                                        <div>
                                            <div class="user-name"
                                                style="font-weight: 600; color: var(--text-primary);">
                                                {{ $user->name }}</div>
                                            <div style="font-size: 12px; color: var(--text-secondary);"
                                                class="user-email-display">
                                                {{ $user->email }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span
                                        class="role-badge 
                                        {{ $user->role === 'admin' ? 'role-admin' : ($user->role === 'management' ? 'role-management' : 'role-leader') }}">
                                        @if ($user->role === 'admin')
                                            IT Manager
                                        @elseif($user->role === 'management')
                                            Management
                                        @else
                                            Contract Manager
                                        @endif
                                    </span>
                                </td>
                                <td>
                                    <span
                                        style="font-size: 13px; color: var(--text-primary); font-weight: 500;">{{ $user->occupation ?: 'N/A' }}</span>
                                </td>
                                <td>
                                    <span
                                        style="font-size: 13px; color: var(--text-secondary);">{{ $user->department ?: 'Unassigned' }}</span>
                                </td>
                                <td>
                                    <span
                                        style="font-size: 12px; color: var(--text-secondary);">{{ $user->created_at->format('M d, Y') }}</span>
                                </td>
                                <td style="text-align: right;">
                                    @if ($user->role === 'team_leader')
                                        <button class="btn-icon monitor-user-btn"
                                            onclick="openMonitorModal({{ $user->id }}, '{{ $user->name }}', @json($assignments[$user->id] ?? []))"
                                            title="Assign Monitored Users" style="color: #8b5cf6;">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    @endif
                                    <button class="btn-icon edit-user-btn" data-user="{{ json_encode($user) }}"
                                        data-assignments="{{ json_encode($assignments[$user->id] ?? []) }}"
                                        onclick="openEditModal(this.getAttribute('data-user'), this.getAttribute('data-assignments'))"
                                        title="Edit User">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form action="{{ route('user-management.destroy', $user->id) }}" method="POST"
                                        style="display: inline;" onsubmit="return confirm('Delete this user?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-icon delete" title="Delete User">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <!-- Create/Edit Modal -->
    <div class="modal-overlay" id="userModal">
        <div class="modal-container">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 28px;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <div
                        style="width: 42px; height: 42px; background: rgba(59, 130, 246, 0.1); color: #3b82f6; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 18px;">
                        <i class="fas fa-user-plus" id="modalIcon"></i>
                    </div>
                    <div>
                        <h3 id="modalTitle" style="font-size: 20px; font-weight: 700; margin: 0;">Add User</h3>
                        <p id="modalSub" style="font-size: 12px; color: var(--text-secondary); margin: 2px 0 0 0;">
                            Configure access and professional details</p>
                    </div>
                </div>
                <button type="button" class="btn-icon close-modal-btn" onclick="closeModal()"
                    style="border: none; background: transparent;">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form id="userForm" method="POST">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="form-group">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="name" id="userName" class="form-input"
                            placeholder="e.g. John Doe" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Email Address</label>
                        <input type="email" name="email" id="userEmail" class="form-input"
                            placeholder="john@example.com" required>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="form-group">
                        <label class="form-label">System Role</label>
                        <select name="role" id="userRole" class="form-input" required
                            onchange="toggleMonitorSelection()">
                            @foreach ($roles as $val => $label)
                                <option value="{{ $val }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Occupation / Title</label>
                        <input type="text" name="occupation" id="userOccupation" class="form-input"
                            placeholder="e.g. Senior Architect">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Department / Team</label>
                    <select name="department" id="userDept" class="form-input">
                        <option value="">Unassigned</option>
                        @foreach ($departments as $dept)
                            <option value="{{ $dept }}">{{ $dept }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group" id="monitorSelectionGroup" style="display: none;">
                    <label class="form-label">Specific Users to Monitor (Optional)</label>
                    <div
                        style="max-height: 150px; overflow-y: auto; background: var(--bg-color); border: 1px solid var(--border-color); border-radius: 12px; padding: 12px;">
                        @foreach ($allMonitorableUsernames as $username)
                            <label
                                style="display: flex; align-items: center; gap: 10px; padding: 6px 10px; cursor: pointer; border-radius: 8px; transition: background 0.2s;"
                                class="monitor-option">
                                <input type="checkbox" name="monitored_usernames[]" value="{{ $username }}"
                                    class="monitor-checkbox-main">
                                <span
                                    style="font-size: 14px; font-weight: 500; color: var(--text-primary);">{{ $username }}</span>
                            </label>
                        @endforeach
                    </div>
                    <p style="font-size: 11px; color: var(--text-secondary); margin-top: 6px; padding-left: 4px;">Leave
                        empty to automatically monitor everyone in their chosen Department.</p>
                </div>

                <div
                    style="background: rgba(125,125,125,0.03); padding: 20px; border-radius: 16px; border: 1px solid var(--border-color); margin-top: 10px;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">Password <span id="passwordHint"
                                    style="font-weight: 400; opacity: 0.6; font-size: 11px;">(min 8)</span></label>
                            <input type="password" name="password" id="userPassword" class="form-input">
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">Confirm Password</label>
                            <input type="password" name="password_confirmation" id="userConfirmPassword"
                                class="form-input">
                        </div>
                    </div>
                    <div
                        style="display: flex; gap: 12px; margin-top: 10px; order: 1; min-width: 100%; justify-content: space-between;">
                        <button type="button" class="btn-primary" onclick="closeModal()"
                            style="flex: 1; background: var(--bg-color); color: var(--text-primary); border: 1px solid var(--border-color); box-shadow: none; justify-content: center;">Cancel</button>
                        <button type="submit" class="btn-primary"
                            style="flex: 1; justify-content: center;">Save</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Monitor Assignment Modal -->
    <div class="modal-overlay" id="monitorModal">
        <div class="modal-container" style="max-width: 500px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <div
                        style="width: 42px; height: 42px; background: rgba(139, 92, 246, 0.1); color: #8b5cf6; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 18px;">
                        <i class="fas fa-eye"></i>
                    </div>
                    <div>
                        <h3 style="font-size: 20px; font-weight: 700; margin: 0;">Monitoring Access</h3>
                        <p style="font-size: 12px; color: var(--text-secondary); margin: 2px 0 0 0;">Assign monitored
                            users to <strong id="leaderNameDisplay"></strong></p>
                    </div>
                </div>
                <button type="button" class="btn-icon" onclick="closeMonitorModal()"
                    style="border: none; background: transparent;">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form action="{{ route('monitor-assignments.sync') }}" method="POST">
                @csrf
                <input type="hidden" name="leader_id" id="monitorLeaderId">

                <div class="form-group">
                    <label class="form-label">Select Users to Monitor</label>
                    <div
                        style="max-height: 300px; overflow-y: auto; background: var(--bg-color); border: 1px solid var(--border-color); border-radius: 12px; padding: 12px;">
                        @foreach ($allMonitorableUsernames as $username)
                            <label
                                style="display: flex; align-items: center; gap: 10px; padding: 10px; cursor: pointer; border-radius: 8px; transition: background 0.2s;"
                                class="monitor-option">
                                <input type="checkbox" name="monitored_usernames[]" value="{{ $username }}"
                                    class="monitor-checkbox">
                                <span
                                    style="font-size: 14px; font-weight: 500; color: var(--text-primary);">{{ $username }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div style="display: flex; gap: 12px; margin-top: 24px;">
                    <button type="button" class="btn-primary" onclick="closeMonitorModal()"
                        style="flex: 1; background: var(--bg-color); color: var(--text-primary); border: 1px solid var(--border-color); box-shadow: none; justify-content: center;">Cancel</button>
                    <button type="submit" class="btn-primary"
                        style="flex: 1; justify-content: center; background: #8b5cf6; box-shadow: 0 4px 14px rgba(139, 92, 246, 0.4);">Save
                        Assignments</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function toggleMonitorSelection() {
            const role = document.getElementById('userRole').value;
            document.getElementById('monitorSelectionGroup').style.display = (role === 'team_leader') ? 'block' : 'none';
        }

        function openCreateModal() {
            const modal = document.getElementById('userModal');
            const form = document.getElementById('userForm');
            document.getElementById('modalTitle').textContent = 'Add User';
            document.getElementById('modalSub').textContent = 'Configure access and professional details';
            document.getElementById('modalIcon').className = 'fas fa-user-plus';
            document.getElementById('formMethod').value = 'POST';
            form.action = "{{ route('user-management.store') }}";
            form.reset();

            // Clear checkboxes
            document.querySelectorAll('.monitor-checkbox-main').forEach(cb => cb.checked = false);

            document.getElementById('userPassword').required = true;
            document.getElementById('userConfirmPassword').required = true;
            document.getElementById('passwordHint').textContent = '(min 8 chars)';
            toggleMonitorSelection();
            modal.classList.add('active');
        }

        function openEditModal(userDataStr, assignmentsStr) {
            const userData = JSON.parse(userDataStr);
            const assignments = JSON.parse(assignmentsStr);
            const modal = document.getElementById('userModal');
            const form = document.getElementById('userForm');
            document.getElementById('modalTitle').textContent = 'Update User Profile';
            document.getElementById('modalSub').textContent = 'Modify existing account permissions';
            document.getElementById('modalIcon').className = 'fas fa-user-edit';
            document.getElementById('formMethod').value = 'PUT';
            form.action = '/user-management/' + userData.id;

            document.getElementById('userName').value = userData.name || '';
            document.getElementById('userEmail').value = userData.email || '';
            document.getElementById('userOccupation').value = userData.occupation || '';
            document.getElementById('userRole').value = userData.role || 'team_leader';
            document.getElementById('userDept').value = userData.department || '';

            // Handle assignments checkboxes
            document.querySelectorAll('.monitor-checkbox-main').forEach(cb => {
                cb.checked = assignments.some(a => a.monitored_user_name === cb.value);
            });

            document.getElementById('userPassword').required = false;
            document.getElementById('userConfirmPassword').required = false;
            document.getElementById('userPassword').value = '';
            document.getElementById('userConfirmPassword').value = '';
            document.getElementById('passwordHint').textContent = '(leave blank to keep current)';

            toggleMonitorSelection();
            modal.classList.add('active');
        }

        function closeModal() {
            document.getElementById('userModal').classList.remove('active');
        }

        function openMonitorModal(leaderId, leaderName, existingAssignments) {
            document.getElementById('monitorLeaderId').value = leaderId;
            document.getElementById('leaderNameDisplay').innerText = leaderName;

            // Clear all checkboxes
            const checkboxes = document.querySelectorAll('.monitor-checkbox');
            checkboxes.forEach(cb => {
                cb.checked = false;
                // Highlight existing
                const assigned = existingAssignments.find(a => a.monitored_user_name === cb.value);
                if (assigned) {
                    cb.checked = true;
                }
            });

            document.getElementById('monitorModal').classList.add('active');
        }

        function closeMonitorModal() {
            document.getElementById('monitorModal').classList.remove('active');
        }

        // Add some hover effect for the monitor options
        document.querySelectorAll('.monitor-option').forEach(opt => {
            opt.addEventListener('mouseenter', () => opt.style.background = 'rgba(139, 92, 246, 0.05)');
            opt.addEventListener('mouseleave', () => opt.style.background = 'transparent');
        });

        // Close on overlay background click
        document.addEventListener('DOMContentLoaded', function() {
            const userModal = document.getElementById('userModal');
            const monitorModal = document.getElementById('monitorModal');

            if (userModal) {
                userModal.addEventListener('click', function(e) {
                    if (e.target === userModal) closeModal();
                });
            }
            if (monitorModal) {
                monitorModal.addEventListener('click', function(e) {
                    if (e.target === monitorModal) closeMonitorModal();
                });
            }
        });

        // Theme Toggle & Sidebar Toggle logic
        const themeToggle = document.getElementById('themeToggle');
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebar = document.querySelector('.sidebar');
        const html = document.documentElement;

        // Sidebar Toggle
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

        const savedTheme = localStorage.getItem('theme') || 'dark';
        html.setAttribute('data-theme', savedTheme);

        if (themeToggle) {
            themeToggle.addEventListener('click', () => {
                const currentTheme = html.getAttribute('data-theme');
                const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
                html.setAttribute('data-theme', newTheme);
                localStorage.setItem('theme', newTheme);
            });
        }

        // Search filtering logic
        const searchInput = document.querySelector('.search-input');
        if (searchInput) {
            searchInput.addEventListener('input', function(e) {
                const term = e.target.value.toLowerCase();
                const rows = document.querySelectorAll('.user-row');

                rows.forEach(row => {
                    const name = row.querySelector('.user-name')?.textContent.toLowerCase() ||
                        '';
                    const email = row.querySelector('.user-email-display')?.textContent
                        .toLowerCase() || '';

                    if (name.includes(term) || email.includes(term)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });
        }
    </script>
</body>

</html>
