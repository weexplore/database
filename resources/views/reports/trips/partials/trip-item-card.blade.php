{{-- resources/views/reports/trips/partials/trip-item-card.blade.php --}}

@php
    $compact = $compact ?? false;

    $dateLabel = $item->itemdate
        ? $item->itemdate->format('d M Y')
        : ($item->startdatetime ? $item->startdatetime->format('d M Y') : '—');

    $itemDestinationItem = $item->destinationItem ?? null;
    $itemPlace = $itemDestinationItem?->place ?? $item->place;
    $location =
        $itemDestinationItem?->itemname
        ?? $itemPlace?->placename
        ?? optional($item->destination)->destinationname
        ?? '—';
@endphp

@if ($compact)
    <div class="px-3 py-3 space-y-3">
        <div class="flex items-start justify-between gap-4">
            <div>
                <div class="text-sm font-semibold text-gray-900">{{ $item->title }}</div>
                <div class="mt-0.5 text-xs text-gray-500">
                    {{ $dateLabel ?: 'No date' }}
                    @if ($item->itemtype)
                        · {{ $item->itemtype }}
                    @endif
                    @if ($item->status)
                        · {{ ucfirst($item->status) }}
                    @endif
                </div>
            </div>

            <div class="text-right text-xs text-gray-600">
                <div>Est. {{ $item->estimatedtotalcost !== null ? number_format($item->estimatedtotalcost, 2) : '—' }}</div>
                <div>Actual {{ $item->actualcost !== null ? number_format($item->actualcost, 2) : '—' }}</div>
            </div>
        </div>

        <div>
            <div class="text-xs uppercase tracking-wide text-gray-500">Location</div>
            <div class="mt-1 text-sm font-medium text-gray-900">{{ $location }}</div>
        </div>

        @if ($item->description)
            <div>
                <div class="text-xs uppercase tracking-wide text-gray-500">Description</div>
                <div class="mt-1 text-sm text-gray-700 markdown-content">
                    @include('partials.markdown.rendered-block', [
                        'content' => $item->description,
                    ])
                </div>
            </div>
        @endif

        @include('reports.trips.partials.location-details', [
            'destinationItem' => $itemDestinationItem,
            'place' => $itemPlace,
            'showPlaceName' => false,
            'showDestinationItemHeading' => false,
        ])
    </div>
@else
    <div class="border border-gray-200 rounded-lg overflow-hidden">
        <div class="px-4 py-3 bg-gray-50 border-b border-gray-200 flex items-start justify-between gap-4">
            <div>
                <div class="text-sm font-semibold text-gray-900">
                    {{ $item->title }}
                </div>
                <div class="mt-0.5 text-xs text-gray-500">
                    {{ $dateLabel }}
                    @if ($item->itemtype)
                        • {{ $item->itemtype }}
                    @endif
                    @if ($item->status)
                        • {{ ucfirst($item->status) }}
                    @endif
                </div>
            </div>

            <div class="text-right text-xs text-gray-500">
                <div>Estimated / Actual</div>
                <div class="mt-1 text-sm font-semibold text-gray-900">
                    {{ $item->estimatedtotalcost !== null ? number_format($item->estimatedtotalcost, 2) : '—' }}
                    /
                    {{ $item->actualcost !== null ? number_format($item->actualcost, 2) : '—' }}
                </div>
            </div>
        </div>

        <div class="p-4 space-y-4">
            <div>
                <div class="text-xs uppercase tracking-wide text-gray-500">Location</div>
                <div class="mt-1 text-sm font-medium text-gray-900">
                    {{ $location }}
                </div>
            </div>

            @if ($item->description)
                <div>
                    <div class="text-xs uppercase tracking-wide text-gray-500">Description</div>
                    <div class="mt-1 text-sm text-gray-700 markdown-content">
                        @include('partials.markdown.rendered-block', [
                            'content' => $item->description,
                        ])
                    </div>
                </div>
            @endif

            @include('reports.trips.partials.location-details', [
                'destinationItem' => $itemDestinationItem,
                'place' => $itemPlace,
            ])
        </div>
    </div>
@endif