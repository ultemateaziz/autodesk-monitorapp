<!DOCTYPE html>
<html lang="en" data-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ArchEng Pro | Settings</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">

    <style>
        .settings-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 24px;
            margin-top: 24px;
        }

        .settings-card {
            background: var(--card-bg);
            border-radius: 24px;
            padding: 32px;
            border: 1px solid var(--border-color);
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .settings-card:hover {
            transform: translateY(-5px);
            border-color: var(--accent-color);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
        }

        .settings-icon-box {
            width: 56px;
            height: 56px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            background: rgba(59, 130, 246, 0.1);
            color: var(--accent-color);
        }

        .settings-info h3 {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 8px;
            color: var(--text-primary);
        }

        .settings-info p {
            font-size: 14px;
            color: var(--text-muted);
            line-height: 1.6;
        }

        .settings-action {
            margin-top: auto;
        }

        .btn-settings {
            width: 100%;
            padding: 12px;
            border-radius: 12px;
            border: 1px solid var(--border-color);
            background: transparent;
            color: var(--text-primary);
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }

        .btn-settings:hover {
            background: var(--accent-color);
            border-color: var(--accent-color);
            color: white;
        }

        .admin-badge {
            background: #f59e0b;
            color: white;
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            display: inline-block;
            margin-bottom: 12px;
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
                <a href="{{ route('settings') }}" class="nav-link active">
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
                    <input type="text" class="search-input" placeholder="Search setting names or categories...">
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
                    <h1>Platform Settings</h1>
                    <p>Configure system preferences and team access levels</p>
                </div>
            </header>
            <div class="settings-grid">

                <!-- Account Settings -->
                <div class="settings-card">
                    <div class="settings-icon-box"><i class="fas fa-user-circle"></i></div>
                    <div class="settings-info">
                        <h3>My Account</h3>
                        <p>Manage your personal profile, security credentials, and department preferences.</p>
                    </div>
                    <div class="settings-action">
                        <a href="{{ route('profile') }}" class="btn-settings">Edit Profile</a>
                    </div>
                </div>

                <!-- Admin Only: User Management Card (Scroll to bottom) -->
                @if (auth()->check() && auth()->user()->role === 'admin')
                    <div class="settings-card" style="border-left: 4px solid #f59e0b;">
                        <div><span class="admin-badge">Master Admin</span></div>
                        <div class="settings-icon-box" style="background: rgba(245, 158, 11, 0.1); color: #f59e0b;"><i
                                class="fas fa-users-cog"></i></div>
                        <div class="settings-info">
                            <h3>Team Management</h3>
                            <p>Directly manage system users, roles, and professional titles below.</p>
                        </div>
                        <div class="settings-action">
                            <button
                                onclick="window.scrollTo({top: document.getElementById('userManagementSection').offsetTop - 20, behavior: 'smooth'})"
                                class="btn-settings"
                                style="background: rgba(245, 158, 11, 0.05); border-color: rgba(245, 158, 11, 0.3); color: #f59e0b; transition: all 0.2s;"
                                onmouseover="this.style.background='#f59e0b'; this.style.color='white'"
                                onmouseout="this.style.background='rgba(245, 158, 11, 0.05)'; this.style.color='#f59e0b'">
                                Go to User List
                            </button>
                        </div>
                    </div>
                @endif

                <!-- Notification Settings -->
                <div class="settings-card">
                    <div class="settings-icon-box" style="background: rgba(139, 92, 246, 0.1); color: #8b5cf6;"><i
                            class="fas fa-bell"></i></div>
                    <div class="settings-info">
                        <h3>System notifications</h3>
                        <p>Configure alerts for inactive licenses and automated reports.</p>
                    </div>
                    <div class="settings-action">
                        <a href="#" class="btn-settings"
                            style="background: rgba(139, 92, 246, 0.05); border-color: rgba(139, 92, 246, 0.3); color: #8b5cf6;"
                            onmouseover="this.style.background='#8b5cf6'; this.style.color='white'"
                            onmouseout="this.style.background='rgba(139, 92, 246, 0.05)'; this.style.color='#8b5cf6'">
                            Configure Alerts
                        </a>
                    </div>
                </div>

                <!-- Infrastructure Card -->
                @if (auth()->check() && auth()->user()->role === 'admin')
                    <div class="settings-card">
                        <div class="settings-icon-box" style="background: rgba(239, 68, 68, 0.1); color: #ef4444;"><i
                                class="fas fa-database"></i></div>
                        <div class="settings-info">
                            <h3>Infrastructure</h3>
                            <p>View server logs, clear cache, and monitor health.</p>
                        </div>
                        <div class="settings-action">
                            <a href="#" class="btn-settings"
                                style="background: rgba(239, 68, 68, 0.05); border-color: rgba(239, 68, 68, 0.3); color: #ef4444;"
                                onmouseover="this.style.background='#ef4444'; this.style.color='white'"
                                onmouseout="this.style.background='rgba(239, 68, 68, 0.05)'; this.style.color='#ef4444'">
                                View Logs
                            </a>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Full User Management Section for Admin -->
            @if (auth()->check() && auth()->user()->role === 'admin')
                <div id="userManagementSection" class="user-management-section"
                    style="margin-top: 64px; padding-top: 32px; border-top: 1px solid var(--border-color);">
                    <div
                        style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 32px;">
                        <div>
                            <h2 style="font-size: 28px; font-weight: 700; color: var(--text-primary);">Global User
                                Access</h2>
                            <p style="color: var(--text-muted); margin-top: 8px;">Manage names, occupations,
                                departments
                                and credentials for all system leaders.</p>
                        </div>
                        <button class="btn-primary" onclick="resetForm()">
                            <i class="fas fa-plus"></i>
                            <span>Create New Lead</span>
                        </button>
                    </div>

                    @if (session('success'))
                        <div
                            style="background: rgba(16, 185, 129, 0.1); color: #10b981; padding: 16px; border-radius: 12px; margin-bottom: 32px; border: 1px solid rgba(16, 185, 129, 0.2);">
                            <i class="fas fa-check-circle" style="margin-right: 8px;"></i> {{ session('success') }}
                        </div>
                    @endif

                    <div style="display: grid; grid-template-columns: 1fr 380px; gap: 40px; align-items: start;">
                        <!-- User Table -->
                        <div
                            style="background: var(--card-bg); border-radius: 20px; padding: 24px; border: 1px solid var(--border-color);">
                            <table style="width: 100%; border-collapse: separate; border-spacing: 0 8px;">
                                <thead>
                                    <tr
                                        style="text-align: left; color: var(--text-muted); font-size: 11px; text-transform: uppercase; letter-spacing: 1px;">
                                        <th style="padding: 12px 16px;">User Information</th>
                                        <th style="padding: 12px 16px;">Role</th>
                                        <th style="padding: 12px 16px;">Occupation</th>
                                        <th style="padding: 12px 16px; text-align: right;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($users as $user)
                                        <tr style="background: rgba(255,255,255,0.02); transition: all 0.2s;">
                                            <td
                                                style="padding: 16px; border-top-left-radius: 12px; border-bottom-left-radius: 12px;">
                                                <div style="font-weight: 600; color: white;">{{ $user->name }}</div>
                                                <div style="font-size: 12px; color: var(--text-muted);">
                                                    {{ $user->email }}</div>
                                            </td>
                                            <td style="padding: 16px;">
                                                <span
                                                    style="font-size: 10px; font-weight: 800; text-transform: uppercase; padding: 4px 8px; border-radius: 6px; background: {{ $user->role === 'admin' ? 'rgba(59, 130, 246, 0.1)' : 'rgba(16, 185, 129, 0.1)' }}; color: {{ $user->role === 'admin' ? '#3b82f6' : '#10b981' }};">
                                                    {{ $user->role === 'admin' ? 'Admin' : 'Lead' }}
                                                </span>
                                            </td>
                                            <td style="padding: 16px; font-size: 13px; color: var(--text-secondary);">
                                                {{ $user->occupation ?: '--' }}
                                            </td>
                                            <td
                                                style="padding: 16px; text-align: right; border-top-right-radius: 12px; border-bottom-right-radius: 12px;">
                                                <button onclick="editUser({{ json_encode($user) }})"
                                                    style="background: transparent; border: 1px solid var(--border-color); color: var(--text-muted); cursor: pointer; width: 32px; height: 32px; border-radius: 8px; transition: all 0.2s;"><i
                                                        class="fas fa-edit"></i></button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- User Creation Form -->
                        <div id="userFormContainer"
                            style="background: var(--card-bg); border-radius: 20px; padding: 32px; border: 1px solid var(--border-color); position: sticky; top: 100px; box-shadow: 0 20px 40px rgba(0,0,0,0.2);">
                            <button type="button" onclick="resetForm()"
                                style="position: absolute; top: 24px; right: 24px; background: transparent; border: none; color: var(--text-muted); cursor: pointer; font-size: 18px; opacity: 0.5; transition: all 0.2s; display: flex; align-items: center; justify-content: center; width: 32px; height: 32px; border-radius: 50%;"
                                onmouseover="this.style.opacity='1'; this.style.background='rgba(255,255,255,0.05)'"
                                onmouseout="this.style.opacity='0.5'; this.style.background='transparent'">
                                <i class="fas fa-times"></i>
                            </button>
                            <h3 id="formTitle"
                                style="font-size: 20px; font-weight: 700; color: white; margin-bottom: 28px; padding-right: 40px;">
                                Add New Head / Leader</h3>
                            <form id="userSettingsForm" action="{{ route('user-management.store') }}"
                                method="POST">
                                @csrf
                                <input type="hidden" name="_method" id="formMethod" value="POST">

                                <div style="margin-bottom: 20px;">
                                    <label
                                        style="display: block; font-size: 13px; font-weight: 600; color: var(--text-muted); margin-bottom: 8px;">Full
                                        Name</label>
                                    <input type="text" name="name" id="userName"
                                        style="width: 100%; background: var(--bg-color); border: 1px solid var(--border-color); padding: 12px 16px; border-radius: 12px; color: white; outline: none;"
                                        required>
                                </div>

                                <div style="margin-bottom: 20px;">
                                    <label
                                        style="display: block; font-size: 13px; font-weight: 600; color: var(--text-muted); margin-bottom: 8px;">Email
                                        Address</label>
                                    <input type="email" name="email" id="userEmail"
                                        style="width: 100%; background: var(--bg-color); border: 1px solid var(--border-color); padding: 12px 16px; border-radius: 12px; color: white; outline: none;"
                                        required>
                                </div>

                                <div style="margin-bottom: 20px;">
                                    <label
                                        style="display: block; font-size: 13px; font-weight: 600; color: var(--text-muted); margin-bottom: 8px;">Occupation
                                        / Title</label>
                                    <input type="text" name="occupation" id="userOccupation"
                                        style="width: 100%; background: var(--bg-color); border: 1px solid var(--border-color); padding: 12px 16px; border-radius: 12px; color: white; outline: none;"
                                        placeholder="e.g. Senior Manager">
                                </div>

                                <div
                                    style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 20px;">
                                    <div>
                                        <label
                                            style="display: block; font-size: 13px; font-weight: 600; color: var(--text-muted); margin-bottom: 8px;">System
                                            Role</label>
                                        <select name="role" id="userRole"
                                            style="width: 100%; background: var(--bg-color); border: 1px solid var(--border-color); padding: 12px 16px; border-radius: 12px; color: white; outline: none;">
                                            @foreach ($roles as $val => $label)
                                                <option value="{{ $val }}">{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label
                                            style="display: block; font-size: 13px; font-weight: 600; color: var(--text-muted); margin-bottom: 8px;">Department</label>
                                        <select name="department" id="userDept"
                                            style="width: 100%; background: var(--bg-color); border: 1px solid var(--border-color); padding: 12px 16px; border-radius: 12px; color: white; outline: none;">
                                            <option value="">Unassigned</option>
                                            @foreach ($departments as $dept)
                                                <option value="{{ $dept }}">{{ $dept }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div style="margin-bottom: 20px;">
                                    <label
                                        style="display: block; font-size: 13px; font-weight: 600; color: var(--text-muted); margin-bottom: 8px;">Password
                                        <span id="passState" style="font-weight: 400; opacity: 0.6;">(min
                                            8)</span></label>
                                    <input type="password" name="password"
                                        style="width: 100%; background: var(--bg-color); border: 1px solid var(--border-color); padding: 12px 16px; border-radius: 12px; color: white; outline: none;"
                                        id="passInput">
                                </div>

                                <div style="margin-bottom: 24px;">
                                    <label
                                        style="display: block; font-size: 13px; font-weight: 600; color: var(--text-muted); margin-bottom: 8px;">Confirm
                                        Password</label>
                                    <input type="password" name="password_confirmation"
                                        style="width: 100%; background: var(--bg-color); border: 1px solid var(--border-color); padding: 12px 16px; border-radius: 12px; color: white; outline: none;">
                                </div>

                                <button type="submit" class="btn-primary"
                                    style="width: 100%; padding: 14px; font-size: 15px;">Save User Account</button>
                                <button type="button" onclick="resetForm()" id="cancelBtn"
                                    style="width: 100%; background: transparent; border: none; color: var(--text-muted); margin-top: 12px; cursor: pointer; font-size: 13px; font-weight: 500;">Clear
                                    Form</button>
                            </form>
                        </div>
                    </div>
                </div>

                <script>
                    function editUser(user) {
                        document.getElementById('formTitle').textContent = 'Modify User Access';
                        document.getElementById('formMethod').value = 'PUT';
                        document.getElementById('userSettingsForm').action = `/user-management/${user.id}`;

                        document.getElementById('userName').value = user.name;
                        document.getElementById('userEmail').value = user.email;
                        document.getElementById('userOccupation').value = user.occupation || '';
                        document.getElementById('userRole').value = user.role;
                        document.getElementById('userDept').value = user.department || '';

                        document.getElementById('passState').textContent = '(leave blank to keep)';
                        document.getElementById('cancelBtn').textContent = 'Cancel Editing';
                        document.getElementById('cancelBtn').style.display = 'block';

                        window.scrollTo({
                            top: document.getElementById('userFormContainer').offsetTop - 100,
                            behavior: 'smooth'
                        });
                    }

                    function resetForm() {
                        document.getElementById('formTitle').textContent = 'Add New Head / Leader';
                        document.getElementById('formMethod').value = 'POST';
                        document.getElementById('userSettingsForm').action = "{{ route('user-management.store') }}";
                        document.getElementById('userSettingsForm').reset();
                        document.getElementById('passState').textContent = '(min 8 chars)';
                        document.getElementById('cancelBtn').textContent = 'Clear Form';
                        document.getElementById('cancelBtn').style.display = 'block';
                        window.scrollTo({
                            top: document.getElementById('userFormContainer').offsetTop - 100,
                            behavior: 'smooth'
                        });
                    }
                </script>
            @endif
        </main>
    </div>

    <script>
        const html = document.documentElement;
        const themeToggle = document.getElementById('themeToggle');
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebar = document.querySelector('.sidebar');

        // Theme Toggle logic
        if (themeToggle) {
            themeToggle.addEventListener('click', () => {
                const current = html.getAttribute('data-theme');
                const newTheme = current === 'dark' ? 'light' : 'dark';
                html.setAttribute('data-theme', newTheme);
                localStorage.setItem('theme', newTheme);
            });
        }

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

        // Restore theme
        const savedTheme = localStorage.getItem('theme') || 'dark';
        html.setAttribute('data-theme', savedTheme);
    </script>
</body>

</html>
