<?php

namespace App\Console\Commands;

use App\Mail\WeeklyTeamReport;
use App\Models\ActivityLog;
use App\Models\User;
use App\Http\Controllers\SettingsController;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendSixMonthReport extends Command
{
    protected $signature   = 'report:sixmonth';
    protected $description = 'Send 6-month performance report to HR (and optionally CC team leaders)';

    public function handle(): void
    {
        $hrEmail = config('app.hr_email');

        if (!$hrEmail) {
            $this->error('HR_EMAIL is not set in .env — aborting.');
            return;
        }

        $appSettings       = SettingsController::getAllSettings();
        $notifyTeamLeaders = $appSettings['notify_team_leaders'] ?? false;

        // Last 6 calendar months (180 days back from today)
        $periodEnd   = Carbon::now()->endOfDay();
        $periodStart = Carbon::now()->subMonths(6)->startOfDay();
        $periodLabel = '6-Month Summary';
        $weekLabel   = $periodStart->format('d M Y') . ' – ' . $periodEnd->format('d M Y');

        $teamLeaders = User::where('role', 'team_leader')->get();

        if ($teamLeaders->isEmpty()) {
            $this->warn('No team leaders found. Skipping.');
            return;
        }

        foreach ($teamLeaders as $leader) {
            $department = $leader->department;

            if (!$department) {
                $this->warn("Team leader {$leader->name} has no department set. Skipping.");
                continue;
            }

            $teamUsers = User::where('department', $department)
                ->whereIn('role', ['user', 'team_leader'])
                ->get();

            $totalTeamHours = 0;
            $userStats = $teamUsers->map(function ($user) use ($periodStart, $periodEnd, &$totalTeamHours) {
                $logs = ActivityLog::where('user_name', $user->name)
                    ->whereBetween('recorded_at', [$periodStart, $periodEnd])
                    ->get();

                $totalSeconds    = $logs->count() * 3;
                $hours           = floor($totalSeconds / 3600);
                $minutes         = floor(($totalSeconds % 3600) / 60);
                $totalTeamHours += $hours;

                $topApp = $logs->groupBy('application')
                    ->map(fn($g) => $g->count())
                    ->sortDesc()
                    ->keys()
                    ->first();

                $daysActive = $logs->groupBy(fn($log) => Carbon::parse($log->recorded_at)->toDateString())
                    ->count();

                return [
                    'name'        => $user->name,
                    'email'       => $user->email,
                    'hours'       => $hours,
                    'minutes'     => $minutes,
                    'top_app'     => $topApp,
                    'days_active' => $daysActive,
                ];
            });

            $leaderEmailForReport = $notifyTeamLeaders ? $leader->email : null;

            try {
                Mail::send(new WeeklyTeamReport(
                    teamLeaderName:  $leader->name,
                    teamLeaderEmail: $leaderEmailForReport,
                    department:      $department,
                    weekLabel:       $weekLabel,
                    weekStart:       $periodStart->toDateString(),
                    weekEnd:         $periodEnd->toDateString(),
                    userStats:       $userStats,
                    totalTeamHours:  $totalTeamHours,
                    hrEmail:         $hrEmail,
                    periodLabel:     $periodLabel,
                ));

                $ccNote = $notifyTeamLeaders ? ", CC: {$leader->email}" : ' (Team Leader not notified)';
                $this->info("✅ 6-month report sent for {$department} (Leader: {$leader->name}) → HR: {$hrEmail}{$ccNote}");
            } catch (\Exception $e) {
                $this->error("❌ Failed to send for {$department}: " . $e->getMessage());
            }
        }

        $this->info('6-month report job completed.');
    }
}
