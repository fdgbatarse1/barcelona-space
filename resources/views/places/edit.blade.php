<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl text-gray-700">
            Edit Place
        </h2>
    </x-slot>


    <div class="max-w-7xl mx-auto py-2 sm:py-4 lg:py-6 px-4 sm:px-6 lg:px-8 rounded-lg">
        <div class="rounded-md bg-gray-50 p-4 text-sm text-gray-600 mb-6">
            Last updated {{ $place->updated_at->diffForHumans() }}
        </div>
        @include('places.partials.form', [
            'place' => $place,
            'action' => route('places.update', $place),
            'method' => 'PUT',
            'submitLabel' => 'Save Changes',
        ])
    </div>

</x-app-layout>

