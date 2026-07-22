<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;

// ── Public ────────────────────────────────────────────────────────────────────
Route::get('/', [AuthController::class, 'welcome'])->name('welcome');

// ── Auth ──────────────────────────────────────────────────────────────────────
Route::post('/register', [AuthController::class, 'register'])->name('register');
Route::post('/login',    [AuthController::class, 'login'])->name('login');
Route::post('/logout',   [AuthController::class, 'logout'])->name('logout');

// ── Peserta Dashboard (requires auth) ─────────────────────────────────────────
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/payment/upload', [PaymentController::class, 'upload'])->name('payment.upload');
    Route::post('/team-members/upload', [\App\Http\Controllers\TeamMemberController::class, 'upload'])->name('team-members.upload');
    Route::post('/submission/upload', [\App\Http\Controllers\SubmissionController::class, 'upload'])->name('submission.upload');
});

// ── Admin file access (the admin dashboard itself is the Filament panel at /admin) ─
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/payment/{id}/download', [PaymentController::class, 'download'])->name('payment.download');
    Route::get('/payment/{id}/view',     [PaymentController::class, 'view'])->name('payment.view');
});
