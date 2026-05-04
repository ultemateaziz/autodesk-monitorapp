<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ── License check — runs every 5 minutes automatically ──
// No batch file needed. Start once with: php artisan schedule:work
Schedule::command('license:check')->everyFiveMinutes();

// ── Weekly team report — every Monday at 8:00 AM ──
// TO: HR | CC: Team Leader | Shows all team members usage
Schedule::command('report:weekly')->weeklyOn(1, '08:00');

// ── Individual user report — every Monday at 8:30 AM ──
// FROM: HR | TO: each user | CC: their team leader | Shows personal profile + usage
// Fires only if "notify_individual_users" is enabled in Settings → Email Configuration
Schedule::command('report:individual')->weeklyOn(1, '08:30');

// ── Monthly team report — 1st of every month at 8:00 AM ──
// Same recipients as weekly but covers the previous full calendar month
Schedule::command('report:monthly')->monthlyOn(1, '08:00');

// ── 6-month team report — 1st of January and July at 8:00 AM ──
// Covers last 6 months; fires twice a year
Schedule::command('report:sixmonth')->cron('0 8 1 1,7 *');
