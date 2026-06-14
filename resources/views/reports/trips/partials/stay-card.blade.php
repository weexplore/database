{{-- resources/views/reports/trips/partials/stay-card.blade.php --}}

@php
    $compact = $compact ?? false;

    $checkIn = $stay->checkindate ? $stay->checkindate->format('d M Y') : null;
    $checkOut = $stay->checkoutdate ? $stay->checkoutdate->format('d M Y') : null;
    $stayDestinationItem = $stay->destinationItem ?? null;
    $stayPlace = $stayDestinationItem?->place ?? $stay->place;
@endphp

@if ($compact)
    <div class="px-3 py-3 space-y-3">
        <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-3">
            <div>
                <div class="text-sm font-semibold text-gray-900">{{ $stay->stayname }}</div>
                <div class="mt-0.5 text-xs text-gray-500">
                    {{ $stay->staytype ?: 'Stay' }}
                    @if ($checkIn || $checkOut)
                        · {{ $checkIn ?: 'Unknown' }} – {{ $checkOut ?: 'Unknown' }}
                    @endif
                    @if ($stay->nights !== null)
                        · {{ $stay->nights }} night{{ (int) $stay->nights === 1 ? '' : 's' }}
                    @endif
                </div>
            </div>

            <div class="text-xs text-gray-600 md:text-right">
                <div>Est. {{ $stay->estimatedtotalcost !== null ? number_format($stay->estimatedtotalcost, 2) : '—' }}</div>
                <div>Actual {{ $stay->actualtotalcost !== null ? number_format($stay->actualtotalcost, 2) : '—' }}</div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
            <div>
                <div class="text-xs uppercase tracking-wide text-gray-500">Place</div>
                <div class="mt-1 text-gray-900 font-medium">{{ $stayPlace?->placename ?: '—' }}</div>
            </div>
            <div>
                <div class="text-xs uppercase tracking-wide text-gray-500">Destination item</div>
                <div class="mt-1 text-gray-900 font-medium">{{ $stayDestinationItem?->itemname ?: '—' }}</div>
            </div>
        </div>

        @include('reports.trips.partials.location-details', [
            'destinationItem' => $stayDestinationItem,
            'place' => $stayPlace,
            'showPlaceName' => false,
            'showDestinationItemHeading' => false,
        ])

        @if ($stay->description || $stay->reviewnotes)
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                @if ($stay->description)
                    <div>
                        <div class="text-xs uppercase tracking-wide text-gray-500">Description</div>
                        <div class="mt-1 text-gray-700 markdown-content">
                            @include('partials.markdown.rendered-block', [
                                'content' => $stay->description,
                            ])
                        </div>
                    </div>
                @endif

                @if ($stay->reviewnotes)
                    <div>
                        <div class="text-xs uppercase tracking-wide text-gray-500">Review notes</div>
                        <div class="mt-1 text-gray-700 markdown-content">
                            @include('partials.markdown.rendered-block', [
                                'content' => $stay->reviewnotes,
                            ])
                        </div>
                    </div>
                @endif
            </div>
        @endif
    </div>
@else
    <div class="border border-gray-200 rounded-lg overflow-hidden">
        <div class="px-4 py-3 bg-gray-50 border-b border-gray-200">
            <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                <div>
                    <div class="text-sm font-semibold text-gray-900">
                        {{ $stay->stayname }}
                    </div>
                    <div class="mt-0.5 text-xs text-gray-500">
                        {{ $stay->staytype ?: '—' }}
                    </div>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-xs text-gray-600">
                    <div>
                        <div class="uppercase tracking-wide text-gray-500">Dates</div>
                        <div class="mt-0.5 text-gray-900">
                            {{ $checkIn ?: 'Unknown' }} – {{ $checkOut ?: 'Unknown' }}
                        </div>
                    </div>
                    <div>
                        <div class="uppercase tracking-wide text-gray-500">Nights</div>
                        <div class="mt-0.5 text-gray-900">
                            {{ $stay->nights ?? '—' }}
                        </div>
                    </div>
                    <div>
                        <div class="uppercase tracking-wide text-gray-500">Est. cost</div>
                        <div class="mt-0.5 text-gray-900">
                            {{ $stay->estimatedtotalcost !== null ? number_format($stay->estimatedtotalcost, 2) : '—' }}
                        </div>
                    </div>
                    <div>
                        <div class="uppercase tracking-wide text-gray-500">Actual cost</div>
                        <div class="mt-0.5 text-gray-900">
                            {{ $stay->actualtotalcost !== null ? number_format($stay->actualtotalcost, 2) : '—' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="p-4 space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div>
                    <div class="text-xs uppercase tracking-wide text-gray-500">Place</div>
                    <div class="mt-1 text-gray-900 font-medium">
                        {{ $stayPlace?->placename ?: '—' }}
                    </div>
                </div>

                <div>
                    <div class="text-xs uppercase tracking-wide text-gray-500">Destination item</div>
                    <div class="mt-1 text-gray-900 font-medium">
                        {{ $stayDestinationItem?->itemname ?: '—' }}
                    </div>
                </div>
            </div>

            @include('reports.trips.partials.location-details', [
                'destinationItem' => $stayDestinationItem,
                'place' => $stayPlace,
            ])

            @if ($stay->description || $stay->reviewnotes)
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    @if ($stay->description)
                        <div>
                            <div class="text-xs uppercase tracking-wide text-gray-500">Description</div>
                            <div class="mt-1 text-gray-700 markdown-content">
                                @include('partials.markdown.rendered-block', [
                                    'content' => $stay->description,
                                ])
                            </div>
                        </div>
                    @endif

                    @if ($stay->reviewnotes)
                        <div>
                            <div class="text-xs uppercase tracking-wide text-gray-500">Review notes</div>
                            <div class="mt-1 text-gray-700 markdown-content">
                                @include('partials.markdown.rendered-block', [
                                    'content' => $stay->reviewnotes,
                                ])
                            </div>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>
@endif