<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // The app runs behind a Cloudflare tunnel that terminates TLS; make
        // generated URLs https whenever the configured app URL is.
        if (strpos(config('app.url'), 'https://') === 0) {
            URL::forceScheme('https');
        }
    }
}
