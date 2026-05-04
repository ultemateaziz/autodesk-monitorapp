<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\UserProfile;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class ProfileController extends Controller
{
    public function index(\Illuminate\Http\Request $request, $userName = null)
    {
        $user = auth()->user();
        if (!$user) abort(401);

        $userRole = $user->role;
        $userDept = $user->department;

        $authorizedUsernames = null;
        if ($userRole === 'team_leader') {
            $assignments = \App\Models\MonitorAssignment::where('leader_id', $user->id)->pluck('monitored_user_name')->toArray();
            if (count($assignments) > 0) {
                $authorizedUsernames = $assignments;
            } elseif ($userDept) {
                $authorizedUsernames = UserProfile::where('department', $userDept)->pluck('user_name')->toArray();
            } else {
                $authorizedUsernames = [];
            }
        }

        if (!$userName) {
            $query = ActivityLog::distinct('user_name');
            if ($authorizedUsernames !== null) {
                $query->whereIn('user_name', $authorizedUsernames);
            }
            $userName = $query->first()?->user_name;
        }

        if (!$userName) {
            return "No user data found in logs that you are authorized to view.";
        }

        if ($userRole === 'team_leader' && $authorizedUsernames !== null && !in_array($userName, $authorizedUsernames)) {
            abort(403, 'Unauthorized. You do not have permission to monitor this user.');
        }

        $startDate = $request->get('from', now()->subDays(29)->toDateString());
        $endDate   = $request->get('to', now()->toDateString());

        $data = $this->buildReportData($userName, $startDate, $endDate);

        return view('profile', array_merge($data, ['startDate' => $startDate, 'endDate' => $endDate]));
    }

    public function exportPdf(\Illuminate\Http\Request $request, string $userName)
    {
        $this->authorizeUserAccess($userName);

        $startDate = $request->get('from', now()->subDays(29)->toDateString());
        $endDate   = $request->get('to', now()->toDateString());

        $data = $this->buildReportData($userName, $startDate, $endDate);

        $pdf = Pdf::loadView('profile_pdf', array_merge($data, [
            'startDate' => $startDate,
            'endDate'   => $endDate,
        ]))->setPaper('a4', 'portrait');

        $filename = 'report_' . preg_replace('/[^a-z0-9_]/i', '_', $userName) . '_' . $startDate . '_to_' . $endDate . '.pdf';

        return $pdf->download($filename);
    }

    public function exportExcel(\Illuminate\Http\Request $request, string $userName)
    {
        $this->authorizeUserAccess($userName);

        $startDate = $request->get('from', now()->subDays(29)->toDateString());
        $endDate   = $request->get('to', now()->toDateString());

        $data = $this->buildReportData($userName, $startDate, $endDate);

        $slug     = preg_replace('/[^a-z0-9_]/i', '_', $data['userName']);
        $filename = "performance_{$slug}_{$startDate}_to_{$endDate}.csv";

        $rows = [];

        // ── Report Header ──────────────────────────────────────────────
        $rows[] = ['HAZEMONITOR — USER PERFORMANCE REPORT', '', '', ''];
        $rows[] = ['Generated',  now()->format('d M Y H:i'), '', ''];
        $rows[] = ['Report Period', $startDate . ' → ' . $endDate, '', ''];
        $rows[] = ['', '', '', ''];

        // ── User Info ──────────────────────────────────────────────────
        $rows[] = ['USER INFORMATION', '', '', ''];
        $rows[] = ['Full Name',       $data['displayName'],         '', ''];
        $rows[] = ['Username',        $data['userName'],            '', ''];
        $rows[] = ['Department',      $data['department'],          '', ''];
        $rows[] = ['Machine',         $data['machineName'] ?? 'Unknown', '', ''];
        $rows[] = ['Online Status',   $data['isOnline'] ? 'Online' : ($data['isIdle'] ? 'Idle' : 'Offline'), '', ''];
        $rows[] = ['', '', '', ''];

        // ── KPI Summary ────────────────────────────────────────────────
        $rows[] = ['PERFORMANCE SUMMARY', '', '', ''];
        $rows[] = ['Metric',                    'Value',                                'Metric',                  'Value'];
        $rows[] = ['Total Hours (Period)',       $data['totalHours'],                   'Productivity Score',       $data['productivityScore'] . '%'];
        $rows[] = ['Days Active',               $data['activeDays'],                   'Primary Software',         $data['primarySoftware']];
        $rows[] = ['Primary Software %',        $data['primarySoftwarePercent'] . '%', 'Lifetime Total Hours',     $data['overallTotalHours']];
        $rows[] = ['All-Time Top Software',     $data['overallTopApp'],                '', ''];
        $rows[] = ['', '', '', ''];

        // ── Software Usage Breakdown ───────────────────────────────────
        $rows[] = ['SOFTWARE USAGE BREAKDOWN', '', '', ''];
        $rows[] = ['#', 'Software', 'Hours (decimal)', 'Minutes', 'Share %'];
        $totalMins = array_sum($data['donutMinutes']) ?: 1;
        foreach ($data['donutLabels'] as $i => $app) {
            $mins     = $data['donutMinutes'][$i];
            $remMins  = (int) round(fmod($mins, 60));
            $pct      = number_format($mins / $totalMins * 100, 1);
            $rows[]   = [
                $i + 1,
                $app,
                number_format($mins / 60, 2),
                str_pad($remMins, 2, '0', STR_PAD_LEFT),
                $pct . '%',
            ];
        }
        $rows[] = ['', '', '', '', ''];

        // ── 7-Day Efficiency Trend ─────────────────────────────────────
        $rows[] = ['7-DAY DAILY EFFICIENCY TREND', '', '', '', ''];
        $rows[] = ['Day', 'Efficiency %', '', '', ''];
        foreach ($data['sevenDaysLabels'] as $i => $day) {
            $rows[] = [$day, $data['sevenDaysData'][$i] . '%', '', '', ''];
        }
        $rows[] = ['', '', '', '', ''];

        // ── Recent Activity Logs ───────────────────────────────────────
        $rows[] = ['RECENT ACTIVITY FEED (Last 5 records)', '', '', '', ''];
        $rows[] = ['Date / Time', 'Application', 'Machine', 'Status', ''];
        foreach ($data['recentLogs'] as $log) {
            $rows[] = [
                $log->recorded_at->format('d M Y  H:i'),
                $log->application,
                $log->machine_name,
                $log->status,
                '',
            ];
        }

        // ── Build CSV ──────────────────────────────────────────────────
        $csv = "\xEF\xBB\xBF"; // UTF-8 BOM — makes Excel open correctly
        foreach ($rows as $row) {
            $csv .= implode(',', array_map(
                fn($v) => '"' . str_replace('"', '""', (string) $v) . '"',
                $row
            )) . "\r\n";
        }

        return response($csv, 200, [
            'Content-Type'        => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control'       => 'no-cache, no-store, must-revalidate',
            'Pragma'              => 'no-cache',
        ]);
    }

    private function authorizeUserAccess(string $userName): void
    {
        $user = auth()->user();
        if (!$user) abort(401);

        if ($user->role === 'team_leader') {
            $assignments = \App\Models\MonitorAssignment::where('leader_id', $user->id)->pluck('monitored_user_name')->toArray();
            $allowed = count($assignments) > 0
                ? $assignments
                : UserProfile::where('department', $user->department)->pluck('user_name')->toArray();

            if (!in_array($userName, $allowed)) {
                abort(403);
            }
        }
    }

    private function buildReportData(string $userName, string $startDate, string $endDate): array
    {
        $targetProfile = UserProfile::where('user_name', $userName)->first();
        $displayName   = $targetProfile?->display_name ?? $userName;
        $department    = $targetProfile?->department ?? 'Unassigned';

        $from = Carbon::parse($startDate)->startOfDay();
        $to   = Carbon::parse($endDate)->endOfDay();

        // Machine + online status
        $lastLog     = ActivityLog::where('user_name', $userName)->latest('recorded_at')->first();
        $machineName = $lastLog?->machine_name;

        $lastActiveLog = ActivityLog::where('user_name', $userName)
            ->latest('recorded_at')
            ->first();
        $lastSeenSec = $lastActiveLog ? $lastActiveLog->recorded_at->diffInSeconds(now()) : PHP_INT_MAX;
        $statusLower = strtolower($lastActiveLog?->status ?? '');
        $isOnline    = $lastSeenSec < 600 && in_array($statusLower, ['active', 'idle', 'open']);
        $isIdle      = $lastSeenSec < 600 && $statusLower === 'idle';

        // Range totals
        $rangeQuery = ActivityLog::where('user_name', $userName)
            ->whereBetween('recorded_at', [$from, $to])
            ->where('status', 'Active');

        $totalLogsInRange    = (clone $rangeQuery)->count();
        $totalSecondsInRange = $totalLogsInRange * 3;
        $th = floor($totalSecondsInRange / 3600);
        $tm = floor(($totalSecondsInRange % 3600) / 60);
        $totalHoursInRangeFormat  = "{$th}h {$tm}m";
        $totalHoursInRangeNumeric = $totalSecondsInRange / 3600;

        // Software aggregation
        $logs            = (clone $rangeQuery)->get();
        $aggregatedUsage = [];
        foreach ($logs as $log) {
            $cleanName = $this->mapApplicationName($log->application);
            $aggregatedUsage[$cleanName] = ($aggregatedUsage[$cleanName] ?? 0) + 1;
        }
        $aggregatedUsage = $this->mergeAppVersions($aggregatedUsage);
        arsort($aggregatedUsage);

        $primarySoftware        = !empty($aggregatedUsage) ? array_key_first($aggregatedUsage) : 'N/A';
        $primarySoftwareCount   = !empty($aggregatedUsage) ? current($aggregatedUsage) : 0;
        $primarySoftwarePercent = $totalLogsInRange > 0 ? round(($primarySoftwareCount / $totalLogsInRange) * 100) : 0;

        // Productivity
        $rangeDays         = max(1, $from->diffInDays($to) + 1);
        $productivityScore = round(min(($totalHoursInRangeNumeric / ($rangeDays * 8)) * 100, 100));

        $activeDays = (clone $rangeQuery)
            ->selectRaw('COUNT(DISTINCT DATE(recorded_at)) as day_count')
            ->value('day_count');

        // Trend
        $currentPeriodHours = (ActivityLog::where('user_name', $userName)->whereBetween('recorded_at', [now()->subDays(29)->startOfDay(), now()])->count() * 3) / 3600;
        $lastPeriodHours    = (ActivityLog::where('user_name', $userName)->whereBetween('recorded_at', [now()->subDays(59)->startOfDay(), now()->subDays(30)->endOfDay()])->count() * 3) / 3600;
        $currentPeriodScore = round(min(($currentPeriodHours / (30 * 8)) * 100, 100));
        $lastPeriodScore    = round(min(($lastPeriodHours    / (30 * 8)) * 100, 100));
        $trend              = $currentPeriodScore - $lastPeriodScore;

        // Timeline datasets (for chart on profile page)
        // Compare date strings directly — avoids Carbon diffInDays edge cases with startOfDay/endOfDay.
        $isMultiDay = $startDate !== $endDate;

        $appTimelineRaw = [];
        $appsFound      = [];

        if ($isMultiDay) {
            // Group by date (Y-m-d) for multi-day ranges
            foreach ($logs as $log) {
                $dateKey   = Carbon::parse($log->recorded_at)->toDateString();
                $cleanName = $this->mapApplicationName($log->application);
                $appTimelineRaw[$cleanName][$dateKey] = ($appTimelineRaw[$cleanName][$dateKey] ?? 0) + 1;
            }

            // Build ordered date axis covering full range
            $dateAxis = [];
            $cursor   = $from->copy();
            while ($cursor->lte($to)) {
                $dateAxis[] = $cursor->toDateString();
                $cursor->addDay();
            }
            $timelineLabels = array_map(fn($d) => Carbon::parse($d)->format('d M'), $dateAxis);
        } else {
            // Group by 30-minute slot for single-day (48 slots: 00:00, 00:30, 01:00, ... 23:30)
            foreach ($logs as $log) {
                $h    = intval($log->recorded_at->format('H'));
                $m    = intval($log->recorded_at->format('i'));
                $slot = $h * 2 + ($m >= 30 ? 1 : 0);
                $cleanName = $this->mapApplicationName($log->application);
                $appTimelineRaw[$cleanName][$slot] = ($appTimelineRaw[$cleanName][$slot] ?? 0) + 1;
            }
            $hourRange      = range(0, 47);
            $timelineLabels = array_map(fn($s) => sprintf("%02d:%02d", intdiv($s, 2), ($s % 2) * 30), $hourRange);
        }

        // Merge versioned duplicates (e.g. "AutoCAD" + "AutoCAD 2025" → "AutoCAD 2025")
        $appTimelineRaw = $this->mergeAppVersions($appTimelineRaw);
        $appsFound      = array_fill_keys(array_keys($appTimelineRaw), true);

        $appColorMap = [
            'AutoCAD' => '#3b82f6', 'Revit' => '#f97316', '3ds Max' => '#a855f7',
            'Navisworks' => '#06b6d4', 'InfraWorks' => '#10b981', 'ReCap Pro' => '#ef4444',
            'Autodesk Docs' => '#6366f1', 'FormIt' => '#ec4899',
            'Robot Structural Analysis' => '#f59e0b', 'Structural Bridge Design' => '#22d3ee',
            'Inventor' => '#84cc16', 'Fusion 360' => '#f43f5e',
            'Fabrication ESTmep' => '#d97706', 'Fabrication CAMduct' => '#7c3aed',
        ];
        $fallbackColors   = ['#94a3b8', '#64748b', '#475569'];
        $colorIdx         = 0;
        $timelineDatasets = [];

        foreach (array_keys($appsFound) as $appName) {
            $color = $fallbackColors[$colorIdx % count($fallbackColors)];
            foreach ($appColorMap as $key => $clr) {
                if (str_starts_with($appName, $key)) { $color = $clr; break; }
            }
            $dataPoints = [];
            if ($isMultiDay) {
                foreach ($dateAxis as $dateKey) {
                    $dataPoints[] = round((($appTimelineRaw[$appName][$dateKey] ?? 0) * 3) / 60, 1);
                }
            } else {
                // null for inactive 30-min slots — spanGaps:true on frontend connects active slots
                foreach ($hourRange as $slot) {
                    $raw = $appTimelineRaw[$appName][$slot] ?? null;
                    $dataPoints[] = $raw !== null ? round(($raw * 3) / 60, 1) : null;
                }
            }
            $timelineDatasets[] = [
                'label' => $appName, 'data' => $dataPoints,
                'borderColor' => $color, 'backgroundColor' => $color . '33',
                'fill' => $isMultiDay, 'tension' => 0.3, 'borderWidth' => 2.5,
                'spanGaps' => !$isMultiDay,
                'pointRadius' => $isMultiDay ? 3 : 5, 'pointHoverRadius' => 8,
                'pointBorderColor' => $color, 'pointBackgroundColor' => $color,
            ];
            $colorIdx++;
        }
        if (empty($timelineDatasets)) {
            $emptyCount = $isMultiDay ? count($dateAxis) : count($hourRange);
            $timelineDatasets[] = ['label' => 'No Activity', 'data' => array_fill(0, $emptyCount, 0), 'borderColor' => '#334155', 'fill' => false];
        }

        // Donut / software breakdown
        $donutLabels        = [];
        $donutMinutes       = [];
        $donutFormattedTimes = [];
        foreach ($aggregatedUsage as $app => $count) {
            $secs   = $count * 3;
            $h      = floor($secs / 3600);
            $m      = floor(($secs % 3600) / 60);
            $donutLabels[]         = $app;
            $donutMinutes[]        = round($secs / 60, 1);
            $donutFormattedTimes[] = $h > 0 ? "{$h}h {$m}m" : "{$m}m";
        }

        // Recent logs
        $recentLogs = (clone $rangeQuery)->orderBy('recorded_at', 'desc')->limit(5)->get();
        foreach ($recentLogs as $log) {
            $log->application = $this->mapApplicationName($log->application);
        }

        // 7-day trend
        $sevenDaysData   = [];
        $sevenDaysLabels = [];
        for ($i = 6; $i >= 0; $i--) {
            $date      = now()->subDays($i);
            $logsCount = ActivityLog::where('user_name', $userName)->whereDate('recorded_at', $date)->where('status', 'Active')->count();
            $eff       = round(min((($logsCount * 3 / 3600) / 8) * 100, 100));
            $sevenDaysData[]   = $eff;
            $sevenDaysLabels[] = $date->format('D');
        }

        // Lifetime totals
        $overallTotalLogs    = ActivityLog::where('user_name', $userName)->where('status', 'Active')->count();
        $overallTotalSeconds = $overallTotalLogs * 3;
        $oh = floor($overallTotalSeconds / 3600);
        $om = floor(($overallTotalSeconds % 3600) / 60);
        $overallTotalHoursFormat = "{$oh}h {$om}m";

        $overallAppRaw = ActivityLog::where('user_name', $userName)
            ->where('status', 'Active')
            ->selectRaw('application, COUNT(*) as cnt')
            ->groupBy('application')
            ->get();
        $overallAppUsage = [];
        foreach ($overallAppRaw as $row) {
            $cleanName = $this->mapApplicationName($row->application);
            $overallAppUsage[$cleanName] = ($overallAppUsage[$cleanName] ?? 0) + $row->cnt;
        }
        $overallAppUsage = $this->mergeAppVersions($overallAppUsage);
        arsort($overallAppUsage);
        $overallTopApp = !empty($overallAppUsage) ? array_key_first($overallAppUsage) : 'N/A';

        return [
            'userName'               => $userName,
            'displayName'            => $displayName,
            'department'             => $department,
            'machineName'            => $machineName,
            'isOnline'               => $isOnline,
            'isIdle'                 => $isIdle,
            'activeDays'             => $activeDays,
            'totalHours'             => $totalHoursInRangeFormat,
            'primarySoftware'        => $primarySoftware,
            'primarySoftwarePercent' => $primarySoftwarePercent,
            'productivityScore'      => $productivityScore,
            'trend'                  => $trend,
            'timelineLabels'         => $timelineLabels,
            'timelineDatasets'       => $timelineDatasets,
            'timelineIsMultiDay'     => $isMultiDay,
            'donutLabels'            => $donutLabels,
            'donutMinutes'           => $donutMinutes,
            'donutFormattedTimes'    => $donutFormattedTimes,
            'recentLogs'             => $recentLogs,
            'sevenDaysLabels'        => $sevenDaysLabels,
            'sevenDaysData'          => $sevenDaysData,
            'overallTotalHours'      => $overallTotalHoursFormat,
            'overallTopApp'          => $overallTopApp,
        ];
    }

    /**
     * Merge duplicate app entries caused by version suffix differences.
     * "AutoCAD" + "AutoCAD 2025" → "AutoCAD 2025" (versioned name wins, counts summed).
     * Works on flat arrays (app => count) and nested arrays (app => [key => count]).
     */
    private function mergeAppVersions(array $usage): array
    {
        $groups = []; // base_name => ['display' => bestName, 'data' => merged]

        foreach ($usage as $appName => $data) {
            $baseName = trim(preg_replace('/\s+20\d{2}$/', '', $appName));

            if (!isset($groups[$baseName])) {
                $groups[$baseName] = ['display' => $appName, 'data' => $data];
            } else {
                // Prefer the versioned name for display
                if ($appName !== $baseName) {
                    $groups[$baseName]['display'] = $appName;
                }
                // Merge data: either int (flat) or array (timeline nested)
                if (is_array($data)) {
                    foreach ($data as $key => $count) {
                        $groups[$baseName]['data'][$key] = ($groups[$baseName]['data'][$key] ?? 0) + $count;
                    }
                } else {
                    $groups[$baseName]['data'] += $data;
                }
            }
        }

        $result = [];
        foreach ($groups as $group) {
            $result[$group['display']] = $group['data'];
        }
        return $result;
    }

    public function exportSessionPdf(\Illuminate\Http\Request $request, string $userName)
    {
        $this->authorizeUserAccess($userName);

        $startDate = $request->get('from', now()->subDays(6)->toDateString());
        $endDate   = $request->get('to', now()->toDateString());

        $profile     = UserProfile::where('user_name', $userName)->first();
        $displayName = $profile?->display_name ?? $userName;
        $department  = $profile?->department ?? 'Unassigned';

        $dayGroups = $this->buildSessions($userName, $startDate, $endDate);

        // Build day-summary rows for PDF
        $daySummary = [];
        $overallSec = 0;
        $cursor     = Carbon::parse($startDate);
        $endCarbon  = Carbon::parse($endDate);

        while ($cursor->lte($endCarbon)) {
            $dateKey  = $cursor->toDateString();
            $sessions = $dayGroups[$dateKey] ?? [];
            $daySec   = array_sum(array_map(fn($s) => $s['poll_count'] * 3, $sessions));
            $overallSec += $daySec;

            $dh = floor($daySec / 3600);
            $dm = floor(($daySec % 3600) / 60);

            $daySummary[] = [
                'date'      => $cursor->format('d M Y'),
                'day'       => $cursor->format('D'),
                'sessions'  => count($sessions),
                'total'     => $daySec > 0 ? ($dh > 0 ? "{$dh}h {$dm}min" : "{$dm}min") : '—',
                'has_data'  => $daySec > 0,
            ];
            $cursor->addDay();
        }

        $oh = floor($overallSec / 3600);
        $om = floor(($overallSec % 3600) / 60);
        $overallTotal = "{$oh}h {$om}min";

        // Enrich dayGroups for the view (formatted times)
        $sessionsByDay = [];
        ksort($dayGroups);
        foreach ($dayGroups as $dateKey => $sessions) {
            $date   = Carbon::parse($dateKey);
            $daySec = 0;
            $rows   = [];

            foreach ($sessions as $session) {
                $secDur  = $session['poll_count'] * 3;
                $daySec += $secDur;
                $h       = floor($secDur / 3600);
                $m       = floor(($secDur % 3600) / 60);

                $rows[] = [
                    'start'    => $session['start']->format('H:i'),
                    'end'      => $session['end']->copy()->addSeconds(3)->format('H:i'),
                    'duration' => $h > 0 ? "{$h}h {$m}min" : "{$m}min",
                ];
            }

            $dh = floor($daySec / 3600);
            $dm = floor(($daySec % 3600) / 60);

            $sessionsByDay[$dateKey] = [
                'date_label' => $date->format('d M Y'),
                'day_label'  => $date->format('l'),
                'day_short'  => $date->format('D'),
                'sessions'   => $rows,
                'day_total'  => $dh > 0 ? "{$dh}h {$dm}min" : "{$dm}min",
            ];
        }

        $pdf = Pdf::loadView('session_report_pdf', [
            'userName'     => $userName,
            'displayName'  => $displayName,
            'department'   => $department,
            'startDate'    => $startDate,
            'endDate'      => $endDate,
            'daySummary'   => $daySummary,
            'sessionsByDay' => $sessionsByDay,
            'overallTotal' => $overallTotal,
        ])->setPaper('a4', 'portrait');

        $slug     = preg_replace('/[^a-z0-9_]/i', '_', $userName);
        $filename = "sessions_{$slug}_{$startDate}_to_{$endDate}.pdf";

        return $pdf->download($filename);
    }

    public function exportSessionReport(\Illuminate\Http\Request $request, string $userName)
    {
        $this->authorizeUserAccess($userName);

        $startDate = $request->get('from', now()->subDays(6)->toDateString());
        $endDate   = $request->get('to', now()->toDateString());

        $profile     = UserProfile::where('user_name', $userName)->first();
        $displayName = $profile?->display_name ?? $userName;
        $department  = $profile?->department ?? 'Unassigned';

        $dayGroups = $this->buildSessions($userName, $startDate, $endDate);

        $rows = [];

        // ── Header ────────────────────────────────────────────────────────
        $rows[] = ['HAZEMONITOR — SESSION ACTIVITY REPORT', '', '', '', ''];
        $rows[] = ['Generated',  now()->format('d M Y H:i'), '', '', ''];
        $rows[] = ['Person',     $displayName . ' (' . $userName . ')', '', '', ''];
        $rows[] = ['Department', $department, '', '', ''];
        $rows[] = ['Period',     Carbon::parse($startDate)->format('d M Y') . ' → ' . Carbon::parse($endDate)->format('d M Y'), '', '', ''];
        $rows[] = ['', '', '', '', ''];

        // ── Daily Summary ─────────────────────────────────────────────────
        $rows[] = ['DAILY SUMMARY', '', '', '', ''];
        $rows[] = ['Day', 'Date', 'Sessions', 'Total Time', ''];

        $overallSec = 0;
        $cursor     = Carbon::parse($startDate);
        $endCarbon  = Carbon::parse($endDate);

        while ($cursor->lte($endCarbon)) {
            $dateKey  = $cursor->toDateString();
            $sessions = $dayGroups[$dateKey] ?? [];
            $daySec   = array_sum(array_map(fn($s) => $s['poll_count'] * 3, $sessions));
            $overallSec += $daySec;

            $dh       = floor($daySec / 3600);
            $dm       = floor(($daySec % 3600) / 60);
            $dayLabel = $daySec > 0 ? ($dh > 0 ? "{$dh}h {$dm}min" : "{$dm}min") : '—';

            $rows[] = [
                $cursor->format('D'),
                $cursor->format('d M Y'),
                count($sessions),
                $dayLabel,
                '',
            ];
            $cursor->addDay();
        }

        $oh     = floor($overallSec / 3600);
        $om     = floor(($overallSec % 3600) / 60);
        $rows[] = ['', '', '', '', ''];
        $rows[] = ['OVERALL TOTAL', '', '', "{$oh}h {$om}min", ''];
        $rows[] = ['', '', '', '', ''];

        // ── Session Detail ────────────────────────────────────────────────
        $rows[] = ['SESSION DETAIL', '', '', '', ''];
        $rows[] = ['Date', 'Day', 'Start', 'End', 'Duration'];

        ksort($dayGroups);
        foreach ($dayGroups as $dateKey => $sessions) {
            $date   = Carbon::parse($dateKey);
            $daySec = 0;

            foreach ($sessions as $session) {
                $secDur  = $session['poll_count'] * 3;
                $daySec += $secDur;
                $h       = floor($secDur / 3600);
                $m       = floor(($secDur % 3600) / 60);
                $durLabel = $h > 0 ? "{$h}h {$m}min" : "{$m}min";

                $endDisplay = $session['end']->copy()->addSeconds(3);

                $rows[] = [
                    $date->format('d M Y'),
                    $date->format('D'),
                    $session['start']->format('H:i'),
                    $endDisplay->format('H:i'),
                    $durLabel,
                ];
            }

            $dh     = floor($daySec / 3600);
            $dm     = floor(($daySec % 3600) / 60);
            $rows[] = ['', '', '', 'Day Total:', ($dh > 0 ? "{$dh}h {$dm}min" : "{$dm}min")];
            $rows[] = ['', '', '', '', ''];
        }

        // ── Build CSV ─────────────────────────────────────────────────────
        $csv = "\xEF\xBB\xBF";
        foreach ($rows as $row) {
            $csv .= implode(',', array_map(
                fn($v) => '"' . str_replace('"', '""', (string) $v) . '"',
                $row
            )) . "\r\n";
        }

        $slug     = preg_replace('/[^a-z0-9_]/i', '_', $userName);
        $filename = "sessions_{$slug}_{$startDate}_to_{$endDate}.csv";

        return response($csv, 200, [
            'Content-Type'        => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control'       => 'no-cache, no-store, must-revalidate',
            'Pragma'              => 'no-cache',
        ]);
    }

    private function buildSessions(string $userName, string $startDate, string $endDate): array
    {
        $from = Carbon::parse($startDate)->startOfDay();
        $to   = Carbon::parse($endDate)->endOfDay();

        $logs = ActivityLog::where('user_name', $userName)
            ->whereBetween('recorded_at', [$from, $to])
            ->where('status', 'Active')
            ->orderBy('recorded_at')
            ->get(['recorded_at']);

        $GAP_THRESHOLD  = 300; // 5 min gap = new session
        $dayGroups      = [];
        $currentSession = null;
        $prevTime       = null;

        foreach ($logs as $log) {
            $time    = $log->recorded_at instanceof Carbon ? $log->recorded_at : Carbon::parse($log->recorded_at);
            $dateKey = $time->toDateString();

            if ($prevTime === null || $time->diffInSeconds($prevTime) > $GAP_THRESHOLD) {
                if ($currentSession !== null) {
                    $dayGroups[$currentSession['date']][] = $currentSession;
                }
                $currentSession = [
                    'date'        => $dateKey,
                    'start'       => $time->copy(),
                    'end'         => $time->copy(),
                    'poll_count'  => 1,
                ];
            } else {
                // Day boundary: close current session, start fresh for new date
                if ($dateKey !== $currentSession['date']) {
                    $dayGroups[$currentSession['date']][] = $currentSession;
                    $currentSession = [
                        'date'        => $dateKey,
                        'start'       => $time->copy(),
                        'end'         => $time->copy(),
                        'poll_count'  => 1,
                    ];
                } else {
                    $currentSession['end'] = $time->copy();
                    $currentSession['poll_count']++;
                }
            }
            $prevTime = $time;
        }

        if ($currentSession !== null) {
            $dayGroups[$currentSession['date']][] = $currentSession;
        }

        return $dayGroups;
    }

    /**
     * Safety Helper to clean up messy process names (e.g., "11892 acad" -> "AutoCAD")
     */
    private function mapApplicationName($rawName)
    {
        // Extract year before stripping it — avoids double append (e.g. "AutoCAD 2025 2025")
        $version = '';
        if (preg_match('/(20\d{2})/', $rawName, $matches)) {
            $version = ' ' . $matches[0];
        }

        // Remove the year from raw name before map lookup
        $cleanRaw = strtolower(trim(preg_replace('/\s*20\d{2}\s*/', ' ', $rawName)));

        $map = [
            'acad'             => 'AutoCAD',
            'revit'            => 'Revit',
            '3dsmax'           => '3ds Max',
            'roamer'           => 'Navisworks',
            'infraworks'       => 'InfraWorks',
            'recap'            => 'ReCap Pro',
            'desktopconnector' => 'Autodesk Docs',
            'formit'           => 'FormIt',
            'robot'            => 'Robot Structural Analysis',
            'sbd'              => 'Structural Bridge Design',
            'inventor'         => 'Inventor',
            'fusion360'        => 'Fusion 360',
            'estmep'           => 'Fabrication ESTmep',
            'camduct'          => 'Fabrication CAMduct',
        ];

        foreach ($map as $key => $clean) {
            if (str_contains($cleanRaw, $key)) {
                return $clean . $version;
            }
        }

        return ucfirst($cleanRaw) . $version;
    }
}
