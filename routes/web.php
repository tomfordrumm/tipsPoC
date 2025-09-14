<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', \App\Http\Controllers\DashboardController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
require __DIR__.'/admin.php';

// Public profile editing (avatar/display name/bio/slug/quick amounts)
Route::middleware('auth')->group(function () {
    Route::get('/public-profile', [\App\Http\Controllers\ProfileController::class, 'edit'])->name('public-profile.edit');
    Route::patch('/public-profile', [\App\Http\Controllers\ProfileController::class, 'update'])->name('public-profile.update');
    Route::post('/payout-requests', [\App\Http\Controllers\PayoutRequestController::class, 'store'])->name('payout-requests.store');
    Route::get('/qr', [\App\Http\Controllers\QrController::class, 'show'])->name('qr.show');
});

// Public tipping page and checkout
Route::get('/t/{profile:slug}', [\App\Http\Controllers\Public\TippingController::class, 'show'])->name('tips.public');
Route::post('/pay/checkout', [\App\Http\Controllers\CheckoutController::class, 'store'])->name('pay.checkout');
Route::get('/pay/success/{profile:slug}', [\App\Http\Controllers\Pay\StatusController::class, 'success'])->name('pay.success');
Route::get('/pay/cancel/{profile:slug}', [\App\Http\Controllers\Pay\StatusController::class, 'cancel'])->name('pay.cancel');
Route::post('/stripe/webhook', [\App\Http\Controllers\Stripe\WebhookController::class, 'handle'])
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class])
    ->name('stripe.webhook');
