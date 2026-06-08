<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Trip Planning
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    {{ $trip->tripname }}
                </p>
                <p class="mt-1 text-sm text-gray-500">
                    Status: {{ ucfirst($trip->tripstatus) }} ·
                    Start: {{ optional($trip->startdate)->format('d M Y') ?? '—' }} ·
                    End: {{ optional($trip->enddate)->format('d M Y') ?? '—' }}
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('trips.planner.generate', ['trip' => $trip->id, 'return_to' => url()->full()]) }}"
                   class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded text-sm hover:bg-indigo-700">
                    Generate Legs & Stays
                </a>

                <a href="{{ route('trips.planner.create', ['trip' => $trip->id]) }}"
                   class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded text-sm hover:bg-blue-700">
                    Add Planning Item
                </a>

                <a href="{{ route('trips.edit', ['trip' => $trip, 'tab' => 'workflow']) }}"
                   class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-800 rounded text-sm hover:bg-gray-300">
                    Back to Trip
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="w-full max-w-none mx-auto px-4 sm:px-6 lg:px-8 xl:px-10 2xl:px-12 space-y-6">
            @include('partials.admin.flash-messages')
            @include('partials.admin.validation-summary')

            <div class="grid grid-cols-1 xl:grid-cols-4 gap-6">
                <div class="xl:col-span-1 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-sm font-semibold text-gray-900">Planner summary</h3>
                    </div>

                    <div class="p-6">
                        <dl class="space-y-3 text-sm">
                            <div class="flex justify-between gap-4">
                                <dt class="text-gray-500">Planning items</dt>
                                <dd class="text-gray-900">{{ $summary['total_items'] }}</dd>
                            </div>
                            <div class="flex justify-between gap-4">
                                <dt class="text-gray-500">Mapped</dt>
                                <dd class="text-gray-900">{{ $summary['mapped_items'] }}</dd>
                            </div>
                            <div class="flex justify-between gap-4">
                                <dt class="text-gray-500">Missing coordinates</dt>
                                <dd class="text-gray-900">{{ $summary['missing_coordinates'] }}</dd>
                            </div>
                            <div class="flex justify-between gap-4">
                                <dt class="text-gray-500">Route anchors</dt>
                                <dd class="text-gray-900">{{ $summary['route_anchors'] }}</dd>
                            </div>
                            <div class="flex justify-between gap-4">
                                <dt class="text-gray-500">Go via</dt>
                                <dd class="text-gray-900">{{ $summary['go_via'] ?? 0 }}</dd>
                            </div>
                            <div class="flex justify-between gap-4">
                                <dt class="text-gray-500">Overnights</dt>
                                <dd class="text-gray-900">{{ $summary['overnights'] }}</dd>
                            </div>
                            <div class="flex justify-between gap-4">
                                <dt class="text-gray-500">Stay targets</dt>
                                <dd class="text-gray-900">{{ $summary['stay_targets'] }}</dd>
                            </div>
                            <div class="flex justify-between gap-4">
                                <dt class="text-gray-500">Linked legs</dt>
                                <dd class="text-gray-900">{{ $summary['generated_legs'] }}</dd>
                            </div>
                            <div class="flex justify-between gap-4">
                                <dt class="text-gray-500">Linked stays</dt>
                                <dd class="text-gray-900">{{ $summary['generated_stays'] }}</dd>
                            </div>
                            <div class="flex justify-between gap-4">
                                <dt class="text-gray-500">Estimated distance</dt>
                                <dd class="text-gray-900">
                                    {{ number_format((float) ($summary['estimated_distance_km'] ?? 0), 1) }} km
                                </dd>
                            </div>
                            <div class="flex justify-between gap-4">
                                <dt class="text-gray-500">Estimated drive time</dt>
                                <dd class="text-gray-900">
                                    {{ $summary['estimated_drive_time_label'] ?? '0 min' }}
                                </dd>
                            </div>
                        </dl>

                        @if($summary['missing_coordinates'] > 0)
                            <div class="mt-4 rounded-md border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-800">
                                Some planning items do not have coordinates, so the map and leg generation will be incomplete until linked records are geocoded.
                            </div>
                        @endif
                    </div>
                </div>

                <div class="xl:col-span-3 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-sm font-semibold text-gray-900">Planner map</h3>
                        <p class="mt-1 text-xs text-gray-500">
                            Ordered points are drawn in sequence. Anchors, go-via points, overnight points, and stay targets are emphasised to support leg planning.
                        </p>
                    </div>

                    <div class="p-6">
                        <div id="trip-planner-map" class="w-full h-[380px] xl:h-[520px] rounded-lg border border-gray-200"></div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900">Leg preview</h3>
                        <p class="mt-1 text-xs text-gray-500">
                            Candidate legs are built from route anchors and other valid boundary planning rows in sequence order.
                        </p>
                    </div>

                    <a href="{{ route('trips.planner.generate', ['trip' => $trip->id, 'return_to' => url()->full()]) }}"
                       class="inline-flex items-center px-3 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 text-xs">
                        Open generation preview
                    </a>
                </div>

                <div class="p-6 space-y-6">
                    @if($candidateLegs->isEmpty())
                        <div class="rounded-md border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                            No candidate legs yet. Add at least two valid boundary rows, usually route anchors, places, destinations, or overnight stops.
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left font-semibold text-gray-600">From</th>
                                        <th class="px-4 py-3 text-left font-semibold text-gray-600">To</th>
                                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Sequence</th>
                                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Dates</th>
                                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Road est.</th>
                                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Drive est.</th>
                                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Map readiness</th>
                                        <th class="px-4 py-3 text-right font-semibold text-gray-600">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 bg-white">
                                    @foreach($candidateLegs as $leg)
                                        <tr>
                                            <td class="px-4 py-3 align-top">
                                                <div class="font-medium text-gray-900">{{ $leg['from_label'] }}</div>
                                            </td>
                                            <td class="px-4 py-3 align-top">
                                                <div class="font-medium text-gray-900">{{ $leg['to_label'] }}</div>
                                            </td>
                                            <td class="px-4 py-3 align-top text-gray-700 whitespace-nowrap">
                                                {{ $leg['from_sequence'] }} → {{ $leg['to_sequence'] }}
                                            </td>
                                            <td class="px-4 py-3 align-top text-gray-700 whitespace-nowrap">
                                                {{ $leg['start_date'] ?? '—' }} → {{ $leg['end_date'] ?? '—' }}
                                            </td>
                                            <td class="px-4 py-3 align-top">
                                                @if($leg['estimated_road_km'] !== null)
                                                    <div class="text-gray-900 whitespace-nowrap">
                                                        {{ number_format($leg['estimated_road_km'], 1) }} km
                                                    </div>
                                                    <div class="text-xs text-gray-500 whitespace-nowrap">
                                                        Straight line {{ number_format($leg['straight_line_km'], 1) }} km
                                                    </div>
                                                @else
                                                    <span class="text-gray-400">—</span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 align-top text-gray-700 whitespace-nowrap">
                                                {{ $leg['estimated_time_label'] ?? '—' }}
                                            </td>
                                            <td class="px-4 py-3 align-top">
                                                @if($leg['from_has_coordinates'] && $leg['to_has_coordinates'])
                                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-green-100 text-green-800 text-xs font-medium">
                                                        Ready
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-amber-100 text-amber-800 text-xs font-medium">
                                                        Missing coordinates
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 align-top">
                                                <div class="flex items-center justify-end gap-2">
                                                    <button type="button"
                                                            class="inline-flex items-center px-3 py-1.5 bg-gray-100 text-gray-800 rounded hover:bg-gray-200 text-xs js-focus-leg-range"
                                                            data-from-item-id="{{ $leg['from_item_id'] }}"
                                                            data-to-item-id="{{ $leg['to_item_id'] }}">
                                                        Focus
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif

                    <div class="border-t border-gray-200 pt-6">
                        <div class="flex items-center justify-between gap-4 mb-3">
                            <div>
                                <h4 class="text-sm font-semibold text-gray-900">Boundary rows</h4>
                                <p class="mt-1 text-xs text-gray-500">
                                    These planning rows currently define where generated legs begin and end.
                                </p>
                            </div>
                        </div>

                        @if($candidateLegBoundaries->isEmpty())
                            <p class="text-sm text-gray-500">No boundaries identified yet.</p>
                        @else
                            <div class="flex flex-wrap gap-2">
                                @foreach($candidateLegBoundaries as $boundary)
                                    <button type="button"
                                            class="inline-flex items-center gap-2 rounded-full border border-gray-300 bg-gray-50 px-3 py-2 text-left hover:bg-gray-100 js-focus-single-row"
                                            data-plan-item-id="{{ $boundary->id }}">
                                        <span class="inline-flex items-center justify-center min-w-[1.75rem] h-7 px-2 rounded-full bg-white border border-gray-200 text-xs font-semibold text-gray-700">
                                            {{ $boundary->sequence_no }}
                                        </span>
                                        <span class="text-xs text-gray-800">
                                            {{ $boundary->display_title }}
                                        </span>
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900">Planning sequence</h3>
                        <p class="mt-1 text-xs text-gray-500">
                            Drag rows by the handle to reorder. You can also update dates and planner flags here.
                        </p>
                    </div>

                    <div class="flex items-center gap-2">
                        <form method="POST" action="{{ route('trips.planner.renumber', $trip) }}">
                            @csrf
                            <button type="submit"
                                    class="inline-flex items-center px-3 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 text-xs">
                                Renumber Sequentially
                            </button>
                        </form>

                        <div class="text-xs text-gray-500">
                            {{ $planItems->count() }} item{{ $planItems->count() === 1 ? '' : 's' }}
                        </div>
                    </div>
                </div>

                <div id="planner-reorder-config"
                     data-reorder-url="{{ route('trips.planner.reorder', $trip) }}"
                     data-csrf-token="{{ csrf_token() }}"></div>

                <form method="POST" action="{{ route('trips.planner.bulk-update', $trip) }}">
                    @csrf

                    <div class="p-6">
                        @if($planItems->isEmpty())
                            <p class="text-sm text-gray-500">
                                No planning items have been added yet.
                            </p>
                        @else
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 text-sm">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-3 py-2 text-left font-semibold text-gray-600 w-10"></th>
                                            <th class="px-3 py-2 text-left font-semibold text-gray-600">Seq</th>
                                            <th class="px-3 py-2 text-left font-semibold text-gray-600">Type</th>
                                            <th class="px-3 py-2 text-left font-semibold text-gray-600">Title</th>
                                            <th class="px-3 py-2 text-left font-semibold text-gray-600">Place</th>
                                            <th class="px-3 py-2 text-left font-semibold text-gray-600">Destination</th>
                                            <th class="px-3 py-2 text-left font-semibold text-gray-600">Start</th>
                                            <th class="px-3 py-2 text-left font-semibold text-gray-600">End</th>
                                            <th class="px-3 py-2 text-left font-semibold text-gray-600">Flags</th>
                                            <th class="px-3 py-2 text-left font-semibold text-gray-600">Map</th>
                                            <th class="px-3 py-2 text-left font-semibold text-gray-600">Outputs</th>
                                            <th class="px-3 py-2 text-right font-semibold text-gray-600">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="planner-table-body" class="divide-y divide-gray-100 bg-white">
                                        @foreach($planItems as $item)
                                            @php
                                                $resolvedPlace = $item->place
                                                    ?? $item->destinationItem?->place
                                                    ?? $item->destinationItem?->destination?->place
                                                    ?? $item->destination?->place;

                                                $resolvedDestination = $item->destination
                                                    ?? $item->destinationItem?->destination;

                                                $resolvedDestinationItem = $item->destinationItem;

                                                $lat = $resolvedDestinationItem->latitude
                                                    ?? $resolvedDestination?->latitude
                                                    ?? $resolvedPlace?->latitude;

                                                $lng = $resolvedDestinationItem->longitude
                                                    ?? $resolvedDestination?->longitude
                                                    ?? $resolvedPlace?->longitude;

                                                $hasCoordinates = !is_null($lat) && !is_null($lng);
                                            @endphp

                                            <tr id="planner-row-{{ $item->id }}" data-plan-row-id="{{ $item->id }}">
                                                <td class="px-3 py-3 align-top text-gray-400">
                                                    <button type="button"
                                                            class="js-drag-handle cursor-move inline-flex items-center justify-center w-6 h-6 rounded hover:bg-gray-100"
                                                            title="Drag to reorder"
                                                            aria-label="Drag to reorder">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor">
                                                            <path d="M7 4a1.5 1.5 0 110 3 1.5 1.5 0 010-3zm6 0a1.5 1.5 0 110 3 1.5 1.5 0 010-3zM7 8.5a1.5 1.5 0 110 3 1.5 1.5 0 010-3zm6 0a1.5 1.5 0 110 3 1.5 1.5 0 010-3zM7 13a1.5 1.5 0 110 3 1.5 1.5 0 010-3zm6 0a1.5 1.5 0 110 3 1.5 1.5 0 010-3z"/>
                                                        </svg>
                                                    </button>
                                                </td>

                                                <td class="px-3 py-3 align-top">
                                                    <input type="hidden" name="items[{{ $item->id }}][id]" value="{{ $item->id }}">
                                                    <input type="number"
                                                           min="1"
                                                           name="items[{{ $item->id }}][sequence_no]"
                                                           value="{{ old("items.{$item->id}.sequence_no", $item->sequence_no) }}"
                                                           class="w-20 rounded-md border-gray-300 shadow-sm text-sm">
                                                </td>

                                                <td class="px-3 py-3 align-top text-gray-700 whitespace-nowrap">
                                                    {{ $planTypeOptions[$item->plantype] ?? $item->plantype }}
                                                </td>

                                                <td class="px-3 py-3 align-top min-w-[220px]">
                                                    <div class="font-medium text-gray-900">
                                                        <a href="{{ route('trips.planner.edit', [
                                                            'trip' => $trip->id,
                                                            'tripPlanItem' => $item->id,
                                                            'return_to' => url()->full(),
                                                        ]) }}"
                                                           class="text-blue-700 hover:text-blue-900 hover:underline">
                                                            {{ $item->display_title }}
                                                        </a>
                                                    </div>

                                                    @if($resolvedDestinationItem)
                                                        <div class="mt-1 text-xs text-gray-500">
                                                            Item: {{ $resolvedDestinationItem->itemname }}
                                                        </div>
                                                    @endif

                                                    @if($item->notes)
                                                        <div class="mt-1 text-xs text-gray-500 line-clamp-2">
                                                            {{ $item->notes }}
                                                        </div>
                                                    @endif
                                                </td>

                                                <td class="px-3 py-3 align-top text-gray-700 whitespace-nowrap">
                                                    {{ $resolvedPlace?->placename ?? '—' }}
                                                </td>

                                                <td class="px-3 py-3 align-top text-gray-700 whitespace-nowrap">
                                                    {{ $resolvedDestination?->destinationname ?? '—' }}
                                                </td>

                                                <td class="px-3 py-3 align-top">
                                                    <input type="date"
                                                           name="items[{{ $item->id }}][planneddate]"
                                                           value="{{ old("items.{$item->id}.planneddate", optional($item->planneddate)->format('Y-m-d')) }}"
                                                           class="w-40 rounded-md border-gray-300 shadow-sm text-sm">
                                                </td>

                                                <td class="px-3 py-3 align-top">
                                                    <input type="date"
                                                           name="items[{{ $item->id }}][plannedenddate]"
                                                           value="{{ old("items.{$item->id}.plannedenddate", optional($item->plannedenddate)->format('Y-m-d')) }}"
                                                           class="w-40 rounded-md border-gray-300 shadow-sm text-sm">
                                                </td>

                                                <td class="px-3 py-3 align-top">
                                                    <div class="space-y-1.5 text-xs min-w-[140px]">
                                                        <label class="flex items-center gap-2">
                                                            <input type="hidden" name="items[{{ $item->id }}][isrouteanchor]" value="0">
                                                            <input type="checkbox"
                                                                name="items[{{ $item->id }}][isrouteanchor]"
                                                                value="1"
                                                                class="rounded border-gray-300 text-blue-600 shadow-sm"
                                                                @checked(old("items.{$item->id}.isrouteanchor", $item->isrouteanchor))>
                                                            <span class="text-gray-700">Anchor</span>
                                                        </label>

                                                        <label class="flex items-center gap-2">
                                                            <input type="hidden" name="items[{{ $item->id }}][isgovia]" value="0">
                                                            <input type="checkbox"
                                                                name="items[{{ $item->id }}][isgovia]"
                                                                value="1"
                                                                class="rounded border-gray-300 text-blue-600 shadow-sm"
                                                                @checked(old("items.{$item->id}.isgovia", $item->isgovia))>
                                                            <span class="text-gray-700">Go via</span>
                                                        </label>

                                                        <label class="flex items-center gap-2">
                                                            <input type="hidden" name="items[{{ $item->id }}][isovernight]" value="0">
                                                            <input type="checkbox"
                                                                name="items[{{ $item->id }}][isovernight]"
                                                                value="1"
                                                                class="rounded border-gray-300 text-blue-600 shadow-sm"
                                                                @checked(old("items.{$item->id}.isovernight", $item->isovernight))>
                                                            <span class="text-gray-700">Overnight</span>
                                                        </label>

                                                        <label class="flex items-center gap-2">
                                                            <input type="hidden" name="items[{{ $item->id }}][isstaytarget]" value="0">
                                                            <input type="checkbox"
                                                                name="items[{{ $item->id }}][isstaytarget]"
                                                                value="1"
                                                                class="rounded border-gray-300 text-blue-600 shadow-sm"
                                                                @checked(old("items.{$item->id}.isstaytarget", $item->isstaytarget))>
                                                            <span class="text-gray-700">Stay target</span>
                                                        </label>
                                                    </div>
                                                </td>

                                                <td class="px-3 py-3 align-top">
                                                    @if($hasCoordinates)
                                                        <button type="button"
                                                                class="inline-flex items-center px-2.5 py-1 rounded-full bg-green-100 text-green-800 text-xs font-medium js-focus-map-item"
                                                                data-plan-item-id="{{ $item->id }}">
                                                            On map
                                                        </button>
                                                    @else
                                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-amber-100 text-amber-800 text-xs font-medium">
                                                            Missing
                                                        </span>
                                                    @endif
                                                </td>

                                                <td class="px-3 py-2 text-sm text-slate-700 align-top">
                                                    <div class="space-y-1">
                                                        {{-- Leg --}}
                                                        <div>
                                                            @if ($item->tripLeg)
                                                                <span class="font-medium">Leg:</span>
                                                                <a
                                                                    href="{{ route('trips.legs.edit', [$trip, $item->tripLeg]) }}"
                                                                    class="text-sky-700 hover:text-sky-900 underline"
                                                                >
                                                                    {{ $item->tripLeg->legnumber ? 'Leg '.$item->tripLeg->legnumber : 'Leg '.$item->tripLeg->id }}
                                                                </a>
                                                                <div class="text-xs text-slate-500">
                                                                    {{ $item->tripLeg->title ?: '—' }}
                                                                </div>
                                                            @else
                                                                <span class="font-medium">Leg:</span> —
                                                            @endif
                                                        </div>

                                                        <div>
                                                        @if ($item->tripStay)
                                                            <span class="font-medium">Stay:</span>
                                                            <a
                                                                href="{{ route('trips.stays.edit', [$trip, $item->tripStay]) }}"
                                                                class="text-sky-700 hover:text-sky-900 underline"
                                                            >
                                                                {{ $item->tripStay->stayname ?: 'Stay '.$item->tripStay->id }}
                                                            </a>
                                                            <div class="text-xs text-slate-500">
                                                                {{ (int) ($item->tripStay->nights ?? 0) }} night{{ (int) ($item->tripStay->nights ?? 0) === 1 ? '' : 's' }}
                                                            </div>
                                                        @elseif (!is_null($item->nightsplanned))
                                                            <span class="font-medium">Stay:</span>
                                                            <span class="text-slate-700">Planned</span>
                                                            <div class="text-xs text-slate-500">
                                                                {{ (int) $item->nightsplanned }} night{{ (int) $item->nightsplanned === 1 ? '' : 's' }}
                                                            </div>
                                                        @else
                                                            <span class="font-medium">Stay:</span> —
                                                        @endif
                                                    </div>
                                                </td>

                                                <td class="px-3 py-3 align-top">
                                                    <div class="flex items-center justify-end gap-2">
                                                        <a href="{{ route('trips.planner.edit', [
                                                            'trip' => $trip->id,
                                                            'tripPlanItem' => $item->id,
                                                            'return_to' => url()->full(),
                                                        ]) }}"
                                                           class="inline-flex items-center px-3 py-1.5 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 text-xs">
                                                            Edit
                                                        </a>

                                                        <button type="button"
                                                                class="inline-flex items-center px-3 py-1.5 bg-red-100 text-red-700 rounded hover:bg-red-200 text-xs js-delete-trip-plan-item"
                                                                data-name="{{ $item->title ?: 'this planning item' }}"
                                                                data-action="{{ route('trips.planner.destroy', ['trip' => $trip->id, 'tripPlanItem' => $item->id]) }}"
                                                                data-return-to="{{ url()->full() }}">
                                                            Delete
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-4 flex justify-end gap-3">
                                <a href="{{ route('trips.planner.create', ['trip' => $trip->id]) }}"
                                   class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">
                                    Add Planning Item
                                </a>

                                <button type="submit"
                                        class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-sm">
                                    Update Planner
                                </button>
                            </div>
                        @endif
                    </div>
                </form>
            </div>

            <form method="POST" id="delete-trip-plan-item-form" class="hidden">
                @csrf
                @method('DELETE')
                <input type="hidden" name="return_to" id="delete-trip-plan-item-return-to" value="">
            </form>

            @if($showCreate)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-sm font-semibold text-gray-900">Add Planning Item</h3>
                        <p class="mt-1 text-xs text-gray-500">
                            Use this for one-off additions and optionally to add related destination items as separate rows.
                        </p>
                    </div>

                    <form method="POST" action="{{ route('trips.planner.store', $trip) }}" class="p-6 space-y-6">
                        @csrf
                        @include('trip-planner._form', [
                            'tripPlanItem' => new \App\Models\TripPlanItem([
                                'plantype' => $selectedPlanType,
                                'placeid' => $selectedPlaceId,
                                'destinationid' => $selectedDestinationId,
                                'destinationitemid' => $selectedDestinationItemId,
                            ]),
                            'trip' => $trip,
                            'places' => $places,
                            'destinations' => $destinations,
                            'destinationItems' => $destinationItems,
                            'tripLegs' => collect(),
                            'tripStays' => collect(),
                            'planTypeOptions' => $planTypeOptions,
                            'stayTypeOptions' => $stayTypeOptions,
                            'returnTo' => route('trips.planner.index', $trip),
                        ])
                    </form>
                </div>
            @endif
        </div>
    </div>

    <link rel="stylesheet"
          href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
          integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
          crossorigin="" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
            integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
            crossorigin=""></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const mapItems = @json($mapItems);
            const mappableItems = mapItems.filter(item => item.latitude !== null && item.longitude !== null);
            const mapEl = document.getElementById('trip-planner-map');
            const rowLookup = new Map();
            const markerLookup = new Map();

            document.querySelectorAll('[data-plan-row-id]').forEach(row => {
                rowLookup.set(String(row.dataset.planRowId), row);
            });

            function flashRow(row) {
                if (!row) return;

                row.scrollIntoView({ behavior: 'smooth', block: 'center' });
                row.classList.add('bg-blue-50');

                setTimeout(() => {
                    row.classList.remove('bg-blue-50');
                }, 1800);
            }

            if (mapEl && mappableItems.length) {
                const map = L.map('trip-planner-map');

                L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener noreferrer">OpenStreetMap</a>',
                }).addTo(map);

                const latLngs = [];
                const bounds = [];

                mappableItems.forEach(item => {
                    const latLng = [item.latitude, item.longitude];
                    latLngs.push(latLng);
                    bounds.push(latLng);

                    let markerColor = '#2563eb';
                    if (item.isgovia) markerColor = '#0f766e';
                    if (item.isovernight) markerColor = '#16a34a';
                    if (item.isstaytarget) markerColor = '#7c3aed';
                    if (item.isrouteanchor) markerColor = '#dc2626';

                    const marker = L.circleMarker(latLng, {
                        radius: item.isrouteanchor ? 9 : 7,
                        color: markerColor,
                        weight: 2,
                        fillColor: markerColor,
                        fillOpacity: 0.85,
                    }).addTo(map);

                    marker.bindPopup(`
                        <div class="text-sm">
                            <div><strong>${item.sequence_no ?? ''} · ${item.title ?? 'Planning item'}</strong></div>
                            <div>${item.place_name ?? '—'}${item.destination_name ? ' · ' + item.destination_name : ''}</div>
                            <div>${item.planneddate ?? 'No date'}</div>
                            <div>
                                ${item.isrouteanchor ? 'Anchor ' : ''}
                                ${item.isgovia ? 'Go Via ' : ''}
                                ${item.isovernight ? 'Overnight ' : ''}
                                ${item.isstaytarget ? 'Stay Target' : ''}
                            </div>
                        </div>
                    `);

                    marker.on('click', function () {
                        const row = rowLookup.get(String(item.id));
                        flashRow(row);
                    });

                    markerLookup.set(String(item.id), marker);
                });

                if (latLngs.length > 1) {
                    L.polyline(latLngs, {
                        color: '#0f766e',
                        weight: 3,
                        opacity: 0.7,
                    }).addTo(map);
                }

                map.fitBounds(bounds, { padding: [30, 30] });

                document.querySelectorAll('.js-focus-map-item').forEach(button => {
                    button.addEventListener('click', function () {
                        const planItemId = this.dataset.planItemId;
                        const marker = markerLookup.get(String(planItemId));
                        if (!marker) return;

                        map.setView(marker.getLatLng(), Math.max(map.getZoom(), 10), { animate: true });
                        marker.openPopup();

                        const row = rowLookup.get(String(planItemId));
                        flashRow(row);
                    });
                });

                document.querySelectorAll('.js-focus-single-row').forEach(button => {
                    button.addEventListener('click', function () {
                        const planItemId = this.dataset.planItemId;
                        const row = rowLookup.get(String(planItemId));
                        flashRow(row);

                        const marker = markerLookup.get(String(planItemId));
                        if (marker) {
                            map.setView(marker.getLatLng(), Math.max(map.getZoom(), 10), { animate: true });
                            marker.openPopup();
                        }
                    });
                });

                document.querySelectorAll('.js-focus-leg-range').forEach(button => {
                    button.addEventListener('click', function () {
                        const fromItemId = this.dataset.fromItemId;
                        const toItemId = this.dataset.toItemId;

                        const fromRow = rowLookup.get(String(fromItemId));
                        const toRow = rowLookup.get(String(toItemId));

                        flashRow(fromRow);

                        if (toRow && toRow !== fromRow) {
                            setTimeout(() => {
                                flashRow(toRow);
                            }, 500);
                        }

                        const fromMarker = markerLookup.get(String(fromItemId));
                        const toMarker = markerLookup.get(String(toItemId));

                        if (fromMarker && toMarker) {
                            const legBounds = L.latLngBounds([
                                fromMarker.getLatLng(),
                                toMarker.getLatLng()
                            ]);
                            map.fitBounds(legBounds, { padding: [40, 40] });
                        } else if (fromMarker) {
                            map.setView(fromMarker.getLatLng(), Math.max(map.getZoom(), 10), { animate: true });
                            fromMarker.openPopup();
                        } else if (toMarker) {
                            map.setView(toMarker.getLatLng(), Math.max(map.getZoom(), 10), { animate: true });
                            toMarker.openPopup();
                        }
                    });
                });
            } else if (mapEl) {
                mapEl.innerHTML = `
                    <div class="h-full flex items-center justify-center rounded-lg bg-gray-50 text-sm text-gray-500">
                        No mappable planning items yet. Add coordinates to linked places, destinations, or destination items.
                    </div>
                `;
            }

            const deleteForm = document.getElementById('delete-trip-plan-item-form');
            const returnToInput = document.getElementById('delete-trip-plan-item-return-to');

            document.querySelectorAll('.js-delete-trip-plan-item').forEach(button => {
                button.addEventListener('click', function () {
                    const action = this.dataset.action;
                    const name = this.dataset.name || 'this planning item';
                    const returnTo = this.dataset.returnTo || '';

                    if (!action) return;
                    if (!confirm(`Delete ${name}? This cannot be undone.`)) return;

                    deleteForm.action = action;
                    returnToInput.value = returnTo;
                    deleteForm.submit();
                });
            });

            const reorderConfig = document.getElementById('planner-reorder-config');
            const plannerTableBody = document.getElementById('planner-table-body');

            if (reorderConfig && plannerTableBody && typeof Sortable !== 'undefined') {
                const reorderUrl = reorderConfig.dataset.reorderUrl;
                const csrfToken = reorderConfig.dataset.csrfToken;
                let isSavingOrder = false;

                Sortable.create(plannerTableBody, {
                    animation: 150,
                    handle: '.js-drag-handle',
                    ghostClass: 'bg-blue-50',
                    chosenClass: 'bg-blue-50',
                    dragClass: 'opacity-75',
                    onEnd: function () {
                        if (isSavingOrder) {
                            return;
                        }

                        const orderedIds = Array.from(plannerTableBody.querySelectorAll('tr[data-plan-row-id]'))
                            .map(row => row.dataset.planRowId)
                            .filter(Boolean);

                        if (!orderedIds.length) {
                            return;
                        }

                        isSavingOrder = true;

                        fetch(reorderUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({
                                ordered_ids: orderedIds,
                            }),
                        })
                        .then(async response => {
                            if (!response.ok) {
                                const data = await response.json().catch(() => ({}));
                                throw new Error(data.message || 'Unable to save planner order.');
                            }

                            return response.json();
                        })
                        .then(() => {
                            window.location.reload();
                        })
                        .catch(error => {
                            alert(error.message || 'Unable to save planner order.');
                            window.location.reload();
                        })
                        .finally(() => {
                            isSavingOrder = false;
                        });
                    }
                });
            }
        });
    </script>

@pushOnce('scripts')
    @include('partials.forms.markdown-field-scripts')
@endPushOnce
</x-app-layout>


