<?php

namespace App\Services\Weather;

use App\Contracts\WeatherProvider;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenWeatherMapProvider implements WeatherProvider
{
    public function __construct(
        private string $apiKey,
    ) {}

    public function getCurrentWeather(float $lat, float $lon): ?array
    {
        if (empty($this->apiKey)) {
            return null;
        }

        try {
            $response = Http::timeout(5)
                ->retry(2, 100)
                ->get('https://api.openweathermap.org/data/2.5/weather', [
                    'lat' => $lat,
                    'lon' => $lon,
                    'appid' => $this->apiKey,
                    'units' => 'metric',
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $icon = $data['weather'][0]['icon'];

                return [
                    'temp' => (int) round($data['main']['temp']),
                    'description' => ucfirst($data['weather'][0]['description']),
                    'icon' => $icon,
                    'icon_url' => "https://openweathermap.org/img/wn/{$icon}@2x.png",
                    'provider' => 'OpenWeatherMap',
                ];
            }

            Log::warning('OpenWeatherMap API returned non-success', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::warning('OpenWeatherMap API failed', [
                'error' => $e->getMessage(),
                'lat' => $lat,
                'lon' => $lon,
            ]);

            return null;
        }
    }
}

