<?php

namespace App\Console\Commands;

use App\Http\Controllers\SettingsController;
use App\Mail\IndividualUserReport;
use App\Models\ActivityLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendIndividualReports extends Command
{
    protected $signature   = 'report:individual';
    protected $description = 'Send individual weekly performance report to each user. FROM: HR, TO: user, CC: team leader';

    public function handle(): void
    {
        $appSettings = SettingsController::getAllSettings();
        if (!($appSettings['notify_individual_users'] ?? false)) {
            $this->warn('Individual user reports are disabled in settings — skipping.');
            return;
        }

        $hrEmail = config('app.hr_email');
        $hrName  = config('mail.from.name', 'ArchEng Pro HR');

        if (!$hrEmail) {
            $this->error('HR_EMAIL is not set in .env — aborting.');
            return;
        }

        $weekEnd   = Carbon::now()->endOfDay();
        $weekStart = Carbon::now()->subDays(6)->startOfDay();
        $weekLabel = $weekStart->format('d M') . ' – ' . $weekEnd->format('d M Y');

        // Get all regular users that have a department and team leader
        $users = User::where('role', 'user')->whereNotNull('department')->get();

        if ($users->isEmpty()) {
            $this->warn('No users found. Skipping.');
            return;
        }

        foreach ($users as $user) {

            // Find their team leader (same department)
            $leader = User::where('role', 'team_leader')
                ->where('department', $user->department)
                ->first();

            if (!$leader) {
                $this->warn("No team leader found for {$user->name} ({$user->department}). Skipping.");
                continue;
            }

            // Get this user's activity logs for the week
            $logs = ActivityLog::where('user_name', $user->name)
                ->whereBetween('recorded_at', [$weekStart, $weekEnd])
                ->get();

            $totalSeconds = $logs->count() * 3;
            $totalHours   = (int) floor($totalSeconds / 3600);
            $totalMinutes = (int) floor(($totalSeconds % 3600) / 60);

            // Days active
            $daysActive = $logs
                ->groupBy(fn($log) => Carbon::parse($log->recorded_at)->toDateString())
                ->count();

            // App breakdown — [ 'AutoCAD' => 12.5, 'Revit' => 3.2, ... ] in hours
            $appBreakdown = $logs
                ->groupBy('application')
                ->map(fn($group) => round(($group->count() * 3) / 3600, 1))
                ->sortDesc()
                ->toArray();

            $topApp = array_key_first($appBreakdown) ?? '';

            // Daily session timeline — gap > 5 min = new session
            $dailySessions = [];
            $byDay = $logs->sortBy('recorded_at')
                ->groupBy(fn($log) => Carbon::parse($log->recorded_at)->toDateString());

            foreach ($byDay as $date => $dayLogs) {
                $sessions   = [];
                $curSession = null;
                $prevTime   = null;

                foreach ($dayLogs as $log) {
                    $t = Carbon::parse($log->recorded_at);
                    if ($curSession === null) {
                        $curSession = ['start' => $t->copy(), 'end' => $t->copy(), 'apps' => [$log->application], 'count' => 1];
                    } elseif ($prevTime && $t->diffInSeconds($prevTime) <= 300) {
                        $curSession['end'] = $t->copy();
                        $curSession['apps'][] = $log->application;
                        $curSession['count']++;
                    } else {
                        $dSecs = $curSession['count'] * 3;
                        $dH = floor($dSecs / 3600);
                        $dM = floor(($dSecs % 3600) / 60);
                        $appCounts = array_count_values($curSession['apps']);
                        arsort($appCounts);
                        $sessions[] = [
                            'start'    => $curSession['start']->format('H:i'),
                            'end'      => $curSession['end']->copy()->addSeconds(3)->format('H:i'),
                            'duration' => $dH > 0 ? "{$dH}h {$dM}m" : "{$dM}m",
                            'top_app'  => array_key_first($appCounts) ?? '',
                        ];
                        $curSession = ['start' => $t->copy(), 'end' => $t->copy(), 'apps' => [$log->application], 'count' => 1];
                    }
                    $prevTime = $t;
                }
                if ($curSession) {
                    $dSecs = $curSession['count'] * 3;
                    $dH = floor($dSecs / 3600);
                    $dM = floor(($dSecs % 3600) / 60);
                    $appCounts = array_count_values($curSession['apps']);
                    arsort($appCounts);
                    $sessions[] = [
                        'start'    => $curSession['start']->format('H:i'),
                        'end'      => $curSession['end']->copy()->addSeconds(3)->format('H:i'),
                        'duration' => $dH > 0 ? "{$dH}h {$dM}m" : "{$dM}m",
                        'top_app'  => array_key_first($appCounts) ?? '',
                    ];
                }
                $dailySessions[Carbon::parse($date)->format('l, d M Y')] = $sessions;
            }

            try {
                Mail::send(new IndividualUserReport(
                    userName:        $user->name,
                    userEmail:       $user->email,
                    department:      $user->department,
                    occupation:      $user->occupation ?? '',
                    teamLeaderName:  $leader->name,
                    teamLeaderEmail: $leader->email,
                    weekLabel:       $weekLabel,
                    weekStart:       $weekStart->toDateString(),
                    weekEnd:         $weekEnd->toDateString(),
                    totalHours:      $totalHours,
                    totalMinutes:    $totalMinutes,
                    daysActive:      $daysActive,
                    appBreakdown:    $appBreakdown,
                    topApp:          $topApp,
                    hrEmail:         $hrEmail,
                    hrName:          $hrName,
                    dailySessions:   $dailySessions,
                ));

                $this->info("✅ Sent to {$user->name} ({$user->email}) | CC: {$leader->name}");
            } catch (\Exception $e) {
                $this->error("❌ Failed for {$user->name}: " . $e->getMessage());
            }
        }

        $this->info('Individual report job completed.');
    }
}
