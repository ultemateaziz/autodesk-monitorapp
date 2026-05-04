<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Session Report &mdash; {{ $displayName }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, Arial, sans-serif; background: #fff; color: #0f172a; font-size: 11px; }

        .report { max-width: 100%; padding: 28px 36px; }

        /* ── Header ── */
        .report-header-table { width: 100%; border-bottom: 3px solid #f59e0b; padding-bottom: 14px; margin-bottom: 20px; }
        .brand-icon { width: 40px; height: 40px; border-radius: 10px; background: #f59e0b; color: white; font-size: 18px; font-weight: 800; text-align: center; line-height: 40px; display: inline-block; }
        .brand-name { font-size: 17px; font-weight: 800; color: #f59e0b; }
        .brand-sub  { font-size: 10px; color: #94a3b8; margin-top: 2px; }
        .report-meta { text-align: right; font-size: 10px; color: #64748b; line-height: 1.8; }
        .report-meta strong { color: #0f172a; font-weight: 700; }

        /* ── User card ── */
        .user-card { background: #f59e0b; border-radius: 10px; padding: 14px 18px; color: white; margin-bottom: 20px; width: 100%; }
        .user-name  { font-size: 16px; font-weight: 800; }
        .user-sub   { font-size: 10px; opacity: 0.85; margin-top: 2px; }
        .user-badge { background: rgba(255,255,255,0.2); border-radius: 8px; padding: 8px 14px; text-align: center; }
        .user-badge-val { font-size: 20px; font-weight: 800; }
        .user-badge-lbl { font-size: 9px; opacity: 0.85; margin-top: 2px; }

        /* ── Section title ── */
        .section-title { font-size: 9px; font-weight: 800; text-transform: uppercase; letter-spacing: 1.2px; color: #f59e0b; margin-bottom: 8px; margin-top: 18px; border-bottom: 1px solid #e2e8f0; padding-bottom: 4px; }

        /* ── Summary table ── */
        .data-table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        .data-table thead th { background: #fef3c7; font-size: 9px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; color: #92400e; padding: 8px 10px; text-align: left; border-bottom: 2px solid #f59e0b; }
        .data-table tbody td { padding: 8px 10px; border-bottom: 1px solid #fef3c7; font-size: 11px; }
        .data-table tbody tr:last-child td { border-bottom: none; }
        .data-table tbody tr.no-data td { color: #94a3b8; }

        /* ── Date header row (bold, amber bg) ── */
        .day-header { background: #fffbeb; }
        .day-header td { padding: 10px 10px 6px; border-bottom: 1px solid #fcd34d; }
        .day-date { font-size: 12px; font-weight: 800; color: #0f172a; }
        .day-name { font-size: 10px; color: #92400e; font-weight: 700; margin-left: 6px; }

        /* ── Session rows ── */
        .session-row td { padding: 6px 10px 6px 22px; border-bottom: 1px solid #f8fafc; font-size: 11px; }
        .session-time { color: #0f172a; }
        .session-dur  { color: #64748b; font-size: 10px; }

        /* ── Day total row ── */
        .day-total-row td { padding: 6px 10px; background: #f8fafc; font-size: 10px; font-weight: 700; color: #475569; border-bottom: 2px solid #e2e8f0; }

        /* ── Overall total ── */
        .overall-row td { padding: 10px 10px; background: #f59e0b; color: white; font-size: 13px; font-weight: 800; border-radius: 8px; }

        /* ── Footer ── */
        .report-footer { border-top: 1px solid #e2e8f0; padding-top: 10px; margin-top: 16px; font-size: 9px; color: #94a3b8; }
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
                        <div class="brand-sub">Individual Session Activity Report</div>
                    </td>
                </tr></table>
            </td>
            <td class="report-meta" style="vertical-align:top; text-align:right;">
                <div><strong>Generated:</strong> {{ now()->format('d M Y, H:i') }}</div>
                <div><strong>Period:</strong> <strong>{{ \Carbon\Carbon::parse($startDate)->format('d M Y') }}</strong> &mdash; <strong>{{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}</strong></div>
                <div><strong>Type:</strong> Session Activity Report</div>
            </td>
        </tr>
    </table>

    {{-- ── User Card ── --}}
    <table class="user-card" style="width:100%;">
        <tr>
            <td style="vertical-align:middle;">
                <div class="user-name">{{ $displayName }}</div>
                <div class="user-sub">{{ $userName }} &bull; {{ $department }}</div>
            </td>
            <td style="vertical-align:middle; text-align:right;">
                <div class="user-badge" style="display:inline-block;">
                    <div class="user-badge-val">{{ $overallTotal }}</div>
                    <div class="user-badge-lbl">TOTAL ACTIVE TIME</div>
                </div>
            </td>
        </tr>
    </table>

    {{-- ── Daily Summary ── --}}
    <div class="section-title">Daily Summary</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width:16%;">Day</th>
                <th style="width:30%;">Date</th>
                <th style="width:18%; text-align:center;">Sessions</th>
                <th style="text-align:right;">Total Time</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($daySummary as $row)
            <tr class="{{ $row['has_data'] ? '' : 'no-data' }}">
                <td style="font-weight:700;">{{ $row['day'] }}</td>
                <td style="font-weight:{{ $row['has_data'] ? '800' : '400' }}; color:{{ $row['has_data'] ? '#0f172a' : '#94a3b8' }};">
                    {{ $row['date'] }}
                </td>
                <td style="text-align:center; color:{{ $row['has_data'] ? '#0f172a' : '#94a3b8' }};">
                    {{ $row['sessions'] > 0 ? $row['sessions'] : '—' }}
                </td>
                <td style="text-align:right; font-weight:{{ $row['has_data'] ? '700' : '400' }}; color:{{ $row['has_data'] ? '#0f172a' : '#94a3b8' }};">
                    {{ $row['total'] }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- ── Session Detail ── --}}
    <div class="section-title">Session Detail</div>
    <table class="data-table" style="margin-bottom:0;">
        <thead>
            <tr>
                <th style="width:34%;">Date</th>
                <th style="width:22%; text-align:center;">Start</th>
                <th style="width:22%; text-align:center;">End</th>
                <th style="text-align:right;">Duration</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($sessionsByDay as $dateKey => $day)

                {{-- Bold date header row --}}
                <tr class="day-header">
                    <td colspan="4">
                        <span class="day-date">{{ $day['date_label'] }}</span>
                        <span class="day-name">{{ $day['day_label'] }}</span>
                    </td>
                </tr>

                {{-- Sessions for this day --}}
                @foreach ($day['sessions'] as $session)
                <tr class="session-row">
                    <td></td>
                    <td class="session-time" style="text-align:center; font-weight:600;">{{ $session['start'] }}</td>
                    <td class="session-time" style="text-align:center; font-weight:600;">{{ $session['end'] }}</td>
                    <td class="session-dur" style="text-align:right;">{{ $session['duration'] }}</td>
                </tr>
                @endforeach

                {{-- Day total --}}
                <tr class="day-total-row">
                    <td colspan="3" style="text-align:right; padding-right:8px;">Day Total:</td>
                    <td style="text-align:right;">{{ $day['day_total'] }}</td>
                </tr>

            @empty
                <tr>
                    <td colspan="4" style="text-align:center; color:#94a3b8; padding:20px;">No session data for this period.</td>
                </tr>
            @endforelse

            {{-- Overall total --}}
            @if (!empty($sessionsByDay))
            <tr>
                <td colspan="4" style="padding:8px 0 0;"></td>
            </tr>
            <tr class="overall-row">
                <td colspan="3" style="text-align:right; padding-right:10px;">Overall Total:</td>
                <td style="text-align:right;">{{ $overallTotal }}</td>
            </tr>
            @endif
        </tbody>
    </table>

    {{-- ── Footer ── --}}
    <div class="report-footer" style="margin-top:20px;">
        <table style="width:100%;"><tr>
            <td>HazeMonitor &mdash; Session Activity Report &mdash; Confidential</td>
            <td style="text-align:right;">Generated {{ now()->format('d M Y H:i') }}</td>
        </tr></table>
    </div>

</div>
</body>
</html>
