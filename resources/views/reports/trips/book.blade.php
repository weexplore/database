{{-- resources/views/reports/trips/book.blade.php --}}

<x-app-layout>
    @php
        $title = 'Trip Book – ' . $trip->tripname;
    @endphp

    <x-slot name="header">
         <x-report-print-styles />

        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ $title }}
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    Printable summary for this trip, including legs, stays, activities, reviews, and fuel.
                </p>
            </div>
            <div class="print-hide flex items-center gap-2">
                <button type="button"
                        onclick="window.print()"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">
                    Print / Save PDF
                </button>
                <a href="{{ route('trips.edit', $trip) }}"
                   class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 text-sm">
                    Back to trip
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="w-full max-w-none mx-auto px-4 sm:px-6 lg:px-8 xl:px-10 2xl:px-12 space-y-6">

            {{-- Trip summary --}}
            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="px-6 py-5 border-b border-gray-200 flex items-center justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">
                            Trip summary
                        </h3>
                        <p class="mt-1 text-xs text-gray-500">
                            Key details and notes for this trip.
                        </p>
                    </div>
                </div>

                <div class="px-6 py-5 space-y-6 text-sm text-gray-800">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <div class="text-xs uppercase tracking-wide text-gray-500">
                                Trip name
                            </div>
                            <div class="mt-1 font-medium">
                                {{ $trip->tripname }}
                            </div>
                        </div>

                        <div>
                            <div class="text-xs uppercase tracking-wide text-gray-500">
                                Status
                            </div>
                            <div class="mt-1">
                                {{ ucfirst($trip->tripstatus) }}
                            </div>
                        </div>

                        <div>
                            <div class="text-xs uppercase tracking-wide text-gray-500">
                                Dates
                            </div>
                            <div class="mt-1">
                                @php
                                    $start = $trip->startdate ? $trip->startdate->format('d M Y') : null;
                                    $end = $trip->enddate ? $trip->enddate->format('d M Y') : null;
                                @endphp
                                {{ $start ?: 'Unknown' }} – {{ $end ?: 'Unknown' }}
                            </div>
                        </div>

                        <div>
                            <div class="text-xs uppercase tracking-wide text-gray-500">
                                Travellers
                            </div>
                            <div class="mt-1">
                                @if ($trip->travellers->isNotEmpty())
                                    {{ $trip->travellers->pluck('displayname')->join(', ') }}
                                @else
                                    Not recorded
                                @endif
                            </div>
                        </div>

                        <div>
                            <div class="text-xs uppercase tracking-wide text-gray-500">
                                Planned distance (km)
                            </div>
                            <div class="mt-1">
                                {{ $trip->estimatedtotaldistancekm ?? 'Not set' }}
                            </div>
                        </div>

                        <div>
                            <div class="text-xs uppercase tracking-wide text-gray-500">
                                Actual distance (km)
                            </div>
                            <div class="mt-1">
                                {{ $trip->actualtotaldistancekm ?? 'Not set' }}
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="md:col-span-1">
                            <div class="text-xs uppercase tracking-wide text-gray-500">
                                Summary
                            </div>
                            <div class="mt-1 text-sm text-gray-800 markdown-content">
                                @if($trip->summary)
                                    @include('partials.markdown.rendered-block', [
                                        'content' => $trip->summary,
                                    ])
                                @else
                                    <span class="text-gray-500">No summary recorded.</span>
                                @endif
                            </div>
                        </div>

                        <div>
                            <div class="text-xs uppercase tracking-wide text-gray-500">
                                Planning notes
                            </div>
                            <div class="mt-1 text-sm text-gray-800 markdown-content">
                                @if($trip->planningnotes)
                                    @include('partials.markdown.rendered-block', [
                                        'content' => $trip->planningnotes,
                                    ])
                                @else
                                    <span class="text-gray-500">No planning notes recorded.</span>
                                @endif
                            </div>
                        </div>

                        <div>
                            <div class="text-xs uppercase tracking-wide text-gray-500">
                                Actual notes
                            </div>
                            <div class="mt-1 text-sm text-gray-800 markdown-content">
                                @if($trip->actualnotes)
                                    @include('partials.markdown.rendered-block', [
                                        'content' => $trip->actualnotes,
                                    ])
                                @else
                                    <span class="text-gray-500">No post-trip notes recorded.</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Budget defaults and totals --}}
            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-sm font-semibold text-gray-900">
                        Budget defaults and totals
                    </h3>
                    <p class="mt-1 text-xs text-gray-500">
                        Daily allowances and rolled-up estimated and actual trip costs.
                    </p>
                </div>

                <div class="px-6 py-4 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                        <div>
                            <div class="text-xs uppercase tracking-wide text-gray-500">Trip days</div>
                            <div class="mt-1">{{ $tripDays ?? 'Not available' }}</div>
                        </div>
                        <div>
                            <div class="text-xs uppercase tracking-wide text-gray-500">Daily food budget</div>
                            <div class="mt-1">
                                {{ $dailyFoodBudget !== null ? number_format($dailyFoodBudget, 2) : 'Not set' }}
                            </div>
                        </div>
                        <div>
                            <div class="text-xs uppercase tracking-wide text-gray-500">Daily misc budget</div>
                            <div class="mt-1">
                                {{ $dailyMiscBudget !== null ? number_format($dailyMiscBudget, 2) : 'Not set' }}
                            </div>
                        </div>
                    </div>

                    <table class="w-full text-xs border-t border-b border-gray-200">
                        <thead class="bg-gray-50 text-gray-600 uppercase tracking-wide">
                            <tr>
                                <th class="px-2 py-2 text-left">Cost type</th>
                                <th class="px-2 py-2 text-right">Estimated</th>
                                <th class="px-2 py-2 text-right">Actual</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr>
                                <td class="px-2 py-2">Food allowance</td>
                                <td class="px-2 py-2 text-right">
                                    {{ $foodBudgetTotal !== null ? number_format($foodBudgetTotal, 2) : '—' }}
                                </td>
                                <td class="px-2 py-2 text-right">—</td>
                            </tr>
                            <tr>
                                <td class="px-2 py-2">Misc allowance</td>
                                <td class="px-2 py-2 text-right">
                                    {{ $miscBudgetTotal !== null ? number_format($miscBudgetTotal, 2) : '—' }}
                                </td>
                                <td class="px-2 py-2 text-right">—</td>
                            </tr>
                            <tr>
                                <td class="px-2 py-2">Trip stays</td>
                                <td class="px-2 py-2 text-right">
                                    {{ $stayEstimatedTotal !== null ? number_format($stayEstimatedTotal, 2) : '—' }}
                                </td>
                                <td class="px-2 py-2 text-right">
                                    {{ $stayActualTotal !== null ? number_format($stayActualTotal, 2) : '—' }}
                                </td>
                            </tr>
                            <tr>
                                <td class="px-2 py-2">Trip items</td>
                                <td class="px-2 py-2 text-right">
                                    {{ $itemEstimatedTotal !== null ? number_format($itemEstimatedTotal, 2) : '—' }}
                                </td>
                                <td class="px-2 py-2 text-right">
                                    {{ $itemActualTotal !== null ? number_format($itemActualTotal, 2) : '—' }}
                                </td>
                            </tr>
                            <tr>
                                <td class="px-2 py-2">Fuel</td>
                                <td class="px-2 py-2 text-right">
                                    {{ $fuelEstimateTotal !== null ? number_format($fuelEstimateTotal, 2) : '—' }}
                                </td>
                                <td class="px-2 py-2 text-right">
                                    {{ $fuelActualTotal !== null ? number_format($fuelActualTotal, 2) : '—' }}
                                </td>
                            </tr>
                            <tr class="bg-gray-50 font-semibold">
                                <td class="px-2 py-2">Overall total</td>
                                <td class="px-2 py-2 text-right">
                                    {{ number_format($overallEstimatedTotal, 2) }}
                                </td>
                                <td class="px-2 py-2 text-right">
                                    {{ number_format($overallActualTotal, 2) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Trip legs --}}
            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-sm font-semibold text-gray-900">
                        Trip legs
                    </h3>
                    <p class="mt-1 text-xs text-gray-500">
                        Planned and actual travel segments for this trip, with related stays and activities grouped under each leg.
                    </p>
                </div>

                <div class="px-6 py-4 space-y-6">
                    @php
                        $unassignedStays = $trip->stays
                            ->whereNull('triplegid')
                            ->sortBy([
                                ['checkindate', 'asc'],
                                ['checkoutdate', 'asc'],
                                ['id', 'asc'],
                            ])
                            ->values();

                        $unassignedItems = $trip->tripItems
                            ->whereNull('triplegid')
                            ->sortBy([
                                ['itemdate', 'asc'],
                                ['startdatetime', 'asc'],
                                ['sortorder', 'asc'],
                                ['id', 'asc'],
                            ])
                            ->values();
                    @endphp

                    @if ($trip->legs->isEmpty())
                        <p class="text-sm text-gray-500">
                            No trip legs are recorded for this trip.
                        </p>
                    @else
                        @foreach ($trip->legs as $leg)
                            @php
                                $start = $leg->startdate ? $leg->startdate->format('d M Y') : null;
                                $end = $leg->enddate ? $leg->enddate->format('d M Y') : null;

                                $fromPlace = $leg->fromPlace;
                                $fromDestination = $leg->fromDestination;
                                $fromDestinationItem = $leg->fromDestinationItem;

                                $toPlace = $leg->toPlace;
                                $toDestination = $leg->toDestination;
                                $toDestinationItem = $leg->toDestinationItem;

                                $fromLabel = $fromDestinationItem?->itemname ?? $fromPlace?->placename ?? '—';
                                $toLabel = $toDestinationItem?->itemname ?? $toPlace?->placename ?? '—';

                                $fromLat = $fromDestinationItem?->latitude ?? $fromPlace?->latitude;
                                $fromLng = $fromDestinationItem?->longitude ?? $fromPlace?->longitude;
                                $toLat = $toDestinationItem?->latitude ?? $toPlace?->latitude;
                                $toLng = $toDestinationItem?->longitude ?? $toPlace?->longitude;

                                $fromMapName = $fromDestinationItem?->itemname ?? $fromPlace?->placename ?? 'Start';
                                $toMapName = $toDestinationItem?->itemname ?? $toPlace?->placename ?? 'Destination';

                                $orderedLegPoints = ($leg->legPoints ?? collect())
                                    ->sortBy([
                                        ['sequenceno', 'asc'],
                                        ['id', 'asc'],
                                    ])
                                    ->values();

                                $routeWaypoints = collect();

                                if ($fromLat !== null && $fromLng !== null) {
                                    $routeWaypoints->push([
                                        'lat' => (float) $fromLat,
                                        'lng' => (float) $fromLng,
                                        'name' => $fromMapName,
                                        'type' => 'from',
                                    ]);
                                }

                                foreach ($orderedLegPoints as $point) {
                                    $pointLat =
                                        $point->destinationItem?->latitude
                                        ?? $point->place?->latitude
                                        ?? $point->destination?->place?->latitude;

                                    $pointLng =
                                        $point->destinationItem?->longitude
                                        ?? $point->place?->longitude
                                        ?? $point->destination?->place?->longitude;

                                    $pointName =
                                        $point->title
                                        ?? optional($point->destinationItem)->itemname
                                        ?? optional($point->destination)->destinationname
                                        ?? optional($point->place)->placename
                                        ?? 'Leg point';

                                    if ($pointLat !== null && $pointLng !== null) {
                                        $routeWaypoints->push([
                                            'lat' => (float) $pointLat,
                                            'lng' => (float) $pointLng,
                                            'name' => $pointName,
                                            'type' => 'leg_point',
                                        ]);
                                    }
                                }

                                if ($toLat !== null && $toLng !== null) {
                                    $routeWaypoints->push([
                                        'lat' => (float) $toLat,
                                        'lng' => (float) $toLng,
                                        'name' => $toMapName,
                                        'type' => 'to',
                                    ]);
                                }

                                $hasMap = $routeWaypoints->count() >= 2;

                                $fromMeta = collect([
                                    $fromPlace?->placename && $fromPlace?->placename !== $fromLabel ? 'Place: ' . $fromPlace->placename : null,
                                    $fromDestination?->destinationname ? 'Destination: ' . $fromDestination->destinationname : null,
                                    $fromDestinationItem?->itemname && $fromDestinationItem?->itemname !== $fromLabel ? 'Item: ' . $fromDestinationItem->itemname : null,
                                ])->filter()->values();

                                $toMeta = collect([
                                    $toPlace?->placename && $toPlace?->placename !== $toLabel ? 'Place: ' . $toPlace->placename : null,
                                    $toDestination?->destinationname ? 'Destination: ' . $toDestination->destinationname : null,
                                    $toDestinationItem?->itemname && $toDestinationItem?->itemname !== $toLabel ? 'Item: ' . $toDestinationItem->itemname : null,
                                ])->filter()->values();

                                $fromHasExtraDetails = $fromDestinationItem && (
                                    $fromDestinationItem->shortdescription ||
                                    $fromDestinationItem->notes ||
                                    $fromDestinationItem->addressline1 ||
                                    $fromDestinationItem->addressline2 ||
                                    $fromDestinationItem->addressline3 ||
                                    $fromDestinationItem->postcode ||
                                    $fromDestinationItem->telephone ||
                                    $fromDestinationItem->website ||
                                    $fromDestinationItem->caravanaccessnotes ||
                                    $fromDestinationItem->disabilityaccessnotes
                                );

                                $toHasExtraDetails = $toDestinationItem && (
                                    $toDestinationItem->shortdescription ||
                                    $toDestinationItem->notes ||
                                    $toDestinationItem->addressline1 ||
                                    $toDestinationItem->addressline2 ||
                                    $toDestinationItem->addressline3 ||
                                    $toDestinationItem->postcode ||
                                    $toDestinationItem->telephone ||
                                    $toDestinationItem->website ||
                                    $toDestinationItem->caravanaccessnotes ||
                                    $toDestinationItem->disabilityaccessnotes
                                );

                                $legStays = $trip->stays
                                    ->where('triplegid', $leg->id)
                                    ->sortBy([
                                        ['checkindate', 'asc'],
                                        ['checkoutdate', 'asc'],
                                        ['id', 'asc'],
                                    ])
                                    ->values();

                                $legItems = $trip->tripItems
                                    ->where('triplegid', $leg->id)
                                    ->sortBy([
                                        ['itemdate', 'asc'],
                                        ['startdatetime', 'asc'],
                                        ['sortorder', 'asc'],
                                        ['id', 'asc'],
                                    ])
                                    ->values();
                            @endphp

                            <div class="trip-leg-card border border-gray-200 rounded-lg">
                                <div class="px-4 py-3 bg-gray-50 border-b border-gray-200 flex items-start justify-between gap-4">
                                    <div>
                                        <h4 class="text-sm font-semibold text-gray-900">
                                            Leg {{ $leg->legnumber }}@if ($leg->title) — {{ $leg->title }}@endif
                                        </h4>
                                        <p class="mt-1 text-xs text-gray-500">
                                            {{ $start ?: 'Unknown' }} – {{ $end ?: 'Unknown' }}
                                            @if ($leg->nightsplanned !== null)
                                                · {{ $leg->nightsplanned }} night{{ (int) $leg->nightsplanned === 1 ? '' : 's' }}
                                            @endif
                                        </p>
                                    </div>

                                    <div class="text-right text-xs text-gray-500">
                                        <div>Distance</div>
                                        <div class="mt-1 text-sm font-semibold text-gray-900">
                                            {{ $leg->distancekm !== null ? number_format((float) $leg->distancekm, 1) . ' km' : '—' }}
                                        </div>
                                    </div>
                                </div>

                                <div class="p-3 space-y-4">
                                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-3 text-sm">
                                        <div class="border border-gray-200 rounded-lg p-3">
                                            <div class="text-[11px] uppercase tracking-wide text-gray-500">From</div>
                                            <div class="mt-1 font-medium text-gray-900">{{ $fromLabel }}</div>

                                            @if ($fromMeta->isNotEmpty())
                                                <div class="mt-1 text-xs text-gray-600">
                                                    {{ $fromMeta->join(' · ') }}
                                                </div>
                                            @endif

                                            @if ($fromHasExtraDetails)
                                                @include('reports.trips.partials.location-details', [
                                                    'destinationItem' => $fromDestinationItem,
                                                    'place' => $fromPlace,
                                                    'showPlaceName' => false,
                                                    'showDestinationItemHeading' => false,
                                                ])
                                            @endif
                                        </div>

                                        <div class="border border-gray-200 rounded-lg p-3">
                                            <div class="text-[11px] uppercase tracking-wide text-gray-500">To</div>
                                            <div class="mt-1 font-medium text-gray-900">{{ $toLabel }}</div>

                                            @if ($toMeta->isNotEmpty())
                                                <div class="mt-1 text-xs text-gray-600">
                                                    {{ $toMeta->join(' · ') }}
                                                </div>
                                            @endif

                                            @if ($toHasExtraDetails)
                                                @include('reports.trips.partials.location-details', [
                                                    'destinationItem' => $toDestinationItem,
                                                    'place' => $toPlace,
                                                    'showPlaceName' => false,
                                                    'showDestinationItemHeading' => false,
                                                ])
                                            @endif
                                        </div>
                                    </div>

                                    @if ($orderedLegPoints->isNotEmpty())
                                        <div class="border border-gray-200 rounded-lg">
                                            <div class="px-3 py-2 bg-gray-50 border-b border-gray-200 flex items-center justify-between">
                                                <div class="text-xs font-semibold uppercase tracking-wide text-gray-600">Leg points</div>
                                                <div class="text-xs text-gray-500">
                                                    {{ $orderedLegPoints->count() }} point{{ $orderedLegPoints->count() === 1 ? '' : 's' }}
                                                </div>
                                            </div>

                                            <div class="divide-y divide-gray-100 text-xs">
                                                @foreach ($orderedLegPoints as $point)
                                                    @php
                                                        $label = $point->title
                                                            ?? optional($point->destinationItem)->itemname
                                                            ?? optional($point->destination)->destinationname
                                                            ?? optional($point->place)->placename
                                                            ?? 'Point';

                                                        $pointTypeLabel = $point->pointtype
                                                            ? ucfirst(str_replace('_', ' ', $point->pointtype))
                                                            : 'Point';

                                                        $pointMeta = collect([
                                                            $point->place ? 'Place: ' . $point->place->placename : null,
                                                            $point->destination ? 'Destination: ' . $point->destination->destinationname : null,
                                                            $point->destinationItem ? 'Item: ' . $point->destinationItem->itemname : null,
                                                        ])->filter()->values();

                                                        $pointHasExtraDetails = $point->destinationItem && (
                                                            $point->destinationItem->shortdescription ||
                                                            $point->destinationItem->notes ||
                                                            $point->destinationItem->addressline1 ||
                                                            $point->destinationItem->addressline2 ||
                                                            $point->destinationItem->addressline3 ||
                                                            $point->destinationItem->postcode ||
                                                            $point->destinationItem->telephone ||
                                                            $point->destinationItem->website ||
                                                            $point->destinationItem->caravanaccessnotes ||
                                                            $point->destinationItem->disabilityaccessnotes
                                                        );
                                                    @endphp

                                                    <div class="px-3 py-2 flex items-start justify-between gap-3">
                                                        <div class="min-w-0 flex-1">
                                                            <div class="font-medium text-gray-900">{{ $label }}</div>
                                                            <div class="mt-0.5 text-[11px] text-gray-500">
                                                                {{ $pointTypeLabel }}
                                                                @if ($pointMeta->isNotEmpty())
                                                                    · {{ $pointMeta->join(' · ') }}
                                                                @endif
                                                            </div>

                                                            @if ($point->notes)
                                                                <div class="mt-1 text-[11px] text-gray-700 markdown-content">
                                                                    @include('partials.markdown.rendered-block', [
                                                                        'content' => $point->notes,
                                                                    ])
                                                                </div>
                                                            @endif

                                                            @if ($pointHasExtraDetails)
                                                                @include('reports.trips.partials.location-details', [
                                                                    'destinationItem' => $point->destinationItem,
                                                                    'place' => $point->place,
                                                                    'showPlaceName' => false,
                                                                    'showDestinationItemHeading' => false,
                                                                ])
                                                            @endif
                                                        </div>

                                                        @if ($point->sequenceno !== null)
                                                            <div class="text-[11px] text-gray-500 font-semibold">
                                                                {{ $point->sequenceno }}
                                                            </div>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif

                                    @if ($legItems->isNotEmpty())
                                        <div class="border border-gray-200 rounded-lg overflow-hidden">
                                            <div class="px-3 py-2 bg-gray-50 border-b border-gray-200 flex items-center justify-between">
                                                <div class="text-xs font-semibold uppercase tracking-wide text-gray-600">
                                                    Items and activities on this leg
                                                </div>
                                                <div class="text-xs text-gray-500">
                                                    {{ $legItems->count() }} item{{ $legItems->count() === 1 ? '' : 's' }}
                                                </div>
                                            </div>

                                            <div class="divide-y divide-gray-100">
                                                @foreach ($legItems as $item)
                                                    @include('reports.trips.partials.trip-item-card', [
                                                        'item' => $item,
                                                        'compact' => true,
                                                    ])
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif

                                    @if ($hasMap)
                                        <div class="trip-leg-map-wrap">
                                            <div
                                                id="trip-leg-map-{{ $leg->id }}"
                                                class="trip-leg-map rounded-lg border border-gray-300"
                                                data-waypoints='@json($routeWaypoints->values())'
                                            ></div>
                                        </div>
                                    @else
                                        <div class="rounded-md border border-yellow-200 bg-yellow-50 px-4 py-3 text-sm text-yellow-800">
                                            Map unavailable because fewer than two route points on this leg have coordinates.
                                        </div>
                                    @endif

                                    @if ($legStays->isNotEmpty())
                                        <div class="border border-gray-200 rounded-lg overflow-hidden">
                                            <div class="px-3 py-2 bg-gray-50 border-b border-gray-200 flex items-center justify-between">
                                                <div class="text-xs font-semibold uppercase tracking-wide text-gray-600">
                                                    Stays on this leg
                                                </div>
                                                <div class="text-xs text-gray-500">
                                                    {{ $legStays->count() }} stay{{ $legStays->count() === 1 ? '' : 's' }}
                                                </div>
                                            </div>

                                            <div class="divide-y divide-gray-100">
                                                @foreach ($legStays as $stay)
                                                    @include('reports.trips.partials.stay-card', [
                                                        'stay' => $stay,
                                                        'compact' => true,
                                                    ])
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif

                                    @if ($leg->description || $leg->drivingnotes || $leg->planningnotes || $leg->actualnotes)
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                            @if ($leg->description)
                                                <div>
                                                    <div class="text-xs uppercase tracking-wide text-gray-500">Description</div>
                                                    <div class="mt-1 text-gray-700 markdown-content">
                                                        @include('partials.markdown.rendered-block', [
                                                            'content' => $leg->description,
                                                        ])
                                                    </div>
                                                </div>
                                            @endif

                                            @if ($leg->drivingnotes)
                                                <div>
                                                    <div class="text-xs uppercase tracking-wide text-gray-500">Driving notes</div>
                                                    <div class="mt-1 text-gray-700 markdown-content">
                                                        @include('partials.markdown.rendered-block', [
                                                            'content' => $leg->drivingnotes,
                                                        ])
                                                    </div>
                                                </div>
                                            @endif

                                            @if ($leg->planningnotes)
                                                <div>
                                                    <div class="text-xs uppercase tracking-wide text-gray-500">Planning notes</div>
                                                    <div class="mt-1 text-gray-700 markdown-content">
                                                        @include('partials.markdown.rendered-block', [
                                                            'content' => $leg->planningnotes,
                                                        ])
                                                    </div>
                                                </div>
                                            @endif

                                            @if ($leg->actualnotes)
                                                <div>
                                                    <div class="text-xs uppercase tracking-wide text-gray-500">Actual notes</div>
                                                    <div class="mt-1 text-gray-700 markdown-content">
                                                        @include('partials.markdown.rendered-block', [
                                                            'content' => $leg->actualnotes,
                                                        ])
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach

                        <div class="bg-gray-50 border border-gray-200 rounded-lg px-4 py-3">
                            <div class="flex items-center justify-between gap-4 text-sm">
                                <div class="font-semibold text-gray-900">Total distance</div>
                                <div class="font-semibold text-gray-900">
                                    {{ number_format((float) $trip->legs->sum('distancekm'), 1) }} km
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            @if ($unassignedStays->isNotEmpty())
                <div class="bg-white shadow-sm sm:rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-sm font-semibold text-gray-900">
                            Unassigned stays
                        </h3>
                        <p class="mt-1 text-xs text-gray-500">
                            Stays recorded for this trip that are not linked to a specific trip leg.
                        </p>
                    </div>

                    <div class="px-6 py-4">
                        <div class="space-y-4">
                            @foreach ($unassignedStays as $stay)
                                @include('reports.trips.partials.stay-card', [
                                    'stay' => $stay,
                                    'compact' => false,
                                ])
                            @endforeach

                            <div class="bg-gray-50 border border-gray-200 rounded-lg px-4 py-3">
                                <div class="flex items-center justify-end gap-8 text-sm font-semibold text-gray-900">
                                    <div>
                                        Estimated total:
                                        {{ $unassignedStays->sum(fn ($stay) => (float) ($stay->estimatedtotalcost ?? 0)) ? number_format($unassignedStays->sum(fn ($stay) => (float) ($stay->estimatedtotalcost ?? 0)), 2) : '—' }}
                                    </div>
                                    <div>
                                        Actual total:
                                        {{ $unassignedStays->sum(fn ($stay) => (float) ($stay->actualtotalcost ?? 0)) ? number_format($unassignedStays->sum(fn ($stay) => (float) ($stay->actualtotalcost ?? 0)), 2) : '—' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            @if ($unassignedItems->isNotEmpty())
                <div class="bg-white shadow-sm sm:rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-sm font-semibold text-gray-900">
                            Unassigned items and activities
                        </h3>
                        <p class="mt-1 text-xs text-gray-500">
                            Activities and items recorded for this trip that are not linked to a specific trip leg.
                        </p>
                    </div>

                    <div class="px-6 py-4">
                        <div class="space-y-4">
                            @foreach ($unassignedItems as $item)
                                @include('reports.trips.partials.trip-item-card', [
                                    'item' => $item,
                                    'compact' => false,
                                ])
                            @endforeach

                            <div class="bg-gray-50 border border-gray-200 rounded-lg px-4 py-3">
                                <div class="flex items-center justify-end gap-8 text-sm font-semibold text-gray-900">
                                    <div>
                                        Estimated total:
                                        {{ $unassignedItems->sum(fn ($item) => (float) ($item->estimatedtotalcost ?? 0)) ? number_format($unassignedItems->sum(fn ($item) => (float) ($item->estimatedtotalcost ?? 0)), 2) : '—' }}
                                    </div>
                                    <div>
                                        Actual total:
                                        {{ $unassignedItems->sum(fn ($item) => (float) ($item->actualcost ?? 0)) ? number_format($unassignedItems->sum(fn ($item) => (float) ($item->actualcost ?? 0)), 2) : '—' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Reviews (including private) --}}
            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-sm font-semibold text-gray-900">
                        Trip reviews
                    </h3>
                    <p class="mt-1 text-xs text-gray-500">
                        All reviews linked to this trip, including private notes.
                    </p>
                </div>

                <div class="px-6 py-4">
                    @if ($trip->reviews->isEmpty())
                        <p class="text-sm text-gray-500">
                            No reviews are recorded for this trip.
                        </p>
                    @else
                        <div class="space-y-4 text-sm">
                            @foreach ($trip->reviews as $review)
                                @php
                                    $reviewDate = $review->reviewdate ? $review->reviewdate->format('d M Y') : 'Unknown date';
                                    $subject =
                                        optional($review->stay)->stayname
                                        ?? optional($review->tripItem)->title
                                        ?? optional($review->destinationItem)->itemname
                                        ?? optional($review->destination)->destinationname
                                        ?? optional($review->place)->placename
                                        ?? 'Trip';
                                @endphp
                                <div class="border border-gray-200 rounded-md px-3 py-2">
                                    <div class="flex justify-between gap-4">
                                        <div class="font-medium text-gray-900">
                                            {{ $review->title ?: $subject }}
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            {{ $reviewDate }}
                                        </div>
                                    </div>
                                    <div class="mt-0.5 text-xs text-gray-500">
                                        @if ($review->traveller)
                                            {{ $review->traveller->displayname }} —
                                        @endif
                                        {{ $subject }}
                                        @if ($review->isprivate)
                                            (private)
                                        @endif
                                    </div>
                                    @if ($review->comments)
                                        <div class="mt-1 text-gray-800 markdown-content">
                                            @include('partials.markdown.rendered-block', [
                                                'content' => $review->comments,
                                            ])
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            {{-- Fuel estimates --}}
            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-sm font-semibold text-gray-900">
                        Fuel estimates
                    </h3>
                    <p class="mt-1 text-xs text-gray-500">
                        Planned fuel costs and quantities for this trip.
                    </p>
                </div>

                <div class="px-6 py-4">
                    @if ($trip->fuelEstimates->isEmpty())
                        <p class="text-sm text-gray-500">
                            No fuel estimates are recorded for this trip.
                        </p>
                    @else
                        <table class="w-full text-xs border-t border-b border-gray-200">
                            <thead class="bg-gray-50 text-gray-600 uppercase tracking-wide">
                                <tr>
                                    <th class="px-2 py-2 text-left">Date</th>
                                    <th class="px-2 py-2 text-left">Leg</th>
                                    <th class="px-2 py-2 text-left">Stop / Place</th>
                                    <th class="px-2 py-2 text-right">Distance (km)</th>
                                    <th class="px-2 py-2 text-right">Litres</th>
                                    <th class="px-2 py-2 text-right">Price / L</th>
                                    <th class="px-2 py-2 text-right">Est. total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($trip->fuelEstimates as $estimate)
                                    @php
                                        $date = $estimate->estimatedate
                                            ? $estimate->estimatedate->format('d M Y')
                                            : '—';

                                        $legLabel = $estimate->tripLeg
                                            ? 'Leg ' . $estimate->tripLeg->legnumber
                                            : '—';

                                        $stopPlace =
                                            optional($estimate->fuelStop)->stopname
                                            ?? optional($estimate->place)->placename
                                            ?? '—';
                                    @endphp
                                    <tr>
                                        <td class="px-2 py-2 align-top">
                                            {{ $date }}
                                        </td>
                                        <td class="px-2 py-2 align-top">
                                            {{ $legLabel }}
                                        </td>
                                        <td class="px-2 py-2 align-top">
                                            {{ $stopPlace }}
                                        </td>
                                        <td class="px-2 py-2 align-top text-right">
                                            {{ $estimate->estimateddistancekm ?? '—' }}
                                        </td>
                                        <td class="px-2 py-2 align-top text-right">
                                            {{ $estimate->estimatedlitres ?? '—' }}
                                        </td>
                                        <td class="px-2 py-2 align-top text-right">
                                            {{ $estimate->expectedpriceperlitre !== null ? number_format($estimate->expectedpriceperlitre, 4) : '—' }}
                                        </td>
                                        <td class="px-2 py-2 align-top text-right">
                                            {{ $estimate->estimatedtotalcost !== null ? number_format($estimate->estimatedtotalcost, 2) : '—' }}
                                        </td>
                                    </tr>
                                @endforeach
                                <tr class="bg-gray-50 font-semibold">
                                    <td colspan="6" class="px-2 py-2 text-right">
                                        Total estimated fuel cost
                                    </td>
                                    <td class="px-2 py-2 text-right">
                                        {{ $fuelEstimateTotal !== null ? number_format($fuelEstimateTotal, 2) : '—' }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>

            {{-- Fuel purchases --}}
            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-sm font-semibold text-gray-900">
                        Fuel purchases
                    </h3>
                    <p class="mt-1 text-xs text-gray-500">
                        Actual fuel purchases for this trip.
                    </p>
                </div>

                <div class="px-6 py-4">
                    @if ($trip->fuelPurchases->isEmpty())
                        <p class="text-sm text-gray-500">
                            No fuel purchases are recorded for this trip.
                        </p>
                    @else
                        <table class="w-full text-xs border-t border-b border-gray-200">
                            <thead class="bg-gray-50 text-gray-600 uppercase tracking-wide">
                                <tr>
                                    <th class="px-2 py-2 text-left">Date</th>
                                    <th class="px-2 py-2 text-left">Leg</th>
                                    <th class="px-2 py-2 text-left">Stop / Place</th>
                                    <th class="px-2 py-2 text-right">Odometer</th>
                                    <th class="px-2 py-2 text-right">Litres</th>
                                    <th class="px-2 py-2 text-right">Price / L</th>
                                    <th class="px-2 py-2 text-right">Fuel total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($trip->fuelPurchases as $purchase)
                                    @php
                                        $date = $purchase->purchasedate
                                            ? $purchase->purchasedate->format('d M Y')
                                            : '—';

                                        $legLabel = $purchase->leg
                                            ? 'Leg ' . $purchase->leg->legnumber
                                            : '—';

                                        $stopPlace =
                                            optional($purchase->fuelStop)->stopname
                                            ?? optional($purchase->place)->placename
                                            ?? '—';
                                    @endphp
                                    <tr>
                                        <td class="px-2 py-2 align-top">
                                            {{ $date }}
                                        </td>
                                        <td class="px-2 py-2 align-top">
                                            {{ $legLabel }}
                                        </td>
                                        <td class="px-2 py-2 align-top">
                                            {{ $stopPlace }}
                                        </td>
                                        <td class="px-2 py-2 align-top text-right">
                                            {{ $purchase->odometerkm ?? '—' }}
                                        </td>
                                        <td class="px-2 py-2 align-top text-right">
                                            {{ $purchase->litres ?? '—' }}
                                        </td>
                                        <td class="px-2 py-2 align-top text-right">
                                            {{ $purchase->priceperlitre !== null ? number_format($purchase->priceperlitre, 4) : '—' }}
                                        </td>
                                        <td class="px-2 py-2 align-top text-right">
                                            {{ $purchase->fueltotal !== null ? number_format($purchase->fueltotal, 2) : '—' }}
                                        </td>
                                    </tr>
                                @endforeach
                                <tr class="bg-gray-50 font-semibold">
                                    <td colspan="6" class="px-2 py-2 text-right">
                                        Total fuel cost
                                    </td>
                                    <td class="px-2 py-2 text-right">
                                        {{ $fuelActualTotal !== null ? number_format($fuelActualTotal, 2) : '—' }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>

        </div>
    </div>

    <style>
    .trip-leg-map-wrap {
        width: 100%;
        max-width: 35rem;
        margin: 0 auto;
        break-inside: avoid;
        page-break-inside: avoid;
    }

    .trip-leg-map {
        width: 100%;
        height: 18rem;
        position: relative;
    }

    .location-detail-stack,
    .location-detail-stack > div {
        break-inside: avoid;
        page-break-inside: avoid;
    }

    @media print {
    .trip-leg-card {
        break-inside: auto !important;
        page-break-inside: auto !important;
    }

    .trip-leg-map-wrap {
        width: 100% !important;
        max-width: none !important;
        margin: 0 auto !important;
        break-inside: avoid !important;
        page-break-inside: avoid !important;
        overflow: visible !important;
    }

    .trip-leg-map {
        width: 100% !important;
        height: 120mm !important;
        min-height: 120mm !important;
        position: relative !important;
        overflow: hidden !important;
    }

    .trip-leg-map .leaflet-control-container {
        display: none !important;
    }
}
</style>

    <link
        rel="stylesheet"
        href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
        crossorigin=""
    />

    <script
        src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
        crossorigin=""
    ></script>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof L === 'undefined') {
            console.error('Leaflet did not load.');
            return;
        }

        const tripBookMaps = [];

        document.querySelectorAll('.trip-leg-map').forEach(function (element) {
            let waypoints = [];

            try {
                waypoints = JSON.parse(element.dataset.waypoints || '[]');
            } catch (error) {
                console.error('Invalid waypoint JSON', error);
                return;
            }

            waypoints = waypoints
                .filter(function (point) {
                    return point
                        && Number.isFinite(parseFloat(point.lat))
                        && Number.isFinite(parseFloat(point.lng));
                })
                .map(function (point) {
                    return {
                        lat: parseFloat(point.lat),
                        lng: parseFloat(point.lng),
                        name: point.name || 'Point',
                        type: point.type || 'leg_point'
                    };
                });

            if (waypoints.length < 2) {
                return;
            }

            const map = L.map(element, {
                scrollWheelZoom: false,
            });

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap contributors',
            }).addTo(map);

            waypoints.forEach(function (point, index) {
                const isFirst = index === 0;
                const isLast = index === waypoints.length - 1;

                const markerStyle = isFirst
                    ? { radius: 7, color: '#1d4ed8', weight: 2, fillColor: '#3b82f6', fillOpacity: 1 }
                    : isLast
                        ? { radius: 7, color: '#b91c1c', weight: 2, fillColor: '#ef4444', fillOpacity: 1 }
                        : { radius: 6, color: '#0369a1', weight: 2, fillColor: '#0ea5e9', fillOpacity: 1 };

                const markerLabel = isFirst
                    ? 'From: ' + point.name
                    : isLast
                        ? 'To: ' + point.name
                        : 'Leg point: ' + point.name;

                L.circleMarker([point.lat, point.lng], markerStyle)
                    .addTo(map)
                    .bindPopup(markerLabel);
            });

            let activeRouteLayer = null;

            function fitMapToLayer(layer) {
                if (!layer || !layer.getBounds().isValid()) {
                    return;
                }

                map.invalidateSize({
                    pan: false,
                    debounceMoveend: true,
                });

                map.fitBounds(layer.getBounds(), {
                    padding: [42, 42],
                    maxZoom: 10,
                    animate: false,
                });
            }

            function storeMapForPrint() {
                tripBookMaps.push({
                    map: map,
                    getLayer: function () {
                        return activeRouteLayer;
                    },
                });
            }

            const coordinates = waypoints.map(function (point) {
                return point.lng + ',' + point.lat;
            }).join(';');

            const routeUrl =
                'https://router.project-osrm.org/route/v1/driving/' +
                coordinates +
                '?overview=full&geometries=geojson&steps=false';

            fetch(routeUrl)
                .then(function (response) {
                    return response.json();
                })
                .then(function (data) {
                    if (data.code !== 'Ok' || !data.routes || !data.routes.length) {
                        throw new Error('Route not available');
                    }

                    activeRouteLayer = L.geoJSON(data.routes[0].geometry, {
                        style: {
                            color: '#2563eb',
                            weight: 4,
                            opacity: 0.85,
                        }
                    }).addTo(map);

                    fitMapToLayer(activeRouteLayer);
                    storeMapForPrint();
                })
                .catch(function () {
                    activeRouteLayer = L.polyline(
                        waypoints.map(function (point) {
                            return [point.lat, point.lng];
                        }),
                        {
                            color: '#2563eb',
                            weight: 4,
                            opacity: 0.85,
                            dashArray: '6,6',
                        }
                    ).addTo(map);

                    fitMapToLayer(activeRouteLayer);
                    storeMapForPrint();
                });
        });

        function refreshTripBookMapsForPrint() {
            tripBookMaps.forEach(function (entry) {
                const layer = entry.getLayer();

                if (!layer || !layer.getBounds().isValid()) {
                    return;
                }

                entry.map.invalidateSize({
                    pan: false,
                    debounceMoveend: true,
                });
            });

            requestAnimationFrame(function () {
                requestAnimationFrame(function () {
                    tripBookMaps.forEach(function (entry) {
                        const layer = entry.getLayer();

                        if (!layer || !layer.getBounds().isValid()) {
                            return;
                        }

                        entry.map.invalidateSize({
                            pan: false,
                            debounceMoveend: true,
                        });

                        entry.map.fitBounds(layer.getBounds(), {
                            padding: [42, 42],
                            maxZoom: 10,
                            animate: false,
                        });
                    });
                });
            });

            /*
            * Chrome/Edge can complete a further pagination/layout pass after
            * beforeprint/matchMedia has fired. Run one final refit afterward.
            */
            setTimeout(function () {
                tripBookMaps.forEach(function (entry) {
                    const layer = entry.getLayer();

                    if (!layer || !layer.getBounds().isValid()) {
                        return;
                    }

                    entry.map.invalidateSize({
                        pan: false,
                        debounceMoveend: true,
                    });

                    entry.map.fitBounds(layer.getBounds(), {
                        padding: [42, 42],
                        maxZoom: 10,
                        animate: false,
                    });
                });
            }, 150);
        }

        window.addEventListener('beforeprint', refreshTripBookMapsForPrint);

        if (window.matchMedia) {
            const mediaQueryList = window.matchMedia('print');

            if (mediaQueryList.addEventListener) {
                mediaQueryList.addEventListener('change', function (event) {
                    if (event.matches) {
                        refreshTripBookMapsForPrint();
                    }
                });
            } else if (mediaQueryList.addListener) {
                mediaQueryList.addListener(function (event) {
                    if (event.matches) {
                        refreshTripBookMapsForPrint();
                    }
                });
            }
        }
    });
    </script>
</x-app-layout>