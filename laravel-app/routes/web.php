<?php

use App\Http\Controllers\AuditController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\LicenseActivationController;
use App\Http\Controllers\AgentLicenseController;
use Illuminate\Support\Facades\Route;

// Public API for monitor clients
Route::get('/api/idle-threshold', [SettingsController::class, 'idleThresholdApi']);

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
    Route::post('/settings/working-hours', [SettingsController::class, 'saveWorkingHours'])->name('settings.working-hours');
    Route::post('/settings/idle-threshold', [SettingsController::class, 'saveIdleThreshold'])->name('settings.idle-threshold');
    Route::post('/settings/change-password', [SettingsController::class, 'changePassword'])->name('settings.change-password');
    Route::post('/settings/email', [SettingsController::class, 'saveEmailSettings'])->name('settings.email');
    Route::post('/settings/test-email', [SettingsController::class, 'testEmail'])->name('settings.test-email');
    Route::get('/audit-trail', [AuditController::class, 'index'])->name('audit.trail');
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
    Route::post('/remove-software-permanently', [DashboardController::class, 'permanentlyRemoveSoftware'])->name('software.remove-permanent');

    // User Management (Master Admin Only)
    Route::get('/user-management', [UserController::class, 'index'])->name('user-management');
    Route::post('/user-management', [UserController::class, 'store'])->name('user-management.store');
    Route::put('/user-management/{user}', [UserController::class, 'update'])->name('user-management.update');
    Route::delete('/user-management/{user}', [UserController::class, 'destroy'])->name('user-management.destroy');
    Route::post('/monitor-assignments', [UserController::class, 'syncMonitorAssignments'])->name('monitor-assignments.sync');

    // Notifications
    Route::post('/dismiss-notification', [DashboardController::class, 'dismissNotification'])->name('notification.dismiss');

    // Machine Licensing Hub
    Route::get('/machine-licensing', [AgentLicenseController::class, 'index'])->name('machine.licensing');
    Route::post('/machine-licensing/{id}/approve', [AgentLicenseController::class, 'approve'])->name('machine.licensing.approve');
    Route::post('/machine-licensing/{id}/revoke', [AgentLicenseController::class, 'revoke'])->name('machine.licensing.revoke');
    Route::delete('/machine-licensing/{id}', [AgentLicenseController::class, 'destroy'])->name('machine.licensing.destroy');

    Route::get('/profile/{userName?}', [ProfileController::class, 'index'])->name('profile');
    Route::get('/profile/{userName}/export-pdf',      [ProfileController::class, 'exportPdf'])->name('profile.export.pdf');
    Route::get('/profile/{userName}/export-excel',    [ProfileController::class, 'exportExcel'])->name('profile.export.excel');
    Route::get('/profile/{userName}/export-sessions',     [ProfileController::class, 'exportSessionReport'])->name('profile.export.sessions');
    Route::get('/profile/{userName}/export-sessions-pdf', [ProfileController::class, 'exportSessionPdf'])->name('profile.export.sessions.pdf');
});
