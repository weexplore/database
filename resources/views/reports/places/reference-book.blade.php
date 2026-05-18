{{-- resources/views/reports/places/reference-book.blade.php --}}
<x-app-layout>
    @php
        $title = 'Reference Book Report';
    @endphp
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin=""/>

    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ $title }}
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    {{ $places->count() }} place{{ $places->count() === 1 ? '' : 's' }} in this filtered report
                </p>
            </div>

            <div class="flex items-center gap-2">
                <button type="button"
                    onclick="window.print()"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">
                    Print / Save PDF
                </button>
                <a href="{{ $returnTo }}"
                   class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 text-sm">
                    Back
                </a>


            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="w-full max-w-none mx-auto px-4 sm:px-6 lg:px-8 xl:px-10 2xl:px-12 space-y-8">

            @if ($places->isEmpty())
                <div class="bg-white shadow-sm sm:rounded-lg px-6 py-8">
                    <p class="text-sm text-gray-500">
                        No places matched the current filter.
                    </p>
                </div>
            @else
                @foreach ($places as $place)
                    @php
                        $mapLat = $place->latitude;
                        $mapLng = $place->longitude;

                        $destinationItems = $place->destinations
                            ->flatMap(fn ($destination) => $destination->items)
                            ->values();
                    @endphp

                    <div class="space-y-6 page-break-inside-avoid">
                        {{-- Place summary --}}
                        <div class="bg-white shadow-sm sm:rounded-lg">
                            <div class="px-6 py-5 border-b border-gray-200 flex items-start justify-between gap-4">
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">
                                        {{ $place->placename ?: 'Place #' . $place->id }}
                                    </h3>
                                    <p class="mt-1 text-xs text-gray-500">
                                        Core location details, map, and reusable reference notes.
                                    </p>
                                </div>

                                <a href="{{ route('places.edit', ['place' => $place, 'return_to' => url()->full()]) }}"
                                   class="inline-flex items-center px-3 py-1.5 bg-gray-100 text-gray-800 rounded hover:bg-gray-200 text-xs">
                                    Edit Place
                                </a>
                            </div>

                            <div class="px-6 py-5 space-y-6">
                                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 text-sm">
                                    <div>
                                        <div class="text-xs uppercase tracking-wide text-gray-500">Place name</div>
                                        <div class="mt-1 font-medium text-gray-900">{{ $place->placename ?: '—' }}</div>
                                    </div>

                                    <div>
                                        <div class="text-xs uppercase tracking-wide text-gray-500">Type</div>
                                        <div class="mt-1 text-gray-900">{{ $place->placetype ?: '—' }}</div>
                                    </div>

                                    <div>
                                        <div class="text-xs uppercase tracking-wide text-gray-500">Status</div>
                                        <div class="mt-1 text-gray-900">{{ $place->isactive ? 'Active' : 'Inactive' }}</div>
                                    </div>

                                    <div>
                                        <div class="text-xs uppercase tracking-wide text-gray-500">Locality</div>
                                        <div class="mt-1 text-gray-900">{{ $place->locality ?: '—' }}</div>
                                    </div>

                                    <div>
                                        <div class="text-xs uppercase tracking-wide text-gray-500">Region</div>
                                        <div class="mt-1 text-gray-900">{{ $place->region?->regionname ?: '—' }}</div>
                                    </div>

                                    <div>
                                        <div class="text-xs uppercase tracking-wide text-gray-500">State</div>
                                        <div class="mt-1 text-gray-900">{{ $place->state?->statename ?: '—' }}</div>
                                    </div>

                                    <div>
                                        <div class="text-xs uppercase tracking-wide text-gray-500">Country</div>
                                        <div class="mt-1 text-gray-900">{{ $place->country?->countryname ?: '—' }}</div>
                                    </div>

                                    <div>
                                        <div class="text-xs uppercase tracking-wide text-gray-500">Postcode</div>
                                        <div class="mt-1 text-gray-900">{{ $place->postcode ?: '—' }}</div>
                                    </div>

                                    <div>
                                        <div class="text-xs uppercase tracking-wide text-gray-500">Coordinates</div>
                                        <div class="mt-1 text-gray-900">
                                            {{ $place->latitude !== null ? $place->latitude : '—' }},
                                            {{ $place->longitude !== null ? $place->longitude : '—' }}
                                        </div>
                                    </div>
                                </div>

                                @if ($mapLat !== null && $mapLng !== null)
                                    <div class="reference-place-map-wrap">
                                        <div id="reference-place-map-{{ $place->id }}"
                                             class="reference-place-map rounded-lg border border-gray-300"
                                             data-lat="{{ $mapLat }}"
                                             data-lng="{{ $mapLng }}"
                                             data-name="{{ $place->placename }}"></div>
                                    </div>
                                @endif

                                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 text-sm">
                                    <div>
                                        <div class="text-xs uppercase tracking-wide text-gray-500">Access notes</div>
                                        <div class="mt-1 text-gray-800 whitespace-pre-line">{{ $place->accessnotes ?: '—' }}</div>
                                    </div>

                                    <div>
                                        <div class="text-xs uppercase tracking-wide text-gray-500">General notes</div>
                                        <div class="mt-1 text-gray-800 whitespace-pre-line">{{ $place->generalnotes ?: '—' }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Linked destinations --}}
                        <div class="bg-white shadow-sm sm:rounded-lg">
                            <div class="px-6 py-4 border-b border-gray-200">
                                <h3 class="text-sm font-semibold text-gray-900">
                                    Linked destinations
                                </h3>
                                <p class="mt-1 text-xs text-gray-500">
                                    {{ $place->destinations->count() }} linked destination{{ $place->destinations->count() === 1 ? '' : 's' }}
                                </p>
                            </div>

                            <div class="px-6 py-4">
                                @if ($place->destinations->isEmpty())
                                    <p class="text-sm text-gray-500">
                                        No destinations are linked to this place.
                                    </p>
                                @else
                                    <div class="space-y-4">
                                        @foreach ($place->destinations as $destination)
                                            <div class="border border-gray-200 rounded-lg overflow-hidden">
                                                <div class="px-4 py-3 bg-gray-50 border-b border-gray-200 flex items-start justify-between gap-4">
                                                    <div>
                                                        <div class="text-sm font-semibold text-gray-900">
                                                            {{ $destination->destinationname }}
                                                        </div>
                                                        <div class="mt-1 text-xs text-gray-500">
                                                            Type: {{ $destination->destinationtype ?: '—' }}
                                                            @if($destination->bestseason)
                                                                • Best season: {{ $destination->bestseason }}
                                                            @endif
                                                            @if(!is_null($destination->revisitinterestlevel))
                                                                • Revisit interest: {{ $destination->revisitinterestlevel }}/10
                                                            @endif
                                                        </div>
                                                    </div>

                                                    <a href="{{ route('destinations.edit', ['destination' => $destination, 'return_to' => url()->full()]) }}"
                                                       class="inline-flex items-center px-3 py-1.5 bg-gray-100 text-gray-800 rounded hover:bg-gray-200 text-xs">
                                                        Edit Destination
                                                    </a>
                                                </div>

                                                <div class="p-4 grid grid-cols-1 lg:grid-cols-2 gap-4 text-sm">
                                                    <div>
                                                        <div class="text-xs uppercase tracking-wide text-gray-500">Overview</div>
                                                        <div class="mt-1 text-gray-800 whitespace-pre-line">{{ $destination->overview ?: '—' }}</div>
                                                    </div>

                                                    <div>
                                                        <div class="text-xs uppercase tracking-wide text-gray-500">Travel notes</div>
                                                        <div class="mt-1 text-gray-800 whitespace-pre-line">{{ $destination->travelnotes ?: '—' }}</div>
                                                    </div>

                                                    <div>
                                                        <div class="text-xs uppercase tracking-wide text-gray-500">Suitability</div>
                                                        <div class="mt-1 text-gray-800 whitespace-pre-line">{{ $destination->suitability ?: '—' }}</div>
                                                    </div>

                                                    <div>
                                                        <div class="text-xs uppercase tracking-wide text-gray-500">Access notes</div>
                                                        <div class="mt-1 text-gray-800 whitespace-pre-line">{{ $destination->accessnotes ?: '—' }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Destination items --}}
