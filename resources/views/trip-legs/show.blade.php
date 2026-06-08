<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Trip Leg Details
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    {{ $tripLeg->trip?->tripname ?? 'Trip' }}
                </p>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('trips.legs.edit', ['trip' => $trip, 'tripLeg' => $tripLeg]) }}"
                class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">
                    Edit Leg
                </a>

                <a href="{{ route('trips.legs.index', ['trip' => $trip]) }}"
                class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 text-sm">
                    Back to List
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="w-full max-w-none mx-auto px-4 sm:px-6 lg:px-8 xl:px-10 2xl:px-12 space-y-6">
            @if (session('success'))
                <div class="rounded-md bg-green-50 border border-green-200 text-green-800 px-4 py-3">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="rounded-md bg-red-50 border border-red-200 text-red-800 px-4 py-3">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6">
                    <div class="space-y-3">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">
                                {{ $tripLeg->title ?: 'Untitled Leg' }}
                            </h3>
                            <p class="mt-1 text-sm text-gray-500">
                                Leg {{ $tripLeg->legnumber ?? '—' }} · Sort {{ $tripLeg->sortorder ?? '—' }}
                            </p>
                        </div>

                        <div class="flex flex-wrap gap-2">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-50 text-blue-700">
                                {{ $tripLeg->fromPlace?->placename ?? 'No start place' }}
                            </span>

                            <span class="inline-flex items-center text-gray-400 text-sm">→</span>

                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-50 text-green-700">
                                {{ $tripLeg->toPlace?->placename ?? 'No end place' }}
                            </span>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 min-w-0 lg:min-w-[32rem]">
                        <div class="rounded-lg border border-gray-200 p-4">
                            <div class="text-xs font-medium uppercase tracking-wide text-gray-500">Start Date</div>
                            <div class="mt-2 text-sm text-gray-900">
                                {{ optional($tripLeg->startdate)->format('d M Y') ?? '—' }}
                            </div>
                        </div>

                        <div class="rounded-lg border border-gray-200 p-4">
                            <div class="text-xs font-medium uppercase tracking-wide text-gray-500">End Date</div>
                            <div class="mt-2 text-sm text-gray-900">
                                {{ optional($tripLeg->enddate)->format('d M Y') ?? '—' }}
                            </div>
                        </div>

                        <div class="rounded-lg border border-gray-200 p-4">
                            <div class="text-xs font-medium uppercase tracking-wide text-gray-500">Nights</div>
                            <div class="mt-2 text-sm text-gray-900">
                                {{ $tripLeg->nightsplanned ?? '—' }}
                            </div>
                        </div>

                        <div class="rounded-lg border border-gray-200 p-4">
                            <div class="text-xs font-medium uppercase tracking-wide text-gray-500">Distance</div>
                            <div class="mt-2 text-sm text-gray-900">
                                {{ $tripLeg->distancekm !== null ? number_format((float) $tripLeg->distancekm, 1) . ' km' : '—' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
                <div class="xl:col-span-2 space-y-6">
                    <div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-6">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">Route</h3>
                            <p class="mt-1 text-sm text-gray-500">
                                Start and end place information for this trip leg.
                            </p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="rounded-lg border border-gray-200 p-4 space-y-2">
                                <div class="text-xs font-medium uppercase tracking-wide text-gray-500">From</div>
                                <div class="text-sm font-medium text-gray-900">
                                    {{ $tripLeg->fromPlace?->placename ?? '—' }}
                                </div>
                                <div class="text-sm text-gray-600">
                                    {{ $tripLeg->fromPlace?->locality ?? '—' }}
                                </div>
                            </div>

                            <div class="rounded-lg border border-gray-200 p-4 space-y-2">
                                <div class="text-xs font-medium uppercase tracking-wide text-gray-500">To</div>
                                <div class="text-sm font-medium text-gray-900">
                                    {{ $tripLeg->toPlace?->placename ?? '—' }}
                                </div>
                                <div class="text-sm text-gray-600">
                                    {{ $tripLeg->toPlace?->locality ?? '—' }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-6">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">Notes</h3>
                            <p class="mt-1 text-sm text-gray-500">
                                Narrative and planning detail recorded for this leg.
                            </p>
                        </div>

                        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                            <div class="space-y-3">
                                <h4 class="text-sm font-semibold text-gray-900">Description</h4>
                                <div class="rounded-lg border border-gray-200 p-4 prose prose-sm max-w-none text-gray-700">
                                    {!! filled($tripLeg->description) ? \Illuminate\Support\Str::markdown($tripLeg->description) : '<p>—</p>' !!}
                                </div>
                            </div>

                            <div class="space-y-3">
                                <h4 class="text-sm font-semibold text-gray-900">Driving Notes</h4>
                                <div class="rounded-lg border border-gray-200 p-4 prose prose-sm max-w-none text-gray-700">
                                    {!! filled($tripLeg->drivingnotes) ? \Illuminate\Support\Str::markdown($tripLeg->drivingnotes) : '<p>—</p>' !!}
                                </div>
                            </div>

                            <div class="space-y-3">
                                <h4 class="text-sm font-semibold text-gray-900">Planning Notes</h4>
                                <div class="rounded-lg border border-gray-200 p-4 prose prose-sm max-w-none text-gray-700">
                                    {!! filled($tripLeg->planningnotes) ? \Illuminate\Support\Str::markdown($tripLeg->planningnotes) : '<p>—</p>' !!}
                                </div>
                            </div>

                            <div class="space-y-3">
                                <h4 class="text-sm font-semibold text-gray-900">Actual Notes</h4>
                                <div class="rounded-lg border border-gray-200 p-4 prose prose-sm max-w-none text-gray-700">
                                    {!! filled($tripLeg->actualnotes) ? \Illuminate\Support\Str::markdown($tripLeg->actualnotes) : '<p>—</p>' !!}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-6">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">Trip Stays</h3>
                            <p class="mt-1 text-sm text-gray-500">
                                Stay records linked to this leg.
                            </p>
                        </div>

                        <div class="space-y-4">
                            @forelse($tripLeg->tripStays as $stay)
                                <div class="rounded-lg border border-gray-200 p-4">
                                    <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $stay->stayname ?: ($stay->place?->placename ?? 'Stay') }}
                                            </div>
                                            <div class="mt-1 text-xs text-gray-500">
                                                {{ $stay->staytype ?: '—' }} ·
                                                {{ $stay->place?->placename ?? '—' }}
                                            </div>
                                        </div>

                                        <div class="text-sm text-gray-600">
                                            {{ optional($stay->checkindate)->format('d M Y') ?? '—' }}
                                            to
                                            {{ optional($stay->checkoutdate)->format('d M Y') ?? '—' }}
                                        </div>
                                    </div>

                                    <div class="mt-3 grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                                        <div>
                                            <div class="text-xs font-medium uppercase tracking-wide text-gray-500">Nights</div>
                                            <div class="mt-1 text-gray-900">{{ $stay->nights ?? '—' }}</div>
                                        </div>

                                        <div>
                                            <div class="text-xs font-medium uppercase tracking-wide text-gray-500">Paid</div>
                                            <div class="mt-1 text-gray-900">{{ $stay->isaccommodationpaid ? 'Yes' : 'No' }}</div>
                                        </div>

                                        <div>
                                            <div class="text-xs font-medium uppercase tracking-wide text-gray-500">Est. Cost</div>
                                            <div class="mt-1 text-gray-900">
                                                {{ $stay->estimatedtotalcost !== null ? '$' . number_format((float) $stay->estimatedtotalcost, 2) : '—' }}
                                            </div>
                                        </div>

                                        <div>
                                            <div class="text-xs font-medium uppercase tracking-wide text-gray-500">Actual Cost</div>
                                            <div class="mt-1 text-gray-900">
                                                {{ $stay->actualtotalcost !== null ? '$' . number_format((float) $stay->actualtotalcost, 2) : '—' }}
                                            </div>
                                        </div>
                                    </div>

                                    @if(filled($stay->description) || filled($stay->reviewnotes))
                                        <div class="mt-4 grid grid-cols-1 xl:grid-cols-2 gap-4">
                                            <div class="rounded-lg border border-gray-100 bg-gray-50 p-3">
                                                <div class="text-xs font-medium uppercase tracking-wide text-gray-500 mb-2">Description</div>
                                                <div class="text-sm text-gray-700 whitespace-pre-line">{{ $stay->description ?: '—' }}</div>
                                            </div>

                                            <div class="rounded-lg border border-gray-100 bg-gray-50 p-3">
                                                <div class="text-xs font-medium uppercase tracking-wide text-gray-500 mb-2">Review Notes</div>
                                                <div class="text-sm text-gray-700 whitespace-pre-line">{{ $stay->reviewnotes ?: '—' }}</div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @empty
                                <div class="rounded-lg border border-dashed border-gray-300 p-4 text-sm text-gray-500">
                                    No trip stays are linked to this leg.
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-6">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">Fuel Estimates</h3>
                            <p class="mt-1 text-sm text-gray-500">
                                Planned fuel assumptions and estimated costs linked to this leg.
                            </p>
                        </div>

                        <div class="space-y-4">
                            @forelse($tripLeg->tripFuelEstimates as $estimate)
                                <div class="rounded-lg border border-gray-200 p-4">
                                    <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $estimate->fuelStop?->stopname ?? $estimate->place?->placename ?? 'Fuel estimate' }}
                                            </div>
                                            <div class="mt-1 text-xs text-gray-500">
                                                {{ $estimate->fueltype ?: '—' }}
                                                @if($estimate->fuelStop?->place?->placename)
                                                    · {{ $estimate->fuelStop->place->placename }}
                                                @endif
                                            </div>
                                        </div>

                                        <div class="text-sm text-gray-600">
                                            {{ optional($estimate->estimatedate)->format('d M Y') ?? '—' }}
                                        </div>
                                    </div>

                                    <div class="mt-3 grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                                        <div>
                                            <div class="text-xs font-medium uppercase tracking-wide text-gray-500">Distance</div>
                                            <div class="mt-1 text-gray-900">
                                                {{ $estimate->estimateddistancekm !== null ? number_format((float) $estimate->estimateddistancekm, 1) . ' km' : '—' }}
                                            </div>
                                        </div>

                                        <div>
                                            <div class="text-xs font-medium uppercase tracking-wide text-gray-500">Litres</div>
                                            <div class="mt-1 text-gray-900">
                                                {{ $estimate->estimatedlitres !== null ? number_format((float) $estimate->estimatedlitres, 3) : '—' }}
                                            </div>
                                        </div>

                                        <div>
                                            <div class="text-xs font-medium uppercase tracking-wide text-gray-500">Price/L</div>
                                            <div class="mt-1 text-gray-900">
                                                {{ $estimate->expectedpriceperlitre !== null ? '$' . number_format((float) $estimate->expectedpriceperlitre, 4) : '—' }}
                                            </div>
                                        </div>

                                        <div>
                                            <div class="text-xs font-medium uppercase tracking-wide text-gray-500">Total</div>
                                            <div class="mt-1 text-gray-900">
                                                {{ $estimate->estimatedtotalcost !== null ? '$' . number_format((float) $estimate->estimatedtotalcost, 2) : '—' }}
                                            </div>
                                        </div>
                                    </div>

                                    @if(filled($estimate->notes) || $estimate->sourceObservation)
                                        <div class="mt-4 grid grid-cols-1 xl:grid-cols-2 gap-4">
                                            <div class="rounded-lg border border-gray-100 bg-gray-50 p-3">
                                                <div class="text-xs font-medium uppercase tracking-wide text-gray-500 mb-2">Notes</div>
                                                <div class="text-sm text-gray-700 whitespace-pre-line">{{ $estimate->notes ?: '—' }}</div>
                                            </div>

                                            <div class="rounded-lg border border-gray-100 bg-gray-50 p-3">
                                                <div class="text-xs font-medium uppercase tracking-wide text-gray-500 mb-2">Source Observation</div>
                                                <div class="text-sm text-gray-700">
                                                    @if($estimate->sourceObservation)
                                                        {{ $estimate->sourceObservation->fueltype ?? '—' }}
                                                        ·
                                                        {{ optional($estimate->sourceObservation->observedon)->format('d M Y') ?? '—' }}
                                                        ·
                                                        {{ $estimate->sourceObservation->priceperlitre !== null ? '$' . number_format((float) $estimate->sourceObservation->priceperlitre, 4) : '—' }}
                                                    @else
                                                        —
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @empty
                                <div class="rounded-lg border border-dashed border-gray-300 p-4 text-sm text-gray-500">
                                    No fuel estimates are linked to this leg.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="bg-white shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900">Destination Context</h3>
                        <p class="mt-1 text-sm text-gray-500">
                            Main editorial destination linked to this leg.
                        </p>

                        <div class="mt-4 space-y-4 text-sm">
                            <div>
                                <div class="text-xs font-medium uppercase tracking-wide text-gray-500">Destination</div>
                                <div class="mt-1 text-gray-900">{{ $tripLeg->destination?->destinationname ?? '—' }}</div>
                            </div>

                            <div>
                                <div class="text-xs font-medium uppercase tracking-wide text-gray-500">Type</div>
                                <div class="mt-1 text-gray-900">{{ $tripLeg->destination?->destinationtype ?? '—' }}</div>
                            </div>

                            <div>
                                <div class="text-xs font-medium uppercase tracking-wide text-gray-500">Linked Place</div>
                                <div class="mt-1 text-gray-900">{{ $tripLeg->destination?->place?->placename ?? '—' }}</div>
                            </div>

                            <div>
                                <div class="text-xs font-medium uppercase tracking-wide text-gray-500">Best Season</div>
                                <div class="mt-1 text-gray-900">{{ $tripLeg->destination?->bestseason ?? '—' }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-4">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wide">Along This Leg</h3>
                                <p class="mt-1 text-sm text-gray-500">
                                    Suggested places are shown in travel order from start to finish. Open a place to see its destination items.
                                </p>
                            </div>

                            @if(!empty($routeSuggestions['bufferKm']))
                                <div class="text-xs text-gray-500 whitespace-nowrap">
                                    Within {{ number_format($routeSuggestions['bufferKm'], 0) }} km of route
                                </div>
                            @endif
                        </div>

                        @if(!($routeSuggestions['hasRoute'] ?? false))
                            <div class="border border-dashed border-gray-300 rounded-lg p-4 text-sm text-gray-500">
                                {{ $routeSuggestions['message'] ?? 'Route suggestions are not available for this leg.' }}
                            </div>
                        @else
                            @forelse (($routeSuggestions['places'] ?? collect()) as $place)
                                <div class="border border-gray-200 rounded-lg overflow-hidden">
                                    <button
                                        type="button"
                                        class="w-full flex items-center justify-between gap-4 px-4 py-4 text-left bg-white hover:bg-gray-50"
                                        onclick="document.getElementById('route-place-{{ $place->id }}').classList.toggle('hidden')"
                                    >
                                        <div>
                                            <div class="text-sm font-semibold text-gray-900">
                                                {{ $place->placename }}
                                            </div>

                                            <div class="mt-1 text-sm text-gray-600">
                                                @if($place->locality)
                                                    {{ $place->locality }}
                                                @endif

                                                @if($place->placetype)
                                                    @if($place->locality) · @endif
                                                    {{ $place->placetype }}
                                                @endif

                                                · {{ $place->path_progress_percent ?? 0 }}% along route

                                                @if(isset($place->route_distance_km))
                                                    · {{ number_format($place->route_distance_km, 1) }} km off route
                                                @endif
                                            </div>
                                        </div>

                                        <div class="text-xs text-gray-500 whitespace-nowrap">
                                            {{ $place->suggested_destination_items->count() }} items
                                        </div>
                                    </button>

                                    <div id="route-place-{{ $place->id }}" class="hidden border-t border-gray-200 bg-gray-50 p-4 space-y-3">
                                        @forelse ($place->suggested_destination_items as $item)
                                            <div class="bg-white border border-gray-200 rounded-lg p-4">
                                                <div class="flex items-start justify-between gap-4">
                                                    <div>
                                                        <div class="text-sm font-semibold text-gray-900">
                                                            {{ $item->itemname }}
                                                        </div>

                                                        <div class="mt-1 text-sm text-gray-600">
                                                            @if($item->destination)
                                                                {{ $item->destination->destinationname }}
                                                            @else
                                                                —
                                                            @endif

                                                            @if($item->itemtype)
                                                                · {{ $item->itemtype }}
                                                            @endif
                                                        </div>
                                                    </div>

                                                    @if(isset($item->route_distance_km))
                                                        <div class="text-xs text-gray-500 whitespace-nowrap">
                                                            {{ number_format($item->route_distance_km, 1) }} km off route
                                                        </div>
                                                    @endif
                                                </div>

                                                @if($item->shortdescription)
                                                    <div class="mt-2 text-sm text-gray-600">
                                                        {{ $item->shortdescription }}
                                                    </div>
                                                @endif

                                                @if($item->caravanaccessnotes)
                                                    <div class="mt-2 text-xs text-amber-700 bg-amber-50 border border-amber-200 rounded p-2">
                                                        {{ $item->caravanaccessnotes }}
                                                    </div>
                                                @endif
                                            </div>
                                        @empty
                                            <div class="text-sm text-gray-500">
                                                No destination items linked to this place yet.
                                            </div>
                                        @endforelse
                                    </div>
                                </div>
                            @empty
                                <div class="border border-dashed border-gray-300 rounded-lg p-4 text-sm text-gray-500">
                                    {{ $routeSuggestions['message'] ?? 'No suggested places found for this leg.' }}
                                </div>
                            @endforelse
                        @endif
                    </div>

                    <div class="bg-white shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900">Elevation</h3>

                        <div class="mt-4 grid grid-cols-1 gap-4 text-sm">
                            <div class="rounded-lg border border-gray-200 p-4">
                                <div class="text-xs font-medium uppercase tracking-wide text-gray-500">Elevation Gain</div>
                                <div class="mt-1 text-gray-900">
                                    {{ $tripLeg->elevationgainm !== null ? number_format((float) $tripLeg->elevationgainm, 1) . ' m' : '—' }}
                                </div>
                            </div>

                            <div class="rounded-lg border border-gray-200 p-4">
                                <div class="text-xs font-medium uppercase tracking-wide text-gray-500">Elevation Loss</div>
                                <div class="mt-1 text-gray-900">
                                    {{ $tripLeg->elevationlossm !== null ? number_format((float) $tripLeg->elevationlossm, 1) . ' m' : '—' }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900">Actions</h3>

                        <div class="mt-4 flex flex-col gap-3">
                            <a href="{{ route('trips.legs.edit', ['trip' => $trip, 'tripLeg' => $tripLeg]) }}"
                               class="inline-flex items-center justify-center px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">
                                Edit Trip Leg
                            </a>

                            <form method="POST"
                                action="{{ route('trips.legs.destroy', ['trip' => $trip, 'tripLeg' => $tripLeg]) }}"
                                onsubmit="return confirm('Delete this trip leg?');">
                                @csrf
                                @method('DELETE')

                                <button type="submit"
                                        class="inline-flex w-full items-center justify-center px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 text-sm">
                                    Delete Trip Leg
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>