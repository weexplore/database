{{-- resources/views/reports/trips/book.blade.php --}}

<x-app-layout>
    @php
        $title = 'Trip Book – ' . $trip->tripname;
    @endphp

    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ $title }}
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    Printable summary for this trip, including legs, stays, activities, reviews, and fuel.
                </p>
            </div>
            <div class="flex items-center gap-2">
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
                                    $end   = $trip->enddate ? $trip->enddate->format('d M Y') : null;
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

                    {{-- Key notes: summary + planning + actual --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="md:col-span-1">
                            <div class="text-xs uppercase tracking-wide text-gray-500">
                                Summary
                            </div>
                            <div class="mt-1 text-sm text-gray-800 whitespace-pre-line">
                                {{ $trip->summary ?: 'No summary recorded.' }}
                            </div>
                        </div>

                        <div>
                            <div class="text-xs uppercase tracking-wide text-gray-500">
                                Planning notes
                            </div>
                            <div class="mt-1 text-sm text-gray-800 whitespace-pre-line">
                                {{ $trip->planningnotes ?: 'No planning notes recorded.' }}
                            </div>
                        </div>

                        <div>
                            <div class="text-xs uppercase tracking-wide text-gray-500">
                                Actual notes
                            </div>
                            <div class="mt-1 text-sm text-gray-800 whitespace-pre-line">
                                {{ $trip->actualnotes ?: 'No post-trip notes recorded.' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

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
                        Planned and actual travel segments for this trip.
                    </p>
                </div>

                <div class="px-6 py-4 space-y-6">
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
                                $toPlace = $leg->toPlace;

                                $fromLat = $fromPlace?->latitude;
                                $fromLng = $fromPlace?->longitude;
                                $toLat = $toPlace?->latitude;
                                $toLng = $toPlace?->longitude;

                                $hasMap = $fromLat !== null && $fromLng !== null && $toLat !== null && $toLng !== null;
                            @endphp

                            <div class="trip-leg-card border border-gray-200 rounded-lg">
                                <div class="px-4 py-3 bg-gray-50 border-b border-gray-200 flex items-start justify-between gap-4">
                                    <div>
                                        <h4 class="text-sm font-semibold text-gray-900">
                                            Leg {{ $leg->legnumber }}
                                            @if ($leg->title)
                                                — {{ $leg->title }}
                                            @endif
                                        </h4>
                                        <p class="mt-1 text-xs text-gray-500">
                                            {{ $start ?: 'Unknown' }} – {{ $end ?: 'Unknown' }}
                                        </p>
                                    </div>

                                    <div class="text-right text-xs text-gray-500">
                                        <div>Distance</div>
                                        <div class="mt-1 text-sm font-semibold text-gray-900">
                                            {{ $leg->distancekm !== null ? number_format((float) $leg->distancekm, 1) . ' km' : '—' }}
                                        </div>
                                    </div>
                                </div>

                                <div class="p-4 space-y-4">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                        <div>
                                            <div class="text-xs uppercase tracking-wide text-gray-500">From</div>
                                            <div class="mt-1 text-gray-900">
                                                {{ $fromPlace?->placename ?: '—' }}
                                            </div>
                                        </div>

                                        <div>
                                            <div class="text-xs uppercase tracking-wide text-gray-500">To</div>
                                            <div class="mt-1 text-gray-900">
                                                {{ $toPlace?->placename ?: '—' }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="p-4 space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div>
                        <div class="text-xs uppercase tracking-wide text-gray-500">From</div>
                        <div class="mt-1 text-gray-900">
                            {{ $fromPlace?->placename ?: '—' }}
                        </div>
                    </div>

                    <div>
                        <div class="text-xs uppercase tracking-wide text-gray-500">To</div>
                        <div class="mt-1 text-gray-900">
                            {{ $toPlace?->placename ?: '—' }}
                        </div>
                    </div>
                </div>

                {{-- Trip leg points (anchors and planned stops) --}}
                @if ($leg->legPoints && $leg->legPoints->isNotEmpty())
                    <div class="border border-gray-200 rounded-lg">
                        <div class="px-3 py-2 bg-gray-50 border-b border-gray-200 flex items-center justify-between">
                            <div class="text-xs font-semibold uppercase tracking-wide text-gray-600">
                                Leg points
                            </div>
                            <div class="text-xs text-gray-500">
                                {{ $leg->legPoints->count() }} point{{ $leg->legPoints->count() === 1 ? '' : 's' }}
                            </div>
                        </div>
                        <div class="divide-y divide-gray-100 text-xs">
                            @foreach ($leg->legPoints as $point)
                                @php
                                    $label =
                                        $point->title
                                        ?? optional($point->destinationItem)->itemname
                                        ?? optional($point->destination)->destinationname
                                        ?? optional($point->place)->placename
                                        ?? 'Point';

                                    $pointTypeLabel = $point->pointtype
                                        ? ucfirst(str_replace('_', ' ', $point->pointtype))
                                        : 'Point';

                                    $plannedDate = $point->planneddate
                                        ? $point->planneddate->format('d M Y')
                                        : null;

                                    $timeRange = null;
                                    if ($point->starttime || $point->endtime) {
                                        $fromTime = $point->starttime ? substr($point->starttime, 0, 5) : '—';
                                        $toTime = $point->endtime ? substr($point->endtime, 0, 5) : '—';
                                        $timeRange = $fromTime . ' – ' . $toTime;
                                    }
                                @endphp
                                <div class="px-3 py-2 flex items-start justify-between gap-3">
                                    <div>
                                        <div class="font-medium text-gray-900">
                                            {{ $label }}
                                        </div>
                                        <div class="mt-0.5 text-[11px] text-gray-500">
                                            {{ $pointTypeLabel }}
                                            @if ($plannedDate)
                                                • {{ $plannedDate }}
                                            @endif
                                            @if ($timeRange)
                                                • {{ $timeRange }}
                                            @endif
                                        </div>
                                        @if ($point->notes)
                                            <div class="mt-1 text-[11px] text-gray-700 whitespace-pre-line">
                                                {{ $point->notes }}
                                            </div>
                                        @endif
                                    </div>
                                    @if ($point->sequenceno !== null)
                                        <div class="text-[11px] text-gray-500 font-semibold">
                                            #{{ $point->sequenceno }}
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if ($hasMap)
                    <div class="trip-leg-map-wrap">
                        <div
                            id="trip-leg-map-{{ $leg->id }}"
                            class="trip-leg-map rounded-lg border border-gray-300"
                            data-from-lat="{{ $fromLat }}"
                            data-from-lng="{{ $fromLng }}"
                            data-to-lat="{{ $toLat }}"
                            data-to-lng="{{ $toLng }}"
                            data-from-name="{{ $fromPlace?->placename }}"
                            data-to-name="{{ $toPlace?->placename }}"
                        ></div>
                    </div>
                @else
                    <div class="rounded-md border border-yellow-200 bg-yellow-50 px-4 py-3 text-sm text-yellow-800">
                        Map unavailable because one or both linked places do not have coordinates.
                    </div>
                @endif

                @if ($leg->description || $leg->drivingnotes || $leg->planningnotes || $leg->actualnotes)
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        @if ($leg->description)
                            <div>
                                <div class="text-xs uppercase tracking-wide text-gray-500">Description</div>
                                <div class="mt-1 text-gray-700 whitespace-pre-line">{{ $leg->description }}</div>
                            </div>
                        @endif

                        @if ($leg->drivingnotes)
                            <div>
                                <div class="text-xs uppercase tracking-wide text-gray-500">Driving notes</div>
                                <div class="mt-1 text-gray-700 whitespace-pre-line">{{ $leg->drivingnotes }}</div>
                            </div>
                        @endif

                        @if ($leg->planningnotes)
                            <div>
                                <div class="text-xs uppercase tracking-wide text-gray-500">Planning notes</div>
                                <div class="mt-1 text-gray-700 whitespace-pre-line">{{ $leg->planningnotes }}</div>
                            </div>
                        @endif

                        @if ($leg->actualnotes)
                            <div>
                                <div class="text-xs uppercase tracking-wide text-gray-500">Actual notes</div>
                                <div class="mt-1 text-gray-700 whitespace-pre-line">{{ $leg->actualnotes }}</div>
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

            {{-- Trip stays --}}
            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-sm font-semibold text-gray-900">
                        Trip stays
                    </h3>
                    <p class="mt-1 text-xs text-gray-500">
                        Accommodation and overnight stays for this trip.
                    </p>
                </div>

                <div class="px-6 py-4">
                    @if ($trip->stays->isEmpty())
                        <p class="text-sm text-gray-500">
                            No stays are recorded for this trip.
                        </p>
                    @else
                        <table class="w-full text-xs border-t border-b border-gray-200">
                            <thead class="bg-gray-50 text-gray-600 uppercase tracking-wide">
                                <tr>
                                    <th class="px-2 py-2 text-left">Stay</th>
                                    <th class="px-2 py-2 text-left">Place</th>
                                    <th class="px-2 py-2 text-left">Dates</th>
                                    <th class="px-2 py-2 text-right">Nights</th>
                                    <th class="px-2 py-2 text-right">Est. cost</th>
                                    <th class="px-2 py-2 text-right">Actual cost</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($trip->stays as $stay)
                                    @php
                                        $checkIn  = $stay->checkindate ? $stay->checkindate->format('d M Y') : null;
                                        $checkOut = $stay->checkoutdate ? $stay->checkoutdate->format('d M Y') : null;
                                    @endphp
                                    <tr>
                                        <td class="px-2 py-2 align-top">
                                            <div class="font-medium">
                                                {{ $stay->stayname }}
                                            </div>
                                            <div class="mt-0.5 text-gray-500">
                                                {{ $stay->staytype ?: '—' }}
                                            </div>
                                        </td>
                                        <td class="px-2 py-2 align-top">
                                            {{ optional($stay->place)->placename ?: '—' }}
                                        </td>
                                        <td class="px-2 py-2 align-top">
                                            {{ $checkIn ?: 'Unknown' }} – {{ $checkOut ?: 'Unknown' }}
                                        </td>
                                        <td class="px-2 py-2 align-top text-right">
                                            {{ $stay->nights ?? '—' }}
                                        </td>
                                        <td class="px-2 py-2 align-top text-right">
                                            {{ $stay->estimatedtotalcost !== null ? number_format($stay->estimatedtotalcost, 2) : '—' }}
                                        </td>
                                        <td class="px-2 py-2 align-top text-right">
                                            {{ $stay->actualtotalcost !== null ? number_format($stay->actualtotalcost, 2) : '—' }}
                                        </td>
                                    </tr>
                                @endforeach
                                <tr class="bg-gray-50 font-semibold">
                                    <td colspan="4" class="px-2 py-2 text-right">
                                        Totals
                                    </td>
                                    <td class="px-2 py-2 text-right">
                                        {{ $stayEstimatedTotal !== null ? number_format($stayEstimatedTotal, 2) : '—' }}
                                    </td>
                                    <td class="px-2 py-2 text-right">
                                        {{ $stayActualTotal !== null ? number_format($stayActualTotal, 2) : '—' }}
                                    </td>
                                    <td></td>
                                </tr>
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>

            {{-- Trip items (activities, etc.) --}}
            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-sm font-semibold text-gray-900">
                        Trip items and activities
                    </h3>
                    <p class="mt-1 text-xs text-gray-500">
                        Planned and completed activities, drives, walks, and other items.
                    </p>
                </div>

                <div class="px-6 py-4">
                    @if ($trip->tripItems->isEmpty())
                        <p class="text-sm text-gray-500">
                            No trip items are recorded for this trip.
                        </p>
                    @else
                        <table class="w-full text-xs border-t border-b border-gray-200">
                            <thead class="bg-gray-50 text-gray-600 uppercase tracking-wide">
                                <tr>
                                    <th class="px-2 py-2 text-left">Date</th>
                                    <th class="px-2 py-2 text-left">Type</th>
                                    <th class="px-2 py-2 text-left">Title</th>
                                    <th class="px-2 py-2 text-left">Location</th>
                                    <th class="px-2 py-2 text-right">Est. total</th>
                                    <th class="px-2 py-2 text-right">Actual</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($trip->tripItems as $item)
                                    @php
                                        $dateLabel = $item->itemdate
                                            ? $item->itemdate->format('d M Y')
                                            : ($item->startdatetime ? $item->startdatetime->format('d M Y') : '—');

                                        $location =
                                            optional($item->place)->placename
                                            ?? optional($item->destination)->destinationname
                                            ?? optional($item->destinationItem)->itemname
                                            ?? '—';
                                    @endphp
                                    <tr>
                                        <td class="px-2 py-2 align-top">
                                            {{ $dateLabel }}
                                        </td>
                                        <td class="px-2 py-2 align-top">
                                            {{ $item->itemtype ?: '—' }}
                                        </td>
                                        <td class="px-2 py-2 align-top">
                                            <div class="font-medium">
                                                {{ $item->title }}
                                            </div>
                                            @if ($item->description)
                                                <div class="mt-0.5 text-gray-600 whitespace-pre-line">
                                                    {{ $item->description }}
                                                </div>
                                            @endif
                                        </td>
                                        <td class="px-2 py-2 align-top">
                                            {{ $location }}
                                        </td>
                                        <td class="px-2 py-2 align-top text-right">
                                            {{ $item->estimatedtotalcost !== null ? number_format($item->estimatedtotalcost, 2) : '—' }}
                                        </td>
                                        <td class="px-2 py-2 align-top text-right">
                                            {{ $item->actualcost !== null ? number_format($item->actualcost, 2) : '—' }}
                                        </td>
                                    </tr>
                                @endforeach
                                <tr class="bg-gray-50 font-semibold">
                                    <td colspan="4" class="px-2 py-2 text-right">
                                        Totals
                                    </td>
                                    <td class="px-2 py-2 text-right">
                                        {{ $itemEstimatedTotal !== null ? number_format($itemEstimatedTotal, 2) : '—' }}
                                    </td>
                                    <td class="px-2 py-2 text-right">
                                        {{ $itemActualTotal !== null ? number_format($itemActualTotal, 2) : '—' }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>

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
                                        <div class="mt-1 text-gray-800 whitespace-pre-line">
                                            {{ $review->comments }}
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
    .trip-leg-card {
        break-inside: avoid;
        page-break-inside: avoid;
    }

    .trip-leg-map-wrap {
        width: 100%;
        max-width: 35rem;
        margin: 0 auto;
    }

    .trip-leg-map {
        width: 100%;
        height: 28rem;
    }

    @media print {
        .trip-leg-card {
            break-inside: avoid;
            page-break-inside: avoid;
        }

        .trip-leg-map-wrap {
            max-width: 36rem !important;
            margin: 0 auto !important;
        }

        .trip-leg-map {
            width: 100% !important;
            height: 18rem !important;
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

<style>
    .trip-leg-map {
        height: 18rem;
        width: 100%;
        position: relative;
    }
</style>

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
        const fromLat = parseFloat(element.dataset.fromLat);
        const fromLng = parseFloat(element.dataset.fromLng);
        const toLat = parseFloat(element.dataset.toLat);
        const toLng = parseFloat(element.dataset.toLng);
        const fromName = element.dataset.fromName || 'Start';
        const toName = element.dataset.toName || 'Destination';

        if (
            Number.isNaN(fromLat) ||
            Number.isNaN(fromLng) ||
            Number.isNaN(toLat) ||
            Number.isNaN(toLng)
        ) {
            return;
        }

        const map = L.map(element, {
            scrollWheelZoom: false,
        });

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap contributors',
        }).addTo(map);

        const points = [
            [fromLat, fromLng],
            [toLat, toLng]
        ];

        L.circleMarker(points[0], {
            radius: 7,
            color: '#1d4ed8',
            weight: 2,
            fillColor: '#3b82f6',
            fillOpacity: 1,
        }).addTo(map).bindPopup('From: ' + fromName);

        L.circleMarker(points[1], {
            radius: 7,
            color: '#b91c1c',
            weight: 2,
            fillColor: '#ef4444',
            fillOpacity: 1,
        }).addTo(map).bindPopup('To: ' + toName);

        let activeRouteLayer = null;

        function fitMapToLayer(layer) {
            map.invalidateSize();

            if (fromLat === toLat && fromLng === toLng) {
                map.setView([fromLat, fromLng], 11);
                return;
            }

            map.fitBounds(layer.getBounds(), {
                padding: [24, 24],
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
                fromLat: fromLat,
                fromLng: fromLng,
                toLat: toLat,
                toLng: toLng,
            });
        }

        const routeUrl =
            'https://router.project-osrm.org/route/v1/driving/' +
            fromLng + ',' + fromLat + ';' + toLng + ',' + toLat +
            '?overview=full&geometries=geojson&steps=false';

        fetch(routeUrl)
            .then(function (response) {
                return response.json();
            })
            .then(function (data) {
                if (data.code !== 'Ok' || !data.routes || !data.routes.length) {
                    throw new Error('Route not available');
                }

                const routeGeometry = data.routes[0].geometry;

                activeRouteLayer = L.geoJSON(routeGeometry, {
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
                activeRouteLayer = L.polyline(points, {
                    color: '#2563eb',
                    weight: 4,
                    opacity: 0.85,
                    dashArray: '6,6',
                }).addTo(map);

                fitMapToLayer(activeRouteLayer);
                storeMapForPrint();
            });
    });

    function refreshTripBookMapsForPrint() {
        setTimeout(function () {
            tripBookMaps.forEach(function (entry) {
                const layer = entry.getLayer();

                if (!layer) {
                    return;
                }

                entry.map.invalidateSize();

                if (entry.fromLat === entry.toLat && entry.fromLng === entry.toLng) {
                    entry.map.setView([entry.fromLat, entry.fromLng], 11);
                } else {
                    entry.map.fitBounds(layer.getBounds(), {
                        padding: [24, 24],
                        maxZoom: 10,
                        animate: false,
                    });
                }
            });
        }, 300);
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