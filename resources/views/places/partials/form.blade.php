@props(['place', 'action', 'method' => 'POST', 'submitLabel' => null])

<form method="POST" action="{{ $action }}" class="space-y-6" data-place-form="true" enctype="multipart/form-data">
    @csrf
    @if (in_array(strtoupper($method), ['PUT', 'PATCH', 'DELETE']))
        @method($method)
    @endif

    <div>
        <label for="name" class="block text-sm font-medium text-gray-700">
            Name
        </label>
        <input id="name" name="name" type="text" value="{{ old('name', $place->name) }}" required maxlength="255"
            class="mt-1 block w-full rounded-md border border-gray-300 focus:border-gray-500 focus:ring-gray-500 sm:text-sm px-3 py-2 bg-gray-50" />
        @error('name')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="description" class="block text-sm font-medium text-gray-700">
            Description
        </label>
        <textarea id="description" name="description" rows="5"
            class="mt-1 block w-full rounded-md border border-gray-300 focus:border-gray-500 focus:ring-gray-500 sm:text-sm px-3 py-2 bg-gray-50 [field-sizing:content] min-h-[120px]">{{ old('description', $place->description) }}</textarea>
        @error('description')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="address" class="block text-sm font-medium text-gray-700">
            Address
        </label>
        <input id="address" name="address" type="text" value="{{ old('address', $place->address) }}" maxlength="255"
            data-place-address-input
            class="mt-1 block w-full rounded-md border border-gray-300 focus:border-gray-500 focus:ring-gray-500 sm:text-sm px-3 py-2 bg-gray-50"
            autocomplete="off" placeholder="Start typing to search for a place" />
        @error('address')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    @if (config('services.google.maps_api_key'))
        <div class="space-y-2">
            <label class="block text-sm font-medium text-gray-700">
                Map Preview
            </label>
            <div data-place-map class="h-64 w-full rounded-md border border-gray-300 bg-gray-100 overflow-hidden"></div>
            <p class="text-xs text-gray-500">
                Drag the marker or click anywhere on the map to update the latitude and longitude below.
            </p>
        </div>
    @else
        <div class="rounded-md border border-yellow-200 bg-yellow-50 px-4 py-3 text-sm text-yellow-800">
            Configure <code>GOOGLE_MAPS_API_KEY</code> in your environment to enable the map picker and address
            autocomplete.
        </div>
    @endif

    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
        <div>
            <label for="latitude" class="block text-sm font-medium text-gray-700">
                Latitude
            </label>
            <input id="latitude" name="latitude" type="number" step="any" min="-90" max="90"
                value="{{ old('latitude', $place->latitude) }}" required data-place-latitude-input
                class="mt-1 block w-full rounded-md border border-gray-300 focus:border-gray-500 focus:ring-gray-500 sm:text-sm px-3 py-2 bg-gray-50" />
            @error('latitude')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="longitude" class="block text-sm font-medium text-gray-700">
                Longitude
            </label>
            <input id="longitude" name="longitude" type="number" step="any" min="-180" max="180"
                value="{{ old('longitude', $place->longitude) }}" required data-place-longitude-input
                class="mt-1 block w-full rounded-md border border-gray-300 focus:border-gray-500 focus:ring-gray-500 sm:text-sm px-3 py-2 bg-gray-50" />
            @error('longitude')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div>
        <label for="image" class="block text-sm font-medium text-gray-700">
            Image
        </label>
        <input id="image" name="image" type="file" accept="image/*"
            class="mt-1 block w-full rounded-md border border-gray-300 focus:border-gray-500 focus:ring-gray-500 sm:text-sm px-3 py-2 bg-gray-50 cursor-pointer" />
        @error('image')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="flex justify-end space-x-3">
        <a href="{{ route('places.index') }}"
            class="inline-flex items-center rounded-md border border-gray-500 px-3 py-2 text-sm font-medium text-gray-500 hover:bg-gray-50 hover:border-gray-700">
            Cancel
        </a>
        <button type="submit"
            class="inline-flex items-center rounded-md border border-gray-500 bg-gray-50 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 hover:border-gray-700 cursor-pointer">
            {{ $submitLabel ?? 'Save Place' }}
        </button>
    </div>
</form>

