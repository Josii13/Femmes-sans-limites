<?php

use App\Http\Controllers\Admin\CampaignTemplateController;
use App\Http\Controllers\Admin\CommunicationController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EbookController as AdminEbookController;
use App\Http\Controllers\Admin\EventController as AdminEventController;
use App\Http\Controllers\Admin\MemberController;
use App\Http\Controllers\Admin\RegistrationController;
use App\Http\Controllers\Admin\SalesController;
use App\Http\Controllers\Admin\ScannerController;
use App\Http\Controllers\Admin\SiteImageController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\EbookController;
use App\Http\Controllers\EbookPurchaseController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\GeniusPayWebhookController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MarketingController;
use App\Http\Controllers\MembershipController;
use App\Http\Controllers\MemberVerifyController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TrackingController;
use App\Models\ActivityLog;
use App\Models\Event;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/a-propos', [HomeController::class, 'about'])->name('about');
Route::get('/contact', [ContactController::class, 'index'])->name('contact');
Route::post('/contact', [ContactController::class, 'send'])->middleware('throttle:5,1')->name('contact.send');
Route::get('/evenements', [EventController::class, 'index'])->name('events.index');
Route::get('/evenements/{slug}', [EventController::class, 'show'])->name('events.show');
Route::post('/evenements/{slug}/inscription', [EventController::class, 'register'])->middleware('throttle:10,1')->name('events.register');
Route::post('/evenements/{slug}/liste-attente', [EventController::class, 'joinWaitingList'])->middleware('throttle:10,1')->name('events.waiting-list');
Route::get('/evenements/{slug}/ical', [EventController::class, 'ical'])->name('events.ical');
Route::get('/ebooks', [EbookController::class, 'index'])->name('ebooks.index');
Route::get('/ebooks/{slug}', [EbookController::class, 'show'])->name('ebooks.show');
Route::get('/rejoindre', [MembershipController::class, 'index'])->name('membership.join');
Route::post('/rejoindre', [MembershipController::class, 'store'])->middleware('throttle:5,1')->name('membership.store');
Route::get('/candidature-envoyee', [MembershipController::class, 'success'])->name('membership.success');

// Newsletter (double opt-in + désinscription confirmée)
Route::post('/newsletter/subscribe', [NewsletterController::class, 'subscribe'])->middleware('throttle:5,1')->name('newsletter.subscribe');
Route::get('/newsletter/confirmer/{token}', [NewsletterController::class, 'confirm'])->name('newsletter.confirm');
Route::get('/newsletter/unsubscribe/{token}', [NewsletterController::class, 'unsubscribe'])->name('newsletter.unsubscribe');
Route::post('/newsletter/unsubscribe/{token}', [NewsletterController::class, 'unsubscribeConfirm'])->name('newsletter.unsubscribe.confirm');

// Désinscription marketing des membres (lien dans les campagnes — RGPD)
Route::get('/communication/desinscription/{token}', [MarketingController::class, 'unsubscribe'])->name('marketing.unsubscribe');
Route::post('/communication/desinscription/{token}', [MarketingController::class, 'unsubscribeConfirm'])->name('marketing.unsubscribe.confirm');

// Tracking pixel email
Route::get('/t/{token}.gif', [TrackingController::class, 'pixel'])->name('track.pixel');

// Vérification publique d'une carte de membre (cible du QR de la carte)
Route::get('/membre/{token}', [MemberVerifyController::class, 'show'])->name('members.verify');

// ── Paiements GeniusPay ──────────────────────────────────────────────
// Retours après paiement (success_url / error_url)
Route::get('/paiement/{payment:reference}/merci', [PaymentController::class, 'success'])->name('payment.success');
Route::get('/paiement/{payment:reference}/echec', [PaymentController::class, 'cancel'])->name('payment.cancel');

// Achat d'ebook
Route::get('/ebooks/{ebook:slug}/acheter', [EbookPurchaseController::class, 'create'])->name('ebooks.buy');
Route::post('/ebooks/{ebook:slug}/acheter', [EbookPurchaseController::class, 'store'])
    ->middleware('throttle:10,1')->name('ebooks.buy.store');
// Téléchargement de l'ebook acheté (lien signé envoyé par email)
Route::get('/ebooks/telechargement/{payment:reference}', [EbookPurchaseController::class, 'download'])
    ->middleware('signed')->name('ebooks.download');
