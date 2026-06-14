<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Generate Trip Outputs - {{ $trip->tripname }}
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    Review the proposed Trip Legs, Leg Points, Trip Stays, and Trip Items and Activities before replacing existing generated outputs.
                </p>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ $returnTo }}"
                   class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 text-sm">
                    Back to Planner
                </a>
            </div>
        </div>
    </x-slot>

    @php
        $resolvePlaceName = function ($item) {
            return $item->place?->placename
                ?? $item->destinationItem?->place?->placename
                ?? $item->destinationItem?->destination?->place?->placename
                ?? $item->destination?->place?->placename
                ?? '—';
        };

        $resolveDestinationName = function ($item) {
            return $item->destination?->destinationname
                ?? $item->destinationItem?->destination?->destinationname
                ?? '—';
        };

        $resolveDestinationItemName = function ($item) {
            return $item->destinationItem?->itemname ?? '—';
        };

        $resolveLinkedLabel = function ($item) use ($resolvePlaceName, $resolveDestinationName, $resolveDestinationItemName) {
            $destinationItemName = $resolveDestinationItemName($item);
            if ($destinationItemName !== '—') {
                return $destinationItemName;
            }

            $destinationName = $resolveDestinationName($item);
            if ($destinationName !== '—') {
                return $destinationName;
            }

            return $resolvePlaceName($item);
        };

        $resolveFlags = function ($item) {
            return collect([
                $item->isrouteanchor ? 'Route anchor' : null,
                $item->isgovia ? 'Go via' : null,
                $item->isovernight ? 'Overnight' : null,
                $item->isstaytarget ? 'Stay target' : null,
            ])->filter()->values();
        };
    @endphp

    <div class="py-6">
        <div class="w-full max-w-none mx-auto px-4 sm:px-6 lg:px-8 xl:px-10 2xl:px-12 space-y-6">
            @include('partials.admin.flash-messages')
            @include('partials.admin.validation-summary')

            <div class="grid grid-cols-1 xl:grid-cols-4 gap-6">
                <div class="xl:col-span-3 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-sm font-semibold text-gray-900">Planner sequence summary</h3>
                        <p class="mt-1 text-xs text-gray-500">
                            These planning items are the source for proposed generated records.
                        </p>
                    </div>

                    <div class="p-6">
                        @if($planItems->isEmpty())
                            <p class="text-sm text-gray-500">
                                No planning items are available for this trip.
                            </p>
                        @else
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 text-sm">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-3 py-2 text-left font-semibold text-gray-600">Seq</th>
                                            <th class="px-3 py-2 text-left font-semibold text-gray-600">Type</th>
                                            <th class="px-3 py-2 text-left font-semibold text-gray-600">Title</th>
                                            <th class="px-3 py-2 text-left font-semibold text-gray-600">Date</th>
                                            <th class="px-3 py-2 text-left font-semibold text-gray-600">Flags</th>
                                            <th class="px-3 py-2 text-left font-semibold text-gray-600">Linked</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 bg-white">
                                        @foreach($planItems as $item)
                                            @php
                                                $flags = $resolveFlags($item)->implode(', ');
                                            @endphp
                                            <tr>
                                                <td class="px-3 py-3 align-top text-gray-700">{{ $item->sequence_no }}</td>
                                                <td class="px-3 py-3 align-top text-gray-700">{{ $item->plantype }}</td>
                                                <td class="px-3 py-3 align-top">
                                                    <div class="font-medium text-gray-900">{{ $item->display_title }}</div>
                                                    @if(!empty($item->notes))
                                                        <div class="mt-1 text-xs text-gray-500 whitespace-pre-line">{{ $item->notes }}</div>
                                                    @endif
                                                </td>
                                                <td class="px-3 py-3 align-top text-gray-700">
                                                    {{ optional($item->planneddate)->format('Y-m-d') ?: '—' }}
                                                </td>
                                                <td class="px-3 py-3 align-top text-xs text-gray-500">
                                                    {{ $flags ?: '—' }}
                                                </td>
                                                <td class="px-3 py-3 align-top text-gray-700">
                                                    {{ $resolveLinkedLabel($item) }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-sm font-semibold text-gray-900">Preview summary</h3>
                    </div>
                    <div class="p-6 text-sm">
                        <dl class="space-y-3">
                            <div class="flex justify-between gap-4">
                                <dt class="text-gray-500">Planning items</dt>
                                <dd class="text-gray-900">{{ $planItems->count() }}</dd>
                            </div>
                            <div class="flex justify-between gap-4">
                                <dt class="text-gray-500">Leg boundaries</dt>
                                <dd class="text-gray-900">{{ $candidateLegBoundaries->count() }}</dd>
                            </div>
                            <div class="flex justify-between gap-4">
                                <dt class="text-gray-500">Proposed legs</dt>
                                <dd class="text-gray-900">{{ $candidateLegs->count() }}</dd>
                            </div>
                            <div class="flex justify-between gap-4">
                                <dt class="text-gray-500">Proposed leg points</dt>
                                <dd class="text-gray-900">{{ $candidateLegPoints->count() }}</dd>
                            </div>
                            <div class="flex justify-between gap-4">
                                <dt class="text-gray-500">Proposed stays</dt>
                                <dd class="text-gray-900">{{ $candidateStayItems->count() }}</dd>
                            </div>
                            <div class="flex justify-between gap-4">
                                <dt class="text-gray-500">Proposed trip items</dt>
                                <dd class="text-gray-900">{{ $candidateTripItems->count() }}</dd>
                            </div>
                            <div class="flex justify-between gap-4">
                                <dt class="text-gray-500">Existing legs</dt>
                                <dd class="text-gray-900">{{ $existingLegs->count() }}</dd>
                            </div>
                            <div class="flex justify-between gap-4">
                                <dt class="text-gray-500">Existing stays</dt>
                                <dd class="text-gray-900">{{ $existingStays->count() }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-sm font-semibold text-gray-900">Trip Legs</h3>
                    <p class="mt-1 text-xs text-gray-500">
                        Proposed trip legs generated from planner stay boundaries and movement between planned locations.
                    </p>
                </div>

                <div class="p-6">
                    @if($candidateLegs->isEmpty())
                        <p class="text-sm text-gray-500">
                            No Trip Legs can be proposed yet.
                        </p>
                    @else
                        <div class="space-y-4">
                            @foreach($candidateLegs as $index => $leg)
                                @php
                                    $fromItem = $leg['from_item'];
                                    $toItem = $leg['to_item'];
                                    $fromFlags = $resolveFlags($fromItem)->implode(', ');
                                    $toFlags = $resolveFlags($toItem)->implode(', ');
                                @endphp

                                <div class="border border-gray-200 rounded-lg overflow-hidden">
                                    <div class="px-4 py-3 bg-gray-50 border-b border-gray-200 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
                                        <div>
                                            <div class="text-sm font-semibold text-gray-900">
                                                Leg {{ $index + 1 }} — {{ $leg['from_label'] }} → {{ $leg['to_label'] }}
                                            </div>
                                            <div class="mt-1 text-xs text-gray-500">
                                                {{ $leg['leg_kind'] ?? 'Generated leg' }}
                                                @if(!empty($leg['start_date']) || !empty($leg['end_date']))
                                                    · {{ optional($leg['start_date'])->format('d M Y') ?: '—' }}
                                                    to
                                                    {{ optional($leg['end_date'])->format('d M Y') ?: '—' }}
                                                @endif
                                            </div>
                                        </div>

                                        <div class="flex flex-wrap gap-2 text-xs">
                                            @if(!empty($leg['estimated_road_km']))
                                                <span class="inline-flex items-center px-2 py-1 rounded bg-blue-50 text-blue-700">
                                                    {{ number_format((float) $leg['estimated_road_km'], 1) }} km est.
                                                </span>
                                            @endif
                                            @if(!empty($leg['estimated_time_label']))
                                                <span class="inline-flex items-center px-2 py-1 rounded bg-green-50 text-green-700">
                                                    {{ $leg['estimated_time_label'] }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="p-4 grid grid-cols-1 xl:grid-cols-2 gap-4">
                                        <div class="rounded-lg border border-gray-200 p-4 bg-white">
                                            <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">From</div>
                                            <div class="mt-2 text-sm font-medium text-gray-900">
                                                {{ $fromItem->display_title }}
                                            </div>
                                            <div class="mt-1 text-sm text-gray-600">
                                                Seq {{ $fromItem->sequence_no }} · {{ $fromItem->plantype }}
                                            </div>
                                            <div class="mt-1 text-sm text-gray-600">
                                                Place: {{ $resolvePlaceName($fromItem) }}
                                            </div>
                                            <div class="mt-1 text-sm text-gray-600">
                                                Destination: {{ $resolveDestinationName($fromItem) }}
                                            </div>
                                            <div class="mt-1 text-sm text-gray-600">
                                                Destination Item: {{ $resolveDestinationItemName($fromItem) }}
                                            </div>
                                            <div class="mt-1 text-sm text-gray-600">
                                                Date: {{ optional($fromItem->planneddate)->format('d M Y') ?: '—' }}
                                            </div>
                                            <div class="mt-1 text-xs text-gray-500">
                                                Flags: {{ $fromFlags ?: '—' }}
                                            </div>
                                            @if(!empty($fromItem->notes))
                                                <div class="mt-2 text-xs text-gray-500 whitespace-pre-line">{{ $fromItem->notes }}</div>
                                            @endif
                                        </div>

                                        <div class="rounded-lg border border-gray-200 p-4 bg-white">
                                            <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">To</div>
                                            <div class="mt-2 text-sm font-medium text-gray-900">
                                                {{ $toItem->display_title }}
                                            </div>
                                            <div class="mt-1 text-sm text-gray-600">
                                                Seq {{ $toItem->sequence_no }} · {{ $toItem->plantype }}
                                            </div>
                                            <div class="mt-1 text-sm text-gray-600">
                                                Place: {{ $resolvePlaceName($toItem) }}
                                            </div>
                                            <div class="mt-1 text-sm text-gray-600">
                                                Destination: {{ $resolveDestinationName($toItem) }}
                                            </div>
                                            <div class="mt-1 text-sm text-gray-600">
                                                Destination Item: {{ $resolveDestinationItemName($toItem) }}
                                            </div>
                                            <div class="mt-1 text-sm text-gray-600">
                                                Date: {{ optional($toItem->planneddate)->format('d M Y') ?: '—' }}
                                            </div>
                                            <div class="mt-1 text-xs text-gray-500">
                                                Flags: {{ $toFlags ?: '—' }}
                                            </div>
                                            @if(!empty($toItem->notes))
                                                <div class="mt-2 text-xs text-gray-500 whitespace-pre-line">{{ $toItem->notes }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-sm font-semibold text-gray-900">Leg Points</h3>
                    <p class="mt-1 text-xs text-gray-500">
                        Planner items that should sit on a leg as go-via points or waypoints rather than become separate legs or stays.
                    </p>
                </div>

                <div class="p-6">
                    @if($candidateLegPoints->isEmpty())
                        <p class="text-sm text-gray-500">
                            No proposed Leg Points were found.
                        </p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-3 py-2 text-left font-semibold text-gray-600">Seq</th>
                                        <th class="px-3 py-2 text-left font-semibold text-gray-600">Type</th>
                                        <th class="px-3 py-2 text-left font-semibold text-gray-600">Title</th>
                                        <th class="px-3 py-2 text-left font-semibold text-gray-600">Date</th>
                                        <th class="px-3 py-2 text-left font-semibold text-gray-600">Place</th>
                                        <th class="px-3 py-2 text-left font-semibold text-gray-600">Linked</th>
                                        <th class="px-3 py-2 text-left font-semibold text-gray-600">Flags</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 bg-white">
                                    @foreach($candidateLegPoints as $item)
                                        <tr>
                                            <td class="px-3 py-3 align-top text-gray-700">{{ $item->sequence_no }}</td>
                                            <td class="px-3 py-3 align-top text-gray-700">{{ $item->plantype }}</td>
                                            <td class="px-3 py-3 align-top">
                                                <div class="font-medium text-gray-900">{{ $item->display_title }}</div>
                                                @if(!empty($item->notes))
                                                    <div class="mt-1 text-xs text-gray-500 whitespace-pre-line">{{ $item->notes }}</div>
                                                @endif
                                            </td>
                                            <td class="px-3 py-3 align-top text-gray-700">
                                                {{ optional($item->planneddate)->format('d M Y') ?: '—' }}
                                            </td>
                                            <td class="px-3 py-3 align-top text-gray-700">{{ $resolvePlaceName($item) }}</td>
                                            <td class="px-3 py-3 align-top text-gray-700">{{ $resolveLinkedLabel($item) }}</td>
                                            <td class="px-3 py-3 align-top text-xs text-gray-500">
                                                {{ $resolveFlags($item)->implode(', ') ?: '—' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-sm font-semibold text-gray-900">Trip Stays</h3>
                    <p class="mt-1 text-xs text-gray-500">
                        Proposed Trip Stays from planner items marked overnight or stay target.
                    </p>
                </div>

                <div class="p-6">
                    @if($candidateStayItems->isEmpty())
                        <p class="text-sm text-gray-500">
                            No Trip Stays can be proposed yet. Mark planning items as overnight or stay target.
                        </p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-3 py-2 text-left font-semibold text-gray-600">Seq</th>
                                        <th class="px-3 py-2 text-left font-semibold text-gray-600">Title</th>
                                        <th class="px-3 py-2 text-left font-semibold text-gray-600">Place</th>
                                        <th class="px-3 py-2 text-left font-semibold text-gray-600">Destination</th>
                                        <th class="px-3 py-2 text-left font-semibold text-gray-600">Destination item</th>
                                        <th class="px-3 py-2 text-left font-semibold text-gray-600">Start date</th>
                                        <th class="px-3 py-2 text-left font-semibold text-gray-600">End date</th>
                                        <th class="px-3 py-2 text-left font-semibold text-gray-600">Stay type</th>
                                        <th class="px-3 py-2 text-left font-semibold text-gray-600">Flags</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 bg-white">
                                    @foreach($candidateStayItems as $item)
                                        <tr>
                                            <td class="px-3 py-3 align-top text-gray-700">{{ $item->sequence_no }}</td>
                                            <td class="px-3 py-3 align-top">
                                                <div class="font-medium text-gray-900">{{ $item->display_title }}</div>
                                                @if(!empty($item->notes))
                                                    <div class="mt-1 text-xs text-gray-500 whitespace-pre-line">{{ $item->notes }}</div>
                                                @endif
                                            </td>
                                            <td class="px-3 py-3 align-top text-gray-700">{{ $resolvePlaceName($item) }}</td>
                                            <td class="px-3 py-3 align-top text-gray-700">{{ $resolveDestinationName($item) }}</td>
                                            <td class="px-3 py-3 align-top text-gray-700">{{ $resolveDestinationItemName($item) }}</td>
                                            <td class="px-3 py-3 align-top text-gray-700">
                                                {{ optional($item->planneddate)->format('Y-m-d') ?: '—' }}
                                            </td>
                                            <td class="px-3 py-3 align-top text-gray-700">
                                                {{ optional($item->plannedenddate)->format('Y-m-d') ?: '—' }}
                                            </td>
                                            <td class="px-3 py-3 align-top text-gray-700">{{ $item->staytype ?: '—' }}</td>
                                            <td class="px-3 py-3 align-top text-xs text-gray-500">
                                                {{ $resolveFlags($item)->implode(', ') ?: '—' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-sm font-semibold text-gray-900">Trip Items and Activities</h3>
                    <p class="mt-1 text-xs text-gray-500">
                        Activities, destination items, fuel stops, dump points, detours, and other non-stay planner items that will become Trip Items.
                    </p>
                </div>

                <div class="p-6">
                    @if($candidateTripItems->isEmpty())
                        <p class="text-sm text-gray-500">
                            No proposed Trip Items or Activities were found.
                        </p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-3 py-2 text-left font-semibold text-gray-600">Seq</th>
                                        <th class="px-3 py-2 text-left font-semibold text-gray-600">Type</th>
                                        <th class="px-3 py-2 text-left font-semibold text-gray-600">Title</th>
                                        <th class="px-3 py-2 text-left font-semibold text-gray-600">Date</th>
                                        <th class="px-3 py-2 text-left font-semibold text-gray-600">Place</th>
                                        <th class="px-3 py-2 text-left font-semibold text-gray-600">Linked</th>
                                        <th class="px-3 py-2 text-left font-semibold text-gray-600">Flags</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 bg-white">
                                    @foreach($candidateTripItems as $item)
                                        <tr>
                                            <td class="px-3 py-3 align-top text-gray-700">{{ $item->sequence_no }}</td>
                                            <td class="px-3 py-3 align-top text-gray-700">{{ $item->plantype }}</td>
                                            <td class="px-3 py-3 align-top">
                                                <div class="font-medium text-gray-900">{{ $item->display_title }}</div>
                                                @if(!empty($item->notes))
                                                    <div class="mt-1 text-xs text-gray-500 whitespace-pre-line">{{ $item->notes }}</div>
                                                @endif
                                            </td>
                                            <td class="px-3 py-3 align-top text-gray-700">
                                                {{ optional($item->planneddate)->format('d M Y') ?: '—' }}
                                            </td>
                                            <td class="px-3 py-3 align-top text-gray-700">{{ $resolvePlaceName($item) }}</td>
                                            <td class="px-3 py-3 align-top text-gray-700">{{ $resolveLinkedLabel($item) }}</td>
                                            <td class="px-3 py-3 align-top text-xs text-gray-500">
                                                {{ $resolveFlags($item)->implode(', ') ?: '—' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-sm font-semibold text-gray-900">Actions</h3>
                    <p class="mt-1 text-xs text-gray-500">
                        Applying generation will clear existing generated legs, stays, leg points, and trip items, then rebuild them from the planner.
                    </p>
                </div>

                <div class="p-6 flex flex-wrap items-center gap-3">
                    <form method="POST" action="{{ route('trips.planner.generate.apply', $trip) }}">
                        @csrf
                        <input type="hidden" name="return_to" value="{{ $returnTo }}">
                        <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-sm">
                            Generate Trip Outputs
                        </button>
                    </form>

                    <a href="{{ $returnTo }}"
                       class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 text-sm">
                        Cancel
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>