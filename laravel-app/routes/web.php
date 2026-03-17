<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\LicenseActivationController;
use Illuminate\Support\Facades\Route;

// Auth Routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// License Activation (requires login but skips license check)
Route::middleware(['auth'])->group(function () {
    Route::get('/activate', [LicenseActivationController::class, 'show'])->name('license.activate');
    Route::post('/activate', [LicenseActivationController::class, 'activate'])->name('license.activate.post');
});

// Group Protected Routes
Route::middleware(['auth', \App\Http\Middleware\CheckLicenseActivated::class])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
    Route::get('/users', [DashboardController::class, 'users'])->name('users');
    Route::get('/export-csv', [DashboardController::class, 'exportCsv'])->name('dashboard.export');
    Route::get('/license-audit', [DashboardController::class, 'licenseAudit'])->name('license.audit');
    Route::get('/license-audit/export', [DashboardController::class, 'exportLicenseAuditCsv'])->name('license.audit.export');
    Route::get('/leaderboard', [DashboardController::class, 'leaderboard'])->name('leaderboard');
    Route::get('/machine-inventory', [DashboardController::class, 'machineInventory'])->name('machine.inventory');
    Route::get('/ghost-machines', [DashboardController::class, 'ghostMachines'])->name('ghost.machines');
    Route::get('/department-efficiency', [DashboardController::class, 'departmentEfficiency'])->name('department.efficiency');
    Route::get('/license-optimization', [DashboardController::class, 'licenseOptimization'])->name('license.optimization');

    // Report Export
    Route::get('/report-hub', [ReportController::class, 'hub'])->name('report.hub');
    Route::get('/report/generate', [ReportController::class, 'generate'])->name('report.generate');

    // License Assignment
    Route::post('/assign-license', [DashboardController::class, 'assignLicense'])->name('license.assign');
    Route::delete('/remove-license', [DashboardController::class, 'removeLicense'])->name('license.remove');
    Route::post('/update-profile', [DashboardController::class, 'updateProfile'])->name('user.update-profile');

    // Manual Software Revocation
    Route::post('/revoke-software', [DashboardController::class, 'revokeSoftware'])->name('software.revoke');
    Route::post('/restore-software', [DashboardController::class, 'restoreSoftware'])->name('software.restore');

    // User Management (Master Admin Only)
    Route::get('/user-management', [UserController::class, 'index'])->name('user-management');
    Route::post('/user-management', [UserController::class, 'store'])->name('user-management.store');
    Route::put('/user-management/{user}', [UserController::class, 'update'])->name('user-management.update');
    Route::delete('/user-management/{user}', [UserController::class, 'destroy'])->name('user-management.destroy');
    Route::post('/monitor-assignments', [UserController::class, 'syncMonitorAssignments'])->name('monitor-assignments.sync');

    // Notifications
    Route::post('/dismiss-notification', [DashboardController::class, 'dismissNotification'])->name('notification.dismiss');

    Route::get('/profile/{userName?}', [ProfileController::class, 'index'])->name('profile');
});
