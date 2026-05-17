<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\LokasiController;
use Livewire\Livewire;
use Illuminate\Support\Facades\Response;

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

// Authentication routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login')->middleware('guest');
Route::post('/login', [AuthController::class, 'login'])->middleware('guest');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register')->middleware('guest');
Route::post('/register', [AuthController::class, 'register'])->middleware('guest');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// API Lokasi routes — export harus di atas agar tidak ditangkap oleh {lokasi}
Route::get('/api/lokasi/export', [LokasiController::class, 'export']);
Route::get('/api/lokasi',          [LokasiController::class, 'index']);
Route::post('/api/lokasi',         [LokasiController::class, 'store']);
Route::get('/api/lokasi/{lokasi}', [LokasiController::class, 'show']);
Route::put('/api/lokasi/{lokasi}', [LokasiController::class, 'update']);
Route::delete('/api/lokasi/{lokasi}', [LokasiController::class, 'destroy']);