@if (config('services.google.maps_api_key'))
    @push('scripts')
        @once
            <script>
                (function () {
                    const FALLBACK_COORDS = { lat: 41.3874, lng: 2.1686 };

                    function parseCoordinate(value) {
                        const number = parseFloat(value);
                        return Number.isFinite(number) ? number : null;
                    }

                    function toLiteralLatLng(latLng) {
                        if (!latLng) return null;
                        if (typeof latLng.lat === 'function' && typeof latLng.lng === 'function') {
                            return { lat: latLng.lat(), lng: latLng.lng() };
                        }
                        return latLng;
                    }

                    function initForm(form) {
                        if (form.dataset.placeMapInitialized === 'true') {
                            return;
                        }

                        const addressInput = form.querySelector('[data-place-address-input]');
                        const mapElement = form.querySelector('[data-place-map]');
                        const latitudeInput = form.querySelector('[data-place-latitude-input]');
                        const longitudeInput = form.querySelector('[data-place-longitude-input]');

                        if (!addressInput || !mapElement || !latitudeInput || !longitudeInput) {
                            return;
                        }

                        form.dataset.placeMapInitialized = 'true';

                        const parsedLat = parseCoordinate(latitudeInput.value);
                        const parsedLng = parseCoordinate(longitudeInput.value);
                        const initialLat = parsedLat ?? FALLBACK_COORDS.lat;
                        const initialLng = parsedLng ?? FALLBACK_COORDS.lng;
                        const hasExistingCoords = Number.isFinite(parsedLat) && Number.isFinite(parsedLng);

                        const map = new google.maps.Map(mapElement, {
                            center: { lat: initialLat, lng: initialLng },
                            zoom: hasExistingCoords ? 14 : 3,
                            mapTypeControl: false,
                            streetViewControl: false,
                            fullscreenControl: false,
                            gestureHandling: 'greedy'
                        });

                        const marker = new google.maps.Marker({
                            map,
                            position: { lat: initialLat, lng: initialLng },
                            draggable: true
                        });

                        const geocoder = new google.maps.Geocoder();

                        function updateCoordinateInputs(latLngLiteral) {
                            const coords = toLiteralLatLng(latLngLiteral);
                            if (!coords) {
                                return;
                            }
                            latitudeInput.value = coords.lat.toFixed(6);
                            longitudeInput.value = coords.lng.toFixed(6);
                        }

                        function updateAddressField(latLngLiteral) {
                            const coords = toLiteralLatLng(latLngLiteral);
                            if (!coords || !geocoder) {
                                return;
                            }
                            geocoder.geocode({ location: coords }, (results, status) => {
                                if (status === 'OK' && results && results[0]) {
                                    addressInput.value = results[0].formatted_address;
                                }
                            });
                        }

                        function setMarkerPosition(latLngLiteral, { shouldPan = true, shouldGeocode = false } = {}) {
                            const coords = toLiteralLatLng(latLngLiteral);
                            if (!coords) {
                                return;
                            }
                            marker.setPosition(coords);
                            if (shouldPan) {
                                map.panTo(coords);
                            }
                            updateCoordinateInputs(coords);
                            if (shouldGeocode) {
                                updateAddressField(coords);
                            }
                        }

                        const autocomplete = new google.maps.places.Autocomplete(addressInput, {
                            fields: ['formatted_address', 'geometry'],
                            types: ['geocode']
                        });
                        autocomplete.bindTo('bounds', map);

                        autocomplete.addListener('place_changed', () => {
                            const place = autocomplete.getPlace();
                            if (!place.geometry || !place.geometry.location) {
                                return;
                            }

                            if (place.formatted_address) {
                                addressInput.value = place.formatted_address;
                            }

                            if (place.geometry.viewport) {
                                map.fitBounds(place.geometry.viewport);
                            } else {
                                map.setCenter(place.geometry.location);
                                map.setZoom(16);
                            }

                            setMarkerPosition(place.geometry.location, { shouldPan: false });
                        });

                        map.addListener('click', (event) => {
                            setMarkerPosition(event.latLng, { shouldGeocode: true });
                        });

                        marker.addListener('dragend', (event) => {
                            setMarkerPosition(event.latLng, { shouldGeocode: true, shouldPan: false });
                        });

                        function syncMarkerWithManualInput() {
                            const lat = parseCoordinate(latitudeInput.value);
                            const lng = parseCoordinate(longitudeInput.value);
                            if (!Number.isFinite(lat) || !Number.isFinite(lng)) {
                                return;
                            }
                            setMarkerPosition({ lat, lng }, { shouldPan: false });
                        }

                        latitudeInput.addEventListener('change', syncMarkerWithManualInput);
                        longitudeInput.addEventListener('change', syncMarkerWithManualInput);
                    }

                    window.initPlacesFormMaps = function initPlacesFormMaps() {
                        document.querySelectorAll('[data-place-form]').forEach(initForm);
                    };
                })();
            </script>
            <script
                src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.maps_api_key') }}&libraries=places&callback=initPlacesFormMaps"
                async defer></script>
        @endonce
    @endpush
@endif