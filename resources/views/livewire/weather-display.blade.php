<div
    class="inline-flex items-center gap-2 text-sm backdrop-blur-md bg-white/60 rounded-lg px-3 py-1.5 shadow-lg border border-white/30">
    <div wire:loading class="flex items-center gap-2">
        <svg class="animate-spin h-4 w-4 text-gray-700" xmlns="http://www.w3.org/2000/svg" fill="none"
            viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor"
                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
            </path>
        </svg>
        <span class="text-gray-700">Loading...</span>
    </div>

    <div wire:loading.remove>
        @if ($weather)
            @if ($weather['icon_url'])
                <img src="{{ $weather['icon_url'] }}" alt="{{ $weather['description'] }}" class="w-8 h-8 -my-1 drop-shadow">
            @endif
            <span class="font-semibold text-gray-800">{{ $weather['temp'] }}°C</span>
            <span class="text-gray-700">{{ $weather['description'] }}</span>
        @else
            <span class="text-gray-600">Weather unavailable</span>
        @endif
    </div>
</div>