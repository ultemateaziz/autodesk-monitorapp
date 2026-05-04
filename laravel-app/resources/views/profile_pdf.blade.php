<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Performance Report &mdash; {{ $displayName }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, Arial, sans-serif; background: #fff; color: #0f172a; font-size: 12px; }

        .report { max-width: 100%; padding: 30px 36px; }

        /* ── Header ── */
        .report-header-table { width: 100%; border-bottom: 3px solid #6366f1; padding-bottom: 16px; margin-bottom: 22px; }
        .brand-icon {
            width: 40px; height: 40px; border-radius: 10px;
            background: #6366f1; color: white;
            font-size: 18px; font-weight: 800; text-align: center;
            line-height: 40px; display: inline-block;
        }
        .brand-name { font-size: 17px; font-weight: 800; color: #6366f1; }
        .brand-sub  { font-size: 10px; color: #94a3b8; margin-top: 2px; }
        .report-meta { text-align: right; font-size: 10px; color: #64748b; line-height: 1.8; }
        .report-meta strong { color: #0f172a; }

        /* ── User card ── */
        .user-card {
            background: #6366f1; border-radius: 12px; padding: 18px 22px;
            color: white; margin-bottom: 22px; width: 100%;
        }
        .user-avatar {
            width: 50px; height: 50px; border-radius: 12px;
            background: rgba(255,255,255,0.2);
            font-size: 20px; font-weight: 800; color: white;
            text-align: center; line-height: 50px; display: inline-block;
        }
        .user-badge {
            background: rgba(255,255,255,0.15); border-radius: 8px;
            padding: 8px 16px; text-align: center;
        }
        .user-badge-val { font-size: 24px; font-weight: 800; color: white; }
        .user-badge-lbl { font-size: 10px; color: rgba(255,255,255,0.8); margin-top: 2px; }

        /* ── Section title ── */
        .section-title {
            font-size: 10px; font-weight: 800; text-transform: uppercase;
            letter-spacing: 1.2px; color: #6366f1; margin-bottom: 10px; margin-top: 20px;
            border-bottom: 1px solid #e2e8f0; padding-bottom: 5px;
        }

        /* ── KPI table ── */
        .kpi-table { width: 100%; border-collapse: separate; border-spacing: 8px; margin-bottom: 10px; }
        .kpi-cell {
            background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px;
            padding: 14px 12px; text-align: center; width: 25%;
        }
        .kpi-val { font-size: 20px; font-weight: 800; color: #0f172a; }
        .kpi-lbl { font-size: 9px; color: #64748b; margin-top: 4px; font-weight: 700;
                   text-transform: uppercase; letter-spacing: 0.5px; }

        /* ── Productivity bar ── */
        .score-bar-track { height: 12px; background: #e2e8f0; border-radius: 6px; width: 100%; margin-top: 4px; }
        .score-bar-fill  { height: 12px; background: #6366f1; border-radius: 6px; }

        /* ── Tables ── */
        .data-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .data-table thead th {
            background: #f1f5f9; font-size: 9px; font-weight: 700;
            text-transform: uppercase; letter-spacing: 0.5px; color: #64748b;
            padding: 9px 12px; text-align: left; border-bottom: 2px solid #e2e8f0;
        }
        .data-table tbody td { padding: 9px 12px; border-bottom: 1px solid #f1f5f9; font-size: 11px; }
        .data-table tbody tr:last-child td { border-bottom: none; }

        /* ── Bar cell ── */
        .bar-track { height: 5px; border-radius: 3px; background: #e2e8f0; }
        .bar-fill  { height: 5px; border-radius: 3px; background: #6366f1; }

        /* ── 7-day trend ── */
        .trend-table { width: 100%; border-collapse: separate; border-spacing: 6px; margin-bottom: 20px; }
        .trend-cell { text-align: center; background: #f8fafc; border-radius: 8px; padding: 10px 4px; }
        .trend-pct { font-size: 13px; font-weight: 800; }
        .trend-lbl { font-size: 9px; color: #64748b; font-weight: 700; margin-top: 4px; }

        /* ── Status chips ── */
        .chip-active { background: #dcfce7; color: #16a34a; padding: 2px 8px; border-radius: 20px; font-size: 9px; font-weight: 700; }
        .chip-idle   { background: #fef3c7; color: #92400e; padding: 2px 8px; border-radius: 20px; font-size: 9px; font-weight: 700; }

        /* ── Trend chips ── */
        .chip-up   { background: #dcfce7; color: #16a34a; padding: 3px 10px; border-radius: 20px; font-size: 10px; font-weight: 700; }
        .chip-down { background: #fee2e2; color: #dc2626; padding: 3px 10px; border-radius: 20px; font-size: 10px; font-weight: 700; }
        .chip-flat { background: #f1f5f9; color: #64748b; padding: 3px 10px; border-radius: 20px; font-size: 10px; font-weight: 700; }

        /* ── Footer ── */
        .report-footer {
            border-top: 1px solid #e2e8f0; padding-top: 12px; margin-top: 16px;
            font-size: 9px; color: #94a3b8;
        }
    </style>
</head>
<body>
<div class="report">

    {{-- ── Header ── --}}
    <table class="report-header-table">
        <tr>
            <td style="vertical-align:middle;">
                <table><tr>
                    <td style="vertical-align:middle; padding-right:12px;">
                        <div class="brand-icon">H</div>
                    </td>
                    <td style="vertical-align:middle;">
                        <div class="brand-name">HazeMonitor</div>
                        <div class="brand-sub">Individual User Performance Report</div>
                    </td>
                </tr></table>
            </td>
            <td class="report-meta" style="vertical-align:top; text-align:right;">
                <div><strong>Generated:</strong> {{ now()->format('d M Y, H:i') }}</div>
                <div><strong>Period:</strong> {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} &mdash; {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}</div>
                <div><strong>Type:</strong> Individual Performance</div>
            </td>
        </tr>
    </table>

    {{-- ── User Card ── --}}
    <table class="user-card" style="width:100%;">
        <tr>
            <td style="vertical-align:middle;">
                <table><tr>
                    <td style="vertical-align:middle; padding-right:14px;">
                        <div class="user-avatar">{{ strtoupper(substr($displayName, 0, 1)) }}</div>
                    </td>
                    <td style="vertical-align:middle; color:white;">
                        <div style="font-size:20px; font-weight:800;">{{ $displayName }}</div>
                        <div style="font-size:11px; opacity:0.85; margin-top:3px;">
                            {{ $userName }} &nbsp;&bull;&nbsp; {{ $department }} &nbsp;&bull;&nbsp; {{ $machineName ?? 'Unknown Machine' }}
                        </div>
                    </td>
                </tr></table>
            </td>
            <td style="text-align:right; vertical-align:middle;">
                <table style="margin-left:auto;"><tr>
                    <td style="padding-left:10px;">
                        <div class="user-badge">
                            <div class="user-badge-val">{{ $productivityScore }}%</div>
                            <div class="user-badge-lbl">Productivity</div>
                        </div>
                    </td>
                    <td style="padding-left:10px;">
                        <div class="user-badge">
                            <div class="user-badge-val">{{ $activeDays }}</div>
                            <div class="user-badge-lbl">Days Active</div>
                        </div>
                    </td>
                    <td style="padding-left:10px;">
                        <div class="user-badge">
                            <div class="user-badge-val">{{ $totalHours }}</div>
                            <div class="user-badge-lbl">Total Hours</div>
                        </div>
                    </td>
                </tr></table>
            </td>
        </tr>
    </table>

    {{-- ── KPI Summary ── --}}
    <div class="section-title">Performance Summary</div>
    <table class="kpi-table">
        <tr>
            <td class="kpi-cell">
                <div class="kpi-val">{{ $totalHours }}</div>
                <div class="kpi-lbl">Total Hours</div>
            </td>
            <td class="kpi-cell">
                <div class="kpi-val">{{ $activeDays }}</div>
                <div class="kpi-lbl">Days Active</div>
            </td>
            <td class="kpi-cell">
                <div class="kpi-val" style="font-size:14px;">{{ $primarySoftware }}</div>
                <div class="kpi-lbl">Primary Software</div>
            </td>
            <td class="kpi-cell">
                <div class="kpi-val">{{ $primarySoftwarePercent }}%</div>
                <div class="kpi-lbl">Primary SW Usage</div>
            </td>
        </tr>
        <tr>
            <td class="kpi-cell">
                <div class="kpi-val" style="font-size:14px;">{{ $overallTotalHours }}</div>
                <div class="kpi-lbl">Lifetime Hours</div>
            </td>
            <td class="kpi-cell">
                <div class="kpi-val" style="font-size:14px;">{{ $overallTopApp }}</div>
                <div class="kpi-lbl">All-Time Top App</div>
            </td>
            <td class="kpi-cell" colspan="2" style="text-align:left; padding:14px 18px;">
                <div style="font-size:11px; font-weight:700; margin-bottom:6px;">Productivity Score</div>
                <div style="font-size:24px; font-weight:800; color:#6366f1; margin-bottom:6px;">{{ $productivityScore }}%</div>
                <div class="score-bar-track">
                    <div class="score-bar-fill" style="width:{{ $productivityScore }}%;"></div>
                </div>
                <div style="margin-top:8px;">
                    @if ($trend > 0)
                        <span class="chip-up">&#8679; +{{ $trend }}% vs last period</span>
                    @elseif ($trend < 0)
                        <span class="chip-down">&#8681; {{ $trend }}% vs last period</span>
                    @else
                        <span class="chip-flat">&#8680; No change vs last period</span>
                    @endif
                </div>
            </td>
        </tr>
    </table>

    {{-- ── Software Usage ── --}}
    <div class="section-title">Software Usage Breakdown</div>
    @if (count($donutLabels) > 0)
    @php $totalMins = array_sum($donutMinutes) ?: 1; @endphp
    <table class="data-table">
        <thead>
            <tr>
                <th style="width:30px;">#</th>
                <th>Application</th>
                <th>Usage Time</th>
                <th>Hours</th>
                <th>Share %</th>
                <th style="width:120px;">Visual</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($donutLabels as $i => $app)
            @php
                $mins = $donutMinutes[$i];
                $pct  = round($mins / $totalMins * 100, 1);
            @endphp
            <tr>
                <td style="color:#94a3b8; font-size:10px;">{{ $i + 1 }}</td>
                <td style="font-weight:700;">{{ $app }}</td>
                <td>{{ $donutFormattedTimes[$i] }}</td>
                <td style="font-family:monospace;">{{ round($mins / 60, 1) }}h</td>
                <td style="font-weight:700; color:#6366f1;">{{ $pct }}%</td>
                <td>
                    <div class="bar-track">
                        <div class="bar-fill" style="width:{{ $pct }}%;"></div>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <p style="color:#94a3b8; margin-bottom:20px;">No software activity recorded in the selected period.</p>
    @endif

    {{-- ── 7-Day Trend ── --}}
    <div class="section-title">7-Day Daily Efficiency</div>
    <table class="trend-table">
        <tr>
            @foreach ($sevenDaysLabels as $i => $day)
            @php
                $eff   = $sevenDaysData[$i];
                $color = $eff >= 70 ? '#10b981' : ($eff >= 40 ? '#f59e0b' : '#ef4444');
            @endphp
            <td class="trend-cell">
                <div class="trend-pct" style="color:{{ $color }};">{{ $eff }}%</div>
                <div class="trend-lbl">{{ $day }}</div>
            </td>
            @endforeach
        </tr>
    </table>

    {{-- ── Recent Activity ── --}}
    <div class="section-title">Recent Activity Feed</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Date / Time</th>
                <th>Application</th>
                <th>Machine</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($recentLogs as $log)
            <tr>
                <td style="color:#64748b;">{{ $log->recorded_at->format('d M Y H:i') }}</td>
                <td style="font-weight:700;">{{ $log->application }}</td>
                <td style="font-size:10px; color:#64748b;">{{ $log->machine_name }}</td>
                <td>
                    @if ($log->status === 'Active')
                        <span class="chip-active">Active</span>
                    @else
                        <span class="chip-idle">{{ $log->status }}</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="4" style="color:#94a3b8; text-align:center; padding:16px;">No recent activity</td></tr>
            @endforelse
        </tbody>
    </table>

    {{-- ── Footer ── --}}
    <table class="report-footer" style="width:100%;">
        <tr>
            <td>HazeMonitor &mdash; Confidential</td>
            <td style="text-align:center;">{{ $displayName }} &bull; {{ $department }}</td>
            <td style="text-align:right;">{{ now()->format('d M Y') }}</td>
        </tr>
    </table>

</div>
</body>
</html>
