<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl text-gray-700">
            Create Place
        </h2>
    </x-slot>

    <div class="max-w-7xl mx-auto py-2 sm:py-4 lg:py-6 px-4 sm:px-6 lg:px-8 rounded-lg">
        @include('places.partials.form', [
            'place' => $place,
            'action' => route('places.store'),
            'method' => 'POST',
            'submitLabel' => 'Create Place',
        ])
    </div>
</x-app-layout>

