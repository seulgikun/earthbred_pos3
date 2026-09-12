<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

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
        if (
            config('app.env') === 'production' ||
            $this->app->environment('production') ||
            str_starts_with(config('app.url', ''), 'https://') ||
            request()->header('x-forwarded-proto') === 'https'
        ) {
            URL::forceScheme('https');
        }
    }
}
