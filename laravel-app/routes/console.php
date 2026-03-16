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
