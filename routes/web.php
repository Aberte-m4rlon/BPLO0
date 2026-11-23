<?php

use App\Http\Controllers\Admin\AuditLogController as AdminAuditLogController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\RequirementTypeController as AdminRequirementTypeController;
use App\Http\Controllers\Admin\SettingController as AdminSettingController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Applicant\ApplicationController as ApplicantApplicationController;
use App\Http\Controllers\Applicant\DashboardController as ApplicantDashboardController;
use App\Http\Controllers\Applicant\PaymentController as ApplicantPaymentController;
use App\Http\Controllers\Applicant\PermitController as ApplicantPermitController;
use App\Http\Controllers\Applicant\RequirementController as ApplicantRequirementController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Bplo\ApplicationController as BploApplicationController;
use App\Http\Controllers\Bplo\DashboardController as BploDashboardController;
use App\Http\Controllers\Bplo\PaymentController as BploPaymentController;
use App\Http\Controllers\Bplo\PermitController as BploPermitController;
use App\Http\Controllers\Inspector\DashboardController as InspectorDashboardController;
use App\Http\Controllers\Inspector\PermitController as InspectorPermitController;
use App\Http\Controllers\PublicPages\LandingController;
use App\Http\Controllers\PublicPages\TrackingController;
use App\Http\Controllers\PublicPages\VerificationController;
use App\Http\Controllers\Treasurer\DashboardController as TreasurerDashboardController;
use App\Http\Controllers\Treasurer\FeeRateController as TreasurerFeeRateController;
use App\Http\Controllers\Treasurer\FeeTypeController as TreasurerFeeTypeController;
use App\Http\Controllers\Treasurer\PaymentController as TreasurerPaymentController;
use Illuminate\Support\Facades\Route;

Route::get('/', [LandingController::class, 'index'])->name('landing');

Route::middleware('guest')->group(function (): void {
    Route::get('/register', [RegisterController::class, 'show'])->name('register');
    Route::post('/register', [RegisterController::class, 'store']);

    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'authenticate'])
        ->middleware('throttle:login');

    Route::get('/forgot-password', [PasswordResetController::class, 'showForgot'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'showReset'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'reset'])->name('password.update');
});

