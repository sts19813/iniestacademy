<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        RateLimiter::for('registration', fn (Request $request) => Limit::perMinute(5)->by($request->ip()));
        RateLimiter::for('admin-login', fn (Request $request) => Limit::perMinutes(15, 30)->by($request->ip()));
        RateLimiter::for('admin-setup-link', fn (Request $request) => Limit::perMinutes(15, 10)->by($request->ip()));
        RateLimiter::for('admin-setup-finish', fn (Request $request) => Limit::perMinutes(15, 5)->by($request->ip()));
        RateLimiter::for('admin-settings', fn (Request $request) => Limit::perMinutes(15, 5)->by($request->ip()));
    }
}
