<?php

use App\Http\Controllers\Public\ArtistController;
use App\Http\Controllers\Public\BookingController;
use App\Http\Controllers\Public\ContactController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\PaymentController;
use App\Http\Controllers\Webhooks\StripeWebhookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Stripe Webhook (No CSRF, No Tenant)
|--------------------------------------------------------------------------
|
| Stripe webhooks need to be outside of tenant middleware and CSRF protection.
|
*/

Route::post('/webhooks/stripe', [StripeWebhookController::class, 'handle'])
    ->name('webhooks.stripe')
    ->withoutMiddleware(['web']);

/*
|--------------------------------------------------------------------------
| Public Studio Routes (Tenant-Scoped)
|--------------------------------------------------------------------------
|
| These routes require tenant identification via the IdentifyTenant middleware.
| The tenant is resolved from custom domain, subdomain, or query parameter.
|
*/

Route::middleware(['tenant'])->group(function () {
    // Landing page
    Route::get('/', [HomeController::class, 'index'])->name('home');

    // Artists listing and detail
    Route::get('/artists', [ArtistController::class, 'index'])->name('artists.index');
    Route::get('/artists/{slug}', [ArtistController::class, 'show'])->name('artists.show');

    // Contact page
    Route::get('/contact', [ContactController::class, 'index'])->name('contact');

    // Booking
    Route::get('/book/{artistSlug?}', [BookingController::class, 'index'])->name('booking');
    Route::get('/book/confirmation/{appointment}', [BookingController::class, 'confirmation'])->name('booking.confirmation');

    // Payment routes
    Route::post('/booking/checkout/{appointment}', [PaymentController::class, 'checkout'])->name('booking.checkout');
    Route::get('/booking/success/{appointment}', [PaymentController::class, 'success'])->name('booking.success');
    Route::get('/booking/cancel/{appointment}', [PaymentController::class, 'cancel'])->name('booking.cancel');
});
