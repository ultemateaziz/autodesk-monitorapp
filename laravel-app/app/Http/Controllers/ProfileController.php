<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\UserProfile;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ProfileController extends Controller
{
    public function index(\Illuminate\Http\Request $request, $userName = null)
    {
        $user = auth()->user();
        if (!$user) abort(401);
        
        $userRole = $user->role;
        $userDept = $user->department;

        // Get authorized usernames for this leader (Assignments override Department)
        $authorizedUsernames = null;
        if ($userRole === 'team_leader') {
            $assignments = \App\Models\MonitorAssignment::where('leader_id', $user->id)->pluck('monitored_user_name')->toArray();
            if (count($assignments) > 0) {
                $authorizedUsernames = $assignments;
            } else if ($userDept) {
                $authorizedUsernames = UserProfile::where('department', $userDept)->pluck('user_name')->toArray();
            } else {
                $authorizedUsernames = []; // No access if no assignments and no dept
            }
        }

        // 1. If no userName provided, get the first one available (Respect Auth)
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

        // 2. Security Check for Team Leaders
        if ($userRole === 'team_leader') {
            if ($authorizedUsernames !== null && !in_array($userName, $authorizedUsernames)) {
                abort(403, 'Unauthorized. You do not have permission to monitor this user.');
            }
        }

        $targetProfile = UserProfile::where('user_name', $userName)->first();

        $displayName = $targetProfile ? $targetProfile->display_name : $userName;
        $department = $targetProfile ? $targetProfile->department : 'Unassigned';

        // --- Date Range Handling ---
        $startDate = $request->get('from', now()->subDays(29)->toDateString());
        $endDate = $request->get('to', now()->toDateString());
        
        // Convert to Carbon for queries
        $from = Carbon::parse($startDate)->startOfDay();
        $to = Carbon::parse($endDate)->endOfDay();

        // 2. Fetch User Machine Details — use last Active/Idle log for status (ignore background Open)
        $lastLog     = ActivityLog::where('user_name', $userName)->latest('recorded_at')->first();
        $machineName = $lastLog?->machine_name;

        $lastActiveLog = ActivityLog::where('user_name', $userName)
            ->whereIn('status', ['Active', 'Idle'])
            ->latest('recorded_at')
            ->first();
        $lastSeenSec = $lastActiveLog ? $lastActiveLog->recorded_at->diffInSeconds(now()) : PHP_INT_MAX;
        $lastStatus  = $lastActiveLog?->status;
        $statusLower = strtolower($lastStatus ?? '');
        $isOnline    = $lastSeenSec < 600 && $statusLower === 'active';
        $isIdle      = $lastSeenSec < 600 && $statusLower === 'idle';

        // 3. Analytics based on Selected Range
        $rangeQuery = ActivityLog::where('user_name', $userName)
            ->whereBetween('recorded_at', [$from, $to])
            ->where('status', 'Active');

        $totalLogsInRange    = (clone $rangeQuery)->count();
        $totalSecondsInRange = $totalLogsInRange * 3;
        $th = floor($totalSecondsInRange / 3600);
        $tm = floor(($totalSecondsInRange % 3600) / 60);
        $totalHoursInRangeFormat  = "{$th}h {$tm}m";
        $totalHoursInRangeNumeric = $totalSecondsInRange / 3600;

        // 4. Primary Software & Usage % (With Selected Range)
        $logs = (clone $rangeQuery)->get();
        
        $aggregatedUsage = [];
        foreach ($logs as $log) {
            $cleanName = $this->mapApplicationName($log->application);
            $aggregatedUsage[$cleanName] = ($aggregatedUsage[$cleanName] ?? 0) + 1;
        }
        
        arsort($aggregatedUsage);
        
        $primarySoftware = !empty($aggregatedUsage) ? array_key_first($aggregatedUsage) : 'N/A';
        $primarySoftwareCount = !empty($aggregatedUsage) ? current($aggregatedUsage) : 0;
        $primarySoftwarePercent = $totalLogsInRange > 0 ? round(($primarySoftwareCount / $totalLogsInRange) * 100) : 0;

        // 5. Productivity Score & Trend
        // Target is proportional to the range length (8h/day baseline)
        $rangeDays = max(1, $from->diffInDays($to) + 1);
        $targetHours = $rangeDays * 8;
        $productivityScore = round(min(($totalHoursInRangeNumeric / $targetHours) * 100, 100));

        // Days Active in selected range
        $activeDays = (clone $rangeQuery)
            ->selectRaw('COUNT(DISTINCT DATE(recorded_at)) as day_count')
            ->value('day_count');

        // Trend: current 30-day period vs previous 30-day period
        $currentPeriodHours = (ActivityLog::where('user_name', $userName)->whereBetween('recorded_at', [now()->subDays(29)->startOfDay(), now()])->count() * 3) / 3600;
        $lastPeriodHours    = (ActivityLog::where('user_name', $userName)->whereBetween('recorded_at', [now()->subDays(59)->startOfDay(), now()->subDays(30)->endOfDay()])->count() * 3) / 3600;
        $currentPeriodScore = round(min(($currentPeriodHours / (30 * 8)) * 100, 100));
        $lastPeriodScore    = round(min(($lastPeriodHours    / (30 * 8)) * 100, 100));
        $trend = $currentPeriodScore - $lastPeriodScore;

        // 6. Daily Activity Timeline (Grouped by Application)
        // Adjusting to show data within the selected range or last 24h if range is 1 day
        $timelineLogs = (clone $rangeQuery)->get();

        $appTimelineRaw = []; 
        $appsFound = [];

        foreach ($timelineLogs as $log) {
            $hour = intval(Carbon::parse($log->recorded_at)->format('H'));
            $cleanName = $this->mapApplicationName($log->application);
            $appsFound[$cleanName] = true;
            $appTimelineRaw[$cleanName][$hour] = ($appTimelineRaw[$cleanName][$hour] ?? 0) + 1;
        }

        $timelineDatasets = [];
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
        $fallbackColors = ['#94a3b8', '#64748b', '#475569'];
        $colorIdx = 0;

        foreach (array_keys($appsFound) as $appName) {
            $color = $fallbackColors[$colorIdx % count($fallbackColors)];
            foreach ($appColorMap as $key => $clr) {
                if (str_starts_with($appName, $key)) { $color = $clr; break; }
            }
            $hourlyMinutes = [];
            for ($i = 0; $i < 24; $i++) {
                $count = $appTimelineRaw[$appName][$i] ?? 0;
                $minutes = round(($count * 3) / 60, 1);
                $hourlyMinutes[] = $minutes;
            }
            $timelineDatasets[] = [
                'label'                => $appName,
                'data'                 => $hourlyMinutes,
                'borderColor'          => $color,
                'backgroundColor'      => $color . '22',
                'fill'                 => true,
                'tension'              => 0.4,
                'borderWidth'          => 2.5,
                'pointRadius'          => 4,
                'pointHoverRadius'     => 7,
                'pointBorderColor'     => $color,
                'pointBackgroundColor' => $color,
            ];
            $colorIdx++;
        }

        if (empty($timelineDatasets)) {
            $timelineDatasets[] = ['label' => 'No Activity', 'data' => array_fill(0, 24, 0), 'borderColor' => '#334155', 'fill' => false];
        }

        $timelineLabels = array_map(fn($h) => sprintf("%02d:00", $h), range(0, 23));

        // 7. Software Usage for Donut
        $donutLabels = [];
        $donutMinutes = [];
        $donutFormattedTimes = [];

        foreach ($aggregatedUsage as $app => $count) {
            $totalSeconds = $count * 3;
            $h = floor($totalSeconds / 3600);
            $m = floor(($totalSeconds % 3600) / 60);
            $formattedTime = $h > 0 ? "{$h}h {$m}m" : "{$m}m";

            $donutLabels[] = $app;
            $donutMinutes[] = round($totalSeconds / 60, 1);
            $donutFormattedTimes[] = $formattedTime;
        }

        // 8. Recent Activity Feed
        $recentLogs = (clone $rangeQuery)->orderBy('recorded_at', 'desc')->limit(5)->get();
        foreach($recentLogs as $log) { $log->application = $this->mapApplicationName($log->application); }

        // 9. 7-Day Performance Trend (Always shows the last 7 days for consistency)
        $sevenDaysData = [];
        $sevenDaysLabels = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $logsCount = ActivityLog::where('user_name', $userName)->whereDate('recorded_at', $date)->where('status', 'Active')->count();
            $hours = ($logsCount * 3) / 3600;
            $eff = round(min(($hours / 8) * 100, 100));
            $sevenDaysData[] = $eff;
            $sevenDaysLabels[] = $date->format('D');
        }

        // 10. Overall Lifetime Metrics (Ignore Filters)
        $overallTotalLogs = ActivityLog::where('user_name', $userName)->where('status', 'Active')->count();
        $overallTotalSeconds = $overallTotalLogs * 3;
        $oh = floor($overallTotalSeconds / 3600);
        $om = floor(($overallTotalSeconds % 3600) / 60);
        $overallTotalHoursFormat = "{$oh}h {$om}m";
        
        $overallLogs = ActivityLog::where('user_name', $userName)->get();
        $overallAppUsage = [];
        foreach ($overallLogs as $log) {
            $cleanName = $this->mapApplicationName($log->application);
            $overallAppUsage[$cleanName] = ($overallAppUsage[$cleanName] ?? 0) + 1;
        }
        arsort($overallAppUsage);
        $overallTopApp = !empty($overallAppUsage) ? array_key_first($overallAppUsage) : 'N/A';

        return view('profile', [
            'userName' => $userName,
            'displayName' => $displayName,
            'department' => $department,
            'machineName' => $machineName,
            'isOnline' => $isOnline,
            'isIdle' => $isIdle,
            'activeDays' => $activeDays,
            'totalHours' => $totalHoursInRangeFormat,
            'primarySoftware' => $primarySoftware,
            'primarySoftwarePercent' => $primarySoftwarePercent,
            'productivityScore' => $productivityScore,
            'trend' => $trend,
            'timelineLabels' => $timelineLabels,
            'timelineDatasets' => $timelineDatasets,
            'donutLabels' => $donutLabels,
            'donutMinutes' => $donutMinutes,
            'donutFormattedTimes' => $donutFormattedTimes,
            'recentLogs' => $recentLogs,
            'sevenDaysLabels' => $sevenDaysLabels,
            'sevenDaysData' => $sevenDaysData,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'overallTotalHours' => $overallTotalHoursFormat,
            'overallTopApp' => $overallTopApp
        ]);
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
