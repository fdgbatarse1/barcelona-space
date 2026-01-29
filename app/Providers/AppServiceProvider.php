<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mailer\Bridge\Sendgrid\Transport\SendgridTransportFactory;
use Symfony\Component\Mailer\Transport\Dsn;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(\App\Contracts\WeatherProvider::class, function ($app) {
            return match (config('services.weather.provider')) {
                'openweathermap' => new \App\Services\Weather\OpenWeatherMapProvider(
                    config('services.openweathermap.key', ''),
                ),
                'openmeteo' => new \App\Services\Weather\OpenMeteoProvider(),
                default => new \App\Services\Weather\OpenWeatherMapProvider(
                    config('services.openweathermap.key', ''),
                ),
            };
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::defaultView('vendor.pagination.tailwind');
        Paginator::defaultSimpleView('vendor.pagination.simple-tailwind');

        Mail::extend('sendgrid', function (array $config = []) {
            return (new SendgridTransportFactory)->create(
                new Dsn(
                    'sendgrid+api',
                    'default',
                    $config['key']
                )
            );
        });
    }
}
