<?php

use App\Http\Controllers\TenantAuthController;
use App\Http\Controllers\LandingController;

use App\Http\Controllers\ReservationController;

// Rute Halaman Utama Publik
Route::get('/', [LandingController::class, 'index'])->name('home');

// Auth Routes (Penyewa)
Route::get('/login', [TenantAuthController::class, 'showLogin'])->name('login')->middleware('guest');
Route::post('/login', [TenantAuthController::class, 'login'])->middleware('guest');
Route::get('/register', [TenantAuthController::class, 'showRegister'])->name('register')->middleware('guest');
Route::post('/register', [TenantAuthController::class, 'register'])->middleware('guest');
Route::post('/logout', [TenantAuthController::class, 'logout'])->name('logout');

// Area Penyewa (Hanya untuk yang sudah login)
Route::middleware('auth')->group(function () {
    // Rute Reservasi Aktif & Riwayat
    Route::get('/reservations', [ReservationController::class, 'index'])->name('reservations.index');
    Route::get('/reservations/history', [ReservationController::class, 'history'])->name('reservations.history');
    
    // Rute Buat Reservasi
    Route::get('/reservations/create', [ReservationController::class, 'create'])->name('reservations.create');
    Route::post('/reservations', [ReservationController::class, 'store'])->name('reservations.store');
    Route::post('/reservations/{reservation}/cancel', [ReservationController::class, 'cancel'])->name('reservations.cancel');
    Route::get('/reservations/{reservation}/payment', [ReservationController::class, 'payment'])->name('reservations.payment');
    Route::post('/reservations/{reservation}/payment', [ReservationController::class, 'uploadPayment'])->name('reservations.uploadPayment');
    
    // API Ketersediaan Kamar
    Route::post('/api/check-availability', [ReservationController::class, 'checkAvailability'])->name('api.check.availability');
    
    // Profil Penyewa
    Route::get('/profile', [\App\Http\Controllers\ProfileController::class, 'index'])->name('profile.index');
    Route::post('/profile', [\App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/password', [\App\Http\Controllers\ProfileController::class, 'updatePassword'])->name('profile.password');
});
