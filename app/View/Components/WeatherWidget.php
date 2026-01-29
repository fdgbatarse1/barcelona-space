<?php

namespace App\View\Components;

use App\Contracts\WeatherProvider;
use Illuminate\View\Component;
use Illuminate\View\View;

class WeatherWidget extends Component
{
    public ?array $weather;

    public function __construct(
        public float $latitude,
        public float $longitude,
    ) {
        $this->weather = app(WeatherProvider::class)
            ->getCurrentWeather($latitude, $longitude);
    }

    public function shouldRender(): bool
    {
        return $this->weather !== null;
    }

    public function render(): View
    {
        return view('components.weather-widget');
    }
}

