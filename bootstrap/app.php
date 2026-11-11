<?php

use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\SetupController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\StripeWebhookController;
use App\Http\Middleware\AdminOrigin;
use App\Http\Middleware\AdminSessionConfig;
use App\Http\Middleware\ApplySiteSettings;
use App\Http\Middleware\RequireAdmin;
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
            Route::get('/sitemap.xml', function () {
                $url = htmlspecialchars(rtrim(config('app.url'), '/').'/', ENT_XML1 | ENT_QUOTES, 'UTF-8');

                return response('<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"><url><loc>'.$url.'</loc></url></urlset>', 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
            });
            Route::get('/aviso-de-privacidad', fn () => view('privacy'))->name('privacy');
            Route::post('/stripe/webhook', StripeWebhookController::class)->name('stripe.webhook');
            Route::middleware('web')->group(function () {
                Route::get('/inscripcion', [RegistrationController::class, 'create'])->name('registration.create');
                Route::post('/inscripcion', [RegistrationController::class, 'store'])->middleware('throttle:registration')->name('registration.store');
                Route::get('/inscripcion/retorno', [RegistrationController::class, 'returned'])->name('registration.return');
                Route::get('/inscripcion/cancelada', [RegistrationController::class, 'cancelled'])->name('registration.cancel');
                Route::prefix('admin')->name('admin.')->middleware(AdminOrigin::class)->group(function () {
                    Route::get('/activar', [SetupController::class, 'start'])->name('setup.start');
                    Route::post('/activar', [SetupController::class, 'redeem'])->middleware('throttle:admin-setup-link')->name('setup.redeem');
                    Route::get('/activar/cuenta', [SetupController::class, 'form'])->name('setup.form');
                    Route::post('/activar/cuenta', [SetupController::class, 'finish'])->middleware('throttle:admin-setup-finish')->name('setup.finish');
                    Route::get('/acceso', [AdminAuthController::class, 'show'])->name('login');
                    Route::post('/acceso', [AdminAuthController::class, 'login'])->middleware('throttle:admin-login')->name('login.submit');
                    Route::middleware(RequireAdmin::class)->group(function () {
                        Route::post('/salir', [AdminAuthController::class, 'logout'])->name('logout');
                        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
                        Route::get('/inscripciones/{registration}', [DashboardController::class, 'show'])->name('registration');
                        Route::get('/configuracion', [SettingsController::class, 'show'])->name('settings');
                        Route::post('/configuracion', [SettingsController::class, 'update'])->middleware('throttle:admin-settings')->name('settings.update');
                    });
                });
            });
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->prepend(AdminSessionConfig::class);
        $middleware->append(ApplySiteSettings::class);
        $middleware->append(SecurityHeaders::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {})->create();
