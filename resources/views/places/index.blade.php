<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap gap-2 items-center justify-between">
            <span class="text-2xl text-gray-700">Barcelona Gems</span>
            <a href="{{ route('places.create') }}" class="py-2 px-4 bg-gray-500 text-white rounded-md">Add Place</a>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto py-2 sm:py-4 lg:py-6 px-4 sm:px-6 lg:px-8">
        @forelse ($places as $place)
            <div
                class="flex flex-col gap-2 border-b border-gray-500 py-4 last:pb-0 first:pt-0 last:border-0 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-lg text-gray-700">{{ $place->name }}</h3>
                    <p class="text-base text-gray-600 line-clamp-2">
                        {{ strip_tags($place->description) ?? 'No description provided' }}
                    </p>
                    <p class="text-sm text-gray-500">
                        Updated {{ $place->updated_at->diffForHumans() }}
                    </p>
                </div>

                <div class="flex flex-wrap sm:flex-nowrap gap-2">
                    <a href="{{ route('places.show', $place) }}"
                        class="inline-flex items-center rounded-md border border-gray-500 px-3 py-1.5 text-gray-500 hover:bg-gray-50 hover:border-gray-700">
                        View
                    </a>
                    @if (auth()->id() === $place->user_id)
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
        @empty
            <div class="py-12 text-center text-gray-500">
                <p>You have not added any places yet.</p>
                <p class="mt-2">
                    <a href="{{ route('places.create') }}" class="text-gray-600 hover:text-gray-500">
                        Create your first place
                    </a>
                </p>
            </div>
        @endforelse

        <div class="mt-6">
            {{ $places->links() }}
        </div>
    </div>
</x-app-layout>