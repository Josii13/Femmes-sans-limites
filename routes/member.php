<?php

use App\Http\Controllers\Member\AuthController;
use App\Http\Controllers\Member\DirectoryController;
use App\Http\Controllers\Member\MembershipPaymentController;
use App\Http\Controllers\Member\PortalController;
use Illuminate\Support\Facades\Route;

/*
| Espace membre.
|
| Guard « member », distinct de celui du back-office : une connexion membre ne
| donne jamais accès à l'administration. Les URL sont en français, comme le reste
| du site public.
*/

Route::prefix('espace-membre')->name('member.')->group(function () {

    // ── Visiteuses non connectées ───────────────────────────────
    Route::middleware('guest:member')->group(function () {
        Route::get('/connexion', [AuthController::class, 'showLogin'])->name('login');
        Route::post('/connexion', [AuthController::class, 'login'])
            ->middleware('throttle:10,1')->name('login.store');

        Route::get('/mot-de-passe', [AuthController::class, 'showForgotPassword'])->name('password.request');
        Route::post('/mot-de-passe', [AuthController::class, 'sendResetLink'])
            ->middleware('throttle:5,1')->name('password.email');

        Route::get('/mot-de-passe/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
        Route::post('/mot-de-passe/definir', [AuthController::class, 'resetPassword'])
            ->middleware('throttle:10,1')->name('password.store');
    });

    // ── Membres connectées ──────────────────────────────────────
    Route::middleware('auth:member')->group(function () {
        Route::get('/', [PortalController::class, 'dashboard'])->name('dashboard');
        Route::get('/mes-evenements', [PortalController::class, 'registrations'])->name('registrations');
        Route::get('/mes-ebooks', [PortalController::class, 'purchases'])->name('purchases');
        Route::get('/ma-carte', [PortalController::class, 'downloadCard'])->name('card');

        Route::get('/annuaire', [DirectoryController::class, 'index'])->name('directory');

        Route::get('/mon-profil', [PortalController::class, 'editProfile'])->name('profile');
        Route::put('/mon-profil', [PortalController::class, 'updateProfile'])->name('profile.update');
        Route::put('/mon-mot-de-passe', [PortalController::class, 'updatePassword'])->name('password.update');

        Route::get('/mon-adhesion', [MembershipPaymentController::class, 'show'])->name('renewal');
        Route::post('/mon-adhesion', [MembershipPaymentController::class, 'store'])
            ->middleware('throttle:10,1')->name('renewal.pay');

        Route::post('/deconnexion', [AuthController::class, 'logout'])->name('logout');
    });
});
