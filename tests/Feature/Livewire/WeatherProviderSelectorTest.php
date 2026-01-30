<?php

use App\Livewire\WeatherProviderSelector;
use Livewire\Livewire;

describe('WeatherProviderSelector Component', function () {
    test('component renders', function () {
        Livewire::test(WeatherProviderSelector::class)
            ->assertStatus(200);
    });

    test('defaults to openweathermap provider', function () {
        $component = Livewire::test(WeatherProviderSelector::class);

        expect($component->get('selectedProvider'))->toBe('openweathermap');
    });

    test('dispatches event when provider changes', function () {
        Livewire::test(WeatherProviderSelector::class)
            ->set('selectedProvider', 'openmeteo')
            ->assertDispatched('weather-provider-changed', provider: 'openmeteo');
    });

    test('dispatches event with openweathermap provider', function () {
        Livewire::test(WeatherProviderSelector::class)
            ->set('selectedProvider', 'openweathermap')
            ->assertDispatched('weather-provider-changed', provider: 'openweathermap');
    });

    test('can switch between providers multiple times', function () {
        $component = Livewire::test(WeatherProviderSelector::class);

        $component->set('selectedProvider', 'openmeteo')
            ->assertDispatched('weather-provider-changed', provider: 'openmeteo');

        $component->set('selectedProvider', 'openweathermap')
            ->assertDispatched('weather-provider-changed', provider: 'openweathermap');
    });

    test('maintains selected provider state', function () {
        $component = Livewire::test(WeatherProviderSelector::class)
            ->set('selectedProvider', 'openmeteo');

        expect($component->get('selectedProvider'))->toBe('openmeteo');
    });
});
