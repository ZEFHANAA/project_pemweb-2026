<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\LokasiController;
use App\Http\Controllers\ProfileController;
use Livewire\Livewire;

/* NOTE: Do Not Remove
/ Livewire asset handling if using sub folder in domain
*/

Livewire::setUpdateRoute(function ($handle) {
    return Route::post(config('app.asset_prefix') . '/livewire/update', $handle);
});

Livewire::setScriptRoute(function ($handle) {
    return Route::get(config('app.asset_prefix') . '/livewire/livewire.js', $handle);
});
/*
/ END
*/

// Public routes
Route::get('/', function () {
    return view('welcome');
});

// Public halaman detail lokasi (tanpa login) — rate limit anti scraping
Route::get('/lokasi/{id}', [LokasiController::class, 'publicDetail'])->name('lokasi.detail')->middleware('throttle:60,1');

// Authentication routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login')->middleware('guest');
Route::post('/login', [AuthController::class, 'login'])->middleware(['guest', 'throttle:5,1']);
Route::get('/register', [AuthController::class, 'showRegister'])->name('register')->middleware('guest');
Route::post('/register', [AuthController::class, 'register'])->middleware(['guest', 'throttle:3,1']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Password Reset Routes
Route::get('/forgot-password', [\App\Http\Controllers\PasswordResetController::class, 'showLinkRequestForm'])->name('password.request')->middleware('guest');
Route::post('/forgot-password', [\App\Http\Controllers\PasswordResetController::class, 'sendResetLinkEmail'])->name('password.email')->middleware(['guest', 'throttle:3,1']);
Route::get('/reset-password/{token}', [\App\Http\Controllers\PasswordResetController::class, 'showResetForm'])->name('password.reset')->middleware('guest');
Route::post('/reset-password', [\App\Http\Controllers\PasswordResetController::class, 'reset'])->name('password.update')->middleware(['guest', 'throttle:3,1']);


// API Lokasi routes
// Public (guest) read-only: index & show return [] / 404 untuk guest (lihat LokasiController).
// WAJIB di luar middleware('auth') — Googlebot guest fetch /api/lokasi butuh JSON, bukan 302->HTML.
Route::get('/api/lokasi',          [LokasiController::class, 'index']);
Route::get('/api/lokasi/export',   [LokasiController::class, 'export']);  // export harus di atas {lokasi}
Route::get('/api/lokasi/{lokasi}', [LokasiController::class, 'show']);
// Semua yang nulis data tetap auth
Route::middleware('auth')->group(function () {
    Route::post('/api/lokasi',         [LokasiController::class, 'store']);
    Route::put('/api/lokasi/{lokasi}', [LokasiController::class, 'update']);
    Route::delete('/api/lokasi/{lokasi}', [LokasiController::class, 'destroy']);
});

// API Profile routes
Route::put('/api/profile', [ProfileController::class, 'update'])->middleware('auth');
Route::put('/api/profile/password', [ProfileController::class, 'updatePassword'])->middleware('auth');


