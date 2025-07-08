<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register( ): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Force HTTPS in production environment
        if (config("app.env") === "production") {
            URL::forceScheme("https" );
        }

         if (!file_exists(public_path('storage'))) {
        app('files')->link(
            storage_path('app/public'),
            public_path('storage')
        );
    }
    }
}
