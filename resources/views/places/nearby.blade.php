<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Nearby Places
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="w-full max-w-none mx-auto px-4 sm:px-6 lg:px-8 xl:px-10 2xl:px-12 space-y-6">

            @include('partials.admin.flash-messages')
            @include('partials.admin.validation-summary')

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-5 border-b border-gray-200">
                    <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">
                        <div>
                            <h3 class="text-base font-semibold text-gray-900">
                                Places near {{ $place->placename }}
                            </h3>
                            <p class="text-sm text-gray-500">
                                Showing places within {{ $radiusKm }} km
                                @if($place->latitude && $place->longitude)
                                    · {{ $place->latitude }}, {{ $place->longitude }}
                                @endif
                            </p>
                        </div>

                        <div class="flex items-end justify-end gap-3">
                            <a href="{{ $returnTo }}"
                            class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 text-sm">
                                Back
                            </a>

                            <form method="GET" action="{{ route('places.nearby', $place) }}" class="flex items-end gap-3">
                                <input type="hidden" name="returnto" value="{{ $returnTo }}">

                                <div>
                                    <label for="radius_km" class="block text-sm font-medium text-gray-700 mb-1">
                                        Radius
                                    </label>
                                    <select name="radius_km" id="radius_km" class="rounded-md border-gray-300 shadow-sm text-sm">
                                        @foreach($radiusOptions as $option)
                                            <option value="{{ $option }}" @selected((int) $radiusKm === (int) $option)>
                                                {{ $option }} km
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm">
                                    Apply
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Place</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Latitude</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Longitude</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Distance</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($nearbyPlaces as $nearby)
                                <tr>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $nearby->placename }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-700">{{ $nearby->placetype }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-700">{{ $nearby->latitude }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-700">{{ $nearby->longitude }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-700">
                                        {{ number_format($nearby->distance_km, 1) }} km
                                    </td>
                                    <td class="px-4 py-3 text-sm whitespace-nowrap">
                                        <a href="{{ route('places.edit', $nearby) }}"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="inline-flex items-center px-3 py-1.5 bg-slate-700 text-white rounded hover:bg-slate-600 text-sm">
                                            Open
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-6 text-center text-sm text-gray-500">
                                        No places found within {{ $radiusKm }} km.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>