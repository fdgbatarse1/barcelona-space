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
    </x-slot>
    <div class="max-w-7xl mx-auto py-2 sm:py-4 lg:py-6 px-4 sm:px-6 lg:px-8">
        <div class="space-y-2 sm:space-y-4 lg:space-y-6">
            <div class="flex justify-between">
                <div>
                    <h2 class="font-semibold text-xl text-gray-700 leading-tight">
                        {{ $place->name }}
                    </h2>
                    <p class="text-sm text-gray-500">
                        Created {{ $place->created_at->toDayDateTimeString() }}
                    </p>
                </div>
                <x-weather-widget :latitude="$place->latitude" :longitude="$place->longitude" />
            </div>
            <div class="flex flex-col gap-2 sm:flex-row">
                <div class="sm:w-1/2">
                    @if ($place->image)
                        <div class="aspect-video overflow-hidden rounded-lg bg-gray-100">
                            <img src="{{ Storage::url($place->image) }}" alt="{{ $place->name }}"
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
                <div class="text-gray-700 rich-text-content">
                    {!! $place->description ?: 'No description provided.' !!}
                </div>
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

            <div class="space-y-2 sm:space-y-4 lg:space-y-6">
                @auth
                    <form action="{{ route('comments.store', $place) }}" method="POST"
                        class="space-y-2 sm:space-y-4 lg:space-y-6">
                        @csrf
                        <div>
                            <label for="text" class="text-lg font-semibold text-gray-900">Comments</label>
                            <textarea name="text" id="text" rows="3"
                                class="mt-1 block w-full rounded-md border border-gray-300 focus:border-gray-500 focus:ring-gray-500 sm:text-sm px-3 py-2 bg-gray-50 [field-sizing:content] min-h-[120px]"
                                placeholder="Add a comment..." required></textarea>
                            @error('text')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="flex justify-end">
                            <button type="submit"
                                class="inline-flex items-center rounded-md border border-gray-500 px-3 py-1.5 text-gray-500 hover:bg-gray-50 hover:border-gray-700 cursor-pointer">
                                Post Comment
                            </button>
                        </div>
                    </form>
                @endauth

                <div class="space-y-1 sm:space-y-2 lg:space-y-4">
                    @forelse ($comments as $comment)
                        <div class="bg-gray-50 rounded-lg p-4 border border-gray-300 border-solid">
                            <div class="flex justify-between items-start">
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $comment->user->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $comment->created_at->diffForHumans() }}</p>
                                </div>
                                @if (auth()->id() === $comment->user_id)
                                    <div class="flex space-x-2">
                                        <button
                                            onclick="document.getElementById('edit-comment-{{ $comment->id }}').classList.toggle('hidden')"
                                            class="text-sm text-gray-500 hover:text-gray-700 cursor-pointer">Edit</button>
                                        <form action="{{ route('comments.destroy', $comment) }}" method="POST"
                                            onsubmit="return confirm('Are you sure?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="text-sm text-red-500 hover:text-red-700 cursor-pointer">Delete</button>
                                        </form>
                                    </div>
                                @endif
                            </div>
                            <p class="mt-2 text-gray-700">{{ $comment->text }}</p>

                            @if (auth()->id() === $comment->user_id)
                                <div id="edit-comment-{{ $comment->id }}" class="hidden mt-4">
                                    <form action="{{ route('comments.update', $comment) }}" method="POST" class="space-y-2">
                                        @csrf
                                        @method('PUT')
                                        <textarea name="text" rows="2"
                                            class="mt-1 block w-full rounded-md border border-gray-300 focus:border-gray-500 focus:ring-gray-500 sm:text-sm px-3 py-2 bg-gray-50 [field-sizing:content]"
                                            required>{{ $comment->text }}</textarea>
                                        <div class="flex justify-end">
                                            <button type="submit"
                                                class="inline-flex items-center rounded-md border border-gray-500 px-3 py-1.5 text-gray-500 hover:bg-gray-50 hover:border-gray-700 cursor-pointer">
                                                Update
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            @endif
                        </div>
                    @empty
                        <p class="text-gray-500">No comments</p>
                    @endforelse

                    <div class="mt-6">
                        {{ $comments->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof tinymce !== 'undefined') {
                tinymce.init({
                    selector: 'textarea#text',
                    height: 200,
                    menubar: false,
                    plugins: [
                        'advlist autolink lists link charmap',
                        'searchreplace visualblocks code',
                        'insertdatetime paste code help wordcount'
                    ],
                    toolbar: 'undo redo | formatselect | bold italic | \
                                                    bullist numlist | removeformat',
                    content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px }'
                });
            }
        });
    </script>
@endpush