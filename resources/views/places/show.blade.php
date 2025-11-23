<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <a href="{{ route('places.index') }}"
                    class="inline-flex items-center rounded-md border border-gray-500 px-3 py-1.5 text-gray-500 hover:bg-gray-50 hover:border-gray-700">
                    Back to list
                </a>
            </div>

            <div class="flex flex-wrap sm:flex-nowrap gap-2">
                @if ($place->user_id === auth()->user()->id)
                    <a href="{{ route('places.edit', $place) }}"
                        class="inline-flex items-center rounded-md border border-gray-500 px-3 py-1.5 text-gray-500 hover:bg-gray-50 hover:border-gray-700">
                        Edit
                    </a>
                    <form action="{{ route('places.destroy', $place) }}" method="POST"
                        onsubmit="return confirm('Are you sure you want to delete this place?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="inline-flex items-center rounded-md border border-gray-500 px-3 py-1.5 text-gray-500 hover:bg-gray-50 hover:border-gray-700 cursor-pointer">
                            Delete
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </x-slot>
    <div class="max-w-7xl mx-auto py-2 sm:py-4 lg:py-6 px-4 sm:px-6 lg:px-8">
        <div class="space-y-2 sm:space-y-4 lg:space-y-6">
            <div>
                <h2 class="font-semibold text-xl text-gray-700 leading-tight">
                    {{ $place->name }}
                </h2>
                <p class="text-sm text-gray-500">
                    Created {{ $place->created_at->toDayDateTimeString() }}
                </p>
            </div>
            <div class="flex flex-col gap-2 sm:flex-row">
                <div class="sm:w-1/2">
                    @if ($place->image)
                        <div class="aspect-video overflow-hidden rounded-lg bg-gray-100">
                            <img src="{{ asset('storage/' . $place->image) }}" alt="{{ $place->name }}"
                                class="h-full w-full object-cover" />
                        </div>
                    @else
                        <div
                            class="aspect-video overflow-hidden rounded-lg bg-gray-100 border border-gray-300 flex items-center justify-center">
                            <div class="text-center p-4">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <p class="mt-2 text-sm text-gray-500">No image available</p>
                            </div>
                        </div>
                    @endif
                </div>
                <div class="flex flex-col space-y-2 sm:w-1/2">
                    @if (config('services.google.maps_api_key'))
                        <div class="w-full aspect-video rounded-lg overflow-hidden border border-gray-300">
                            <iframe width="100%" height="100%" style="border:0" loading="lazy" allowfullscreen
                                referrerpolicy="no-referrer-when-downgrade"
                                src="https://www.google.com/maps/embed/v1/place?key={{ config('services.google.maps_api_key') }}&q={{ $place->latitude }},{{ $place->longitude }}&zoom=15">
                            </iframe>
                        </div>
                    @else
                        <div
                            class="w-full aspect-video rounded-lg overflow-hidden border border-gray-300 bg-gray-100 flex items-center justify-center">
                            <div class="text-center p-4">
                                <p class="text-gray-600 mb-2">Map unavailable. API key not configured.</p>
                                <a href="https://www.google.com/maps?q={{ $place->latitude }},{{ $place->longitude }}"
                                    target="_blank" rel="noopener noreferrer"
                                    class="inline-flex items-center rounded-md border border-gray-500 px-3 py-1.5 text-gray-500 hover:bg-gray-50 hover:border-gray-700">
                                    View on Google Maps
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
            <div class="flex flex-col space-y-2">
                <h3 class="text-lg font-semibold text-gray-900">Description</h3>
                <p class="text-gray-700">
                    {{ $place->description ?: 'No description provided.' }}
                </p>
            </div>
            <div class="flex flex-col space-y-2">
                <h3 class="text-lg font-semibold text-gray-900">Address</h3>
                <p class="text-gray-700">
                    {{ $place->address ?: 'No description provided.' }}
                </p>
            </div>
            <p class="border-t border-gray-500 pt-4 text-gray-500">
                Published by {{ $place->user->name ?? 'Unknown' }}
            </p>
        </div>
    </div>
</x-app-layout>