// Renvoyer son lien de téléchargement
Route::get('/ebooks-mes-achats', [EbookPurchaseController::class, 'resendForm'])->name('ebooks.resend');
Route::post('/ebooks-mes-achats', [EbookPurchaseController::class, 'resend'])
    ->middleware('throttle:5,1')->name('ebooks.resend.store');

// Webhook GeniusPay (signé HMAC, exempté de CSRF)
Route::post('/webhooks/geniuspay', [GeniusPayWebhookController::class, 'handle'])->name('webhooks.geniuspay');

// Pages légales
Route::get('/mentions-legales', fn () => view('public.legal.mentions-legales'))->name('legal.mentions');
Route::get('/conditions-generales-utilisation', fn () => view('public.legal.cgu'))->name('legal.cgu');

// Breeze auth routes
require __DIR__.'/auth.php';

// Redirection « dashboard » → back-office admin (utilisée par les contrôleurs d'auth Breeze).
Route::get('/dashboard', fn () => redirect()->route('admin.dashboard'))
    ->middleware('auth')
    ->name('dashboard');

// Profil du compte connecté (nom, email, mot de passe, suppression).
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Admin protected routes
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Members
    Route::post('members/bulk-action', [MemberController::class, 'bulkAction'])->name('members.bulk-action');
    Route::get('members/export-csv', [MemberController::class, 'exportCsv'])->name('members.export-csv');
    Route::resource('members', MemberController::class);
    Route::post('members/{member}/send-card', [MemberController::class, 'sendCard'])->name('members.send-card');
    Route::get('members/{member}/download-card', [MemberController::class, 'downloadCard'])->name('members.download-card');
    Route::post('members/{member}/activate', [MemberController::class, 'activate'])->name('members.activate');
    Route::post('members/{member}/reject', [MemberController::class, 'reject'])->name('members.reject');
    Route::post('members/{member}/regenerate-card', [MemberController::class, 'regenerateCard'])->name('members.regenerate-card');

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
    Route::get('registrations/{registration}/qr', [RegistrationController::class, 'downloadQr'])->name('registrations.qr');

    // QR Code Scanner
    Route::get('scanner/{event}', [ScannerController::class, 'index'])->name('scanner.index');
    Route::post('scanner/{event}/verify', [ScannerController::class, 'verify'])->name('scanner.verify');

    // Waiting list per event
    Route::get('events/{event}/waiting-list', function (Event $event) {
        $waitingList = $event->waitingList()->latest()->get();

        return view('admin.events.waiting-list', compact('event', 'waitingList'));
    })->name('events.waiting-list-admin');

    // Communication / Templates (doit être avant la resource communication pour éviter le conflit {campaign})
    Route::prefix('communication')->name('communication.')->group(function () {
        Route::get('templates', [CampaignTemplateController::class, 'index'])->name('templates.index');
        Route::get('templates/create', [CampaignTemplateController::class, 'create'])->name('templates.create');
        Route::post('templates', [CampaignTemplateController::class, 'store'])->name('templates.store');
        Route::get('templates/{template}/edit', [CampaignTemplateController::class, 'edit'])->name('templates.edit');
        Route::put('templates/{template}', [CampaignTemplateController::class, 'update'])->name('templates.update');
        Route::delete('templates/{template}', [CampaignTemplateController::class, 'destroy'])->name('templates.destroy');
        Route::get('templates/{template}/apply', [CampaignTemplateController::class, 'apply'])->name('templates.apply');
    });

    // Communication / Campagnes
    Route::resource('communication', CommunicationController::class)
        ->except(['show'])
        ->parameters(['communication' => 'campaign']);
    Route::get('communication/{campaign}', [CommunicationController::class, 'show'])->name('communication.show');
    Route::post('communication/{campaign}/send', [CommunicationController::class, 'send'])->name('communication.send');
    Route::get('communication/{campaign}/preview', [CommunicationController::class, 'preview'])->name('communication.preview');

    // Site images
    Route::get('site-images', [SiteImageController::class, 'index'])->name('site-images.index');
    Route::put('site-images/{siteImage}', [SiteImageController::class, 'update'])->name('site-images.update');
    Route::delete('site-images/{siteImage}/reset', [SiteImageController::class, 'reset'])->name('site-images.reset');

    // Ventes / paiements
    Route::get('sales', [SalesController::class, 'index'])->name('sales.index');

    // Activity log
    Route::get('activity', function () {
        $logs = ActivityLog::with('user')->latest()->paginate(50);

        return view('admin.activity', compact('logs'));
    })->name('activity');
});
