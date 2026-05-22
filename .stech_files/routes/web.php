<?php

use App\Http\Controllers\DemoController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| S-Tech – Soedirman Technophoria Demo Routes
|--------------------------------------------------------------------------
|
| These routes serve a high-fidelity prototype for the S-Tech annual event.
| Authentication is simulated via a simple session flag — no real DB needed.
|
*/

// ── Landing Page ──────────────────────────────────────────────────────────────
Route::get('/', [DemoController::class, 'welcome'])->name('welcome');

// ── Auth (Dummy – no real DB check) ───────────────────────────────────────────
Route::post('/login', [DemoController::class, 'login'])->name('login');
Route::post('/logout', [DemoController::class, 'logout'])->name('logout');

// ── Protected Dashboard (guarded by demo session flag) ────────────────────────
Route::middleware('demo.auth')->group(function () {
    Route::get('/dashboard', [DemoController::class, 'dashboard'])->name('dashboard');
});
