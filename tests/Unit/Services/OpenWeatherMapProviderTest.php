<?php

use App\Services\Weather\OpenWeatherMapProvider;
use Illuminate\Support\Facades\Http;

describe('OpenWeatherMapProvider', function () {
    beforeEach(function () {
        Http::preventStrayRequests();
    });

    test('returns weather data on successful API response', function () {
        Http::fake([
            'api.openweathermap.org/*' => Http::response([
                'main' => [
                    'temp' => 22.5,
                ],
                'weather' => [
                    [
                        'description' => 'clear sky',
                        'icon' => '01d',
                    ],
                ],
            ], 200),
        ]);

        $provider = new OpenWeatherMapProvider('test-api-key');
        $result = $provider->getCurrentWeather(41.3851, 2.1734);

        expect($result)->not->toBeNull()
            ->and($result['temp'])->toBe(23) // rounded from 22.5
            ->and($result['description'])->toBe('Clear sky') // ucfirst applied
            ->and($result['icon'])->toBe('01d')
            ->and($result['provider'])->toBe('OpenWeatherMap')
            ->and($result['icon_url'])->toBe('https://openweathermap.org/img/wn/01d@2x.png');
    });

    test('returns null when API key is empty', function () {
        $provider = new OpenWeatherMapProvider('');
        $result = $provider->getCurrentWeather(41.3851, 2.1734);

        expect($result)->toBeNull();
    });

    test('returns null on API failure', function () {
        Http::fake([
            'api.openweathermap.org/*' => Http::response(['message' => 'Invalid API key'], 401),
        ]);

        $provider = new OpenWeatherMapProvider('invalid-key');
        $result = $provider->getCurrentWeather(41.3851, 2.1734);

        expect($result)->toBeNull();
    });

    test('returns null on connection exception', function () {
        Http::fake([
            'api.openweathermap.org/*' => function () {
                throw new \Exception('Connection timeout');
            },
        ]);

        $provider = new OpenWeatherMapProvider('test-api-key');
        $result = $provider->getCurrentWeather(41.3851, 2.1734);

        expect($result)->toBeNull();
    });

    test('capitalizes first letter of description', function () {
        Http::fake([
            'api.openweathermap.org/*' => Http::response([
                'main' => ['temp' => 20],
                'weather' => [
                    ['description' => 'broken clouds', 'icon' => '04d'],
                ],
            ], 200),
        ]);

        $provider = new OpenWeatherMapProvider('test-api-key');
        $result = $provider->getCurrentWeather(41.3851, 2.1734);

        expect($result['description'])->toBe('Broken clouds');
    });

    test('rounds temperature to nearest integer', function () {
        Http::fake([
            'api.openweathermap.org/*' => Http::response([
                'main' => ['temp' => 22.4],
                'weather' => [
                    ['description' => 'clear sky', 'icon' => '01d'],
                ],
            ], 200),
        ]);

        $provider = new OpenWeatherMapProvider('test-api-key');
        $result = $provider->getCurrentWeather(41.3851, 2.1734);

        expect($result['temp'])->toBe(22);
    });

    test('sends correct query parameters with API key', function () {
        Http::fake([
            'api.openweathermap.org/*' => Http::response([
                'main' => ['temp' => 20],
                'weather' => [
                    ['description' => 'clear sky', 'icon' => '01d'],
                ],
            ], 200),
        ]);

        $provider = new OpenWeatherMapProvider('my-secret-key');
        $provider->getCurrentWeather(41.3851, 2.1734);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'lat=41.3851')
                && str_contains($request->url(), 'lon=2.1734')
                && str_contains($request->url(), 'appid=my-secret-key')
                && str_contains($request->url(), 'units=metric');
        });
    });

    test('handles negative temperatures', function () {
        Http::fake([
            'api.openweathermap.org/*' => Http::response([
                'main' => ['temp' => -5.7],
                'weather' => [
                    ['description' => 'snow', 'icon' => '13d'],
                ],
            ], 200),
        ]);

        $provider = new OpenWeatherMapProvider('test-api-key');
        $result = $provider->getCurrentWeather(41.3851, 2.1734);

        expect($result['temp'])->toBe(-6);
    });

    test('handles night icons', function () {
        Http::fake([
            'api.openweathermap.org/*' => Http::response([
                'main' => ['temp' => 20],
                'weather' => [
                    ['description' => 'clear sky', 'icon' => '01n'],
                ],
            ], 200),
        ]);

        $provider = new OpenWeatherMapProvider('test-api-key');
        $result = $provider->getCurrentWeather(41.3851, 2.1734);

        expect($result['icon'])->toBe('01n')
            ->and($result['icon_url'])->toBe('https://openweathermap.org/img/wn/01n@2x.png');
    });

    test('handles 404 response', function () {
        Http::fake([
            'api.openweathermap.org/*' => Http::response(['message' => 'Not found'], 404),
        ]);

        $provider = new OpenWeatherMapProvider('test-api-key');
        $result = $provider->getCurrentWeather(999, 999);

        expect($result)->toBeNull();
    });

    test('handles rate limiting 429 response', function () {
        Http::fake([
            'api.openweathermap.org/*' => Http::response(['message' => 'Rate limit exceeded'], 429),
        ]);

        $provider = new OpenWeatherMapProvider('test-api-key');
        $result = $provider->getCurrentWeather(41.3851, 2.1734);

        expect($result)->toBeNull();
    });
});
