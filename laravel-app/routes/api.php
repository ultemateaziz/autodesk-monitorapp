<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\AgentLicenseController;
use App\Http\Controllers\SettingsController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/log-activity', [ActivityController::class, 'store']);
Route::get('/idle-threshold', [SettingsController::class, 'idleThresholdApi']);

// Machine licensing — agent endpoints (no auth, token-based)
Route::post('/agent/register', [AgentLicenseController::class, 'register']);
Route::post('/agent/validate', [AgentLicenseController::class, 'validate']);
