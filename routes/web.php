<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\MemberController;
use App\Http\Controllers\Admin\EventController as AdminEventController;
use App\Http\Controllers\Admin\RegistrationController;
use App\Http\Controllers\Admin\ScannerController;

// Public routes
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/a-propos', [HomeController::class, 'about'])->name('about');
Route::get('/contact', [ContactController::class, 'index'])->name('contact');
Route::post('/contact', [ContactController::class, 'send'])->name('contact.send');
Route::get('/evenements', [EventController::class, 'index'])->name('events.index');
Route::get('/evenements/{slug}', [EventController::class, 'show'])->name('events.show');
Route::post('/evenements/{slug}/inscription', [EventController::class, 'register'])->name('events.register');

// Breeze auth routes
require __DIR__.'/auth.php';

// Admin protected routes
Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Members
    Route::resource('members', MemberController::class);
    Route::post('members/{member}/send-card', [MemberController::class, 'sendCard'])->name('members.send-card');
    Route::get('members/{member}/download-card', [MemberController::class, 'downloadCard'])->name('members.download-card');

    // Events
    Route::resource('events', AdminEventController::class);

    // Registrations
    Route::get('events/{event}/registrations', [RegistrationController::class, 'index'])->name('registrations.index');
    Route::post('registrations/{registration}/send-payment', [RegistrationController::class, 'sendPaymentLink'])->name('registrations.send-payment');
    Route::post('registrations/{registration}/confirm-payment', [RegistrationController::class, 'confirmPayment'])->name('registrations.confirm-payment');
    Route::post('registrations/{registration}/cancel', [RegistrationController::class, 'cancel'])->name('registrations.cancel');

    // QR Code Scanner
    Route::get('scanner/{event}', [ScannerController::class, 'index'])->name('scanner.index');
    Route::post('scanner/verify', [ScannerController::class, 'verify'])->name('scanner.verify');
});
