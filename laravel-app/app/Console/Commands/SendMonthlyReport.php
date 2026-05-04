<?php

namespace App\Console\Commands;

use App\Mail\WeeklyTeamReport;
use App\Models\ActivityLog;
use App\Models\User;
use App\Http\Controllers\SettingsController;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendMonthlyReport extends Command
{
    protected $signature   = 'report:monthly';
    protected $description = 'Send monthly performance report to HR (and optionally CC team leaders)';

    public function handle(): void
    {
        $hrEmail = config('app.hr_email');

        if (!$hrEmail) {
            $this->error('HR_EMAIL is not set in .env — aborting.');
            return;
        }

        $appSettings       = SettingsController::getAllSettings();
        $notifyTeamLeaders = $appSettings['notify_team_leaders'] ?? false;

        // Last full calendar month
        $monthEnd   = Carbon::now()->subMonth()->endOfMonth()->endOfDay();
        $monthStart = Carbon::now()->subMonth()->startOfMonth()->startOfDay();
        $periodLabel = 'Monthly';
        $weekLabel   = $monthStart->format('d M Y') . ' – ' . $monthEnd->format('d M Y');

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
            $userStats = $teamUsers->map(function ($user) use ($monthStart, $monthEnd, &$totalTeamHours) {
                $logs = ActivityLog::where('user_name', $user->name)
                    ->whereBetween('recorded_at', [$monthStart, $monthEnd])
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
                    weekStart:       $monthStart->toDateString(),
                    weekEnd:         $monthEnd->toDateString(),
                    userStats:       $userStats,
                    totalTeamHours:  $totalTeamHours,
                    hrEmail:         $hrEmail,
                    periodLabel:     $periodLabel,
                ));

                $ccNote = $notifyTeamLeaders ? ", CC: {$leader->email}" : ' (Team Leader not notified)';
                $this->info("✅ Monthly report sent for {$department} (Leader: {$leader->name}) → HR: {$hrEmail}{$ccNote}");
            } catch (\Exception $e) {
                $this->error("❌ Failed to send for {$department}: " . $e->getMessage());
            }
        }

        $this->info('Monthly report job completed.');
    }
}
