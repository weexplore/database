@php
    $destinationItem = $destinationItem ?? null;
    $place = $place ?? null;

    $showAddress = $showAddress ?? true;
    $showPlaceName = $showPlaceName ?? true;
    $showDestinationItemHeading = $showDestinationItemHeading ?? true;
    $showNotes = $showNotes ?? true;
    $showCosts = $showCosts ?? true;
    $showAccess = $showAccess ?? true;

    $resolvedPlace = $destinationItem?->place ?? $place ?? null;

    $destinationItemTypeLabel = $destinationItem && $destinationItem->relationLoaded('itemTypes')
        ? $destinationItem->itemTypes->pluck('typename')->filter()->join(', ')
        : null;

    $addressLine1 = $destinationItem?->addressline1 ?: $resolvedPlace?->addressline1;
    $addressLine2 = $destinationItem?->addressline2 ?: $resolvedPlace?->addressline2;
    $addressLine3 = $destinationItem?->addressline3;
    $postcode = $destinationItem?->postcode ?: $resolvedPlace?->postcode;

    $telephone = $destinationItem?->telephone;
    $website = $destinationItem?->website;
    $disabilityAccessNotes = $destinationItem?->disabilityaccessnotes;

    $hasDestinationItemDetails = $destinationItem && (
        $destinationItemTypeLabel ||
        $destinationItem->shortdescription ||
        ($showNotes && $destinationItem->notes) ||
        ($showCosts && $destinationItem->estimatedcostperperson !== null) ||
        ($showAccess && $destinationItem->caravanaccessnotes) ||
        ($showAccess && $disabilityAccessNotes)
    );

    $hasPlaceDetails = $resolvedPlace && (
        ($showPlaceName && !$destinationItem) ||
        ($showAddress && ($addressLine1 || $addressLine2 || $addressLine3 || $postcode)) ||
        $telephone ||
        $website ||
        $resolvedPlace->accessnotes ||
        $resolvedPlace->generalnotes
    );
@endphp

