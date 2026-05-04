<!DOCTYPE html>
<html lang="en-GB">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Autodesk Monitor — {{ request('title') ?: 'Software Utilization Report' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* ===== BASE RESET ===== */
        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        :root {
            --blue: #3b82f6;
            --violet: #6366f1;
            --green: #10b981;
            --amber: #f59e0b;
            --red: #ef4444;
            --pink: #ec4899;
            --teal: #14b8a6;
            --navy: #0f172a;
            --navy2: #1e293b;
            --border: #334155;
            --text: #f1f5f9;
            --sub: #94a3b8;
            --card: #1e293b;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background: var(--navy);
            color: var(--text);
            font-size: 14px;
            line-height: 1.5;
        }

        /* ===== PRINT ACTION BAR (screen only) ===== */
        .print-bar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: linear-gradient(135deg, #1e293b, #0f172a);
            border-bottom: 1px solid var(--border);
            padding: 14px 32px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            z-index: 9999;
            gap: 20px;
        }

        .print-bar-left {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .print-bar-title {
            font-size: 16px;
            font-weight: 700;
            color: var(--text);
        }

        .print-bar-sub {
            font-size: 12px;
            color: var(--sub);
            margin-top: 1px;
        }

        .btn-print {
            background: linear-gradient(135deg, var(--blue), var(--violet));
            color: white;
            border: none;
            padding: 12px 28px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 700;
            font-family: 'Outfit', sans-serif;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 14px rgba(59, 130, 246, 0.4);
            transition: all 0.2s;
        }

        .btn-print:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 22px rgba(59, 130, 246, 0.5);
        }

        .btn-back {
            background: rgba(255, 255, 255, 0.06);
            color: var(--text);
            border: 1px solid var(--border);
            padding: 12px 24px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 600;
            font-family: 'Outfit', sans-serif;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            transition: all 0.2s;
        }

        .btn-back:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        /* ===== REPORT BODY ===== */
        .report-body {
            max-width: 1200px;
            margin: 0 auto;
            padding: 80px 40px 60px;
            /* top pad for the fixed bar */
        }

        /* ===== COVER PAGE ===== */
        .cover {
            background: linear-gradient(145deg, #1e293b 0%, #0f172a 100%);
            border: 1px solid var(--border);
            border-radius: 28px;
            padding: 50px 60px;
            margin-bottom: 36px;
            position: relative;
            overflow: visible;
            /* Prevent clipping metadata on edges */
        }

        .cover::before {
            content: '';
            position: absolute;
            top: -80px;
            right: -80px;
            width: 350px;
            height: 350px;
            background: radial-gradient(circle, rgba(99, 102, 241, 0.12) 0%, transparent 70%);
            border-radius: 50%;
        }

        .cover::after {
            content: '';
            position: absolute;
            bottom: -60px;
            left: -60px;
            width: 280px;
            height: 280px;
            background: radial-gradient(circle, rgba(59, 130, 246, 0.1) 0%, transparent 70%);
            border-radius: 50%;
        }

        .cover-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(59, 130, 246, 0.1);
            border: 1px solid rgba(59, 130, 246, 0.25);
            color: #93c5fd;
            padding: 6px 16px;
            border-radius: 30px;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.4px;
            text-transform: uppercase;
            margin-bottom: 20px;
        }

        .cover h1 {
            font-size: 44px;
            font-weight: 900;
            line-height: 1.2;
            margin-bottom: 18px;
            background: linear-gradient(135deg, #f1f5f9, #93c5fd);
            -webkit-background-clip: text;
            background-clip: text;
            color: #f1f5f9;
            /* Fallback for browsers not supporting bg-clip */
        }

        .cover-meta {
            font-size: 14px;
            color: var(--sub);
            display: flex;
            gap: 16px 32px;
            /* Row and column gap */
            flex-wrap: wrap;
            margin-top: 10px;
        }

        .cover-meta span {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            white-space: nowrap;
        }

        /* ===== SECTION HEADERS ===== */
        .section-title {
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--sub);
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .section-title::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--border);
        }

        /* ===== KPI CARDS ===== */
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-bottom: 32px;
        }

        .kpi-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 24px;
            position: relative;
            overflow: hidden;
            transition: transform 0.2s;
        }

        .kpi-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 3px;
            height: 100%;
            background: var(--accent, var(--blue));
        }

        .kpi-card .kpi-icon {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            background: var(--icon-bg, rgba(59, 130, 246, 0.1));
            color: var(--accent, var(--blue));
            margin-bottom: 14px;
        }

        .kpi-card .kpi-value {
            font-size: 32px;
            font-weight: 800;
            color: var(--text);
            line-height: 1;
        }

        .kpi-card .kpi-label {
            font-size: 12px;
            color: var(--sub);
            font-weight: 600;
            margin-top: 6px;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        /* ===== CHART PANELS ===== */
        .chart-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 32px;
        }

        .chart-panel {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 28px;
        }

        .chart-panel.full {
            grid-column: 1 / -1;
        }

        .chart-panel h3 {
            font-size: 15px;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .chart-panel p {
            font-size: 12px;
            color: var(--sub);
            margin-bottom: 20px;
        }

        .chart-wrap {
            height: 260px;
        }

        .chart-wrap.tall {
            height: 320px;
        }

        /* ===== HORIZONTAL BARS (App Usage) ===== */
        .bar-list {}

        .bar-row {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 10px;
        }

        .bar-row-label {
            width: 130px;
            font-size: 12px;
            font-weight: 600;
            color: var(--text);
            flex-shrink: 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .bar-track {
            flex: 1;
            height: 10px;
            background: rgba(255, 255, 255, 0.06);
            border-radius: 10px;
            overflow: hidden;
        }

        .bar-fill {
            height: 100%;
            border-radius: 10px;
            background: linear-gradient(90deg, var(--blue), var(--violet));
            transition: width 1s ease;
        }

        .bar-val {
            font-size: 12px;
            font-weight: 700;
            color: var(--sub);
            width: 58px;
            text-align: right;
            flex-shrink: 0;
        }

        /* ===== DEPT TABLE ===== */
        .dept-table {
            width: 100%;
            border-collapse: collapse;
        }

        .dept-table th {
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--sub);
            padding: 10px 14px;
            text-align: left;
            border-bottom: 1px solid var(--border);
        }

        .dept-table td {
            padding: 13px 14px;
            font-size: 13px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.04);
        }

        .dept-table tr:last-child td {
            border-bottom: none;
        }

        .dept-table tr:hover td {
            background: rgba(255, 255, 255, 0.02);
        }

        .rank-badge {
            width: 26px;
            height: 26px;
            border-radius: 8px;
            background: rgba(59, 130, 246, 0.1);
            color: var(--blue);
            font-size: 11px;
            font-weight: 800;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .rank-badge.gold {
            background: rgba(245, 158, 11, 0.15);
            color: var(--amber);
        }

        .rank-badge.silver {
            background: rgba(148, 163, 184, 0.12);
            color: #94a3b8;
        }

        .rank-badge.bronze {
            background: rgba(180, 83, 9, 0.12);
            color: #b45309;
        }

        /* ===== LICENSE HEALTH ===== */
        .lic-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 14px;
        }

        .lic-card {
            padding: 20px;
            border-radius: 16px;
            border: 1px solid var(--border);
            text-align: center;
        }

        .lic-card .lic-count {
            font-size: 36px;
            font-weight: 800;
            line-height: 1;
        }

        .lic-card .lic-label {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 8px;
        }

        .lic-card .lic-desc {
            font-size: 11px;
            color: var(--sub);
            margin-top: 4px;
        }

        /* ===== FOOTER ===== */
        .report-footer {
            margin-top: 48px;
            padding-top: 24px;
            border-top: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 12px;
            color: var(--sub);
        }

        /* ===== PRINT STYLES ===== */
        @media print {
            @page {
                size: A4 landscape;
                margin: 15mm 15mm;
            }

            body {
                background: #fff !important;
                color: #0f172a !important;
            }

            .print-bar {
                display: none !important;
            }

            .report-body {
                padding: 0;
                max-width: 100%;
            }

            :root {
                --navy: #fff;
                --navy2: #f8fafc;
                --card: #f8fafc;
                --border: #e2e8f0;
                --text: #0f172a;
                --sub: #64748b;
            }

            .cover {
                background: linear-gradient(145deg, #eff6ff, #f0f9ff) !important;
            }

            .cover h1 {
                -webkit-text-fill-color: #1e293b !important;
            }

            .chart-panel,
            .kpi-card,
            .lic-card,
            .dept-table td {
                break-inside: avoid;
            }

            .chart-grid {
                break-inside: avoid;
            }
        }
    </style>
</head>

<body>
    <!-- Print Action Bar (hidden when printing) -->
    <div class="print-bar">
        <div class="print-bar-left">
            <div
                style="width:38px;height:38px;border-radius:10px;background:linear-gradient(135deg,#3b82f6,#6366f1);display:flex;align-items:center;justify-content:center;font-size:16px;">
                <i class="fas fa-compass-drafting" style="color:white;"></i>
            </div>
            <div>
                <div class="print-bar-title">ACLM — Report Preview</div>
                <div class="print-bar-sub">Generated for {{ auth()->user()->name }} · {{ now()->format('d M Y, H:i') }}
                </div>
            </div>
        </div>
        <div style="display:flex;gap:12px;align-items:center;">
            <span
                style="font-size:12px;color:var(--sub);background:rgba(255,255,255,0.05);padding:8px 14px;border-radius:10px;border:1px solid var(--border);">
                <i class="fas fa-info-circle" style="margin-right:6px;color:#3b82f6;"></i>
                Press <strong>Ctrl+P</strong> → Save as PDF → Landscape
            </span>
            <a href="{{ route('report.hub') }}" class="btn-back">
                <i class="fas fa-arrow-left"></i> Back
            </a>
            <button class="btn-print" onclick="window.print()">
                <i class="fas fa-print"></i> Print / Save PDF
            </button>
        </div>
    </div>

    <div class="report-body">
        <!-- ===== COVER ===== -->
        <div class="cover">
            <div class="cover-badge">
                <i class="fas fa-compass-drafting"></i> Autodesk Monitor
            </div>
            <h1>{{ request('title') ?: 'Software Utilization Report' }}</h1>
            <div class="cover-meta">
                <span><i class="fas fa-calendar-alt"></i> {{ $from->format('d M Y') }} –
                    {{ $to->format('d M Y') }}</span>
                <span><i class="fas fa-clock"></i> {{ $days }}-Day Period</span>
                <span><i class="fas fa-building"></i> {{ $dept === 'all' ? 'All Departments' : $dept }}</span>
                <span><i class="fas fa-user-tie"></i> Prepared by {{ auth()->user()->name }}</span>
                <span><i class="fas fa-calendar-check"></i> {{ now()->format('d M Y, H:i') }}</span>
            </div>
        </div>

        @php $sections = request('sections', ['kpi','app_usage','dept','top_users','license','ghost','trend']); @endphp

        <!-- ===== KPI SUMMARY ===== -->
        @if (in_array('kpi', $sections))
            <div class="section-title">
                <i class="fas fa-chart-bar"></i> KPI Summary
            </div>
            <div class="kpi-grid" style="margin-bottom: 32px;">
                <div class="kpi-card" style="--accent:var(--blue);--icon-bg:rgba(59,130,246,0.1);">
                    <div class="kpi-icon"><i class="fas fa-clock"></i></div>
                    <div class="kpi-value">{{ $totalUsageLabel }}</div>
                    <div class="kpi-label">{{ $totalUsageSub }}</div>
                </div>
                <div class="kpi-card" style="--accent:var(--green);--icon-bg:rgba(16,185,129,0.1);">
                    <div class="kpi-icon"><i class="fas fa-users"></i></div>
                    <div class="kpi-value">{{ $uniqueUsers }}</div>
                    <div class="kpi-label">Unique Users</div>
                </div>
                <div class="kpi-card" style="--accent:var(--violet);--icon-bg:rgba(99,102,241,0.1);">
                    <div class="kpi-icon"><i class="fas fa-layer-group"></i></div>
                    <div class="kpi-value">{{ $uniqueApps }}</div>
                    <div class="kpi-label">Unique Applications</div>
                </div>
                <div class="kpi-card" style="--accent:var(--amber);--icon-bg:rgba(245,158,11,0.1);">
                    <div class="kpi-icon"><i class="fas fa-ghost"></i></div>
                    <div class="kpi-value">{{ $ghostCount }}</div>
                    <div class="kpi-label">Ghost Machines</div>
                </div>
            </div>
        @endif

        <!-- ===== TREND + DEPT SIDE BY SIDE ===== -->
        <div class="chart-grid">
            @if (in_array('trend', $sections))
                <div class="chart-panel">
                    <h3><i class="fas fa-chart-line" style="color:var(--green);margin-right:8px;"></i>Daily Usage Trend
                    </h3>
                    <p>Total hours logged per day over the last {{ min($days, 30) }} days</p>
                    <div class="chart-wrap">
                        <canvas id="trendChart"></canvas>
                    </div>
                </div>
            @endif

            @if (in_array('app_usage', $sections))
                <div class="chart-panel">
                    <h3><i class="fas fa-layer-group" style="color:var(--violet);margin-right:8px;"></i>Application
                        Usage</h3>
                    <p>Top apps by total hours used in the period</p>
                    <div class="bar-list" style="margin-top:8px;">
                        @foreach ($appUsage as $idx => $row)
                            <div class="bar-row">
                                <div class="bar-row-label">{{ $row['app'] }}</div>
                                <div class="bar-track">
                                    <div class="bar-fill" style="width:{{ $row['pct'] }}%;"></div>
                                </div>
                                <div class="bar-val">{{ $row['label'] }}</div>
                            </div>
                        @endforeach
                        @if (empty($appUsage))
                            <p style="color:var(--sub);text-align:center;padding:30px 0;">No usage data found for this
                                period.</p>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        <!-- ===== DEPT EFFICIENCY + TOP USERS ===== -->
        <div class="chart-grid">
            @if (in_array('dept', $sections))
                <div class="chart-panel">
                    <h3><i class="fas fa-building" style="color:var(--amber);margin-right:8px;"></i>Department
                        Efficiency</h3>
                    <p>Average daily software usage hours per user</p>
                    <table class="dept-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Department</th>
                                <th>Users</th>
                                <th>Total Hrs</th>
                                <th>Avg / Day</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($deptData as $i => $d)
                                <tr>
                                    <td>
                                        <span
                                            class="rank-badge {{ $i === 0 ? 'gold' : ($i === 1 ? 'silver' : ($i === 2 ? 'bronze' : '')) }}">
                                            {{ $i + 1 }}
                                        </span>
                                    </td>
                                    <td style="font-weight:600;">{{ $d['dept'] }}</td>
                                    <td style="color:var(--sub);">{{ $d['users'] }}</td>
                                    <td>{{ $d['total_hrs'] }}h</td>
                                    <td>
                                        <span
                                            style="background:rgba(59,130,246,0.1);color:var(--blue);padding:3px 10px;border-radius:8px;font-weight:700;font-size:12px;">
                                            {{ $d['avg_day'] }}h/day
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                            @if (empty($deptData))
                                <tr>
                                    <td colspan="5" style="color:var(--sub);text-align:center;padding:30px;">No
                                        department data available.</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            @endif

            @if (in_array('top_users', $sections))
                <div class="chart-panel">
                    <h3><i class="fas fa-medal" style="color:var(--pink);margin-right:8px;"></i>Top Performers</h3>
                    <p>Users ranked by total software hours in the period</p>
                    <table class="dept-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>User</th>
                                <th>Dept</th>
                                <th>Usage</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($topUsers as $i => $u)
                                <tr>
                                    <td>
                                        <span
                                            class="rank-badge {{ $i === 0 ? 'gold' : ($i === 1 ? 'silver' : ($i === 2 ? 'bronze' : '')) }}">
                                            {{ $i + 1 }}
                                        </span>
                                    </td>
                                    <td style="font-weight:600;">{{ $u['name'] }}</td>
                                    <td style="color:var(--sub);font-size:12px;">{{ $u['dept'] }}</td>
                                    <td>
                                        <span
                                            style="background:rgba(236,72,153,0.1);color:var(--pink);padding:3px 10px;border-radius:8px;font-weight:700;font-size:12px;">
                                            {{ $u['label'] }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                            @if (empty($topUsers))
                                <tr>
                                    <td colspan="4" style="color:var(--sub);text-align:center;padding:30px;">No
                                        user data available.</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <!-- ===== LICENSE HEALTH ===== -->
        @if (in_array('license', $sections))
            <div class="section-title"><i class="fas fa-key"></i> License Health Overview</div>
            <div class="lic-grid" style="margin-bottom:32px;">
                <div class="lic-card" style="border-color:rgba(16,185,129,0.3);background:rgba(16,185,129,0.05);">
                    <div class="lic-count" style="color:var(--green);">{{ $licenseStats['justified'] }}</div>
                    <div class="lic-label" style="color:var(--green);">✓ Justified</div>
                    <div class="lic-desc">License usage is appropriate and efficient</div>
                </div>
                <div class="lic-card" style="border-color:rgba(245,158,11,0.3);background:rgba(245,158,11,0.05);">
                    <div class="lic-count" style="color:var(--amber);">{{ $licenseStats['warning'] }}</div>
                    <div class="lic-label" style="color:var(--amber);">⚠ Warning</div>
                    <div class="lic-desc">Bundle user using only 3–4 applications</div>
                </div>
                <div class="lic-card" style="border-color:rgba(239,68,68,0.3);background:rgba(239,68,68,0.05);">
                    <div class="lic-count" style="color:var(--red);">{{ $licenseStats['critical'] }}</div>
                    <div class="lic-label" style="color:var(--red);">✕ Critical</div>
                    <div class="lic-desc">Bundle user using only 1–2 apps — downgrade candidate</div>
                </div>
                <div class="lic-card" style="border-color:rgba(148,163,184,0.2);background:rgba(148,163,184,0.04);">
                    <div class="lic-count" style="color:var(--sub);">{{ $licenseStats['ghost'] }}</div>
                    <div class="lic-label" style="color:var(--sub);">◯ Ghost</div>
                    <div class="lic-desc">No software usage detected in the past 90 days</div>
                </div>
            </div>

            <!-- License Health Doughnut via Chart.js -->
            <div class="chart-panel full" style="margin-bottom:32px;">
                <div style="display:grid;grid-template-columns:220px 1fr;gap:40px;align-items:center;">
                    <div>
                        <h3 style="margin-bottom:4px;"><i class="fas fa-chart-pie"
                                style="color:var(--violet);margin-right:8px;"></i>License Distribution</h3>
                        <p style="margin-bottom:16px;">Visual breakdown of all {{ array_sum($licenseStats) }} assigned
                            licenses</p>
                        <div class="chart-wrap" style="height:200px;">
                            <canvas id="licenseChart"></canvas>
                        </div>
                    </div>
                    <div>
                        @php
                            $totalLic = array_sum($licenseStats);
                            $licRows = [
                                ['Justified', $licenseStats['justified'], '#10b981'],
                                ['Warning', $licenseStats['warning'], '#f59e0b'],
                                ['Critical', $licenseStats['critical'], '#ef4444'],
                                ['Ghost', $licenseStats['ghost'], '#64748b'],
                            ];
                        @endphp
                        @foreach ($licRows as [$label, $count, $color])
                            <div style="margin-bottom:14px;">
                                <div style="display:flex;justify-content:space-between;margin-bottom:6px;">
                                    <span
                                        style="font-size:13px;font-weight:600;display:flex;align-items:center;gap:8px;">
                                        <span
                                            style="width:10px;height:10px;background:{{ $color }};border-radius:3px;display:inline-block;"></span>
                                        {{ $label }}
                                    </span>
                                    <span
                                        style="font-size:13px;font-weight:700;color:{{ $color }};">{{ $count }}</span>
                                </div>
                                <div
                                    style="height:8px;background:rgba(255,255,255,0.06);border-radius:8px;overflow:hidden;">
                                    <div
                                        style="height:100%;width:{{ $totalLic > 0 ? round(($count / $totalLic) * 100) : 0 }}%;background:{{ $color }};border-radius:8px;">
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        <!-- ===== GHOST MACHINES SUMMARY ===== -->
        @if (in_array('ghost', $sections))
            <div class="chart-panel full" style="margin-bottom:32px;border-color:rgba(239,68,68,0.2);">
                <div style="display:flex;align-items:center;gap:20px;">
                    <div
                        style="width:60px;height:60px;border-radius:16px;background:rgba(239,68,68,0.1);color:var(--red);font-size:28px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="fas fa-ghost"></i>
                    </div>
                    <div style="flex:1;">
                        <h3 style="font-size:17px;margin-bottom:4px;"><i class="fas fa-exclamation-triangle"
                                style="color:var(--red);margin-right:8px;"></i>Ghost Machines Detected</h3>
                        <p>Machines with <strong style="color:var(--text);">no agent heartbeat in the last 30
                                days.</strong> These may be decommissioned, offline, or have a crashed monitoring agent.
                        </p>
                    </div>
                    <div
                        style="text-align:center;flex-shrink:0;padding:16px 32px;border-left:1px solid var(--border);">
                        <div style="font-size:48px;font-weight:900;color:var(--red);line-height:1;">
                            {{ $ghostCount }}</div>
                        <div
                            style="font-size:12px;color:var(--sub);font-weight:700;text-transform:uppercase;margin-top:4px;">
                            Ghost Machines</div>
                    </div>
                </div>
                @if ($ghostCount > 0)
                    <div
                        style="margin-top:20px;padding:14px 18px;background:rgba(239,68,68,0.06);border-radius:12px;border:1px solid rgba(239,68,68,0.15);font-size:13px;color:#fca5a5;">
                        <i class="fas fa-lightbulb" style="margin-right:8px;"></i>
                        <strong>Recommendation:</strong> Investigate these machines. Uninstall the monitoring agent if
                        decommissioned, or re-install if the workstation is still in use. Visit the Ghost Machines
                        section for the full list.
                    </div>
                @endif
            </div>
        @endif

        <!-- ===== REPORT FOOTER ===== -->
        <div class="report-footer">
            <div>
                <strong>ACLM</strong> — Autodesk Monitor Platform
            </div>
            <div>
                This report is confidential. Generated on {{ now()->format('l, d F Y \a\t H:i') }}.
            </div>
            <div>
                Prepared by <strong>{{ auth()->user()->name }}</strong> ·
                @if (auth()->user()->role === 'admin')
                    IT Manager
                @elseif(auth()->user()->role === 'management')
                    Management
                @else
                    Contract Manager
                @endif
            </div>
        </div>
    </div><!-- /report-body -->

    <script>
        // Trend Chart
        @if (in_array('trend', $sections))
            (function() {
                const ctx = document.getElementById('trendChart');
                if (!ctx) return;
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: {!! json_encode($trendLabels) !!},
                        datasets: [{
                            label: 'Hours',
                            data: {!! json_encode($trendValues) !!},
                            borderColor: '#10b981',
                            backgroundColor: 'rgba(16,185,129,0.08)',
                            borderWidth: 2.5,
                            pointBackgroundColor: '#10b981',
                            pointRadius: 4,
                            pointHoverRadius: 6,
                            fill: true,
                            tension: 0.35
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
                                    family: 'Outfit'
                                },
                                bodyFont: {
                                    family: 'Outfit'
                                },
                                padding: 12,
                                cornerRadius: 10
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: {
                                    color: 'rgba(255,255,255,0.05)'
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
                                        family: 'Outfit',
                                        size: 10
                                    },
                                    maxRotation: 45
                                }
                            }
                        }
                    }
                });
            })();
        @endif

        // License Doughnut
        @if (in_array('license', $sections))
            (function() {
                const ctx = document.getElementById('licenseChart');
                if (!ctx) return;
                new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Justified', 'Warning', 'Critical', 'Ghost'],
                        datasets: [{
                            data: [{{ $licenseStats['justified'] }}, {{ $licenseStats['warning'] }},
                                {{ $licenseStats['critical'] }}, {{ $licenseStats['ghost'] }}
                            ],
                            backgroundColor: ['#10b981', '#f59e0b', '#ef4444', '#475569'],
                            borderColor: '#1e293b',
                            borderWidth: 3,
                            hoverOffset: 6
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '68%',
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                backgroundColor: '#1e293b',
                                titleFont: {
                                    family: 'Outfit'
                                },
                                bodyFont: {
                                    family: 'Outfit'
                                },
                                padding: 12,
                                cornerRadius: 10
                            }
                        }
                    }
                });
            })();
        @endif
    </script>
</body>

</html>