<div class="bg-white shadow-sm sm:rounded-lg">
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-sm font-semibold text-gray-900">Destination items</h3>
        <p class="mt-1 text-xs text-gray-500">
            {{ $destinationItems->count() }} item{{ $destinationItems->count() === 1 ? '' : 's' }} across linked destinations
        </p>
    </div>

    <div class="px-6 py-4">
        @if ($destinationItems->isEmpty())
            <p class="text-sm text-gray-500">
                No destination items are linked through this place’s destinations.
            </p>
        @else
            <div class="space-y-4">
                @foreach ($place->destinations as $destination)
                    @if ($destination->items->isNotEmpty())
                        <div class="border border-gray-200 rounded-lg overflow-hidden">
                            <div class="px-4 py-3 bg-gray-50 border-b border-gray-200">
                                <div class="text-sm font-semibold text-gray-900">
                                    {{ $destination->destinationname }}
                                </div>
                                <div class="mt-1 text-xs text-gray-500">
                                    {{ $destination->items->count() }} linked item{{ $destination->items->count() === 1 ? '' : 's' }}
                                </div>
                            </div>

                            <div class="p-4 space-y-4">
                                @foreach ($destination->items as $item)
                                    @php
                                        $itemTypeLabel = \App\Models\DestinationItem::itemTypeOptions()[$item->itemtype] ?? ($item->itemtype ?: '—');

                                        $addressParts = array_filter([
                                            $item->addressline1,
                                            $item->addressline2,
                                            $item->addressline3,
                                        ], fn ($value) => filled($value));

                                        $hasAddress = !empty($addressParts) || filled($item->postcode);
                                        $hasCoords = !is_null($item->latitude) && !is_null($item->longitude);
                                    @endphp

                                    <div class="border border-gray-200 rounded-lg overflow-hidden">
                                        <div class="px-4 py-3 bg-white border-b border-gray-200 flex items-start justify-between gap-4">
                                            <div>
                                                <div class="text-sm font-semibold text-gray-900">
                                                    {{ $item->itemname ?: 'Destination item' }}
                                                </div>
                                                <div class="mt-1 text-xs text-gray-500">
                                                    {{ $itemTypeLabel }}
                                                    @if (!is_null($item->isactive))
                                                        • {{ $item->isactive ? 'Active' : 'Inactive' }}
                                                    @endif
                                                    @if ($item->bookingrequired)
                                                        • Booking required
                                                    @endif
                                                    @if (!is_null($item->recommendedstayminutes))
                                                        • Recommended stay {{ $item->recommendedstayminutes }} mins
                                                    @endif
                                                </div>
                                            </div>

                                            <a href="{{ route('destination-items.edit', [
                                                    'destinationItem' => $item,
                                                    'return_to' => url()->full(),
                                                ]) }}"
                                               class="inline-flex items-center px-3 py-1.5 bg-gray-100 text-gray-800 rounded hover:bg-gray-200 text-xs">
                                                Edit Item
                                            </a>
                                        </div>

                                        <div class="p-4 space-y-4 text-sm">
                                            <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                                                <div class="space-y-4">
                                                    <div>
                                                        <div class="text-xs uppercase tracking-wide text-gray-500">Short description</div>
                                                        <div class="mt-1 text-gray-800 whitespace-pre-line">{{ $item->shortdescription ?: '—' }}</div>
                                                    </div>

                                                    <div>
                                                        <div class="text-xs uppercase tracking-wide text-gray-500">Notes</div>
                                                        <div class="mt-1 text-gray-800 whitespace-pre-line">{{ $item->notes ?: '—' }}</div>
                                                    </div>

                                                    <div>
                                                        <div class="text-xs uppercase tracking-wide text-gray-500">Caravan access notes</div>
                                                        <div class="mt-1 text-gray-800 whitespace-pre-line">{{ $item->caravanaccessnotes ?: '—' }}</div>
                                                    </div>
                                                </div>

                                                <div class="space-y-4">
                                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                        <div>
                                                            <div class="text-xs uppercase tracking-wide text-gray-500">Estimated cost per person</div>
                                                            <div class="mt-1 text-gray-900">
                                                                {{ !is_null($item->estimatedcostperperson) ? '$' . number_format((float) $item->estimatedcostperperson, 2) : '—' }}
                                                            </div>
                                                        </div>

                                                        <div>
                                                            <div class="text-xs uppercase tracking-wide text-gray-500">Estimated total cost</div>
                                                            <div class="mt-1 text-gray-900">
                                                                {{ !is_null($item->estimatedtotalcost) ? '$' . number_format((float) $item->estimatedtotalcost, 2) : '—' }}
                                                            </div>
                                                        </div>

                                                        <div>
                                                            <div class="text-xs uppercase tracking-wide text-gray-500">Booking required</div>
                                                            <div class="mt-1 text-gray-900">{{ $item->bookingrequired ? 'Yes' : 'No' }}</div>
                                                        </div>

                                                        <div>
                                                            <div class="text-xs uppercase tracking-wide text-gray-500">Sort order</div>
                                                            <div class="mt-1 text-gray-900">{{ !is_null($item->sortorder) ? $item->sortorder : '—' }}</div>
                                                        </div>

                                                        <div>
                                                            <div class="text-xs uppercase tracking-wide text-gray-500">Linked place</div>
                                                            <div class="mt-1 text-gray-900">
                                                                {{ $item->place?->placename ?: '—' }}
                                                            </div>
                                                        </div>

                                                        <div>
                                                            <div class="text-xs uppercase tracking-wide text-gray-500">Coordinates</div>
                                                            <div class="mt-1 text-gray-900">
                                                                {{ $hasCoords ? $item->latitude . ', ' . $item->longitude : '—' }}
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div>
                                                        <div class="text-xs uppercase tracking-wide text-gray-500">Address</div>
                                                        <div class="mt-1 text-gray-800 whitespace-pre-line">
                                                            @if ($hasAddress)
                                                                {{ implode("\n", $addressParts) }}@if(filled($item->postcode))
{{ !empty($addressParts) ? "\n" : '' }}{{ $item->postcode }}
                                                                @endif
                                                            @else
                                                                —
                                                            @endif
                                                        </div>
                                                    </div>

                                                    <div class="grid grid-cols-1 gap-4">
                                                        <div>
                                                            <div class="text-xs uppercase tracking-wide text-gray-500">Telephone</div>
                                                            <div class="mt-1 text-gray-900">
                                                                {{ $item->telephone ?: '—' }}
                                                            </div>
                                                        </div>

                                                        <div>
                                                            <div class="text-xs uppercase tracking-wide text-gray-500">Website</div>
                                                            <div class="mt-1">
                                                                @if ($item->website)
                                                                    <a href="{{ $item->website }}"
                                                                       target="_blank"
                                                                       rel="noopener noreferrer"
                                                                       class="text-blue-600 hover:text-blue-800 break-all">
                                                                        {{ $item->website }}
                                                                    </a>
                                                                @else
                                                                    <span class="text-gray-900">—</span>
                                                                @endif
                                                            </div>
                                                        </div>

                                                        <div>
                                                            <div class="text-xs uppercase tracking-wide text-gray-500">Internet search</div>
                                                            <div class="mt-1">
                                                                @if ($item->internetsearch)
                                                                    <a href="{{ $item->internetsearch }}"
                                                                       target="_blank"
                                                                       rel="noopener noreferrer"
                                                                       class="text-blue-600 hover:text-blue-800 break-all">
                                                                        {{ $item->internetsearch }}
                                                                    </a>
                                                                @else
                                                                    <span class="text-gray-900">—</span>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            @if ($hasCoords)
                                                <div class="destination-item-map-wrap">
                                                    <div id="destination-item-map-{{ $item->id }}"
                                                         class="destination-item-map rounded-lg border border-gray-300"
                                                         data-lat="{{ $item->latitude }}"
                                                         data-lng="{{ $item->longitude }}"
                                                         data-name="{{ $item->itemname ?: 'Destination item' }}"></div>
                                                </div>
                                            @else
                                                <div class="text-xs text-gray-500 italic">
                                                    No map coordinates recorded for this destination item.
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        @endif
    </div>
