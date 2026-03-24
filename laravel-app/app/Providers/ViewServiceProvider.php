<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Cache;
use App\Models\UserLicense;
use App\Models\ActivityLog;
use App\Models\DismissedNotification;
use Carbon\Carbon;

class ViewServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        View::composer('*', function ($view) {
            // Cache the notification query for 5 minutes to avoid heavy DB hits on every page load
            $notifications = \Illuminate\Support\Facades\Cache::remember('global_notifications', 300, function () {
                $thirtyDaysAgo = Carbon::now()->subDays(30);

                // Get dismissed notification keys in one query
                $dismissed = DismissedNotification::pluck('software_name', 'user_name')
                    ->map(fn($sw, $un) => $un . '|' . $sw)
                    ->values()
                    ->toArray();

                // Get ALL dismissed keys as a flat array of "user|software" strings
                $dismissedKeys = DismissedNotification::selectRaw("CONCAT(user_name, '|', software_name) as dk")
                    ->pluck('dk')
                    ->toArray();

                // Single query: find licenses where the user has NO recent activity for that software
                // Uses a LEFT JOIN so we only scan the DB once instead of N times
                $assignments = UserLicense::all();

                $results = [];

                // Get the last log per user in one query (avoid N queries in loop)
                $lastLogs = ActivityLog::select('user_name', 'machine_name', 'recorded_at')
                    ->whereIn('user_name', $assignments->pluck('user_name')->unique())
                    ->orderBy('recorded_at', 'desc')
                    ->get()
                    ->groupBy('user_name')
                    ->map(fn($logs) => $logs->first());

                // Get users who have recent activity per software in one grouped query
                $recentActivity = ActivityLog::selectRaw('user_name, application')
                    ->where('recorded_at', '>=', $thirtyDaysAgo)
                    ->get()
                    ->groupBy('user_name');

                foreach ($assignments as $assignment) {
                    $key = $assignment->user_name . '|' . $assignment->software_name;

                    // Skip dismissed
                    if (in_array($key, $dismissedKeys)) {
                        continue;
                    }

                    // Check if this user has recent activity for this software
                    $userLogs = $recentActivity->get($assignment->user_name, collect());
                    $softwareLower = strtolower($assignment->software_name);
                    $hasRecent = $userLogs->contains(fn($log) => str_contains(strtolower($log->application), $softwareLower));

                    if (!$hasRecent) {
                        $lastLog = $lastLogs->get($assignment->user_name);
                        $results[] = (object)[
                            'user_name'     => $assignment->user_name,
                            'software_name' => $assignment->software_name,
                            'last_seen'     => $lastLog ? $lastLog->recorded_at->diffForHumans() : 'Never',
                            'machine'       => $lastLog ? $lastLog->machine_name : 'Unknown',
                        ];
                    }
                }

                return $results;
            });

            $view->with('globalNotifications', $notifications);

            // Share license status with every view
            $licenseStatus = Cache::get('license_status', [
                'status'   => 'not_configured',
                'days_left' => null,
                'tier'      => '',
                'expires_at'=> '',
                'customer'  => '',
                'checked'   => null,
            ]);
            $view->with('licenseStatus', $licenseStatus);
        });
    }

    public function register(): void
    {
        //
    }
}
