<?php

use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\FeeRateController;
use App\Http\Controllers\Admin\FeeTypeController;
use App\Http\Controllers\Admin\RequirementTypeController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\PublicPages\LandingController;
use App\Http\Controllers\PublicPages\VerificationController;
use Illuminate\Support\Facades\Route;

// Homepage / Landing
Route::get('/', [LandingController::class, 'index'])->name('landing');

// Permit verification (public)
Route::get('/verify', [VerificationController::class, 'lookup'])->name('verify.lookup');
Route::get('/verify/{token}', [VerificationController::class, 'show'])->name('verify.show');

// Guest routes (login / password reset)
Route::middleware('guest')->group(function (): void {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'authenticate'])
        ->middleware('throttle:login');

    Route::get('/forgot-password', [PasswordResetController::class, 'showForgot'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'showReset'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'reset'])->name('password.update');
});

// Logout
Route::post('/logout', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

// Admin routes
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function (): void {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    Route::resource('/users', UserController::class)->except(['show']);
    Route::resource('/fee-types', FeeTypeController::class)->except(['show']);
    Route::resource('/fee-rates', FeeRateController::class)->except(['show']);
    Route::resource('/requirement-types', RequirementTypeController::class)->except(['show']);

    Route::get('/settings', [SettingController::class, 'show'])->name('settings.index');
    Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
});
