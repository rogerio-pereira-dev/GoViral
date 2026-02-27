<?php

use App\Http\Controllers\Core\DashboardController;
use App\Http\Controllers\FormController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\ThankYouController;
use Illuminate\Support\Facades\Route;

Route::get('/locale/{locale}', [LocaleController::class, 'index'])
    ->name('locale.switch');

Route::get('/', [LandingController::class, 'index'])
    ->name('home');

Route::get('/start-growth', [FormController::class, 'index'])
    ->name('form.index');

Route::post('/start-growth', [FormController::class, 'store'])
    ->name('form.store');

Route::get('/start-growth/payment-intent', [FormController::class, 'paymentIntent'])
    ->name('form.payment-intent');

Route::get('/thank-you', [ThankYouController::class, 'index'])
    ->name('form.thank-you');

/*
 * =====================================================================================================================
 * Core Routes (Admin)
 * =====================================================================================================================
 */
Route::middleware(['auth', 'verified'])
    ->prefix('core')
    ->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])
            ->name('dashboard');
    });

require __DIR__.'/settings.php';
