<div class="inline-flex items-center gap-2 text-sm text-gray-600 bg-gray-50 rounded-lg px-3 py-1.5 border border-gray-200">
    @if ($weather['icon_url'])
        <img src="{{ $weather['icon_url'] }}" alt="{{ $weather['description'] }}" class="w-8 h-8 -my-1">
    @endif
    <span class="font-medium">{{ $weather['temp'] }}°C</span>
    <span class="text-gray-500">{{ $weather['description'] }}</span>
    <span class="text-xs text-gray-400">({{ $weather['provider'] }})</span>
</div>

