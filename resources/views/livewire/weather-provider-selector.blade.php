<div
    class="flex items-center gap-2 backdrop-blur-md bg-white/60 rounded-lg px-2.5 py-1.5 shadow-lg border border-white/30">
    <label for="provider" class="sr-only">Weather Source</label>
    <select wire:model.live="selectedProvider" id="provider"
        class="text-xs bg-transparent border-0 focus:ring-0 text-gray-700 font-medium py-0 pl-0 pr-6 cursor-pointer">
        <option value="openweathermap">Open Weather Map</option>
        <option value="openmeteo">Open Meteo</option>
    </select>
</div>