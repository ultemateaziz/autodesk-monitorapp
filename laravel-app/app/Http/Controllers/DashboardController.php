<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\AuditLog;
use App\Models\UserLicense;
use App\Models\DismissedNotification;
use App\Models\UserProfile;
use App\Models\RevokedSoftware;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // --- Input Handling ---
        $period = $request->get('period', 'daily');
        $startDate = $request->get('from', now()->toDateString());
        $endDate = $request->get('to', now()->toDateString());
        
        $dept = $request->get('department', 'all');
        if (auth()->user()->role === 'team_leader') {
            $dept = auth()->user()->department ?: 'unassigned';
        }

        $from = Carbon::parse($startDate)->startOfDay();
        $to = Carbon::parse($endDate)->endOfDay();

        // Get authorized usernames (Respecting monitor assignments or department)
        $authorizedUsernames = $this->getAuthorizedUsernames($dept);

        // 1. Current Online Users (Always Live - ignore date filters, but respect auth)
        $onlineQuery = ActivityLog::where('recorded_at', '>=', now()->subSeconds(600));
        if ($authorizedUsernames !== null) {
            $onlineQuery->whereIn('user_name', $authorizedUsernames);
        }
        $onlineUsers = $onlineQuery->distinct('user_name')->count('user_name');

        // 2. Total Time Worked in Selected Range (Respect Auth)
        $totalLogsQuery = ActivityLog::whereBetween('recorded_at', [$from, $to]);
        if ($authorizedUsernames !== null) {
            $totalLogsQuery->whereIn('user_name', $authorizedUsernames);
        }
        $totalLogsInRange = (clone $totalLogsQuery)->count();
        $totalSeconds     = $totalLogsInRange * 3;
        $totalHours       = floor($totalSeconds / 3600);
        $totalMinutes     = floor(($totalSeconds % 3600) / 60);

        // 3. Market Share — period-adjusted date range (matches timeline chart)
        $marketFrom = match($period) {
            'weekly'  => Carbon::parse($endDate)->subDays(6)->startOfDay(),
            'monthly' => Carbon::parse($endDate)->subDays(29)->startOfDay(),
            default   => $from,
        };
        $marketTo = $to;

        $marketQuery = ActivityLog::whereBetween('recorded_at', [$marketFrom, $marketTo])
            ->selectRaw('application, COUNT(*) as cnt, COUNT(DISTINCT user_name) as user_count');
        if ($authorizedUsernames !== null) {
            $marketQuery->whereIn('user_name', $authorizedUsernames);
        }
        $rawMarket = $marketQuery->groupBy('application')->get();

        // Map raw application names to friendly names and aggregate
        $aggregatedMarket      = [];
        $aggregatedUserCounts  = [];
        foreach ($rawMarket as $row) {
            $cleanName = $this->mapApplicationName($row->application);
            $aggregatedMarket[$cleanName]     = ($aggregatedMarket[$cleanName] ?? 0) + $row->cnt;
            $aggregatedUserCounts[$cleanName] = ($aggregatedUserCounts[$cleanName] ?? 0) + $row->user_count;
        }
        arsort($aggregatedMarket);
        $marketShare = [];
        foreach ($aggregatedMarket as $app => $count) {
            $totalSeconds = $count * 3;
            $h = floor($totalSeconds / 3600);
            $m = floor(($totalSeconds % 3600) / 60);
            $formattedTime = $h > 0 ? "{$h}h {$m}m" : "{$m}m";

            $marketShare[] = (object)[
                'application'    => $app,
                'count'          => $count,
                'user_count'     => $aggregatedUserCounts[$app] ?? 0,
                'minutes'        => round($totalSeconds / 60, 1),
                'formatted_time' => $formattedTime
            ];
        }

        // 4. Dynamic Productivity Data Layout (Respect Department)
        $timelineLabels = [];
        
        // Define common query closure for timeline
        // Only select the columns we actually need for the timeline (recorded_at + application)
        $getTimelineLogs = function($f, $t) use ($authorizedUsernames) {
            $q = ActivityLog::whereBetween('recorded_at', [$f, $t])
                ->select('application', 'recorded_at');
            if ($authorizedUsernames !== null) {
                $q->whereIn('user_name', $authorizedUsernames);
            }
            return $q->get();
        };

        $appTimelineRaw = [];
        $appsFound = [];
        
        if ($period == 'hourly') {
            // Bucketed by 5-minute intervals for the selected day/range
            $timelineLabels = ["00'","05'","10'","15'","20'","25'","30'","35'","40'","45'","50'","55'"];
            $logs = $getTimelineLogs($from, $to);
            foreach ($logs as $log) {
                $cleanName = $this->mapApplicationName($log->application);
                $appsFound[$cleanName] = true;
                $minute = intval(Carbon::parse($log->recorded_at)->format('i'));
                $bucket = floor($minute / 5);
                $appTimelineRaw[$cleanName][$bucket] = ($appTimelineRaw[$cleanName][$bucket] ?? 0) + 1;
            }
            $numBuckets = 12;
        } elseif ($period == 'weekly') {
            // Last 7 days bucketed by day
            $from = Carbon::parse($endDate)->subDays(6)->startOfDay();
            $timelineLabels = [];
            for ($i = 6; $i >= 0; $i--) { $timelineLabels[] = Carbon::parse($endDate)->subDays($i)->format('D d/m'); }
            $logs = $getTimelineLogs($from, $to);
            foreach ($logs as $log) {
                $cleanName = $this->mapApplicationName($log->application);
                $appsFound[$cleanName] = true;
                $dateStr = Carbon::parse($log->recorded_at)->format('D d/m');
                $bucketIdx = array_search($dateStr, $timelineLabels);
                if ($bucketIdx !== false) $appTimelineRaw[$cleanName][$bucketIdx] = ($appTimelineRaw[$cleanName][$bucketIdx] ?? 0) + 1;
            }
            $numBuckets = 7;
        } elseif ($period == 'monthly') {
            // Last 30 days bucketed by day
            $from = Carbon::parse($endDate)->subDays(29)->startOfDay();
            $timelineLabels = [];
            for ($i = 29; $i >= 0; $i--) { $timelineLabels[] = Carbon::parse($endDate)->subDays($i)->format('d'); }
            $logs = $getTimelineLogs($from, $to);
            foreach ($logs as $log) {
                $cleanName = $this->mapApplicationName($log->application);
                $appsFound[$cleanName] = true;
                $dayStr = Carbon::parse($log->recorded_at)->format('d');
                $bucketIdx = array_search($dayStr, $timelineLabels);
                if ($bucketIdx !== false) $appTimelineRaw[$cleanName][$bucketIdx] = ($appTimelineRaw[$cleanName][$bucketIdx] ?? 0) + 1;
            }
            $numBuckets = 30;
        } else {
            // Default: Daily (24 hours bucketed by hour)
            $timelineLabels = array_map(fn($h) => sprintf("%02d:00", $h), range(0, 23));
            $logs = $getTimelineLogs($from, $to);
            foreach ($logs as $log) {
                $cleanName = $this->mapApplicationName($log->application);
                $appsFound[$cleanName] = true;
                $hour = intval(Carbon::parse($log->recorded_at)->format('H'));
                $appTimelineRaw[$cleanName][$hour] = ($appTimelineRaw[$cleanName][$hour] ?? 0) + 1;
            }
            $numBuckets = 24;
        }

        $chartDatasets = [];
        $appColorMap = [
            'AutoCAD'                   => '#3b82f6',
            'Revit'                     => '#f97316',
            '3ds Max'                   => '#a855f7',
            'Navisworks'                => '#06b6d4',
            'InfraWorks'                => '#10b981',
            'ReCap Pro'                 => '#ef4444',
            'Autodesk Docs'             => '#6366f1',
            'FormIt'                    => '#ec4899',
            'Robot Structural Analysis' => '#f59e0b',
            'Structural Bridge Design'  => '#22d3ee',
            'Inventor'                  => '#84cc16',
            'Fusion 360'                => '#f43f5e',
            'Fabrication ESTmep'        => '#d97706',
            'Fabrication CAMduct'       => '#7c3aed',
        ];
        $fallbackColors = ['#94a3b8', '#64748b', '#475569', '#334155'];
        $colorIdx = 0;

        foreach (array_keys($appsFound) as $appName) {
            $color = $fallbackColors[$colorIdx % count($fallbackColors)];
            foreach ($appColorMap as $key => $clr) {
                if (str_starts_with($appName, $key)) { $color = $clr; break; }
            }
            $bucketValues = [];
            for ($i = 0; $i < $numBuckets; $i++) {
                $count = $appTimelineRaw[$appName][$i] ?? 0;
                $minutes = round(($count * 3) / 60, 1);
                $bucketValues[] = $minutes;
            }
            $chartDatasets[] = [
                'label'           => $appName,
                'data'            => $bucketValues,
                'borderColor'     => $color,
                'backgroundColor' => $color . '22',
                'fill'            => true,
                'tension'         => 0.4,
                'borderWidth'     => 2.5,
                'pointRadius'     => 4,
                'pointHoverRadius'=> 7,
                'pointBorderColor'=> $color,
                'pointBackgroundColor' => $color,
            ];
            $colorIdx++;
        }

        $deptList = ['Architecture', 'MEP', 'Structural', 'Infrastructure', 'Visualization'];

        // --- Top Performers (across all software) ---
        $topUsersRaw = ActivityLog::whereBetween('recorded_at', [$from, $to]);
        if ($authorizedUsernames !== null) {
            $topUsersRaw->whereIn('user_name', $authorizedUsernames);
        }
        $topUsersSummary = $topUsersRaw->selectRaw('user_name, COUNT(*) as cnt')
            ->groupBy('user_name')
            ->orderBy('cnt', 'desc')
            ->limit(5)
            ->get();

        $topUsers = [];
        foreach ($topUsersSummary as $u) {
            $profile = UserProfile::where('user_name', $u->user_name)->first();
            $totalSeconds = $u->cnt * 3;
            $h = floor($totalSeconds / 3600);
            $m = floor(($totalSeconds % 3600) / 60);

            $topUsers[] = (object)[
                'user_name' => $u->user_name,
                'name' => ($profile && $profile->display_name) ? $profile->display_name : $u->user_name,
                'department' => $profile ? $profile->department : 'Unassigned',
                'time_formatted' => "{$h}h {$m}m",
                'avatar' => "https://ui-avatars.com/api/?name=" . urlencode(($profile && $profile->display_name) ? $profile->display_name : $u->user_name) . "&background=random&color=fff"
            ];
        }

        return view('dashboard', [
            'marketShare' => $marketShare,
            'onlineUsers' => $onlineUsers,
            'totalTime'    => "{$totalHours}h {$totalMinutes}m",
            'totalHours'   => $totalHours,
            'totalMinutes' => $totalMinutes,
            'timelineLabels' => $timelineLabels,
            'chartDatasets'  => $chartDatasets,
            'period'         => $period,
            'startDate'      => $startDate,
            'endDate'        => $endDate,
            'selectedDept'   => $dept,
            'deptList'       => $deptList,
            'topUsers'       => $topUsers
        ]);
    }

    public function exportCsv(Request $request)
    {
        $startDate = $request->get('from', now()->toDateString());
        $endDate = $request->get('to', now()->toDateString());
        $from = Carbon::parse($startDate)->startOfDay();
        $to = Carbon::parse($endDate)->endOfDay();

        $dept = 'all';
        if (auth()->user()->role === 'team_leader') {
            $dept = auth()->user()->department ?: 'unassigned';
        }

        $authorizedUsernames = $this->getAuthorizedUsernames($dept);

        // 1. Data Collection
        $query = ActivityLog::whereBetween('recorded_at', [$from, $to]);
        if ($authorizedUsernames !== null) {
            $query->whereIn('user_name', $authorizedUsernames);
        }
        $logs = $query->get();
        $totalLogsInRange = $logs->count();
        $totalSeconds_summary = $totalLogsInRange * 3;
        $totalHours = round($totalSeconds_summary / 3600, 2);
        $totalHours_h = floor($totalSeconds_summary / 3600);
        $totalHours_m = floor(($totalSeconds_summary % 3600) / 60);
        $totalFormattedTime = $totalHours_h > 0 ? "{$totalHours_h}h {$totalHours_m}m" : "{$totalHours_m}m";
        $totalRegisteredUsers = ActivityLog::whereBetween('recorded_at', [$from, $to]);
        if ($authorizedUsernames !== null) {
            $totalRegisteredUsers->whereIn('user_name', $authorizedUsernames);
        }
        $totalRegisteredUsers = $totalRegisteredUsers->distinct('user_name')->count('user_name');
        
        $appsFoundTotal = [];
        foreach ($logs as $log) {
            $cleanApp = $this->mapApplicationName($log->application);
            $appsFoundTotal[$cleanApp] = true;
        }
        $totalAppsTracted = count($appsFoundTotal);

        // --- NEW: User-wise Aggregation ---
        $userWiseSummary = [];
        foreach ($logs as $log) {
            $user = $log->user_name;
            $app = $this->mapApplicationName($log->application);
            $key = $user . '|' . $app;
            
            if (!isset($userWiseSummary[$key])) {
                $userWiseSummary[$key] = [
                    'user' => $user,
                    'machine' => $log->machine_name,
                    'app' => $app,
                    'count' => 0
                ];
            }
            $userWiseSummary[$key]['count']++;
        }

        $fileName = "Autodesk_Monitor_Report_{$startDate}_to_{$endDate}.csv";
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use ($logs, $totalRegisteredUsers, $totalFormattedTime, $totalAppsTracted, $startDate, $endDate, $userWiseSummary) {
            $file = fopen('php://output', 'w');

            // --- Summary Section ---
            fputcsv($file, ["AUTODESK MONITOR - ORGANIZATION PERFORMANCE REPORT"]);
            fputcsv($file, ["Report Period:", "$startDate to $endDate"]);
            fputcsv($file, []); // Empty line
            fputcsv($file, ["SUMMARY ANALYTICS"]);
            fputcsv($file, ["Total Users Active", $totalRegisteredUsers]);
            fputcsv($file, ["Total Productivity Time", $totalFormattedTime]);
            fputcsv($file, ["Unique Applications Tracked", $totalAppsTracted]);
            fputcsv($file, []); // Empty line
            fputcsv($file, []); // Empty line

            // --- User Summary Section ---
            fputcsv($file, ["USER-WISE USAGE SUMMARY (GROUPED BY PRODUCT)"]);
            fputcsv($file, ["User Name", "Team", "Machine", "Application", "Usage Time"]);

            foreach ($userWiseSummary as $item) {
                $totalSeconds = $item['count'] * 3;
                $h = floor($totalSeconds / 3600);
                $m = floor(($totalSeconds % 3600) / 60);
                $formattedTime = $h > 0 ? "{$h}h {$m}m" : "{$m}m";

                $profile = UserProfile::where('user_name', $item['user'])->first();
                $dept = $profile ? $profile->department : 'Unassigned';
                $nameForExport = $profile && $profile->display_name ? $profile->display_name . ' (' . $item['user'] . ')' : $item['user'];

                fputcsv($file, [
                    $nameForExport,
                    $dept,
                    $item['machine'],
                    $item['app'],
                    $formattedTime,
                ]);
            }
            fputcsv($file, []); // Empty line
            fputcsv($file, []); // Empty line

            // --- Detailed Data Section ---
            fputcsv($file, ["DETAILED ACTIVITY LOGS (RAW SECONDS DATA)"]);
            fputcsv($file, ["Log ID", "Date", "Time", "User Name", "Machine Name", "Application", "Status", "Duration"]);

            foreach ($logs as $log) {
                $dt = Carbon::parse($log->recorded_at);
                fputcsv($file, [
                    $log->id,
                    $dt->format('Y-m-d'),
                    $dt->format('H:i:s'),
                    $log->user_name,
                    $log->machine_name,
                    $this->mapApplicationName($log->application),
                    $log->status,
                    "3 Seconds"
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function mapApplicationName($rawName, $withVersion = true)
    {
        // Extract year before stripping it — avoids double append (e.g. "AutoCAD 2025 2025")
        $version = '';
        if (preg_match('/(20\d{2})/', $rawName, $matches)) {
            $version = ' ' . $matches[0];
        }

        // Remove the year from the raw name before map lookup
        $cleanRaw = strtolower(trim(preg_replace('/\s*20\d{2}\s*/', ' ', $rawName)));

        $map = [
            'acad' => 'AutoCAD',
            'revit' => 'Revit',
            '3dsmax' => '3ds Max',
            'roamer' => 'Navisworks',
            'infraworks' => 'InfraWorks',
            'recap' => 'ReCap Pro',
            'desktopconnector' => 'Autodesk Docs',
            'formit' => 'FormIt',
            'robot' => 'Robot Structural Analysis',
            'sbd' => 'Structural Bridge Design',
            'inventor' => 'Inventor',
            'fusion360' => 'Fusion 360',
            'estmep' => 'Fabrication ESTmep',
            'camduct' => 'Fabrication CAMduct',
        ];
        foreach ($map as $key => $clean) {
            if (str_contains($cleanRaw, $key)) return $clean . ($withVersion ? $version : '');
        }
        return ucfirst($cleanRaw) . ($withVersion ? $version : '');
    }

    public function users()
    {
        $dept = 'all';
        if (auth()->user()->role === 'team_leader') {
            $dept = auth()->user()->department ?: 'unassigned';
        }

        $authorizedUsernames = $this->getAuthorizedUsernames($dept);

        // Get distinct machines (each PC = one row, no more IP flipping)
        $machineQuery = ActivityLog::selectRaw('machine_name, MAX(user_name) as user_name')
            ->groupBy('machine_name');
        if ($authorizedUsernames !== null) {
            $machineQuery->whereIn('user_name', $authorizedUsernames);
        }
        $machines = $machineQuery->get(); // collection of {machine_name, user_name}
        $machineNames = $machines->pluck('machine_name');
        $usernames    = $machines->pluck('user_name')->unique();

        // 1. Latest log per machine — for display (app name, last seen)
        $lastLogs = ActivityLog::select('user_name', 'application', 'machine_name', 'ip_address', 'recorded_at', 'status')
            ->whereIn('machine_name', $machineNames)
            ->orderBy('recorded_at', 'desc')
            ->get()
            ->groupBy('machine_name')
            ->map(fn($logs) => $logs->first());

        // 1b. Latest Active/Idle log per machine — for status only (ignore background Open logs)
        $lastStatusLogs = ActivityLog::select('machine_name', 'recorded_at', 'status')
            ->whereIn('machine_name', $machineNames)
            ->whereIn('status', ['Active', 'Idle'])
            ->orderBy('recorded_at', 'desc')
            ->get()
            ->groupBy('machine_name')
            ->map(fn($logs) => $logs->first());

        // 2. Today's active log counts per machine
        $todayCounts = ActivityLog::selectRaw('machine_name, COUNT(*) as cnt')
            ->whereIn('machine_name', $machineNames)
            ->whereDate('recorded_at', today())
            ->where('status', 'Active')
            ->groupBy('machine_name')
            ->pluck('cnt', 'machine_name');

        // 3. Distinct apps per machine
        $allUsedSoftware = ActivityLog::selectRaw('machine_name, application')
            ->whereIn('machine_name', $machineNames)
            ->distinct()
            ->get()
            ->groupBy('machine_name')
            ->map(fn($rows) => $rows
                ->map(fn($r) => [
                    'base' => $this->mapApplicationName($r->application, false),
                    'full' => $this->mapApplicationName($r->application, true),
                ])
                ->sortByDesc('full')        // prefer versioned name ("AutoCAD 2025" over "AutoCAD")
                ->unique('base')            // deduplicate by base name
                ->pluck('full')
                ->values()
                ->toArray());

        // 4. Profiles keyed by user_name
        $allProfiles = UserProfile::whereIn('user_name', $usernames)
            ->get()
            ->keyBy('user_name');

        $users = [];
        foreach ($machines as $machine) {
            $machineName = $machine->machine_name;
            $userName    = $machine->user_name;
            $lastLog     = $lastLogs->get($machineName);
            $seconds     = $todayCounts->get($machineName, 0) * 3;
            $h = floor($seconds / 3600);
            $m = floor(($seconds % 3600) / 60);

            $profile     = $allProfiles->get($userName);
            $department  = $profile ? $profile->department  : 'Unassigned';
            $displayName = $profile ? $profile->display_name : null;
            $email       = $profile ? $profile->email : null;

            $statusLog   = $lastStatusLogs->get($machineName);
            $lastSeenSec = $statusLog ? $statusLog->recorded_at->diffInSeconds(now()) : PHP_INT_MAX;
            $statusLower = strtolower($statusLog->status ?? '');
            $isOnline    = $lastSeenSec < 600 && $statusLower === 'active';
            $isIdle      = $lastSeenSec < 600 && $statusLower === 'idle';

            $users[] = (object)[
                'name'             => $userName,
                'display_name'     => $displayName,
                'email'            => $email,
                'last_app'         => $this->mapApplicationName($lastLog ? $lastLog->application : 'N/A'),
                'last_seen'        => $lastLog ? $lastLog->recorded_at->diffForHumans() : 'Never',
                'is_online'        => $isOnline,
                'is_idle'          => $isIdle,
                'total_time_today' => "{$h}h {$m}m",
                'machine'          => $machineName,
                'ip_address'       => $lastLog ? $lastLog->ip_address : null,
                'used_software'    => $allUsedSoftware->get($machineName, []),
                'department'       => $department
            ];
        }

        $deptList = ['Architecture', 'MEP', 'Structural', 'Infrastructure', 'Visualization'];

        // Load all revoked software entries — keyed as user_name → [software_name => type]
        $revokedMap = RevokedSoftware::whereIn('user_name', $usernames)
            ->get()
            ->groupBy('user_name')
            ->map(fn($rows) => $rows->pluck('type', 'software_name')->toArray());

        return view('users', [
            'users'      => $users,
            'deptList'   => $deptList,
            'revokedMap' => $revokedMap,
        ]);
    }

    public function updateProfile(Request $request)
    {
        $request->validate([
            'user_name'    => 'required',
            'display_name' => 'nullable|string|max:255',
            'department'   => 'required',
            'email'        => 'nullable|email|max:255',
        ]);

        UserProfile::updateOrCreate(
            ['user_name' => $request->user_name],
            [
                'display_name' => $request->display_name,
                'department'   => $request->department,
                'email'        => $request->email,
            ]
        );

        return back()->with('success', 'User profile updated successfully!');
    }

    public function assignLicense(Request $request)
    {
        $validated = $request->validate([
            'user_name' => 'required|string',
            'software_name' => 'required|string',
        ]);

        UserLicense::firstOrCreate([
            'user_name' => $validated['user_name'],
            'software_name' => $validated['software_name'],
        ], [
            'assigned_date' => now()->toDateString(),
        ]);

        AuditLog::record($request, 'license_assigned', $validated['user_name'],
            'Assigned ' . $validated['software_name'] . ' license to ' . $validated['user_name']);

        return redirect()->back()->with('success', $validated['software_name'] . ' assigned to ' . $validated['user_name']);
    }

    public function removeLicense(Request $request)
    {
        $validated = $request->validate([
            'user_name' => 'required|string',
            'software_name' => 'required|string',
        ]);

        UserLicense::where('user_name', $validated['user_name'])
            ->where('software_name', $validated['software_name'])
            ->delete();

        AuditLog::record($request, 'license_removed', $validated['user_name'],
            'Removed ' . $validated['software_name'] . ' license from ' . $validated['user_name']);

        return redirect()->back()->with('success', $validated['software_name'] . ' removed from ' . $validated['user_name']);
    }

    public function revokeSoftware(Request $request)
    {
        $validated = $request->validate([
            'user_name'     => 'required|string',
            'software_name' => 'required|string',
        ]);

        RevokedSoftware::firstOrCreate(
            [
                'user_name'     => $validated['user_name'],
                'software_name' => $validated['software_name'],
            ],
            [
                'revoked_by' => auth()->user()->name,
                'type'       => 'suspended',
            ]
        );

        AuditLog::record($request, 'software_suspended', $validated['user_name'],
            'Suspended ' . $validated['software_name'] . ' access for ' . $validated['user_name']);

        return redirect()->back()->with('success', $validated['software_name'] . ' suspended for ' . $validated['user_name'] . '. Can be restored anytime.');
    }

    public function permanentlyRemoveSoftware(Request $request)
    {
        $validated = $request->validate([
            'user_name'     => 'required|string',
            'software_name' => 'required|string',
        ]);

        RevokedSoftware::updateOrCreate(
            [
                'user_name'     => $validated['user_name'],
                'software_name' => $validated['software_name'],
            ],
            [
                'revoked_by' => auth()->user()->name,
                'type'       => 'permanent',
            ]
        );

        AuditLog::record($request, 'software_permanent_removal', $validated['user_name'],
            'Permanently removed ' . $validated['software_name'] . ' from ' . $validated['user_name']);

        return redirect()->back()->with('success', $validated['software_name'] . ' permanently removed from ' . $validated['user_name'] . '. Monitoring stopped.');
    }

    public function restoreSoftware(Request $request)
    {
        $validated = $request->validate([
            'user_name'     => 'required|string',
            'software_name' => 'required|string',
        ]);

        RevokedSoftware::where('user_name', $validated['user_name'])
            ->where('software_name', $validated['software_name'])
            ->delete();

        AuditLog::record($request, 'software_restored', $validated['user_name'],
            'Restored ' . $validated['software_name'] . ' access for ' . $validated['user_name']);

        return redirect()->back()->with('success', $validated['software_name'] . ' access restored for ' . $validated['user_name']);
    }

    public function licenseAudit(Request $request)
    {
        $startDate = $request->get('from', now()->subDays(30)->toDateString());
        $endDate   = $request->get('to', now()->toDateString());
        $selectedApp = $request->get('software', 'all');
        
        $selectedDept = $request->get('department', 'all');
        if (auth()->user()->role === 'team_leader') {
            $selectedDept = auth()->user()->department ?: 'unassigned';
        }

        $from = $startDate ? Carbon::parse($startDate)->startOfDay() : now()->subDays(30)->startOfDay();
        $to = $endDate ? Carbon::parse($endDate)->endOfDay() : now()->endOfDay();

        $authorizedUsernames = $this->getAuthorizedUsernames($selectedDept);

        // Get all users who have assigned licenses
        $query = UserLicense::query();
        if ($selectedApp !== 'all') {
            $query->where('software_name', $selectedApp);
        }

        // Department Filter
        if ($authorizedUsernames !== null) {
            $query->whereIn('user_name', $authorizedUsernames);
        }

        $assignedUsers = $query->get();

        // Group assignments by user
        $userAssignments = [];
        foreach ($assignedUsers as $assignment) {
            $userAssignments[$assignment->user_name][] = $assignment->software_name;
        }

        // For each user with assignments, check actual usage
        $auditResults = [];
        $totalUnused = 0;
        $totalUsed = 0;

        foreach ($userAssignments as $userName => $assignedSoftware) {
            $lastLog = ActivityLog::where('user_name', $userName)->latest('recorded_at')->first();
            
            // Get user department
            $profile = UserProfile::where('user_name', $userName)->first();
            $dept = $profile ? $profile->department : 'Unassigned';

            foreach ($assignedSoftware as $software) {
                // Check if user used this specific software in the date range
                $usageCount = ActivityLog::where('user_name', $userName)
                    ->where('application', 'LIKE', "%{$software}%")
                    ->whereBetween('recorded_at', [$from, $to])
                    ->count();

                $totalSeconds = $usageCount * 3;
                $h = floor($totalSeconds / 3600);
                $m = floor(($totalSeconds % 3600) / 60);
                $formattedTime = $h > 0 ? "{$h}h {$m}m" : "{$m}m";

                $isUsed = $usageCount > 0;
                if ($isUsed) {
                    $totalUsed++;
                } else {
                    $totalUnused++;
                }

                $latestActivity = ActivityLog::where('user_name', $userName)
                    ->where('application', 'LIKE', "%{$software}%")
                    ->latest('recorded_at')
                    ->first();

                $daysInactive = $latestActivity ? $latestActivity->recorded_at->diffInDays(now()) : 999;
                
                $urgency = 'Monitor';
                if ($daysInactive > 90) $urgency = 'Critical';
                elseif ($daysInactive > 30) $urgency = 'Warning';

                $auditResults[] = (object)[
                    'user_name'      => $userName,
                    'machine'        => $lastLog ? $lastLog->machine_name : 'Unknown',
                    'software'       => $software,
                    'usage_count'    => $usageCount,
                    'usage_time'     => $formattedTime,
                    'is_used'        => $isUsed,
                    'last_seen'      => $latestActivity ? $latestActivity->recorded_at->diffForHumans() : 'Never',
                    'days_inactive'  => $daysInactive,
                    'urgency'        => $urgency,
                    'department'     => $dept
                ];
            }
        }

        // Sort: Critical first, then unused, then used
        usort($auditResults, function($a, $b) {
            if ($a->urgency === $b->urgency) {
                return $b->days_inactive - $a->days_inactive;
            }
            $weights = ['Critical' => 3, 'Warning' => 2, 'Monitor' => 1];
            return $weights[$b->urgency] - $weights[$a->urgency];
        });

        $totalAssignments = count($auditResults);
        $usersWithNoAssignments = ActivityLog::distinct('user_name')->count('user_name') - count($userAssignments);

        $softwareList = [
            'AutoCAD', 'Revit', '3ds Max', 'Navisworks', 'InfraWorks',
            'ReCap Pro', 'Autodesk Docs', 'FormIt', 'Robot Structural Analysis',
            'Structural Bridge Design', 'Inventor', 'Fusion 360',
            'Fabrication ESTmep', 'Fabrication CAMduct'
        ];

        $deptList = ['Architecture', 'MEP', 'Structural', 'Infrastructure', 'Visualization'];

        return view('license_audit', [
            'auditResults'          => $auditResults,
            'totalAssignments'      => $totalAssignments,
            'totalUsed'             => $totalUsed,
            'totalUnused'           => $totalUnused,
            'usersWithNoAssignments'=> $usersWithNoAssignments,
            'softwareList'          => $softwareList,
            'selectedApp'           => $selectedApp,
            'startDate'             => $startDate,
            'endDate'               => $endDate,
            'deptList'              => $deptList,
            'selectedDept'          => $selectedDept
        ]);
    }

    public function exportLicenseAuditCsv(Request $request)
    {
        $startDate = $request->get('from', now()->subDays(30)->toDateString());
        $endDate   = $request->get('to', now()->toDateString());
        $selectedApp = $request->get('software', 'all');
        $selectedDept = $request->get('department', 'all');
        if (auth()->user()->role === 'team_leader') {
            $selectedDept = auth()->user()->department ?: 'unassigned';
        }

        $from = Carbon::parse($startDate)->startOfDay();
        $to   = Carbon::parse($endDate)->endOfDay();

        $authorizedUsernames = $this->getAuthorizedUsernames($selectedDept);

        $query = UserLicense::query();
        if ($selectedApp !== 'all') {
            $query->where('software_name', $selectedApp);
        }
        if ($authorizedUsernames !== null) {
            $query->whereIn('user_name', $authorizedUsernames);
        }
        $assignedUsers = $query->get();

        $userAssignments = [];
        foreach ($assignedUsers as $assignment) {
            $userAssignments[$assignment->user_name][] = $assignment->software_name;
        }

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=license_audit_" . now()->format('Y-m-d') . ".csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use ($userAssignments, $from, $to, $selectedApp, $selectedDept, $startDate, $endDate) {
            $file = fopen('php://output', 'w');
            
            fputcsv($file, ["LICENSE AUDIT REPORT"]);
            fputcsv($file, ["Filtered Software: " . ($selectedApp === 'all' ? 'All Assigned' : $selectedApp)]);
            fputcsv($file, ["Filtered Team: " . ($selectedDept === 'all' ? 'All Teams' : $selectedDept)]);
            fputcsv($file, ["Period: " . $startDate . " to " . $endDate]);
            fputcsv($file, []); // Empty line

            fputcsv($file, ["User Name", "Team", "Machine", "Software", "Days Inactive", "Status", "Last Seen", "Urgency"]);

            foreach ($userAssignments as $userName => $assignedSoftware) {
                $lastLog = ActivityLog::where('user_name', $userName)->latest('recorded_at')->first();
                $profile = UserProfile::where('user_name', $userName)->first();
                $dept = $profile ? $profile->department : 'Unassigned';

                foreach ($assignedSoftware as $software) {
                    $latestActivity = ActivityLog::where('user_name', $userName)
                        ->where('application', 'LIKE', "%{$software}%")
                        ->latest('recorded_at')
                        ->first();

                    $usageCount = ActivityLog::where('user_name', $userName)
                        ->where('application', 'LIKE', "%{$software}%")
                        ->whereBetween('recorded_at', [$from, $to])
                        ->count();

                    $daysInactive = $latestActivity ? $latestActivity->recorded_at->diffInDays(now()) : 999;
                    
                    $urgency = 'Monitor';
                    if ($daysInactive > 90) $urgency = 'Critical';
                    elseif ($daysInactive > 30) $urgency = 'Warning';

                    fputcsv($file, [
                        $userName,
                        $dept,
                        $lastLog ? $lastLog->machine_name : 'Unknown',
                        $software,
                        $daysInactive >= 999 ? 'Never' : $daysInactive,
                        ($usageCount > 0 ? 'Active' : 'Unused'),
                        ($latestActivity ? $latestActivity->recorded_at->toDateTimeString() : 'Never'),
                        $urgency
                    ]);
                }
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function leaderboard(Request $request)
    {
        $software = $request->get('software', 'AutoCAD');
        $period = $request->get('period', 'daily'); // 'daily' or 'hourly'
        $startDate = $request->get('from', now()->subDays(6)->toDateString());
        $endDate = $request->get('to', now()->toDateString());

        $dept = 'all';
        if (auth()->user()->role === 'team_leader') {
            $dept = auth()->user()->department ?: 'unassigned';
        }

        $from = Carbon::parse($startDate)->startOfDay();
        $to = Carbon::parse($endDate)->endOfDay();

        $authorizedUsernames = $this->getAuthorizedUsernames($dept);

        $softwareList = [
            'AutoCAD', 'Revit', '3ds Max', 'Navisworks', 'InfraWorks',
            'ReCap Pro', 'Autodesk Docs', 'FormIt', 'Robot Structural Analysis',
            'Structural Bridge Design', 'Inventor', 'Fusion 360',
            'Fabrication ESTmep', 'Fabrication CAMduct'
        ];

        // 1. Get Top Users for Selected Software in the range
        $query = ActivityLog::where('application', 'LIKE', "%{$software}%")
            ->whereBetween('recorded_at', [$from, $to]);
        
        if ($authorizedUsernames !== null) {
            $query->whereIn('user_name', $authorizedUsernames);
        }

        $topUsersRaw = $query->selectRaw('user_name, COUNT(*) as cnt')
            ->groupBy('user_name')
            ->orderBy('cnt', 'desc')
            ->limit(5)
            ->get();

        $topUsers = [];
        foreach ($topUsersRaw as $u) {
            $profile = UserProfile::where('user_name', $u->user_name)->first();
            $totalSeconds = $u->cnt * 3;
            $h = floor($totalSeconds / 3600);
            $m = floor(($totalSeconds % 3600) / 60);

            $topUsers[] = (object)[
                'user_name' => $u->user_name,
                'name' => ($profile && $profile->display_name) ? $profile->display_name : $u->user_name,
                'department' => $profile ? $profile->department : 'Unassigned',
                'total_time' => "{$h}h {$m}m",
                'minutes' => round($totalSeconds / 60, 1),
                'avatar' => "https://ui-avatars.com/api/?name=" . urlencode(($profile && $profile->display_name) ? $profile->display_name : $u->user_name) . "&background=random&color=fff"
            ];
        }

        // 2. Chart Layout Logic
        $timelineLabels = [];
        $chartDatasets = [];
        $colors = ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6'];

        // Determine buckets based on range and period
        $diffDays = $from->diffInDays($to);

        if ($period == 'hourly' || $diffDays == 0) {
            // Hourly view
            $timelineLabels = array_map(fn($h) => sprintf("%02d:00", $h), range(0, 23));
            $numBuckets = 24;

            foreach ($topUsers as $idx => $user) {
                $bucketValues = array_fill(0, $numBuckets, 0);
                $logs = ActivityLog::where('user_name', $user->user_name)
                    ->where('application', 'LIKE', "%{$software}%")
                    ->whereBetween('recorded_at', [$from, $to])
                    ->select('recorded_at')
                    ->get();

                foreach ($logs as $log) {
                    $hour = intval(Carbon::parse($log->recorded_at)->format('H'));
                    $bucketValues[$hour]++;
                }
                
                $bucketValues = array_map(fn($c) => round(($c * 3) / 60, 1), $bucketValues);
                $chartDatasets[] = [
                    'label' => $user->name,
                    'data' => $bucketValues,
                    'borderColor' => $colors[$idx % count($colors)],
                    'backgroundColor' => $colors[$idx % count($colors)] . '1a',
                    'tension' => 0.4,
                    'fill' => true
                ];
            }
        } else {
            // Daily View for dynamic range
            $current = $from->copy();
            while ($current <= $to) {
                $timelineLabels[] = $current->format('D d/m');
                $current->addDay();
            }
            $numBuckets = count($timelineLabels);

            foreach ($topUsers as $idx => $user) {
                $bucketValues = array_fill(0, $numBuckets, 0);
                $logs = ActivityLog::where('user_name', $user->user_name)
                    ->where('application', 'LIKE', "%{$software}%")
                    ->whereBetween('recorded_at', [$from, $to])
                    ->select('recorded_at')
                    ->get();

                foreach ($logs as $log) {
                    $dateStr = Carbon::parse($log->recorded_at)->format('D d/m');
                    $bucketIdx = array_search($dateStr, $timelineLabels);
                    if ($bucketIdx !== false) {
                        $bucketValues[$bucketIdx]++;
                    }
                }

                $bucketValues = array_map(fn($c) => round(($c * 3) / 60, 1), $bucketValues);
                $chartDatasets[] = [
                    'label' => $user->name,
                    'data' => $bucketValues,
                    'borderColor' => $colors[$idx % count($colors)],
                    'backgroundColor' => $colors[$idx % count($colors)] . '1a',
                    'tension' => 0.4,
                    'fill' => true
                ];
            }
        }

        $deptList = ['Architecture', 'MEP', 'Structural', 'Infrastructure', 'Visualization'];
        return view('leaderboard', [
            'softwareList' => $softwareList,
            'selectedSoftware' => $software,
            'topUsers' => $topUsers,
            'timelineLabels' => $timelineLabels,
            'chartDatasets' => $chartDatasets,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'period' => $period,
            'selectedDept' => $dept,
            'deptList' => $deptList
        ]);
    }

    public function machineInventory(Request $request)
    {
        $dept = $request->get('department', 'all');
        if (auth()->user()->role === 'team_leader') {
            $dept = auth()->user()->department ?: 'unassigned';
        }

        $authorizedUsernames = $this->getAuthorizedUsernames($dept);

        // Get distinct machine and application pairs
        $query = ActivityLog::select('machine_name', 'application', 'user_name', 'recorded_at');

        if ($authorizedUsernames !== null) {
            $query->whereIn('user_name', $authorizedUsernames);
        }

        $logs = $query->orderBy('recorded_at', 'desc')->get();

        $machines = [];
        foreach ($logs as $log) {
            $machine = $log->machine_name;
            $app = $this->mapApplicationName($log->application);
            
            if (!isset($machines[$machine])) {
                $machines[$machine] = [
                    'name' => $machine,
                    'last_user' => $log->user_name,
                    'last_seen' => $log->recorded_at->diffForHumans(),
                    'apps' => []
                ];
            }
            
            if (!in_array($app, $machines[$machine]['apps'])) {
                $machines[$machine]['apps'][] = $app;
            }
        }

        $deptList = ['Architecture', 'MEP', 'Structural', 'Infrastructure', 'Visualization'];

        return view('machine_inventory', [
            'machines' => $machines,
            'selectedDept' => $dept,
            'deptList' => $deptList
        ]);
    }

    public function ghostMachines(Request $request)
    {
        if (auth()->user()->role === 'team_leader') {
            return redirect()->route('dashboard')->with('error', 'Unauthorized access.');
        }

        $inactiveDays = $request->get('days', 30);
        $dept = $request->get('department', 'all');
        
        if (auth()->user()->role === 'team_leader') {
            $dept = auth()->user()->department ?: 'unassigned';
        }

        $authorizedUsernames = $this->getAuthorizedUsernames($dept);
        
        // Find latest activity per machine
        $subQuery = ActivityLog::select(DB::raw('MAX(id)'))
            ->groupBy('machine_name');

        if ($authorizedUsernames !== null) {
            $subQuery->whereIn('user_name', $authorizedUsernames);
        }

        $allMachines = ActivityLog::whereIn('id', $subQuery)->get();
        
        $ghostMachines = [];
        $threshold = now()->subDays($inactiveDays);

        foreach ($allMachines as $machine) {
            $lastActive = $machine->recorded_at;
            if ($lastActive->lt($threshold)) {
                $ghostMachines[] = [
                    'name' => $machine->machine_name,
                    'last_user' => $machine->user_name,
                    'last_seen' => $lastActive->diffForHumans(),
                    'last_seen_date' => $lastActive->toDateTimeString(),
                    'days_inactive' => $lastActive->diffInDays(now())
                ];
            }
        }

        // Sort by days inactive desc
        usort($ghostMachines, function($a, $b) {
            return $b['days_inactive'] - $a['days_inactive'];
        });

        $deptList = ['Architecture', 'MEP', 'Structural', 'Infrastructure', 'Visualization'];

        return view('ghost_machines', [
            'ghostMachines' => $ghostMachines,
            'inactiveDays' => $inactiveDays,
            'selectedDept' => $dept,
            'deptList' => $deptList
        ]);
    }

    public function licenseOptimization(Request $request)
    {
        if (auth()->user()->role === 'team_leader') {
            return redirect()->route('dashboard')->with('error', 'Unauthorized access.');
        }

        $days = $request->get('days', 90);
        $threshold = now()->subDays($days);

        // Get all assigned licenses
        $allAssignments = UserLicense::all();

        // Build a map: user_name => [list of assigned software]
        $userAssignments = [];
        foreach ($allAssignments as $a) {
            $userAssignments[$a->user_name][] = $a->software_name;
        }

        $results = [];

        foreach ($userAssignments as $userName => $assignedSoftware) {
            // Check if this user is a "Bundle User" (AEC Collection)
            $isBundleUser = false;
            foreach ($assignedSoftware as $software) {
                if (stripos($software, 'AEC Collection') !== false || count($assignedSoftware) > 1) {
                    $isBundleUser = true;
                    break;
                }
            }

            // Get the distinct apps this user actually opened in the period
            $usedApps = ActivityLog::where('user_name', $userName)
                ->where('recorded_at', '>=', $threshold)
                ->select('application')
                ->distinct()
                ->pluck('application')
                ->map(fn($app) => $this->mapApplicationName($app))
                ->unique()
                ->values()
                ->toArray();

            $usedCount = count($usedApps);

            // Determine efficiency rating
            if ($usedCount === 0) {
                $rating = 'ghost';   // No usage at all
            } elseif ($isBundleUser && $usedCount <= 2) {
                $rating = 'critical'; // Bundle user with only 1-2 apps — strong downgrade signal
            } elseif ($isBundleUser && $usedCount <= 4) {
                $rating = 'warning';  // Bundle user with 3-4 apps — worth reviewing
            } else {
                // If they are standalone (not bundle) and using their app, it's justified
                // If they are bundle and using many apps, it's justified
                $rating = 'justified'; 
            }

            $profile = UserProfile::where('user_name', $userName)->first();

            $results[] = [
                'user_name'       => $userName,
                'department'      => $profile ? $profile->department : 'Unassigned',
                'assigned_count'  => count($assignedSoftware),
                'assigned_list'   => $assignedSoftware,
                'is_bundle'       => $isBundleUser,
                'used_apps'       => $usedApps,
                'used_count'      => $usedCount,
                'rating'          => $rating,
                'unused_apps'     => array_diff($assignedSoftware, $usedApps),
            ];
        }

        // Sort: ghost first, then critical, warning, justified
        $order = ['ghost' => 0, 'critical' => 1, 'warning' => 2, 'justified' => 3];
        usort($results, fn($a, $b) => $order[$a['rating']] <=> $order[$b['rating']]);

        return view('license_optimization', [
            'results' => $results,
            'days'    => $days,
        ]);
    }

    public function departmentEfficiency(Request $request)
    {
        if (auth()->user()->role === 'team_leader') {
            return redirect()->route('dashboard')->with('error', 'Unauthorized access.');
        }

        $days = $request->get('days', 30);
        $threshold = now()->subDays($days);
        
        // Calculate days in period - if threshold is before first log, adjust? 
        // For simplicity, we use the requested 'days'
        $daysCount = $days;

        // Get all department-user usage
        $stats = ActivityLog::join('user_profiles', 'activity_logs.user_name', '=', 'user_profiles.user_name')
            ->select(
                'user_profiles.department',
                \DB::raw('count(*) as heartbeat_count'),
                \DB::raw('count(distinct activity_logs.user_name) as user_count')
            )
            ->where('recorded_at', '>=', $threshold)
            ->groupBy('user_profiles.department')
            ->get();

        $benchmarks = [];
        $deptLabels = [];
        $usageData = [];
        $userCounts = [];

        foreach ($stats as $stat) {
            $dept = $stat->department ?: 'Unassigned';
            $totalHours = ($stat->heartbeat_count * 3) / 3600;
            
            // Avoid division by zero
            $avgPerUserPerDay = $stat->user_count > 0 ? ($totalHours / $stat->user_count / $daysCount) : 0;

            $benchmarks[] = [
                'department' => $dept,
                'avg_hours' => round($avgPerUserPerDay, 1),
                'user_count' => $stat->user_count,
                'total_hours' => round($totalHours, 1)
            ];

            $deptLabels[] = $dept;
            $usageData[] = round($avgPerUserPerDay, 1);
            $userCounts[] = $stat->user_count;
        }

        // Sort by avg_hours desc
        usort($benchmarks, function($a, $b) {
            return $b['avg_hours'] <=> $a['avg_hours'];
        });

        return view('department_efficiency', [
            'benchmarks' => $benchmarks,
            'days' => $days,
            'chartLabels' => json_encode($deptLabels),
            'chartData' => json_encode($usageData),
            'userCounts' => json_encode($userCounts)
        ]);
    }

    private function getAuthorizedUsernames($dept = 'all')
    {
        $user = auth()->user();
        if (!$user) return [];

        // 1. Management gets to see everything
        if ($user->role === 'management') {
            if ($dept !== 'all') {
                return UserProfile::where('department', $dept)->pluck('user_name')->toArray();
            }
            return null; // All
        }

        // 2. If Team Leader has specific monitor assignments, use those (override department)
        if ($user->role === 'team_leader') {
            $assignments = \App\Models\MonitorAssignment::where('leader_id', $user->id)->pluck('monitored_user_name')->toArray();
            if (count($assignments) > 0) {
                return $assignments;
            }
            
            // If no specific assignments, they can only see their own department
            $userDept = $user->department ?: 'unassigned';
            return UserProfile::where('department', $userDept)->pluck('user_name')->toArray();
        }

        // 3. Fallback to Department filtering if specified (for Admin)
        if ($dept !== 'all') {
            return UserProfile::where('department', $dept)->pluck('user_name')->toArray();
        }

        // 4. Admin viewing 'all'
        return null;
    }

    public function dismissNotification(Request $request)
    {
        $validated = $request->validate([
            'user_name' => 'required|string',
            'software_name' => 'required|string',
        ]);

        DismissedNotification::firstOrCreate([
            'user_name' => $validated['user_name'],
            'software_name' => $validated['software_name'],
        ]);

        return redirect()->back()->with('success', 'Notification cleared');
    }
}
