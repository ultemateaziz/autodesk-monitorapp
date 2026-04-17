<?php

namespace App\Console\Commands;

use App\Mail\WeeklyTeamReport;
use App\Models\ActivityLog;
use App\Models\User;
use App\Http\Controllers\SettingsController;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendWeeklyReport extends Command
{
    protected $signature   = 'report:weekly';
    protected $description = 'Send weekly performance report to HR with team leader CC';

    public function handle(): void
    {
        $hrEmail = config('app.hr_email');

        if (!$hrEmail) {
            $this->error('HR_EMAIL is not set in .env — aborting.');
            return;
        }

        // Read the "notify team leaders" toggle from settings
        $appSettings          = SettingsController::getAllSettings();
        $notifyTeamLeaders    = $appSettings['notify_team_leaders'] ?? false;

        $weekEnd   = Carbon::now()->endOfDay();
        $weekStart = Carbon::now()->subDays(6)->startOfDay();
        $weekLabel = $weekStart->format('d M') . ' – ' . $weekEnd->format('d M Y');

        // Get all team leaders
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

            // Get all users in this department (exclude admins/team_leaders)
            $teamUsers = User::where('department', $department)
                ->whereIn('role', ['user', 'team_leader'])
                ->get();

            $totalTeamHours = 0;
            $userStats = $teamUsers->map(function ($user) use ($weekStart, $weekEnd, &$totalTeamHours) {

                // Total activity logs for this user this week
                $logs = ActivityLog::where('user_name', $user->name)
                    ->whereBetween('recorded_at', [$weekStart, $weekEnd])
                    ->get();

                $totalSeconds  = $logs->count() * 3;
                $hours         = floor($totalSeconds / 3600);
                $minutes       = floor(($totalSeconds % 3600) / 60);
                $totalTeamHours += $hours;

                // Top application used
                $topApp = $logs->groupBy('application')
                    ->map(fn($g) => $g->count())
                    ->sortDesc()
                    ->keys()
                    ->first();

                // Days active this week
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

            // Pass team leader email only if the toggle is on; null otherwise (no CC)
            $leaderEmailForReport = $notifyTeamLeaders ? $leader->email : null;

            // Send the email
            try {
                Mail::send(new WeeklyTeamReport(
                    teamLeaderName:  $leader->name,
                    teamLeaderEmail: $leaderEmailForReport,
                    department:      $department,
                    weekLabel:       $weekLabel,
                    weekStart:       $weekStart->toDateString(),
                    weekEnd:         $weekEnd->toDateString(),
                    userStats:       $userStats,
                    totalTeamHours:  $totalTeamHours,
                    hrEmail:         $hrEmail,
                ));

                $ccNote = $notifyTeamLeaders ? ", CC: {$leader->email}" : ' (Team Leader not notified)';
                $this->info("✅ Report sent for {$department} (Leader: {$leader->name}) → HR: {$hrEmail}{$ccNote}");
            } catch (\Exception $e) {
                $this->error("❌ Failed to send for {$department}: " . $e->getMessage());
            }
        }

        $this->info('Weekly report job completed.');
    }
}
