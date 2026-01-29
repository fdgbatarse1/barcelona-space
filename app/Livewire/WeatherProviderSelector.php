<?php

namespace App\Livewire;

use Livewire\Component;

class WeatherProviderSelector extends Component
{
    public string $selectedProvider = 'openweathermap';

    public function updatedSelectedProvider($value)
    {
        $this->dispatch('weather-provider-changed', provider: $value);
    }

    public function render()
    {
        return view('livewire.weather-provider-selector');
    }
}

