<?php

use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\StripeWebhookController;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        commands: __DIR__.'/../routes/console.php',
        then: function () {
            Route::get('/', fn () => view('landing'))->name('landing');
            Route::get('/aviso-de-privacidad', fn () => view('privacy'))->name('privacy');
            Route::post('/stripe/webhook', StripeWebhookController::class)->name('stripe.webhook');
            Route::middleware('web')->group(function () {
                Route::get('/inscripcion', [RegistrationController::class, 'create'])->name('registration.create');
                Route::post('/inscripcion', [RegistrationController::class, 'store'])->middleware('throttle:5,1')->name('registration.store');
                Route::get('/inscripcion/retorno', [RegistrationController::class, 'returned'])->name('registration.return');
                Route::get('/inscripcion/cancelada', [RegistrationController::class, 'cancelled'])->name('registration.cancel');
            });
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(SecurityHeaders::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {})->create();
