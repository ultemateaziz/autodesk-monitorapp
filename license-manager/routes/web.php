<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LicenseController;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::get('/dashboard', [LicenseController::class, 'dashboard'])->name('dashboard');
Route::post('/generate-key', [LicenseController::class, 'generateKey'])->name('license.generate');
Route::post('/toggle-lock/{id}', [LicenseController::class, 'toggleLock'])->name('license.toggle-lock');
