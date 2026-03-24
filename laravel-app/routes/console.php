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
Schedule::command('report:individual')->weeklyOn(1, '08:30');
