<!DOCTYPE html>
<html lang="en-GB">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Weekly Performance Report</title>
<style>
    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family: 'Segoe UI', Arial, sans-serif; background:#f0f4ff; color:#0f172a; }
    .wrapper { max-width:680px; margin:0 auto; padding:24px 16px; }

    /* Header */
    .header {
        background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
        border-radius: 16px 16px 0 0;
        padding: 32px 36px;
        text-align: center;
    }
    .header-logo { font-size: 13px; font-weight: 700; color: rgba(255,255,255,0.7); letter-spacing: 2px; text-transform: uppercase; margin-bottom: 6px; }
    .header h1 { font-size: 24px; font-weight: 800; color: #ffffff; letter-spacing: -0.5px; }
    .header-sub { font-size: 13px; color: rgba(255,255,255,0.75); margin-top: 6px; }
    .week-badge {
        display: inline-block; margin-top: 14px;
        background: rgba(255,255,255,0.2); border: 1px solid rgba(255,255,255,0.3);
        color: #fff; padding: 6px 18px; border-radius: 20px;
        font-size: 12px; font-weight: 700; letter-spacing: 0.5px;
    }

    /* Body */
    .body { background: #ffffff; padding: 32px 36px; }

    /* Greeting */
    .greeting { font-size: 15px; color: #334155; margin-bottom: 20px; line-height: 1.6; }
    .greeting strong { color: #0f172a; }

    /* Summary cards */
    .summary-row { display: table; width: 100%; border-collapse: separate; border-spacing: 10px; margin: 20px 0; }
    .summary-cell { display: table-cell; width: 33.33%; text-align: center; }
    .summary-card {
        background: #f8faff; border: 1px solid #e2e8f0;
        border-radius: 12px; padding: 16px 12px;
    }
    .summary-val  { font-size: 26px; font-weight: 800; color: #6366f1; font-family: monospace; }
    .summary-label{ font-size: 11px; color: #94a3b8; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-top: 4px; }

    /* Section title */
    .section-title {
        font-size: 12px; font-weight: 800; color: #94a3b8;
        text-transform: uppercase; letter-spacing: 1.2px;
        margin: 28px 0 12px; padding-bottom: 8px;
        border-bottom: 1px solid #e2e8f0;
    }

    /* User table */
    table.user-table { width: 100%; border-collapse: collapse; }
    table.user-table thead th {
        font-size: 11px; font-weight: 700; color: #94a3b8;
        text-transform: uppercase; letter-spacing: 0.5px;
        padding: 8px 12px; background: #f8faff;
        border-bottom: 1px solid #e2e8f0; text-align: left;
    }
    table.user-table tbody tr { border-bottom: 1px solid #f1f5f9; }
    table.user-table tbody tr:last-child { border-bottom: none; }
    table.user-table tbody td { padding: 12px 12px; font-size: 13px; color: #334155; vertical-align: middle; }

    .avatar {
        display: inline-flex; width: 30px; height: 30px; border-radius: 8px;
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        color: white; font-size: 11px; font-weight: 800;
        align-items: center; justify-content: center;
        margin-right: 8px; vertical-align: middle;
    }
    .user-name  { font-weight: 700; color: #0f172a; }
    .user-email { font-size: 11px; color: #94a3b8; }

    .hours-bar-wrap { background: #f1f5f9; border-radius: 4px; height: 6px; width: 100px; display: inline-block; vertical-align: middle; margin-left: 8px; }
    .hours-bar { height: 100%; border-radius: 4px; background: linear-gradient(90deg, #6366f1, #8b5cf6); }

    .badge-app {
        display: inline-block; padding: 3px 9px; border-radius: 20px;
        background: rgba(99,102,241,0.1); color: #6366f1;
        font-size: 11px; font-weight: 700;
    }

    .perf-high   { color: #10b981; font-weight: 700; }
    .perf-medium { color: #f59e0b; font-weight: 700; }
    .perf-low    { color: #ef4444; font-weight: 700; }

    /* Footer */
    .footer {
        background: #f8faff; border: 1px solid #e2e8f0;
        border-radius: 0 0 16px 16px;
        padding: 20px 36px; text-align: center;
    }
    .footer p { font-size: 12px; color: #94a3b8; line-height: 1.7; }
    .footer strong { color: #475569; }

    /* Note box */
    .note-box {
        background: #fffbeb; border: 1px solid #fde68a;
        border-radius: 10px; padding: 14px 16px;
        font-size: 13px; color: #92400e; margin-top: 20px;
    }
    .note-box i { margin-right: 6px; }
</style>
</head>
<body>
<div class="wrapper">

    <!-- Header -->
    <div class="header">
        <div class="header-logo">ASCLAM</div>
        <h1>Weekly Performance Report</h1>
        <div class="header-sub">{{ $department }} Department</div>
        <div class="week-badge">{{ $weekLabel }}</div>
    </div>

    <!-- Body -->
    <div class="body">

        <!-- Greeting -->
        <p class="greeting">
            Dear HR Team,<br><br>
            Please find below the weekly software usage performance report for the
            <strong>{{ $department }}</strong> department, compiled by
            <strong>{{ $teamLeaderName }}</strong> (Team Leader) for the period
            <strong>{{ $weekLabel }}</strong>.
        </p>

        <!-- Summary cards -->
        <div class="summary-row">
            <div class="summary-cell">
                <div class="summary-card">
                    <div class="summary-val">{{ $userStats->count() }}</div>
                    <div class="summary-label">Team Members</div>
                </div>
            </div>
            <div class="summary-cell">
                <div class="summary-card">
                    <div class="summary-val">{{ $totalTeamHours }}h</div>
                    <div class="summary-label">Total Hours</div>
                </div>
            </div>
            <div class="summary-cell">
                <div class="summary-card">
                    <div class="summary-val">
                        {{ $userStats->count() > 0 ? round($totalTeamHours / $userStats->count(), 1) : 0 }}h
                    </div>
                    <div class="summary-label">Avg Per User</div>
                </div>
            </div>
        </div>

        <!-- User Performance Table -->
        <div class="section-title">Individual Performance</div>

        <table class="user-table">
            <thead>
                <tr>
                    <th>Team Member</th>
                    <th>Hours This Week</th>
                    <th>Top Application</th>
                    <th>Days Active</th>
                    <th>Performance</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($userStats as $stat)
                @php
                    $maxHours = $userStats->max('hours');
                    $barWidth = $maxHours > 0 ? round(($stat['hours'] / $maxHours) * 100) : 0;
                    $perfClass = $stat['hours'] >= 30 ? 'perf-high' : ($stat['hours'] >= 15 ? 'perf-medium' : 'perf-low');
                    $perfLabel = $stat['hours'] >= 30 ? 'High' : ($stat['hours'] >= 15 ? 'Medium' : 'Low');
                @endphp
                <tr>
                    <td>
                        <span class="avatar">{{ strtoupper(substr($stat['name'], 0, 1)) }}</span>
                        <span class="user-name">{{ $stat['name'] }}</span><br>
                        <span class="user-email" style="padding-left:38px;">{{ $stat['email'] }}</span>
                    </td>
                    <td>
                        <strong>{{ $stat['hours'] }}h {{ $stat['minutes'] }}m</strong>
                        <div class="hours-bar-wrap">
                            <div class="hours-bar" style="width:{{ $barWidth }}%"></div>
                        </div>
                    </td>
                    <td>
                        @if ($stat['top_app'])
                            <span class="badge-app">{{ $stat['top_app'] }}</span>
                        @else
                            <span style="color:#cbd5e1;">—</span>
                        @endif
                    </td>
                    <td>{{ $stat['days_active'] }} / 7 days</td>
                    <td class="{{ $perfClass }}">{{ $perfLabel }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="text-align:center;padding:24px;color:#94a3b8;">No activity recorded this week.</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Note -->
        <div class="note-box">
            ⚠️ <strong>Note:</strong> Hours are calculated from active Autodesk software focus time (AutoCAD, Revit, Inventor, etc.).
            Idle time and non-Autodesk applications are excluded from this report.
        </div>

    </div>

    <!-- Footer -->
    <div class="footer">
        <p>
            This report was automatically generated by <strong>ASCLAM</strong>.<br>
            Team Leader: <strong>{{ $teamLeaderName }}</strong> ({{ $teamLeaderEmail }}) — CC'd on this email.<br>
            For issues or queries, contact your system administrator.
        </p>
        <p style="margin-top:8px;font-size:11px;color:#cbd5e1;">
            © {{ date('Y') }} ASCLAM · Automated Weekly Report
        </p>
    </div>

</div>
</body>
</html>
