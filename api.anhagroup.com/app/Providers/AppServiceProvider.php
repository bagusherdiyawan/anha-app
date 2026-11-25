<?php

namespace App\Providers;

use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

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
        // Batas umum untuk seluruh endpoint API: 90 request/menit per user
        // (kalau login) atau per IP (kalau belum login).
        RateLimiter::for('api', function ($request) {
            return Limit::perMinute(1000)->by($request->user()?->id ?: $request->ip());
        });

        // API-only app: jangan pernah coba redirect ke route 'login' saat request
        // tidak terautentikasi (route itu tidak ada di sini) — biarkan selalu
        // jatuh ke AuthenticationException supaya konsisten dapat JSON 401,
        // apa pun header Accept yang dikirim client.
        Authenticate::redirectUsing(fn () => null);
    }
}
