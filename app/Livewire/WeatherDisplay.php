<?php

namespace App\Livewire;

use App\Services\Weather\OpenMeteoProvider;
use App\Services\Weather\OpenWeatherMapProvider;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\On;
use Livewire\Component;

class WeatherDisplay extends Component
{
    public float $latitude;
    public float $longitude;
    public string $provider = 'openweathermap';
    public ?array $weather = null;

    public function mount(float $latitude, float $longitude)
    {
        $this->latitude = $latitude;
        $this->longitude = $longitude;
        $this->fetchWeather();
    }

    #[On('weather-provider-changed')]
    public function switchProvider(string $provider)
    {
        $this->provider = $provider;
        $this->fetchWeather();
    }

    public function fetchWeather()
    {
        $cacheKey = "weather_{$this->provider}_{$this->latitude}_{$this->longitude}";

        $this->weather = Cache::remember($cacheKey, now()->addMinutes(10), function () {
            return $this->getProviderInstance()->getCurrentWeather(
                $this->latitude,
                $this->longitude
            );
        });
    }

    protected function getProviderInstance()
    {
        return match ($this->provider) {
            'openmeteo' => new OpenMeteoProvider(),
            default => new OpenWeatherMapProvider(config('services.openweathermap.key', '')),
        };
    }

    public function render()
    {
        return view('livewire.weather-display');
    }
}

