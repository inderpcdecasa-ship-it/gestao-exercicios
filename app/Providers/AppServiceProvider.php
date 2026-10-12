<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
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
        // Atrás do ngrok (ou outro proxy HTTPS), força as URLs a usarem https
        if (request()->header('x-forwarded-proto') === 'https') {
            URL::forceScheme('https');
        }
    }
}