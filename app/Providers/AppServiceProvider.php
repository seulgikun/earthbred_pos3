<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Mail;
use App\Mail\Transport\ResendTransport;
use App\Mail\Transport\BrevoTransport;

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

        // Register custom Resend mail transport
        Mail::extend('resend', function (array $config = []) {
            $apiKey = $config['api_key'] ?? config('services.resend.key', env('RESEND_API_KEY'));
            return new ResendTransport($apiKey);
        });

        // Register custom Brevo mail transport
        Mail::extend('brevo', function (array $config = []) {
            $apiKey = $config['api_key'] ?? config('services.brevo.key', env('BREVO_API_KEY'));
            return new BrevoTransport($apiKey);
        });
    }
}
