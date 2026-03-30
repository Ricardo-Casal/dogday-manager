<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Owner\BookingController as OwnerBookingController;
use App\Http\Controllers\Owner\DashboardController as OwnerDashboardController;
use App\Http\Controllers\OwnerController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Staff\BookingController as StaffBookingController;
use App\Http\Controllers\Staff\SettingController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

// Staff routes
Route::middleware(['auth', 'verified', 'staff'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('owners', OwnerController::class);

    Route::get('/attendances/create', [AttendanceController::class, 'create'])->name('attendances.create');
    Route::post('/attendances', [AttendanceController::class, 'store'])->name('attendances.store');
    Route::delete('/attendances/{attendance}', [AttendanceController::class, 'destroy'])->name('attendances.destroy');

    Route::get('/staff/bookings', [StaffBookingController::class, 'index'])->name('staff.bookings.index');
    Route::patch('/staff/bookings/{booking}', [StaffBookingController::class, 'update'])->name('staff.bookings.update');

    Route::get('/staff/settings', [SettingController::class, 'index'])->name('staff.settings.index');
    Route::patch('/staff/settings/{setting}', [SettingController::class, 'update'])->name('staff.settings.update');
});

// Owner routes
Route::middleware(['auth', 'verified', 'owner'])->prefix('owner')->name('owner.')->group(function () {
    Route::get('/dashboard', [OwnerDashboardController::class, 'index'])->name('dashboard');
    Route::get('/bookings/create', [OwnerBookingController::class, 'create'])->name('bookings.create');
    Route::post('/bookings', [OwnerBookingController::class, 'store'])->name('bookings.store');
});

// Profile (both roles)
Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
