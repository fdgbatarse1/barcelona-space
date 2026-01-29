<?php

namespace App\Services\Weather;

use App\Contracts\WeatherProvider;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenMeteoProvider implements WeatherProvider
{
    /**
     * WMO Weather interpretation codes mapped to descriptions.
     * @see https://open-meteo.com/en/docs
     */
    private const WMO_CODES = [
        0 => 'Clear sky',
        1 => 'Mainly clear',
        2 => 'Partly cloudy',
        3 => 'Overcast',
        45 => 'Foggy',
        48 => 'Depositing rime fog',
        51 => 'Light drizzle',
        53 => 'Moderate drizzle',
        55 => 'Dense drizzle',
        56 => 'Light freezing drizzle',
        57 => 'Dense freezing drizzle',
        61 => 'Slight rain',
        63 => 'Moderate rain',
        65 => 'Heavy rain',
        66 => 'Light freezing rain',
        67 => 'Heavy freezing rain',
        71 => 'Slight snow',
        73 => 'Moderate snow',
        75 => 'Heavy snow',
        77 => 'Snow grains',
        80 => 'Slight rain showers',
        81 => 'Moderate rain showers',
        82 => 'Violent rain showers',
        85 => 'Slight snow showers',
        86 => 'Heavy snow showers',
        95 => 'Thunderstorm',
        96 => 'Thunderstorm with slight hail',
        99 => 'Thunderstorm with heavy hail',
    ];

    public function getCurrentWeather(float $lat, float $lon): ?array
    {
        try {
            $response = Http::timeout(5)
                ->retry(2, 100)
                ->get('https://api.open-meteo.com/v1/forecast', [
                    'latitude' => $lat,
                    'longitude' => $lon,
                    'current' => 'temperature_2m,weather_code',
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $weatherCode = $data['current']['weather_code'];
                $icon = $this->mapWeatherCodeToIcon($weatherCode);

                return [
                    'temp' => (int) round($data['current']['temperature_2m']),
                    'description' => self::WMO_CODES[$weatherCode] ?? 'Unknown',
                    'icon' => $icon,
                    'icon_url' => "https://openweathermap.org/img/wn/{$icon}@2x.png",
                    'provider' => 'Open-Meteo',
                ];
            }

            Log::warning('Open-Meteo API returned non-success', [
                'status' => $response->status(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::warning('Open-Meteo API failed', [
                'error' => $e->getMessage(),
                'lat' => $lat,
                'lon' => $lon,
            ]);

            return null;
        }
    }

    /**
     * Map WMO weather codes to OpenWeatherMap icon codes for consistent display.
     */
    private function mapWeatherCodeToIcon(int $code): string
    {
        return match (true) {
            $code === 0 => '01d',                    // Clear
            $code <= 3 => '02d',                     // Partly cloudy
            $code <= 48 => '50d',                    // Fog
            $code <= 57 => '09d',                    // Drizzle
            $code <= 67 => '10d',                    // Rain
            $code <= 77 => '13d',                    // Snow
            $code <= 82 => '09d',                    // Rain showers
            $code <= 86 => '13d',                    // Snow showers
            default => '11d',                        // Thunderstorm
        };
    }
}

