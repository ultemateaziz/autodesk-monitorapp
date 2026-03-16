<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LicenseController;
use App\Http\Controllers\AuthController;

// ── Auth ──────────────────────────────────────────────────────
Route::get('/login',  [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ── Protected ─────────────────────────────────────────────────
Route::middleware('auth')->group(function () {

    Route::get('/', function () {
        return redirect()->route('dashboard');
    });

    Route::get('/dashboard', [LicenseController::class, 'dashboard'])->name('dashboard');
    Route::get('/licenses',  [LicenseController::class, 'index'])->name('license.list');
    Route::post('/generate-key', [LicenseController::class, 'generateKey'])->name('license.generate');
    Route::post('/toggle-lock/{id}', [LicenseController::class, 'toggleLock'])->name('license.toggle-lock');
    Route::delete('/licenses/{id}', [LicenseController::class, 'destroy'])->name('license.destroy');
    Route::post('/licenses/{id}/regenerate', [LicenseController::class, 'regenerate'])->name('license.regenerate');
    Route::get('/api-reference', [LicenseController::class, 'apiReference'])->name('api.reference');
    Route::get('/settings', [LicenseController::class, 'settings'])->name('settings');

});
