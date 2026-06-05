<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EasypayWebhookController;
use App\Http\Controllers\Owner\BookingController as OwnerBookingController;
use App\Http\Controllers\Owner\DashboardController as OwnerDashboardController;
use App\Http\Controllers\Owner\DogController as OwnerDogController;
use App\Http\Controllers\Owner\PaymentController as OwnerPaymentController;
use App\Http\Controllers\OwnerController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Staff\BookingController as StaffBookingController;
use App\Http\Controllers\Staff\PaymentController as StaffPaymentController;
use App\Http\Controllers\Staff\ScheduleController as StaffScheduleController;
use App\Http\Controllers\Staff\SettingController;
use App\Http\Controllers\Staff\UserController as StaffUserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

// Easypay webhook (no CSRF, no auth)
Route::post('/webhooks/easypay', [EasypayWebhookController::class, 'handle'])
    ->name('webhooks.easypay')
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);

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

    Route::get('/staff/users', [StaffUserController::class, 'index'])->name('staff.users.index');
    Route::get('/staff/users/create', [StaffUserController::class, 'create'])->name('staff.users.create');
    Route::post('/staff/users', [StaffUserController::class, 'store'])->name('staff.users.store');
    Route::get('/staff/users/{user}', [StaffUserController::class, 'show'])->name('staff.users.show');
    Route::patch('/staff/users/{user}', [StaffUserController::class, 'update'])->name('staff.users.update');
    Route::patch('/staff/users/{user}/promote', [StaffUserController::class, 'promote'])->name('staff.users.promote');
    Route::delete('/staff/users/{user}', [StaffUserController::class, 'destroy'])->name('staff.users.destroy');
    Route::post('/staff/users/{user}/dogs', [StaffUserController::class, 'addDog'])->name('staff.users.dogs.store');
    Route::delete('/staff/users/{user}/dogs/{dog}', [StaffUserController::class, 'removeDog'])->name('staff.users.dogs.destroy');

    Route::get('/staff/schedule', [StaffScheduleController::class, 'index'])->name('staff.schedule.index');

    Route::get('/staff/payments', [StaffPaymentController::class, 'index'])->name('staff.payments.index');
    Route::post('/staff/payments/{booking}/generate', [StaffPaymentController::class, 'generate'])->name('staff.payments.generate');
    Route::post('/staff/payments/{payment}/resend', [StaffPaymentController::class, 'resend'])->name('staff.payments.resend');
    Route::post('/staff/payments/{payment}/simulate', [StaffPaymentController::class, 'simulate'])->name('staff.payments.simulate');
    Route::post('/staff/payments/{payment}/check', [StaffPaymentController::class, 'check'])->name('staff.payments.check');
});

// Owner routes
Route::middleware(['auth', 'verified', 'owner'])->prefix('owner')->name('owner.')->group(function () {
    Route::get('/dashboard', [OwnerDashboardController::class, 'index'])->name('dashboard');
    Route::get('/bookings/create', [OwnerBookingController::class, 'create'])->name('bookings.create');
    Route::post('/bookings', [OwnerBookingController::class, 'store'])->name('bookings.store');

    Route::get('/dogs/create', [OwnerDogController::class, 'create'])->name('dogs.create');
    Route::post('/dogs', [OwnerDogController::class, 'store'])->name('dogs.store');

    Route::get('/payments', [OwnerPaymentController::class, 'index'])->name('payments.index');
    Route::post('/payments/{payment}/resend', [OwnerPaymentController::class, 'resend'])->name('payments.resend');
    Route::post('/payments/{payment}/change-method', [OwnerPaymentController::class, 'changeMethod'])->name('payments.change-method');
    Route::post('/payments/{payment}/simulate', [OwnerPaymentController::class, 'simulate'])->name('payments.simulate');
    Route::post('/payments/{payment}/check', [OwnerPaymentController::class, 'check'])->name('payments.check');
});

// Profile (both roles)
Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
