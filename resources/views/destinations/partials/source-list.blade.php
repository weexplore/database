@php
    $sourceReturnTo = $sourceReturnTo ?? url()->full();
    $sourceFormAction = $sourceFormAction ?? null;
    $sourceFormId = $sourceFormId ?? null;
@endphp

@forelse ($destination->sources as $source)
    @include('destinations.partials.source-card', [
        'destination' => $destination,
        'source' => $source,
        'sourceReturnTo' => $sourceReturnTo,
        'sourceFormAction' => $sourceFormAction,
        'sourceFormId' => $sourceFormId,
    ])
@empty
    <p class="text-xs text-gray-500">
        No internet sources recorded yet.
        Use “Find Internet Sources” or Add Source.
    </p>
@endforelse