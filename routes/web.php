<?php

use App\Http\Controllers\Admin\AdminController;
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
    Route::post('/submission/upload', [\App\Http\Controllers\SubmissionController::class, 'upload'])->name('submission.upload');
});

// ── Admin Panel (requires auth + admin role) ──────────────────────────────────
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/',                           [AdminController::class, 'index'])->name('index');
    Route::get('/peserta/{id}',               [AdminController::class, 'show'])->name('show');
    Route::post('/peserta/{id}/approve',      [AdminController::class, 'approve'])->name('approve');
    Route::post('/peserta/{id}/reject',       [AdminController::class, 'reject'])->name('reject');
    Route::get('/payment/{id}/download',      [PaymentController::class, 'download'])->name('payment.download');
    Route::get('/payment/{id}/view',          [PaymentController::class, 'view'])->name('payment.view');
});
