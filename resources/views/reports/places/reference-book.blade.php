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
                        @if($place->destinations->isNotEmpty())
    <section>
        <h3 class="text-base font-semibold text-slate-900">Destinations</h3>

        <div class="mt-3 space-y-5">
            @foreach($place->destinations as $destination)
                <div class="rounded-lg border border-slate-200 overflow-hidden">
                    <div class="border-b border-slate-200 bg-slate-50 px-4 py-4">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <div class="text-sm font-semibold text-slate-900">
                                    {{ $destination->destinationname ?: 'Unnamed destination' }}
                                </div>

                                <div class="mt-2 flex flex-wrap gap-2 text-xs text-slate-600">
                                    @if($destination->place)
                                        <span class="rounded-full bg-slate-100 px-2.5 py-1">
                                            Linked Place: {{ $destination->place->placename }}
                                        </span>
                                    @endif

                                    @if($destination->destinationtype)
                                        <span class="rounded-full bg-blue-50 px-2.5 py-1 text-blue-700 border border-blue-200">
                                            Type: {{ ucfirst($destination->destinationtype) }}
                                        </span>
                                    @endif

                                    @if($destination->bestseason)
                                        <span class="rounded-full bg-sky-50 px-2.5 py-1 text-sky-700 border border-sky-200">
                                            Best Season: {{ $destination->bestseason }}
                                        </span>
                                    @endif

                                    @if($destination->revisitinterestlevel)
                                        <span class="rounded-full bg-purple-50 px-2.5 py-1 text-purple-700 border border-purple-200">
                                            Revisit Interest:
                                            {{ [
                                                'very_likely' => 'Very Likely',
                                                'likely' => 'Likely',
                                                'neutral' => 'Neutral',
                                                'unlikely' => 'Unlikely',
                                                'very_unlikely' => 'Very Unlikely',
                                            ][$destination->revisitinterestlevel] ?? ucfirst(str_replace('_', ' ', $destination->revisitinterestlevel)) }}
                                        </span>
                                    @endif

                                    <span class="rounded-full {{ !empty($destination->isfeatured) ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-slate-100 text-slate-600 border border-slate-200' }} px-2.5 py-1">
                                        Featured: {{ !empty($destination->isfeatured) ? 'Yes' : 'No' }}
                                    </span>

                                    <span class="rounded-full {{ !empty($destination->hasvisited) ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-600 border border-slate-200' }} px-2.5 py-1">
                                        Has Visited: {{ !empty($destination->hasvisited) ? 'Yes' : 'No' }}
                                    </span>
                                </div>
                            </div>

                            <div class="shrink-0 text-xs text-slate-500">
                                {{ $destination->items->count() }} item{{ $destination->items->count() === 1 ? '' : 's' }}
                            </div>
                        </div>
                    </div>

                    <div class="px-4 py-4 space-y-5">
                        @if(filled($destination->overview))
                            <div>
                                <div class="text-xs font-medium text-slate-500 mb-1">Overview</div>
                                <div class="text-sm text-slate-700 markdown-content">
                                    @include('partials.markdown.rendered-block', [
                                        'content' => $destination->overview,
                                    ])
                                </div>
                            </div>
                        @endif

                        @if(filled($destination->travelnotes))
                            <div>
                                <div class="text-xs font-medium text-slate-500 mb-1">Travel Notes</div>
                                <div class="text-sm text-slate-700 markdown-content">
                                    @include('partials.markdown.rendered-block', [
                                        'content' => $destination->travelnotes,
                                    ])
                                </div>
                            </div>
                        @endif

                        @if(filled($destination->suitability))
                            <div>
                                <div class="text-xs font-medium text-slate-500 mb-1">Suitability</div>
                                <div class="text-sm text-slate-700 markdown-content">
                                    @include('partials.markdown.rendered-block', [
                                        'content' => $destination->suitability,
                                    ])
                                </div>
                            </div>
                        @endif

                        @if(filled($destination->accessnotes))
                            <div>
                                <div class="text-xs font-medium text-slate-500 mb-1">Access Notes</div>
                                <div class="text-sm text-slate-700 markdown-content">
                                    @include('partials.markdown.rendered-block', [
                                        'content' => $destination->accessnotes,
                                    ])
                                </div>
                            </div>
                        @endif

                        @if(filled($destination->personalcommentary))
                            <div>
                                <div class="text-xs font-medium text-slate-500 mb-1">Personal Commentary</div>
                                <div class="text-sm text-slate-700 markdown-content">
                                    @include('partials.markdown.rendered-block', [
                                        'content' => $destination->personalcommentary,
                                    ])
                                </div>
                            </div>
                        @endif

                        <div class="border-t border-slate-200 pt-4">
                            <div class="text-xs font-medium text-slate-500 mb-2">Destination Items</div>

