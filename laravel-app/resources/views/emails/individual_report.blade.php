<!DOCTYPE html>
<html lang="en-GB">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Your Weekly Performance Summary</title>
<style>
    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family: 'Segoe UI', Arial, sans-serif; background:#f0f4ff; color:#0f172a; }
    .wrapper { max-width:640px; margin:0 auto; padding:24px 16px; }

    /* Header */
    .header {
        background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
        border-radius: 16px 16px 0 0;
        padding: 36px;
        text-align: center;
        position: relative;
        overflow: hidden;
    }
    .header::before {
        content:''; position:absolute; top:-40px; right:-40px;
        width:160px; height:160px; border-radius:50%;
        background: rgba(255,255,255,0.06);
    }
    .header::after {
        content:''; position:absolute; bottom:-30px; left:-30px;
        width:120px; height:120px; border-radius:50%;
        background: rgba(255,255,255,0.04);
    }
    .header-logo { font-size:11px; font-weight:700; color:rgba(255,255,255,0.65); letter-spacing:2.5px; text-transform:uppercase; margin-bottom:14px; }

    .avatar-large {
        width:70px; height:70px; border-radius:18px;
        background: rgba(255,255,255,0.2);
        border: 2px solid rgba(255,255,255,0.35);
        display:inline-flex; align-items:center; justify-content:center;
        font-size:28px; font-weight:800; color:#fff;
        margin-bottom:14px;
    }
    .header h1 { font-size:22px; font-weight:800; color:#fff; letter-spacing:-0.5px; }
    .header-role { font-size:13px; color:rgba(255,255,255,0.7); margin-top:4px; }
    .week-badge {
        display:inline-block; margin-top:14px;
        background:rgba(255,255,255,0.18); border:1px solid rgba(255,255,255,0.28);
        color:#fff; padding:5px 16px; border-radius:20px;
        font-size:12px; font-weight:700; letter-spacing:0.5px;
    }

    /* Profile card */
    .body { background:#fff; padding:30px 36px; }

    .profile-row {
        display:table; width:100%; border-collapse:separate; border-spacing:10px;
        margin-bottom:20px;
    }
    .profile-cell { display:table-cell; width:50%; vertical-align:top; }
    .profile-card {
        background:#f8faff; border:1px solid #e2e8f0;
        border-radius:12px; padding:14px 16px;
    }
    .profile-label { font-size:10px; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:0.8px; margin-bottom:4px; }
    .profile-val   { font-size:14px; font-weight:700; color:#0f172a; }

    /* KPI Row */
    .kpi-row { display:table; width:100%; border-collapse:separate; border-spacing:8px; margin:20px 0; }
    .kpi-cell { display:table-cell; width:25%; text-align:center; }
    .kpi-card {
        background:#f8faff; border:1px solid #e2e8f0;
        border-radius:12px; padding:14px 8px;
    }
    .kpi-val   { font-size:22px; font-weight:800; color:#6366f1; font-family:monospace; line-height:1; }
    .kpi-label { font-size:10px; color:#94a3b8; font-weight:600; text-transform:uppercase; letter-spacing:0.5px; margin-top:5px; }

    /* Section */
    .section-title {
        font-size:11px; font-weight:800; color:#94a3b8;
        text-transform:uppercase; letter-spacing:1.2px;
        margin:24px 0 12px; padding-bottom:8px;
        border-bottom:1px solid #e2e8f0;
    }

    /* App breakdown */
    .app-row { margin-bottom:12px; }
    .app-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:5px; }
    .app-name  { font-size:13px; font-weight:700; color:#334155; }
    .app-hours { font-size:12px; color:#6366f1; font-weight:700; font-family:monospace; }
    .bar-track { background:#f1f5f9; border-radius:4px; height:8px; }
    .bar-fill  { height:100%; border-radius:4px; background:linear-gradient(90deg,#6366f1,#8b5cf6); }

    /* Performance badge */
    .perf-section { text-align:center; padding:20px; background:#f8faff; border-radius:12px; border:1px solid #e2e8f0; margin-top:20px; }
    .perf-label { font-size:11px; color:#94a3b8; font-weight:700; text-transform:uppercase; letter-spacing:0.8px; margin-bottom:8px; }
    .perf-badge {
        display:inline-block; padding:8px 24px;
        border-radius:30px; font-size:15px; font-weight:800;
    }
    .perf-high   { background:rgba(16,185,129,0.12); color:#10b981; border:1px solid rgba(16,185,129,0.25); }
    .perf-medium { background:rgba(245,158,11,0.12); color:#f59e0b; border:1px solid rgba(245,158,11,0.25); }
    .perf-low    { background:rgba(239,68,68,0.12);  color:#ef4444; border:1px solid rgba(239,68,68,0.25); }
    .perf-desc { font-size:12px; color:#94a3b8; margin-top:6px; }

    /* Message */
    .message-box {
        background:linear-gradient(135deg,rgba(99,102,241,0.06),rgba(139,92,246,0.04));
        border:1px solid rgba(99,102,241,0.15);
        border-radius:12px; padding:16px 20px;
        font-size:13px; color:#334155; line-height:1.7;
        margin-top:20px;
    }
    .message-box strong { color:#0f172a; }

    /* Footer */
    .footer {
        background:#f8faff; border:1px solid #e2e8f0;
        border-radius:0 0 16px 16px;
        padding:18px 36px; text-align:center;
    }
    .footer p { font-size:12px; color:#94a3b8; line-height:1.7; }
    .footer strong { color:#475569; }
    .divider { border:none; border-top:1px solid #e2e8f0; margin:16px 0; }
</style>
</head>
<body>
<div class="wrapper">

    <!-- Header -->
    <div class="header">
        <div class="header-logo">ArchEng Pro Monitor</div>
        <div class="avatar-large">{{ strtoupper(substr($userName, 0, 1)) }}</div>
        <h1>{{ $userName }}</h1>
        <div class="header-role">{{ $occupation ?: 'Team Member' }} &nbsp;·&nbsp; {{ $department }}</div>
        <div class="week-badge">Week: {{ $weekLabel }}</div>
    </div>

    <!-- Body -->
    <div class="body">

        <!-- Profile details -->
        <div class="section-title">Your Profile</div>
        <div class="profile-row">
            <div class="profile-cell">
                <div class="profile-card">
                    <div class="profile-label">Full Name</div>
                    <div class="profile-val">{{ $userName }}</div>
                </div>
            </div>
            <div class="profile-cell">
                <div class="profile-card">
                    <div class="profile-label">Department</div>
                    <div class="profile-val">{{ $department }}</div>
                </div>
            </div>
        </div>
        <div class="profile-row">
            <div class="profile-cell">
                <div class="profile-card">
                    <div class="profile-label">Occupation</div>
                    <div class="profile-val">{{ $occupation ?: '—' }}</div>
                </div>
            </div>
            <div class="profile-cell">
                <div class="profile-card">
                    <div class="profile-label">Team Leader</div>
                    <div class="profile-val">{{ $teamLeaderName }}</div>
                </div>
            </div>
        </div>

        <!-- KPI Cards -->
        <div class="section-title">This Week at a Glance</div>
        <div class="kpi-row">
            <div class="kpi-cell">
                <div class="kpi-card">
                    <div class="kpi-val">{{ $totalHours }}h</div>
                    <div class="kpi-label">Total Hours</div>
                </div>
            </div>
            <div class="kpi-cell">
                <div class="kpi-card">
                    <div class="kpi-val">{{ $daysActive }}</div>
                    <div class="kpi-label">Days Active</div>
                </div>
            </div>
            <div class="kpi-cell">
                <div class="kpi-card">
                    <div class="kpi-val">{{ $daysActive > 0 ? round($totalHours / $daysActive, 1) : 0 }}h</div>
                    <div class="kpi-label">Avg / Day</div>
                </div>
            </div>
            <div class="kpi-cell">
                <div class="kpi-card">
                    <div class="kpi-val">{{ count($appBreakdown) }}</div>
                    <div class="kpi-label">Apps Used</div>
                </div>
            </div>
        </div>

        <!-- App Breakdown -->
        @if (!empty($appBreakdown))
        <div class="section-title">Software Usage Breakdown</div>
        @php $maxHrs = max(array_values($appBreakdown)); @endphp
        @foreach ($appBreakdown as $app => $hrs)
        <div class="app-row">
            <div class="app-header">
                <span class="app-name">{{ $app }}</span>
                <span class="app-hours">{{ $hrs }}h {{ round(fmod($hrs, 1) * 60) }}m</span>
            </div>
            <div class="bar-track">
                <div class="bar-fill" style="width:{{ $maxHrs > 0 ? round(($hrs/$maxHrs)*100) : 0 }}%"></div>
            </div>
        </div>
        @endforeach
        @endif

        <!-- Performance Rating -->
        @php
            $perfClass = $totalHours >= 30 ? 'perf-high' : ($totalHours >= 15 ? 'perf-medium' : 'perf-low');
            $perfLabel = $totalHours >= 30 ? '⭐ High Performance' : ($totalHours >= 15 ? '✔ Medium Performance' : '⚠ Needs Improvement');
            $perfDesc  = $totalHours >= 30
                ? 'Excellent work this week! You are consistently productive.'
                : ($totalHours >= 15
                    ? 'Good effort this week. Keep maintaining consistency.'
                    : 'Activity was lower than expected this week. Please check with your team leader.');
        @endphp
        <div class="perf-section">
            <div class="perf-label">Weekly Performance Rating</div>
            <div class="perf-badge {{ $perfClass }}">{{ $perfLabel }}</div>
            <div class="perf-desc">{{ $perfDesc }}</div>
        </div>

        <!-- Message from HR -->
        <div class="message-box">
            Dear <strong>{{ $userName }}</strong>,<br><br>
            This is your automated weekly performance summary from the ArchEng Pro monitoring system.
            Your team leader <strong>{{ $teamLeaderName }}</strong> has been CC'd on this email.<br><br>
            If you have any questions about your activity data or this report, please reach out to HR or your team leader directly.
        </div>

    </div>

    <!-- Footer -->
    <div class="footer">
        <p>
            Sent by <strong>ArchEng Pro Monitor</strong> on behalf of <strong>HR Department</strong>.<br>
            Team Leader: <strong>{{ $teamLeaderName }}</strong> ({{ $teamLeaderEmail }}) — CC'd.<br>
        </p>
        <hr class="divider">
        <p style="font-size:11px;color:#cbd5e1;">
            © {{ date('Y') }} ArchEng Pro · Automated Individual Report · Week ending {{ $weekEnd }}
        </p>
    </div>

</div>
</body>
</html>
