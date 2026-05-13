<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\EbookController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\Admin\EbookController as AdminEbookController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\MemberController;
use App\Http\Controllers\Admin\EventController as AdminEventController;
use App\Http\Controllers\Admin\RegistrationController;
use App\Http\Controllers\Admin\ScannerController;
use App\Http\Controllers\MembershipController;

// Public routes
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/a-propos', [HomeController::class, 'about'])->name('about');
Route::get('/contact', [ContactController::class, 'index'])->name('contact');
Route::post('/contact', [ContactController::class, 'send'])->name('contact.send');
Route::get('/evenements', [EventController::class, 'index'])->name('events.index');
Route::get('/evenements/{slug}', [EventController::class, 'show'])->name('events.show');
Route::post('/evenements/{slug}/inscription', [EventController::class, 'register'])->name('events.register');
Route::post('/evenements/{slug}/liste-attente', [EventController::class, 'joinWaitingList'])->name('events.waiting-list');
Route::get('/evenements/{slug}/ical', [EventController::class, 'ical'])->name('events.ical');
Route::get('/ebooks', [EbookController::class, 'index'])->name('ebooks.index');
Route::get('/rejoindre', [MembershipController::class, 'index'])->name('membership.join');
Route::post('/rejoindre', [MembershipController::class, 'store'])->name('membership.store');
Route::get('/candidature-envoyee', [MembershipController::class, 'success'])->name('membership.success');

// Breeze auth routes
require __DIR__.'/auth.php';

// Admin protected routes
Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Members
    Route::post('members/bulk-action', [MemberController::class, 'bulkAction'])->name('members.bulk-action');
    Route::get('members/export-csv', [MemberController::class, 'exportCsv'])->name('members.export-csv');
    Route::resource('members', MemberController::class);
    Route::post('members/{member}/send-card', [MemberController::class, 'sendCard'])->name('members.send-card');
    Route::get('members/{member}/download-card', [MemberController::class, 'downloadCard'])->name('members.download-card');
    Route::post('members/{member}/activate', [MemberController::class, 'activate'])->name('members.activate');

    // Ebooks
    Route::resource('ebooks', AdminEbookController::class)->except(['show']);

    // Events
    Route::resource('events', AdminEventController::class);
    Route::post('events/{event}/duplicate', [AdminEventController::class, 'duplicate'])->name('events.duplicate');

    // Registrations
    Route::get('events/{event}/registrations', [RegistrationController::class, 'index'])->name('registrations.index');
    Route::get('events/{event}/registrations/export', [RegistrationController::class, 'exportCsv'])->name('registrations.export-csv');
    Route::post('registrations/{registration}/send-payment', [RegistrationController::class, 'sendPaymentLink'])->name('registrations.send-payment');
    Route::post('registrations/{registration}/confirm-payment', [RegistrationController::class, 'confirmPayment'])->name('registrations.confirm-payment');
    Route::post('registrations/{registration}/cancel', [RegistrationController::class, 'cancel'])->name('registrations.cancel');

    // QR Code Scanner
    Route::get('scanner/{event}', [ScannerController::class, 'index'])->name('scanner.index');
    Route::post('scanner/verify', [ScannerController::class, 'verify'])->name('scanner.verify');

    // Waiting list per event
    Route::get('events/{event}/waiting-list', function (\App\Models\Event $event) {
        $waitingList = $event->waitingList()->latest()->get();
        return view('admin.events.waiting-list', compact('event', 'waitingList'));
    })->name('events.waiting-list-admin');

    // Activity log
    Route::get('activity', function () {
        $logs = \App\Models\ActivityLog::with('user')->latest()->paginate(50);
        return view('admin.activity', compact('logs'));
    })->name('activity');
});
