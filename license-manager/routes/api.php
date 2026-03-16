<?php

use App\Http\Controllers\LicenseController;
use Illuminate\Support\Facades\Route;

// Called by the monitor agent on first launch
Route::post('/license/activate', [LicenseController::class, 'apiActivate']);

// Called every time the monitor app starts / every N minutes to check status
Route::post('/license/verify',   [LicenseController::class, 'apiVerify']);

// Heartbeat: monitor pings every few minutes while running
Route::post('/license/pulse',    [LicenseController::class, 'apiPulse']);
