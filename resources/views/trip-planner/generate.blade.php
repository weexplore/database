<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Generate Legs & Stays - {{ $trip->tripname }}
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    Review proposed trip legs and stays from the planning sequence before applying any changes.
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
                                                $flags = collect([
                                                    $item->isrouteanchor ? 'Route anchor' : null,
                                                    $item->isovernight ? 'Overnight' : null,
                                                    $item->isstaytarget ? 'Stay target' : null,
                                                ])->filter()->implode(', ');

                                                $linkedLabel = $item->destinationItem?->itemname
                                                    ?? $item->destination?->destinationname
                                                    ?? $item->place?->placename
                                                    ?? '—';
                                            @endphp
                                            <tr>
                                                <td class="px-3 py-3 align-top text-gray-700">{{ $item->sequence_no }}</td>
                                                <td class="px-3 py-3 align-top text-gray-700">{{ $item->plantype }}</td>
                                                <td class="px-3 py-3 align-top">
                                                    <div class="font-medium text-gray-900">{{ $item->display_title }}</div>
                                                </td>
                                                <td class="px-3 py-3 align-top text-gray-700">
                                                    {{ optional($item->planneddate)->format('Y-m-d') ?: '—' }}
                                                </td>
                                                <td class="px-3 py-3 align-top text-xs text-gray-500">
                                                    {{ $flags ?: '—' }}
                                                </td>
                                                <td class="px-3 py-3 align-top text-gray-700">
                                                    {{ $linkedLabel }}
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
                                <dt class="text-gray-500">Route anchors</dt>
                                <dd class="text-gray-900">{{ $candidateLegAnchors->count() }}</dd>
                            </div>
                            <div class="flex justify-between gap-4">
                                <dt class="text-gray-500">Proposed legs</dt>
                                <dd class="text-gray-900">{{ $candidateLegs->count() }}</dd>
                            </div>
                            <div class="flex justify-between gap-4">
                                <dt class="text-gray-500">Proposed stays</dt>
                                <dd class="text-gray-900">{{ $candidateStayItems->count() }}</dd>
                            </div>
                            <div class="flex justify-between gap-4">
                                <dt class="text-gray-500">Leg points</dt>
                                <dd class="text-gray-900">{{ $candidateLegPoints->count() }}</dd>
                            </div>
                            <div class="flex justify-between gap-4">
                                <dt class="text-gray-500">Trip items</dt>
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
                    <h3 class="text-sm font-semibold text-gray-900">Proposed trip legs</h3>
                    <p class="mt-1 text-xs text-gray-500">
                        Each leg is proposed between consecutive route-anchor planning items.
                    </p>
                </div>

                <div class="p-6">
                    @if($candidateLegs->isEmpty())
                        <p class="text-sm text-gray-500">
                            No trip legs can be proposed yet. Add at least two route-anchor planning items.
                        </p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-3 py-2 text-left font-semibold text-gray-600">From seq</th>
                                        <th class="px-3 py-2 text-left font-semibold text-gray-600">From</th>
                                        <th class="px-3 py-2 text-left font-semibold text-gray-600">To seq</th>
                                        <th class="px-3 py-2 text-left font-semibold text-gray-600">To</th>
                                        <th class="px-3 py-2 text-left font-semibold text-gray-600">Start date</th>
                                        <th class="px-3 py-2 text-left font-semibold text-gray-600">End date</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 bg-white">
                                    @foreach($candidateLegs as $leg)
                                        <tr>
                                            <td class="px-3 py-3 align-top text-gray-700">{{ $leg['from_item']->sequence_no }}</td>
                                            <td class="px-3 py-3 align-top text-gray-900">{{ $leg['from_label'] }}</td>
                                            <td class="px-3 py-3 align-top text-gray-700">{{ $leg['to_item']->sequence_no }}</td>
                                            <td class="px-3 py-3 align-top text-gray-900">{{ $leg['to_label'] }}</td>
                                            <td class="px-3 py-3 align-top text-gray-700">
                                                {{ optional($leg['planned_start'])->format('Y-m-d') ?: '—' }}
                                            </td>
                                            <td class="px-3 py-3 align-top text-gray-700">
                                                {{ optional($leg['planned_end'])->format('Y-m-d') ?: '—' }}
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
                    <h3 class="text-sm font-semibold text-gray-900">Proposed leg points</h3>
                    <p class="mt-1 text-xs text-gray-500">Route waypoints and anchor items that should sit on a leg rather than create a new leg.</p>
                </div>
                <div class="px-6 py-4">
                    @if ($candidateLegPoints->isEmpty())
                        <p class="text-sm text-gray-500">No proposed leg points were found.</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full text-xs border-t border-b border-gray-200">
                                <thead class="bg-gray-50 text-gray-600 uppercase tracking-wide">
                                    <tr>
                                        <th class="px-2 py-2 text-left">Seq</th>
                                        <th class="px-2 py-2 text-left">Type</th>
                                        <th class="px-2 py-2 text-left">Title</th>
                                        <th class="px-2 py-2 text-left">Date</th>
                                        <th class="px-2 py-2 text-left">Linked</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach ($candidateLegPoints as $item)
                                        <tr>
                                            <td class="px-2 py-2 align-top">{{ $item->sequencenumber }}</td>
                                            <td class="px-2 py-2 align-top">{{ $item->plantype }}</td>
                                            <td class="px-2 py-2 align-top font-medium text-gray-900">{{ $item->display_title }}</td>
                                            <td class="px-2 py-2 align-top">{{ optional($item->planneddate)?->format('d M Y') }}</td>
                                            <td class="px-2 py-2 align-top text-gray-600">{{ $item->linked_display ?? '—' }}</td>
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
                    <h3 class="text-sm font-semibold text-gray-900">Proposed trip stays</h3>
                    <p class="mt-1 text-xs text-gray-500">
                        Stays are proposed from planning items marked overnight or stay target.
                    </p>
                </div>

                <div class="p-6">
                    @if($candidateStayItems->isEmpty())
                        <p class="text-sm text-gray-500">
                            No trip stays can be proposed yet. Mark planning items as overnight or stay target.
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
                                        <th class="px-3 py-2 text-left font-semibold text-gray-600">Start date</th>
                                        <th class="px-3 py-2 text-left font-semibold text-gray-600">End date</th>
                                        <th class="px-3 py-2 text-left font-semibold text-gray-600">Stay type</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 bg-white">
                                    @foreach($candidateStayItems as $item)
                                        <tr>
                                            <td class="px-3 py-3 align-top text-gray-700">{{ $item->sequence_no }}</td>
                                            <td class="px-3 py-3 align-top text-gray-900">{{ $item->display_title }}</td>
                                            <td class="px-3 py-3 align-top text-gray-700">{{ $item->place?->placename ?? '—' }}</td>
                                            <td class="px-3 py-3 align-top text-gray-700">{{ $item->destination?->destinationname ?? '—' }}</td>
                                            <td class="px-3 py-3 align-top text-gray-700">
                                                {{ optional($item->planneddate)->format('Y-m-d') ?: '—' }}
                                            </td>
                                            <td class="px-3 py-3 align-top text-gray-700">
                                                {{ optional($item->plannedenddate)->format('Y-m-d') ?: '—' }}
                                            </td>
                                            <td class="px-3 py-3 align-top text-gray-700">{{ $item->staytype ?: '—' }}</td>
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
                    <h3 class="text-sm font-semibold text-gray-900">Proposed trip items</h3>
                    <p class="mt-1 text-xs text-gray-500">Activities, destination items, dump points, fuel stops, and other non-stay planner items.</p>
                </div>
                <div class="px-6 py-4">
                    @if ($candidateTripItems->isEmpty())
                        <p class="text-sm text-gray-500">No proposed trip items were found.</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full text-xs border-t border-b border-gray-200">
                                <thead class="bg-gray-50 text-gray-600 uppercase tracking-wide">
                                    <tr>
                                        <th class="px-2 py-2 text-left">Seq</th>
                                        <th class="px-2 py-2 text-left">Type</th>
                                        <th class="px-2 py-2 text-left">Title</th>
                                        <th class="px-2 py-2 text-left">Date</th>
                                        <th class="px-2 py-2 text-left">Linked</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach ($candidateTripItems as $item)
                                        <tr>
                                            <td class="px-2 py-2 align-top">{{ $item->sequencenumber }}</td>
                                            <td class="px-2 py-2 align-top">{{ $item->plantype }}</td>
                                            <td class="px-2 py-2 align-top">
                                                <div class="font-medium text-gray-900">{{ $item->display_title }}</div>
                                                @if (!empty($item->notes))
                                                    <div class="mt-0.5 text-gray-600 whitespace-pre-line">{{ $item->notes }}</div>
                                                @endif
                                            </td>
                                            <td class="px-2 py-2 align-top">{{ optional($item->planneddate)?->format('d M Y') }}</td>
                                            <td class="px-2 py-2 align-top text-gray-600">{{ $item->linked_display ?? '—' }}</td>
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
                        These actions are scaffolded first so generation and rollback can be added safely next.
                    </p>
                </div>

                <div class="p-6 flex flex-wrap items-center gap-3">
                    <form method="POST" action="{{ route('trips.planner.generate.apply', $trip) }}">
                        @csrf
                        <input type="hidden" name="return_to" value="{{ $returnTo }}">
                        <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-sm">
                            Generate Legs & Stays
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