<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\UserLicense;
use App\Models\UserProfile;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Show the Report Hub page (Management & Admin only)
     */
    public function hub(Request $request)
    {
        // Access allowed for all logged in users (Admin, Management, and now Team Leader)
        return view('report_hub');
    }

    /**
     * Generate the full PDF/Presentation report view
     */
    public function generate(Request $request)
    {
        $days   = (int) $request->get('days', 30);
        $dept   = $request->get('department', 'all');

        // Force team_leader to their own department
        if (auth()->user()->role === 'team_leader') {
            $dept = auth()->user()->department ?: 'unassigned';
        }
        $from   = now()->subDays($days)->startOfDay();
        $to     = now()->endOfDay();

        // ------- Authorized Users -------
        $authorizedUsernames = null;
        if ($dept !== 'all') {
            $authorizedUsernames = UserProfile::where('department', $dept)->pluck('user_name')->toArray();
        }

        // ------- 1. Summary KPIs -------
        $totalLogsQuery = ActivityLog::whereBetween('recorded_at', [$from, $to]);
        if ($authorizedUsernames) {
            $totalLogsQuery->whereIn('user_name', $authorizedUsernames);
        }
        $totalLogs     = $totalLogsQuery->count();
        $totalSeconds  = $totalLogs * 3;
        $totalHours    = round($totalSeconds / 3600, 1);
        $totalMinutes  = ceil($totalSeconds / 60);

        // Formatted for the KPI card
        $totalUsageLabel = $totalHours > 0.1 ? number_format($totalHours, 1) . 'h' : $totalMinutes . 'm';
        $totalUsageSub   = $totalHours > 0.1 ? 'Total Hours Logged' : 'Total Minutes Logged';

        $uniqueUsers = ActivityLog::whereBetween('recorded_at', [$from, $to])
            ->when($authorizedUsernames, fn($q) => $q->whereIn('user_name', $authorizedUsernames))
            ->distinct('user_name')
            ->count('user_name');

        $uniqueApps = ActivityLog::whereBetween('recorded_at', [$from, $to])
            ->when($authorizedUsernames, fn($q) => $q->whereIn('user_name', $authorizedUsernames))
            ->distinct('application')
            ->count('application');

        // Active user = logged at least 1 hour in period
        $activeUsersRaw = ActivityLog::whereBetween('recorded_at', [$from, $to])
            ->when($authorizedUsernames, fn($q) => $q->whereIn('user_name', $authorizedUsernames))
            ->selectRaw('user_name, COUNT(*) as cnt')
            ->groupBy('user_name')
            ->havingRaw('cnt >= 1200') // 1200 polls * 3s = 3600s = 1 hour
            ->pluck('user_name')
            ->count();

        // ------- 2. Application Usage (Top 10) -------
        $appUsageRaw = ActivityLog::whereBetween('recorded_at', [$from, $to])
            ->when($authorizedUsernames, fn($q) => $q->whereIn('user_name', $authorizedUsernames))
            ->selectRaw('application, COUNT(*) as cnt')
            ->groupBy('application')
            ->orderByDesc('cnt')
            ->get();

        $appUsageMap = [];
        foreach ($appUsageRaw as $row) {
            $clean = $this->mapAppName($row->application);
            $appUsageMap[$clean] = ($appUsageMap[$clean] ?? 0) + $row->cnt;
        }
        arsort($appUsageMap);
        $topApps = array_slice($appUsageMap, 0, 10, true);

        $maxCnt = max($topApps ?: [1]);
        $appUsage = [];
        foreach ($topApps as $app => $cnt) {
            $secs = $cnt * 3;
            $h    = floor($secs / 3600);
            $m    = floor(($secs % 3600) / 60);
            $appUsage[] = [
                'app'     => $app,
                'hours'   => round($secs / 3600, 1),
                'label'   => $h > 0 ? "{$h}h {$m}m" : "{$m}m",
                'pct'     => round($cnt / $maxCnt * 100),
                'cnt'     => $cnt,
            ];
        }

        // ------- 3. Department Efficiency -------
        $deptData = [];
        $departments = ['Architecture', 'MEP', 'Structural', 'Infrastructure', 'Visualization'];
        foreach ($departments as $deptName) {
            if ($dept !== 'all' && $deptName !== $dept) continue;

            $deptUsers = UserProfile::where('department', $deptName)->pluck('user_name')->toArray();
            if (empty($deptUsers)) continue;

            $logs = ActivityLog::whereBetween('recorded_at', [$from, $to])
                ->whereIn('user_name', $deptUsers)
                ->count();

            if ($logs === 0) continue;

            $deptSecs  = $logs * 3;
            $avgPerDay = round(($deptSecs / 3600) / ($days * count($deptUsers)), 2);

            $deptData[] = [
                'dept'      => $deptName,
                'users'     => count($deptUsers),
                'total_hrs' => round($deptSecs / 3600, 1),
                'avg_day'   => $avgPerDay,
            ];
        }
        usort($deptData, fn($a, $b) => $b['avg_day'] <=> $a['avg_day']);

        // ------- 4. Top Performers (Top 10 users by hours) -------
        $topUsersRaw = ActivityLog::whereBetween('recorded_at', [$from, $to])
            ->when($authorizedUsernames, fn($q) => $q->whereIn('user_name', $authorizedUsernames))
            ->selectRaw('user_name, COUNT(*) as cnt')
            ->groupBy('user_name')
            ->orderByDesc('cnt')
            ->limit(10)
            ->get();

        $topUsers = [];
        foreach ($topUsersRaw as $u) {
            $secs       = $u->cnt * 3;
            $h          = floor($secs / 3600);
            $m          = floor(($secs % 3600) / 60);
            $profile    = UserProfile::where('user_name', $u->user_name)->first();
            $topUsers[] = [
                'name'   => $u->user_name,
                'dept'   => $profile ? $profile->department : 'Unknown',
                'label'  => "{$h}h {$m}m",
                'hours'  => round($secs / 3600, 1),
            ];
        }

        // ------- 5. Ghost Machines -------
        $ghostThreshold = now()->subDays(30);
        $latestPerMachine = ActivityLog::select(DB::raw('MAX(id) as id'))
            ->groupBy('machine_name');

        $allMachines = ActivityLog::joinSub($latestPerMachine, 'latest', fn($j) => $j->on('activity_logs.id', '=', 'latest.id'))
            ->select('machine_name', 'user_name', 'recorded_at')
            ->get();

        $ghostCount = 0;
        foreach ($allMachines as $m) {
            if (Carbon::parse($m->recorded_at)->lt($ghostThreshold)) {
                $ghostCount++;
            }
        }

        // ------- 6. License Optimization Summary -------
        $licenseStats = ['ghost' => 0, 'critical' => 0, 'warning' => 0, 'justified' => 0];
        $allAssignments = UserLicense::all()->groupBy('user_name');
        $licThreshold   = now()->subDays(90);

        foreach ($allAssignments as $userName => $licenses) {
            $assignedSoftware = $licenses->pluck('software_name')->toArray();
            $isBundleUser     = collect($assignedSoftware)->contains(fn($s) => str_contains(strtolower($s), 'collection'));

            $usedApps = ActivityLog::where('user_name', $userName)
                ->where('recorded_at', '>=', $licThreshold)
                ->distinct('application')
                ->pluck('application')
                ->map(fn($a) => $this->mapAppName($a))
                ->unique()
                ->values()
                ->toArray();

            $usedCount = count($usedApps);

            if ($usedCount === 0) {
                $licenseStats['ghost']++;
            } elseif ($isBundleUser && $usedCount <= 2) {
                $licenseStats['critical']++;
            } elseif ($isBundleUser && $usedCount <= 4) {
                $licenseStats['warning']++;
            } else {
                $licenseStats['justified']++;
            }
        }

        // ------- 7. Daily usage trend (last N days) -------
        $trendDays  = min($days, 30);
        $trendLabels = [];
        $trendValues = [];
        for ($i = $trendDays - 1; $i >= 0; $i--) {
            $day  = now()->subDays($i)->toDateString();
            $cnt  = ActivityLog::whereDate('recorded_at', $day)
                ->when($authorizedUsernames, fn($q) => $q->whereIn('user_name', $authorizedUsernames))
                ->count();
            $trendLabels[] = now()->subDays($i)->format('d M');
            $trendValues[] = round($cnt * 3 / 3600, 1); // hours
        }

        return view('report_pdf', compact(
            'days', 'dept',
            'totalHours', 'totalUsageLabel', 'totalUsageSub', 'uniqueUsers', 'uniqueApps', 'activeUsersRaw',
            'appUsage', 'deptData', 'topUsers',
            'ghostCount', 'licenseStats',
            'trendLabels', 'trendValues',
            'from', 'to'
        ));
    }

    private function mapAppName(string $raw): string
    {
        // Preserve version year (e.g. "AutoCAD 2025") before lowercasing
        $version = '';
        if (preg_match('/(20\d{2})/', $raw, $matches)) {
            $version = ' ' . $matches[1];
        }

        $map = [
            'acad'             => 'AutoCAD',
            'autocad'          => 'AutoCAD',
            'revit'            => 'Revit',
            'navis'            => 'Navisworks',
            'roamer'           => 'Navisworks',
            'civil'            => 'Civil 3D',
            'plant'            => 'Plant 3D',
            '3dsmax'           => '3ds Max',
            '3ds max'          => '3ds Max',
            'inventor'         => 'Inventor',
            'fusion'           => 'Fusion 360',
            'infraworks'       => 'InfraWorks',
            'recap'            => 'ReCap Pro',
            'desktopconnector' => 'Autodesk Docs',
            'formit'           => 'FormIt',
            'robot'            => 'Robot Structural Analysis',
            'sbd'              => 'Structural Bridge Design',
            'estmep'           => 'Fabrication ESTmep',
            'camduct'          => 'Fabrication CAMduct',
            'vault'            => 'Vault',
        ];
        $lower = strtolower($raw);
        foreach ($map as $key => $label) {
            if (str_contains($lower, $key)) return $label . $version;
        }
        return ucwords(strtolower($raw)) . $version;
    }
}
