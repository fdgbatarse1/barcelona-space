<?php

use App\Livewire\WeatherDisplay;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

describe('WeatherDisplay Component', function () {
    beforeEach(function () {
        Http::preventStrayRequests();
        Cache::flush();
    });

    test('component renders', function () {
        Http::fake([
            'api.openweathermap.org/*' => Http::response([
                'main' => ['temp' => 22],
                'weather' => [['description' => 'clear sky', 'icon' => '01d']],
            ], 200),
        ]);

        Livewire::test(WeatherDisplay::class, [
            'latitude' => 41.3851,
            'longitude' => 2.1734,
        ])->assertStatus(200);
    });

    test('component receives coordinates', function () {
        Http::fake([
            'api.openweathermap.org/*' => Http::response([
                'main' => ['temp' => 22],
                'weather' => [['description' => 'clear sky', 'icon' => '01d']],
            ], 200),
        ]);

        $component = Livewire::test(WeatherDisplay::class, [
            'latitude' => 41.3851,
            'longitude' => 2.1734,
        ]);

        expect($component->get('latitude'))->toBe(41.3851)
            ->and($component->get('longitude'))->toBe(2.1734);
    });

    test('defaults to openweathermap provider', function () {
        Http::fake([
            'api.openweathermap.org/*' => Http::response([
                'main' => ['temp' => 22],
                'weather' => [['description' => 'clear sky', 'icon' => '01d']],
            ], 200),
        ]);

        $component = Livewire::test(WeatherDisplay::class, [
            'latitude' => 41.3851,
            'longitude' => 2.1734,
        ]);

        expect($component->get('provider'))->toBe('openweathermap');
    });

    test('fetches weather on mount', function () {
        Http::fake([
            'api.openweathermap.org/*' => Http::response([
                'main' => ['temp' => 25],
                'weather' => [['description' => 'sunny', 'icon' => '01d']],
            ], 200),
        ]);

        $component = Livewire::test(WeatherDisplay::class, [
            'latitude' => 41.3851,
            'longitude' => 2.1734,
        ]);

        expect($component->get('weather'))->not->toBeNull()
            ->and($component->get('weather')['temp'])->toBe(25);
    });

    test('can switch to openmeteo provider', function () {
        Http::fake([
            'api.openweathermap.org/*' => Http::response([
                'main' => ['temp' => 22],
                'weather' => [['description' => 'clear sky', 'icon' => '01d']],
            ], 200),
            'api.open-meteo.com/*' => Http::response([
                'current' => ['temperature_2m' => 20, 'weather_code' => 0],
            ], 200),
        ]);

        $component = Livewire::test(WeatherDisplay::class, [
            'latitude' => 41.3851,
            'longitude' => 2.1734,
        ]);

        $component->dispatch('weather-provider-changed', provider: 'openmeteo');

        expect($component->get('provider'))->toBe('openmeteo')
            ->and($component->get('weather')['provider'])->toBe('Open-Meteo');
    });

    test('caches weather data', function () {
        Http::fake([
            'api.openweathermap.org/*' => Http::response([
                'main' => ['temp' => 22],
                'weather' => [['description' => 'clear sky', 'icon' => '01d']],
            ], 200),
        ]);

        // First request - should call API
        Livewire::test(WeatherDisplay::class, [
            'latitude' => 41.3851,
            'longitude' => 2.1734,
        ]);

        Http::assertSentCount(1);

        // Second request with same coords - should use cache
        Livewire::test(WeatherDisplay::class, [
            'latitude' => 41.3851,
            'longitude' => 2.1734,
        ]);

        Http::assertSentCount(1); // Still only 1 because cached
    });

    test('uses different cache keys for different providers', function () {
        Http::fake([
            'api.openweathermap.org/*' => Http::response([
                'main' => ['temp' => 22],
                'weather' => [['description' => 'from OWM', 'icon' => '01d']],
            ], 200),
            'api.open-meteo.com/*' => Http::response([
                'current' => ['temperature_2m' => 20, 'weather_code' => 0],
            ], 200),
        ]);

        $component = Livewire::test(WeatherDisplay::class, [
            'latitude' => 41.3851,
            'longitude' => 2.1734,
        ]);

        // First provider call
        expect($component->get('weather')['provider'])->toBe('OpenWeatherMap');

        // Switch provider
        $component->dispatch('weather-provider-changed', provider: 'openmeteo');

        // Should fetch from new provider (different cache key)
        expect($component->get('weather')['provider'])->toBe('Open-Meteo');
    });

    test('handles null weather gracefully', function () {
        Http::fake([
            'api.openweathermap.org/*' => Http::response(null, 500),
        ]);

        $component = Livewire::test(WeatherDisplay::class, [
            'latitude' => 41.3851,
            'longitude' => 2.1734,
        ]);

        // Component should still render without errors
        $component->assertStatus(200);
        expect($component->get('weather'))->toBeNull();
    });

    test('uses correct cache key format', function () {
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

        expect(Cache::has('weather_openweathermap_41.3851_2.1734'))->toBeTrue();
    });
});