</div>

                        {{-- Trip stays and travel legs --}}
                        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                            <div class="bg-white shadow-sm sm:rounded-lg">
                                <div class="px-6 py-4 border-b border-gray-200">
                                    <h3 class="text-sm font-semibold text-gray-900">Trip stays</h3>
                                    <p class="mt-1 text-xs text-gray-500">
                                        Accommodation history linked to this place.
                                    </p>
                                </div>

                                <div class="px-6 py-4">
                                    @if ($place->tripStays->isEmpty())
                                        <p class="text-sm text-gray-500">No trip stays are linked to this place.</p>
                                    @else
                                        <ul class="space-y-3">
                                            @foreach ($place->tripStays as $stay)
                                                @php
                                                    $checkIn = $stay->checkindate ? \Carbon\Carbon::parse($stay->checkindate)->format('d M Y') : null;
                                                    $checkOut = $stay->checkoutdate ? \Carbon\Carbon::parse($stay->checkoutdate)->format('d M Y') : null;
                                                @endphp
                                                <li class="border border-gray-200 rounded-md px-3 py-2">
                                                    <div class="text-sm font-medium text-gray-900">
                                                        {{ $stay->stayname ?: 'Trip stay' }}
                                                    </div>
                                                    <div class="mt-1 text-xs text-gray-500">
                                                        Trip {{ $stay->trip?->tripname ?: '—' }}
                                                    </div>
                                                    <div class="mt-1 text-xs text-gray-500">
                                                        {{ $checkIn ?: 'Unknown' }} to {{ $checkOut ?: 'Unknown' }}
                                                    </div>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </div>
                            </div>

                            <div class="bg-white shadow-sm sm:rounded-lg">
                                <div class="px-6 py-4 border-b border-gray-200">
                                    <h3 class="text-sm font-semibold text-gray-900">Travel legs</h3>
                                    <p class="mt-1 text-xs text-gray-500">
                                        Trip legs arriving at or departing from this place.
                                    </p>
                                </div>

                                <div class="px-6 py-4 space-y-4">
                                    <div>
                                        <div class="text-xs uppercase tracking-wide text-gray-500 mb-2">Arrivals</div>
                                        @if ($place->tripLegsTo->isEmpty())
                                            <p class="text-sm text-gray-500">No arriving trip legs.</p>
                                        @else
                                            <ul class="space-y-2">
                                                @foreach ($place->tripLegsTo as $leg)
                                                    <li class="border border-gray-200 rounded-md px-3 py-2">
                                                        <div class="text-sm font-medium text-gray-900">
                                                            Leg {{ $leg->legnumber }}{{ $leg->title ? ' — ' . $leg->title : '' }}
                                                        </div>
                                                        <div class="mt-1 text-xs text-gray-500">
                                                            From {{ $leg->fromPlace?->placename ?: '—' }}
                                                        </div>
                                                        <div class="mt-1 text-xs text-gray-500">
                                                            Trip {{ $leg->trip?->tripname ?: '—' }}
                                                        </div>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @endif
                                    </div>

                                    <div>
                                        <div class="text-xs uppercase tracking-wide text-gray-500 mb-2">Departures</div>
                                        @if ($place->tripLegsFrom->isEmpty())
                                            <p class="text-sm text-gray-500">No departing trip legs.</p>
                                        @else
                                            <ul class="space-y-2">
                                                @foreach ($place->tripLegsFrom as $leg)
                                                    <li class="border border-gray-200 rounded-md px-3 py-2">
                                                        <div class="text-sm font-medium text-gray-900">
                                                            Leg {{ $leg->legnumber }}{{ $leg->title ? ' — ' . $leg->title : '' }}
                                                        </div>
                                                        <div class="mt-1 text-xs text-gray-500">
                                                            To {{ $leg->toPlace?->placename ?: '—' }}
                                                        </div>
                                                        <div class="mt-1 text-xs text-gray-500">
                                                            Trip {{ $leg->trip?->tripname ?: '—' }}
                                                        </div>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Fuel stops --}}
                        @if ($place->fuelStops->isNotEmpty())
                            <div class="bg-white shadow-sm sm:rounded-lg">
                                <div class="px-6 py-4 border-b border-gray-200">
                                    <h3 class="text-sm font-semibold text-gray-900">Fuel stops</h3>
                                    <p class="mt-1 text-xs text-gray-500">
                                        Practical service points linked to this place.
                                    </p>
                                </div>

                                <div class="px-6 py-4">
                                    <ul class="space-y-3">
                                        @foreach ($place->fuelStops as $fuelStop)
                                            <li class="border border-gray-200 rounded-md px-3 py-2">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ $fuelStop->stopname ?: 'Fuel stop' }}
                                                </div>
                                                <div class="mt-1 text-xs text-gray-500">
                                                    Brand {{ $fuelStop->brandname ?: '—' }}
                                                </div>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        @endif

                        {{-- Divider between places --}}
                        <div class="border-t border-dashed border-gray-300 pt-4"></div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>

    <style>
    .reference-place-map-wrap {
        width: 100%;
        max-width: 42rem;
        margin: 0 auto;
    }

    .reference-place-map {
        width: 100%;
        height: 20rem;
    }

    .destination-item-map-wrap {
        width: 100%;
        max-width: 36rem;
        margin: 0 auto;
    }

    .destination-item-map {
        width: 100%;
        height: 16rem;
    }

    .page-break-inside-avoid {
        break-inside: avoid;
        page-break-inside: avoid;
    }

    @media print {
        header,
        nav,
        footer,
        .no-print {
            display: none !important;
        }

        body {
            background: #ffffff !important;
        }

        .shadow-sm,
        .sm\:rounded-lg {
            box-shadow: none !important;
            border-radius: 0 !important;
        }

        .reference-place-map-wrap,
        .destination-item-map-wrap {
            margin: 0 auto !important;
            break-inside: avoid;
            page-break-inside: avoid;
        }

        .reference-place-map {
            width: 100% !important;
            height: 15rem !important;
        }

        .destination-item-map {
            width: 100% !important;
            height: 12rem !important;
        }

        .reference-place-map .leaflet-control-container,
        .destination-item-map .leaflet-control-container {
            display: none !important;
        }

        a[href]:after {
            content: '';
        }
    }
</style>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof L === 'undefined') {
            return;
        }

        const maps = [];

        document.querySelectorAll('.reference-place-map, .destination-item-map').forEach(function (element) {
            const lat = parseFloat(element.dataset.lat);
            const lng = parseFloat(element.dataset.lng);
            const name = element.dataset.name || 'Location';
            const zoom = element.classList.contains('destination-item-map') ? 14 : 12;

            if (Number.isNaN(lat) || Number.isNaN(lng)) {
                return;
            }

            const map = L.map(element, {
                scrollWheelZoom: false,
            });

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap contributors',
            }).addTo(map);

            L.marker([lat, lng]).addTo(map).bindPopup(name);
            map.setView([lat, lng], zoom);

            setTimeout(function () {
                map.invalidateSize();
                map.setView([lat, lng], zoom);
            }, 150);

            maps.push({ map, lat, lng, zoom });
        });

        function refreshMaps() {
            setTimeout(function () {
                maps.forEach(function (entry) {
                    entry.map.invalidateSize();
                    entry.map.setView([entry.lat, entry.lng], entry.zoom);
                });
            }, 200);
        }

        window.addEventListener('load', refreshMaps);
        window.addEventListener('beforeprint', refreshMaps);
    });
</script>
</x-app-layout>