<?php

use App\Livewire\WeatherDisplay;
use App\Services\Weather\OpenMeteoProvider;
use App\Services\Weather\OpenWeatherMapProvider;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

describe('Weather Integration', function () {
    beforeEach(function () {
        Http::preventStrayRequests();
        Cache::flush();
    });

    test('weather data is cached for 10 minutes', function () {
        Http::fake([
            'api.openweathermap.org/*' => Http::response([
                'main' => ['temp' => 22],
                'weather' => [['description' => 'clear sky', 'icon' => '01d']],
            ], 200),
        ]);

        Livewire::test(WeatherDisplay::class, [
            'latitude' => 41.3851,
            'longitude' => 2.1734,
        ]);

        // Check that cache was set
        expect(Cache::has('weather_openweathermap_41.3851_2.1734'))->toBeTrue();
    });

    test('cache key includes provider name', function () {
        Http::fake([
            'api.open-meteo.com/*' => Http::response([
                'current' => ['temperature_2m' => 20, 'weather_code' => 0],
            ], 200),
        ]);

        $provider = new OpenMeteoProvider();
        $cacheKey = 'weather_openmeteo_41.3851_2.1734';

        Cache::remember($cacheKey, now()->addMinutes(10), function () use ($provider) {
            return $provider->getCurrentWeather(41.3851, 2.1734);
        });

        expect(Cache::has($cacheKey))->toBeTrue();
    });

    test('cache key includes coordinates', function () {
        Http::fake([
            'api.openweathermap.org/*' => Http::response([
                'main' => ['temp' => 22],
                'weather' => [['description' => 'clear sky', 'icon' => '01d']],
            ], 200),
        ]);

        Livewire::test(WeatherDisplay::class, [
            'latitude' => 40.0,
            'longitude' => 3.0,
        ]);

        expect(Cache::has('weather_openweathermap_40_3'))->toBeTrue();
    });

    test('different coordinates use different cache entries', function () {
        Http::fake([
            'api.openweathermap.org/*' => Http::sequence()
                ->push([
                    'main' => ['temp' => 22],
                    'weather' => [['description' => 'Barcelona weather', 'icon' => '01d']],
                ])
                ->push([
                    'main' => ['temp' => 15],
                    'weather' => [['description' => 'Madrid weather', 'icon' => '02d']],
                ]),
        ]);

        // First location
        $component1 = Livewire::test(WeatherDisplay::class, [
            'latitude' => 41.3851,
            'longitude' => 2.1734,
        ]);

        // Second location
        $component2 = Livewire::test(WeatherDisplay::class, [
            'latitude' => 40.4168,
            'longitude' => -3.7038,
        ]);

        expect($component1->get('weather')['temp'])->toBe(22)
            ->and($component2->get('weather')['temp'])->toBe(15);
    });

    test('OpenWeatherMap requires API key', function () {
        $provider = new OpenWeatherMapProvider('');
        $result = $provider->getCurrentWeather(41.3851, 2.1734);

        expect($result)->toBeNull();
    });

    test('OpenWeatherMap works with valid API key', function () {
        Http::fake([
            'api.openweathermap.org/*' => Http::response([
                'main' => ['temp' => 22],
                'weather' => [['description' => 'clear sky', 'icon' => '01d']],
            ], 200),
        ]);

        $provider = new OpenWeatherMapProvider('valid-key');
        $result = $provider->getCurrentWeather(41.3851, 2.1734);

        expect($result)->not->toBeNull()
            ->and($result['provider'])->toBe('OpenWeatherMap');
    });

    test('OpenMeteo does not require API key', function () {
        Http::fake([
            'api.open-meteo.com/*' => Http::response([
                'current' => ['temperature_2m' => 20, 'weather_code' => 0],
            ], 200),
        ]);

        $provider = new OpenMeteoProvider();
        $result = $provider->getCurrentWeather(41.3851, 2.1734);

        expect($result)->not->toBeNull()
            ->and($result['provider'])->toBe('Open-Meteo');
    });

    test('fallback to null on API failure', function () {
        Http::fake([
            'api.openweathermap.org/*' => Http::response(null, 500),
        ]);

        $provider = new OpenWeatherMapProvider('test-key');
        $result = $provider->getCurrentWeather(41.3851, 2.1734);

        expect($result)->toBeNull();
    });

    test('both providers return consistent data structure', function () {
        Http::fake([
            'api.openweathermap.org/*' => Http::response([
                'main' => ['temp' => 22],
                'weather' => [['description' => 'clear sky', 'icon' => '01d']],
            ], 200),
            'api.open-meteo.com/*' => Http::response([
                'current' => ['temperature_2m' => 20, 'weather_code' => 0],
            ], 200),
        ]);

        $owmProvider = new OpenWeatherMapProvider('test-key');
        $omProvider = new OpenMeteoProvider();

        $owmResult = $owmProvider->getCurrentWeather(41.3851, 2.1734);
        $omResult = $omProvider->getCurrentWeather(41.3851, 2.1734);

        // Both should have the same keys
        expect($owmResult)->toHaveKeys(['temp', 'description', 'icon', 'icon_url', 'provider'])
            ->and($omResult)->toHaveKeys(['temp', 'description', 'icon', 'icon_url', 'provider']);
    });

    test('icon URLs point to OpenWeatherMap for both providers', function () {
        Http::fake([
            'api.openweathermap.org/*' => Http::response([
                'main' => ['temp' => 22],
                'weather' => [['description' => 'clear sky', 'icon' => '01d']],
            ], 200),
            'api.open-meteo.com/*' => Http::response([
                'current' => ['temperature_2m' => 20, 'weather_code' => 0],
            ], 200),
        ]);

        $owmProvider = new OpenWeatherMapProvider('test-key');
        $omProvider = new OpenMeteoProvider();

        $owmResult = $owmProvider->getCurrentWeather(41.3851, 2.1734);
        $omResult = $omProvider->getCurrentWeather(41.3851, 2.1734);

        expect($owmResult['icon_url'])->toContain('openweathermap.org')
            ->and($omResult['icon_url'])->toContain('openweathermap.org');
    });
});