@if ($hasDestinationItemDetails || $hasPlaceDetails)
    <div class="location-detail-stack mt-3 space-y-2">
        @if ($hasDestinationItemDetails)
            <div class="rounded-md border border-blue-100 bg-blue-50/60 px-3 py-2.5">
                @if ($showDestinationItemHeading)
                    <div class="text-[11px] font-semibold uppercase tracking-wide text-blue-700">
                        Destination item details
                    </div>
                @endif

                @if ($destinationItemTypeLabel)
                    <div class="{{ $showDestinationItemHeading ? 'mt-0.5' : '' }} text-[11px] uppercase tracking-wide text-gray-500">
                        {{ $destinationItemTypeLabel }}
                    </div>
                @endif

                @if ($destinationItem->shortdescription)
                    <div class="mt-2">
                        <div class="text-[11px] font-semibold uppercase tracking-wide text-gray-500">
                            Short description
                        </div>
                        <div class="mt-0.5 text-xs text-gray-800 markdown-content">
                            @include('partials.markdown.rendered-block', [
                                'content' => $destinationItem->shortdescription,
                            ])
                        </div>
                    </div>
                @endif

                @if ($showNotes && $destinationItem->notes)
                    <div class="mt-2">
                        <div class="text-[11px] font-semibold uppercase tracking-wide text-gray-500">
                            Notes
                        </div>
                        <div class="mt-0.5 text-xs text-gray-800 markdown-content">
                            @include('partials.markdown.rendered-block', [
                                'content' => $destinationItem->notes,
                            ])
                        </div>
                    </div>
                @endif

                @if ($showCosts && $destinationItem->estimatedcostperperson !== null)
                    <div class="mt-2">
                        <div class="text-[11px] font-semibold uppercase tracking-wide text-gray-500">
                            Est. cost per person
                        </div>
                        <div class="mt-0.5 text-xs text-gray-800">
                            {{ number_format($destinationItem->estimatedcostperperson, 2) }}
                        </div>
                    </div>
                @endif

                @if ($showAccess && $destinationItem->caravanaccessnotes)
                    <div class="mt-2">
                        <div class="text-[11px] font-semibold uppercase tracking-wide text-gray-500">
                            Caravan access
                        </div>
                        <div class="mt-0.5 text-xs text-gray-800 markdown-content">
                            @include('partials.markdown.rendered-block', [
                                'content' => $destinationItem->caravanaccessnotes,
                            ])
                        </div>
                    </div>
                @endif

                @if ($showAccess && $disabilityAccessNotes)
                    <div class="mt-2">
                        <div class="text-[11px] font-semibold uppercase tracking-wide text-gray-500">
                            Disability access
                        </div>
                        <div class="mt-0.5 text-xs text-gray-800 markdown-content">
                            @include('partials.markdown.rendered-block', [
                                'content' => $disabilityAccessNotes,
                            ])
                        </div>
                    </div>
                @endif
            </div>
        @endif

        @if ($hasPlaceDetails)
            <div class="rounded-md border border-gray-200 bg-gray-50 px-3 py-2.5">
                <div class="text-[11px] font-semibold uppercase tracking-wide text-gray-600">
                    Place details
                </div>

                @if ($showPlaceName && !$destinationItem)
                    <div class="mt-1 text-sm font-medium text-gray-900">
                        {{ $resolvedPlace->placename }}
                    </div>
                @endif

                @if ($showAddress && ($addressLine1 || $addressLine2 || $addressLine3 || $postcode))
                    <div class="mt-2">
                        <div class="text-[11px] font-semibold uppercase tracking-wide text-gray-500">
                            Address
                        </div>
                        <div class="mt-0.5 text-xs text-gray-800">
                            @if ($addressLine1)
                                <div>{{ $addressLine1 }}</div>
                            @endif
                            @if ($addressLine2)
                                <div>{{ $addressLine2 }}</div>
                            @endif
                            @if ($addressLine3)
                                <div>{{ $addressLine3 }}</div>
                            @endif
                            @if ($postcode)
                                <div>{{ $postcode }}</div>
                            @endif
                        </div>
                    </div>
                @endif

                @if ($telephone)
                    <div class="mt-2">
                        <div class="text-[11px] font-semibold uppercase tracking-wide text-gray-500">
                            Telephone
                        </div>
                        <div class="mt-0.5 text-xs text-gray-800">
                            {{ $telephone }}
                        </div>
                    </div>
                @endif

                @if ($website)
                    <div class="mt-2">
                        <div class="text-[11px] font-semibold uppercase tracking-wide text-gray-500">
                            Website
                        </div>
                        <div class="mt-0.5 text-xs text-gray-800 break-all">
                            <a href="{{ $website }}" target="_blank" rel="noopener noreferrer" class="text-blue-700 hover:text-blue-900 underline">
                                {{ $website }}
                            </a>
                        </div>
                    </div>
                @endif

                @if ($resolvedPlace->accessnotes)
                    <div class="mt-2">
                        <div class="text-[11px] font-semibold uppercase tracking-wide text-gray-500">
                            Place access notes
                        </div>
                        <div class="mt-0.5 text-xs text-gray-800 markdown-content">
                            @include('partials.markdown.rendered-block', [
                                'content' => $resolvedPlace->accessnotes,
                            ])
                        </div>
                    </div>
                @endif

                @if ($resolvedPlace->generalnotes)
                    <div class="mt-2">
                        <div class="text-[11px] font-semibold uppercase tracking-wide text-gray-500">
                            Place notes
                        </div>
                        <div class="mt-0.5 text-xs text-gray-800 markdown-content">
                            @include('partials.markdown.rendered-block', [
                                'content' => $resolvedPlace->generalnotes,
                            ])
                        </div>
                    </div>
                @endif
            </div>
        @endif
    </div>
@endif
