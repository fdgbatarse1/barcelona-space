<?php

namespace App\Contracts;

interface WeatherProvider
{
    /**
     * Get current weather for the given coordinates.
     *
     * @return array{temp: int, description: string, icon: string, icon_url: string|null, provider: string}|null
     */
    public function getCurrentWeather(float $lat, float $lon): ?array;
}

