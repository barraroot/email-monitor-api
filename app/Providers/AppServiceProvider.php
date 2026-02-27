<?php

namespace App\Providers;

use Dedoc\Scramble\Scramble;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('auth', function (Request $request): Limit {
            return Limit::perMinute(20)->by($request->ip());
        });

        RateLimiter::for('ingest', function (Request $request): Limit {
            return Limit::perMinute(600)->by($request->ip());
        });

        RateLimiter::for('api', function (Request $request): Limit {
            return Limit::perMinute(120)->by($request->user()?->id ?? $request->ip());
        });

        Gate::define('viewApiDocs', function (): bool {
            return (bool) config('email-monitor.swagger.enabled');
        });

        if (config('email-monitor.swagger.enabled')) {
            Scramble::configure()->expose('/api/docs', '/api/openapi.json');
        } else {
            Scramble::configure()->expose(false);
        }
    }
}
