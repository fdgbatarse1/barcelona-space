<?php

use App\Services\Weather\OpenMeteoProvider;
use Illuminate\Support\Facades\Http;

describe('OpenMeteoProvider', function () {
    beforeEach(function () {
        Http::preventStrayRequests();
    });

    test('returns weather data on successful API response', function () {
        Http::fake([
            'api.open-meteo.com/*' => Http::response([
                'current' => [
                    'temperature_2m' => 22.5,
                    'weather_code' => 0,
                ],
            ], 200),
        ]);

        $provider = new OpenMeteoProvider();
        $result = $provider->getCurrentWeather(41.3851, 2.1734);

        expect($result)->not->toBeNull()
            ->and($result['temp'])->toBe(23) // rounded from 22.5
            ->and($result['description'])->toBe('Clear sky')
            ->and($result['icon'])->toBe('01d')
            ->and($result['provider'])->toBe('Open-Meteo')
            ->and($result['icon_url'])->toContain('openweathermap.org');
    });

    test('returns null on API failure', function () {
        Http::fake([
            'api.open-meteo.com/*' => Http::response(null, 500),
        ]);

        $provider = new OpenMeteoProvider();
        $result = $provider->getCurrentWeather(41.3851, 2.1734);

        expect($result)->toBeNull();
    });

    test('returns null on connection exception', function () {
        Http::fake([
            'api.open-meteo.com/*' => function () {
                throw new \Exception('Connection timeout');
            },
        ]);

        $provider = new OpenMeteoProvider();
        $result = $provider->getCurrentWeather(41.3851, 2.1734);

        expect($result)->toBeNull();
    });

    test('maps WMO code 0 to Clear sky', function () {
        Http::fake([
            'api.open-meteo.com/*' => Http::response([
                'current' => [
                    'temperature_2m' => 20,
                    'weather_code' => 0,
                ],
            ], 200),
        ]);

        $provider = new OpenMeteoProvider();
        $result = $provider->getCurrentWeather(41.3851, 2.1734);

        expect($result['description'])->toBe('Clear sky')
            ->and($result['icon'])->toBe('01d');
    });

    test('maps WMO code 3 to Overcast', function () {
        Http::fake([
            'api.open-meteo.com/*' => Http::response([
                'current' => [
                    'temperature_2m' => 18,
                    'weather_code' => 3,
                ],
            ], 200),
        ]);

        $provider = new OpenMeteoProvider();
        $result = $provider->getCurrentWeather(41.3851, 2.1734);

        expect($result['description'])->toBe('Overcast')
            ->and($result['icon'])->toBe('02d');
    });

    test('maps WMO code 45 to Foggy', function () {
        Http::fake([
            'api.open-meteo.com/*' => Http::response([
                'current' => [
                    'temperature_2m' => 10,
                    'weather_code' => 45,
                ],
            ], 200),
        ]);

        $provider = new OpenMeteoProvider();
        $result = $provider->getCurrentWeather(41.3851, 2.1734);

        expect($result['description'])->toBe('Foggy')
            ->and($result['icon'])->toBe('50d');
    });

    test('maps WMO code 63 to Moderate rain', function () {
        Http::fake([
            'api.open-meteo.com/*' => Http::response([
                'current' => [
                    'temperature_2m' => 15,
                    'weather_code' => 63,
                ],
            ], 200),
        ]);

        $provider = new OpenMeteoProvider();
        $result = $provider->getCurrentWeather(41.3851, 2.1734);

        expect($result['description'])->toBe('Moderate rain')
            ->and($result['icon'])->toBe('10d');
    });

    test('maps WMO code 73 to Moderate snow', function () {
        Http::fake([
            'api.open-meteo.com/*' => Http::response([
                'current' => [
                    'temperature_2m' => -2,
                    'weather_code' => 73,
                ],
            ], 200),
        ]);

        $provider = new OpenMeteoProvider();
        $result = $provider->getCurrentWeather(41.3851, 2.1734);

        expect($result['description'])->toBe('Moderate snow')
            ->and($result['icon'])->toBe('13d');
    });

    test('maps WMO code 95 to Thunderstorm', function () {
        Http::fake([
            'api.open-meteo.com/*' => Http::response([
                'current' => [
                    'temperature_2m' => 25,
                    'weather_code' => 95,
                ],
            ], 200),
        ]);

        $provider = new OpenMeteoProvider();
        $result = $provider->getCurrentWeather(41.3851, 2.1734);

        expect($result['description'])->toBe('Thunderstorm')
            ->and($result['icon'])->toBe('11d');
    });

    test('handles unknown weather code', function () {
        Http::fake([
            'api.open-meteo.com/*' => Http::response([
                'current' => [
                    'temperature_2m' => 20,
                    'weather_code' => 999,
                ],
            ], 200),
        ]);

        $provider = new OpenMeteoProvider();
        $result = $provider->getCurrentWeather(41.3851, 2.1734);

        expect($result['description'])->toBe('Unknown')
            ->and($result['icon'])->toBe('11d'); // defaults to thunderstorm icon
    });

    test('rounds temperature to nearest integer', function () {
        Http::fake([
            'api.open-meteo.com/*' => Http::response([
                'current' => [
                    'temperature_2m' => 22.4,
                    'weather_code' => 0,
                ],
            ], 200),
        ]);

        $provider = new OpenMeteoProvider();
        $result = $provider->getCurrentWeather(41.3851, 2.1734);

        expect($result['temp'])->toBe(22);
    });

    test('sends correct query parameters', function () {
        Http::fake([
            'api.open-meteo.com/*' => Http::response([
                'current' => [
                    'temperature_2m' => 20,
                    'weather_code' => 0,
                ],
            ], 200),
        ]);

        $provider = new OpenMeteoProvider();
        $provider->getCurrentWeather(41.3851, 2.1734);

        Http::assertSent(function ($request) {
            $url = $request->url();
            return str_contains($url, 'api.open-meteo.com')
                && str_contains($url, 'latitude')
                && str_contains($url, 'longitude');
        });
    });
});
