<?php

namespace App\Console\Commands;

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
                ));

                $this->info("✅ Sent to {$user->name} ({$user->email}) | CC: {$leader->name}");
            } catch (\Exception $e) {
                $this->error("❌ Failed for {$user->name}: " . $e->getMessage());
            }
        }

        $this->info('Individual report job completed.');
    }
}