Route::post('/logout', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::post('/notifications/read-all', [\App\Http\Controllers\NotificationController::class, 'markAllRead'])
    ->middleware('auth')
    ->name('notifications.readAll');

Route::middleware(['auth', 'role:applicant'])->group(function (): void {
    Route::get('/dashboard', [ApplicantDashboardController::class, 'index'])->name('applicant.dashboard');
    Route::get('/applications/create', [ApplicantApplicationController::class, 'create'])->name('applications.create');
    Route::post('/applications', [ApplicantApplicationController::class, 'store'])->name('applications.store');
    Route::get('/applications/{application}', [ApplicantApplicationController::class, 'show'])->name('applications.show');
    Route::get('/applications/{application}/edit', [ApplicantApplicationController::class, 'edit'])->name('applications.edit');
    Route::put('/applications/{application}', [ApplicantApplicationController::class, 'update'])->name('applications.update');
    Route::get('/applications/{application}/summary', [ApplicantApplicationController::class, 'summary'])->name('applications.summary');
    Route::post('/applications/{application}/submit', [ApplicantApplicationController::class, 'submit'])->name('applications.submit');

    Route::post('/applications/{application}/requirements/upload-many', [ApplicantRequirementController::class, 'uploadMany'])->name('applications.requirements.uploadMany');
    Route::post('/applications/{application}/requirements/{type}', [ApplicantRequirementController::class, 'upload'])->name('applications.requirements.upload');
    Route::delete('/applications/{application}/requirements/{type}', [ApplicantRequirementController::class, 'remove'])->name('applications.requirements.remove');
    Route::get('/requirements/{applicationRequirement}/file', [ApplicantRequirementController::class, 'file'])->name('requirements.file');

    Route::get('/applications/{application}/pay', [ApplicantPaymentController::class, 'show'])->name('applications.pay');
    Route::post('/applications/{application}/pay', [ApplicantPaymentController::class, 'start'])->name('applications.pay.start');
    Route::get('/applications/{application}/payments', [ApplicantPaymentController::class, 'history'])->name('applications.payments');
    Route::get('/payments/{payment}/proof', [ApplicantPaymentController::class, 'proof'])->name('payments.proof');

    Route::get('/applications/{application}/permit', [ApplicantPermitController::class, 'show'])->name('applications.permit');
    Route::get('/applications/{application}/permit/download', [ApplicantPermitController::class, 'download'])->name('applications.permit.download');
});

Route::middleware(['auth', 'role:bplo'])->prefix('bplo')->name('bplo.')->group(function (): void {
    Route::get('/dashboard', [BploDashboardController::class, 'index'])->name('dashboard');

    Route::get('/applications', [BploApplicationController::class, 'index'])->name('applications.index');
    Route::get('/applications/{application}', [BploApplicationController::class, 'show'])->name('applications.show');
    Route::post('/applications/{application}/review', [BploApplicationController::class, 'beginReview'])->name('applications.review');
    Route::post('/applications/{application}/approve', [BploApplicationController::class, 'approve'])->name('applications.approve');
    Route::post('/applications/{application}/return', [BploApplicationController::class, 'returnForCorrection'])->name('applications.return');
    Route::post('/applications/{application}/reject', [BploApplicationController::class, 'reject'])->name('applications.reject');
    Route::post('/applications/{application}/requirements/{req}/flag', [BploApplicationController::class, 'flagRequirement'])->name('applications.requirements.flag');
    Route::get('/requirements/{applicationRequirement}/file', [BploApplicationController::class, 'file'])->name('requirements.file');

    Route::get('/permits', [BploPermitController::class, 'index'])->name('permits.index');
    Route::get('/payments/{payment}/receipt', [BploPaymentController::class, 'proof'])->name('payments.proof');
    Route::post('/applications/{application}/release', [BploPermitController::class, 'release'])->name('applications.release');
    Route::post('/permits/{permit}/regenerate', [BploPermitController::class, 'regenerate'])->name('permits.regenerate');
});

Route::middleware(['auth', 'role:treasurer'])->prefix('treasurer')->name('treasurer.')->group(function (): void {
    Route::get('/dashboard', [TreasurerDashboardController::class, 'index'])->name('dashboard');
    Route::get('/analytics', [TreasurerDashboardController::class, 'analytics'])->name('analytics');
    Route::get('/payments', [TreasurerPaymentController::class, 'index'])->name('payments.index');
    Route::get('/payments/export', [TreasurerPaymentController::class, 'export'])->name('payments.export');
    Route::get('/payments/{payment}', [TreasurerPaymentController::class, 'show'])->name('payments.show');
    Route::post('/payments/{payment}/confirm', [TreasurerPaymentController::class, 'confirm'])->name('payments.confirm');
    Route::post('/payments/{payment}/reject', [TreasurerPaymentController::class, 'reject'])->name('payments.reject');
    Route::get('/payments/{payment}/proof', [TreasurerPaymentController::class, 'proof'])->name('payments.proof');
    Route::resource('fee-types', TreasurerFeeTypeController::class)->except(['show', 'destroy']);
    Route::resource('fee-rates', TreasurerFeeRateController::class)->except(['show', 'destroy']);
});

Route::middleware(['auth', 'role:inspector'])->prefix('inspector')->name('inspector.')->group(function (): void {
    Route::get('/dashboard', [InspectorDashboardController::class, 'index'])->name('dashboard');
    Route::get('/permits', [InspectorPermitController::class, 'index'])->name('permits.index');
    Route::get('/permits/{permit}', [InspectorPermitController::class, 'show'])->name('permits.show');
});

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function (): void {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::resource('users', AdminUserController::class)->except(['show', 'destroy']);
    Route::resource('requirement-types', AdminRequirementTypeController::class)->except(['show', 'destroy']);
    Route::get('/audit-logs', [AdminAuditLogController::class, 'index'])->name('audit-logs.index');
    Route::get('/settings', [AdminSettingController::class, 'show'])->name('settings');
});

Route::get('/track', [TrackingController::class, 'show'])->name('track.show');
Route::post('/track', [TrackingController::class, 'lookup'])->name('track.lookup');

Route::get('/verify', [VerificationController::class, 'lookup'])->name('verify.lookup');
Route::get('/verify/{token}', [VerificationController::class, 'show'])
    ->middleware('throttle:verify')
    ->name('verify.show');