<div class="border-t border-slate-200 pt-4">
    <div class="text-xs font-medium text-slate-500 mb-2">Destination Items</div>

    @if($destination->items->isNotEmpty())
        <div class="space-y-4">
            @foreach($destination->items as $item)
                <div class="rounded-lg border border-slate-200 overflow-hidden bg-white">
                    <div class="border-b border-slate-200 bg-slate-50 px-4 py-4">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <div class="text-sm font-semibold text-slate-900">
                                    {{ $item->itemname ?: 'Unnamed item' }}
                                </div>

                                <div class="mt-2 flex flex-wrap gap-2 text-xs text-slate-600">
                                    @if($item->place)
                                        <span class="rounded-full bg-slate-100 px-2.5 py-1 border border-slate-200">
                                            Linked Place: {{ $item->place->placename }}
                                        </span>
                                    @endif

                                    <span class="rounded-full bg-slate-100 px-2.5 py-1 border border-slate-200">
                                        Destination: {{ $destination->destinationname ?: 'Unnamed destination' }}
                                    </span>

                                    @if($item->itemTypes->isNotEmpty())
                                        <span class="rounded-full bg-blue-50 px-2.5 py-1 text-blue-700 border border-blue-200">
                                            Item Types: {{ $item->itemTypes->pluck('typename')->filter()->join(', ') }}
                                        </span>
                                    @endif

                                    <span class="rounded-full {{ !empty($item->bookingrequired) ? 'bg-amber-50 text-amber-700 border-amber-200' : 'bg-slate-100 text-slate-600 border-slate-200' }} px-2.5 py-1 border">
                                        Booking Required: {{ !empty($item->bookingrequired) ? 'Yes' : 'No' }}
                                    </span>

                                    <span class="rounded-full {{ !empty($item->isactive) ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-rose-50 text-rose-700 border-rose-200' }} px-2.5 py-1 border">
                                        Active: {{ !empty($item->isactive) ? 'Yes' : 'No' }}
                                    </span>
                                </div>
                                @if(filled($item->shortdescription))
                                    <div>
                                        <div class="text-xs font-medium text-slate-500 mb-1">Short Description</div>
                                        <div class="text-sm text-slate-700 markdown-content">
                                            @include('partials.markdown.rendered-block', [
                                                'content' => $item->shortdescription,
                                            ])
                                        </div>
                                    </div>
                                @endif

                                @if(filled($item->notes))
                                    <div>
                                        <div class="text-xs font-medium text-slate-500 mb-1">Notes</div>
                                        <div class="text-sm text-slate-700 markdown-content">
                                            @include('partials.markdown.rendered-block', [
                                                'content' => $item->notes,
                                            ])
                                        </div>
                                    </div>
                                @endif

                                @if(filled($item->caravanaccessnotes))
                                    <div>
                                        <div class="text-xs font-medium text-slate-500 mb-1">Caravan Access Notes</div>
                                        <div class="text-sm text-slate-700 markdown-content">
                                            @include('partials.markdown.rendered-block', [
                                                'content' => $item->caravanaccessnotes,
                                            ])
                                        </div>
                                    </div>
                                @endif

                                @if(filled($item->disabilityaccessnotes))
                                    <div>
                                        <div class="text-xs font-medium text-slate-500 mb-1">Disability Access Notes</div>
                                        <div class="text-sm text-slate-700 markdown-content">
                                            @include('partials.markdown.rendered-block', [
                                                'content' => $item->disabilityaccessnotes,
                                            ])
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="px-4 py-4 space-y-5">
                        @if(!is_null($item->latitude) || !is_null($item->longitude))
                            <div>
                                <div class="text-xs font-medium text-slate-500 mb-2">Map</div>

                                <div class="rounded-lg border border-slate-200 overflow-hidden">
                                    <iframe
                                        width="100%"
                                        height="260"
                                        style="border:0;"
                                        loading="lazy"
                                        referrerpolicy="no-referrer-when-downgrade"
                                        src="https://www.google.com/maps?q={{ $item->latitude ?? '' }},{{ $item->longitude ?? '' }}&z=14&output=embed">
                                    </iframe>
                                </div>

                                <div class="mt-3 grid grid-cols-1 md:grid-cols-2 gap-3 text-sm text-slate-700">
                                    <div class="rounded-md border border-slate-200 px-3 py-2">
                                        <div class="text-xs font-medium text-slate-500 mb-1">Latitude</div>
                                        <div>{{ $item->latitude ?? '—' }}</div>
                                    </div>

                                    <div class="rounded-md border border-slate-200 px-3 py-2">
                                        <div class="text-xs font-medium text-slate-500 mb-1">Longitude</div>
                                        <div>{{ $item->longitude ?? '—' }}</div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm text-slate-700">
                            <div class="rounded-md border border-slate-200 px-3 py-2">
                                <div class="text-xs font-medium text-slate-500 mb-1">Estimated Cost Per Person</div>
                                <div>
                                    {{ $item->estimatedcostperperson !== null && $item->estimatedcostperperson !== '' ? number_format((float) $item->estimatedcostperperson, 2) : '—' }}
                                </div>
                            </div>

                            <div class="rounded-md border border-slate-200 px-3 py-2">
                                <div class="text-xs font-medium text-slate-500 mb-1">Estimated Total Cost</div>
                                <div>
                                    {{ $item->estimatedtotalcost !== null && $item->estimatedtotalcost !== '' ? number_format((float) $item->estimatedtotalcost, 2) : '—' }}
                                </div>
                            </div>

                            <div class="rounded-md border border-slate-200 px-3 py-2">
                                <div class="text-xs font-medium text-slate-500 mb-1">Recommended Stay Minutes</div>
                                <div>{{ $item->recommendedstayminutes ?? '—' }}</div>
                            </div>

                            <div class="rounded-md border border-slate-200 px-3 py-2">
                                <div class="text-xs font-medium text-slate-500 mb-1">Sort Order</div>
                                <div>{{ $item->sortorder ?? '—' }}</div>
                            </div>
                        </div>

                        
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <p class="text-sm text-slate-500">
            No destination items linked to this destination.
        </p>
    @endif
</div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </section>
@endif


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