<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="bg-white shadow-sm sm:rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between gap-3">
            <h3 class="text-lg font-medium text-gray-900">Linked Place</h3>
            @if ($destination->place)
                <a href="{{ route('places.edit', [
                    'place' => $destination->place,
                    'return_to' => $tabReturnUrls['record-information'],
                ]) }}" class="px-3 py-1.5 bg-gray-100 text-gray-800 rounded hover:bg-gray-200 text-xs">Edit Place</a>
            @endif
        </div>
        <div class="p-6">
            @if ($destination->place)
                <dl class="space-y-3 text-sm">
                    @foreach ([
                        'placename' => 'Place', 'placetype' => 'Type',
                        'locality' => 'Locality', 'postcode' => 'Postcode',
                        'accessnotes' => 'Access notes', 'generalnotes' => 'General notes',
                    ] as $field => $label)
                        <div>
                            <dt class="text-gray-500">{{ $label }}</dt>
                            <dd class="text-gray-900 whitespace-pre-line">{{ $destination->place->getAttribute($field) ?: '—' }}</dd>
                        </div>
                    @endforeach
                </dl>
            @else
                <p class="text-sm text-gray-500">No Place is linked. Choose a Linked Place on the Destination tab.</p>
            @endif
        </div>
    </div>

    <div class="bg-white shadow-sm sm:rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200"><h3 class="text-lg font-medium text-gray-900">Record Summary</h3></div>
        @php
            $summaryRows = [
                'Destination ID' => $destination->id,
                'Type' => ucfirst($destination->destinationtype ?? ''),
                'Linked Place' => $destination->place?->placename ?: '—',
                'Created' => optional($destination->createdat)->format('d M Y') ?: '—',
                'Updated' => optional($destination->updatedat)->format('d M Y H:i') ?: '—',
                'Featured' => $destination->isfeatured ? 'Yes' : 'No',
                'Travel interest' => $destination->visitinterestlevel
                    ? (\App\Models\Destination::visitInterestOptions()[$destination->visitinterestlevel] ?? $destination->visitinterestlevel)
                    : '—',
                'Visited' => ($destination->hasvisited ? 'Yes' : 'No')
                    . ($destination->visitedat ? ' · ' . $destination->visitedat->format('d M Y') : ''),
            ];
            if ($destination->wishlistaddedat) {
                $summaryRows['Added to interest list'] = $destination->wishlistaddedat->format('d M Y');
            }
        @endphp
        <dl class="p-6 space-y-3 text-sm">
            @foreach ($summaryRows as $label => $value)
                <div class="flex justify-between gap-4">
                    <dt class="text-gray-500">{{ $label }}</dt>
                    <dd class="text-right text-gray-900">{{ $value ?: '—' }}</dd>
                </div>
            @endforeach
        </dl>
    </div>

    <div class="lg:col-span-2 bg-white shadow-sm sm:rounded-lg border border-red-200">
        <div class="px-6 py-4 border-b border-red-200 bg-red-50">
            <h3 class="text-sm font-semibold text-red-800">Delete Destination</h3>
            <p class="mt-1 text-xs text-red-700">This permanently removes this destination record. Existing server-side deletion rules still apply.</p>
        </div>
        <div class="p-6">
            <form method="POST" action="{{ route('destinations.destroy', $destination) }}"
                  onsubmit="return confirm('Delete this destination? This cannot be undone.');">
                @csrf
                @method('DELETE')
                <input type="hidden" name="return_to" value="{{ $returnTo }}">
                <input type="hidden" name="tab" value="record-information">
                <div class="flex justify-end">
                    <button type="submit" class="px-4 py-2 border border-red-300 rounded-md text-xs font-semibold text-red-700 bg-white uppercase tracking-widest hover:bg-red-50">Delete Destination</button>
                </div>
            </form>
        </div>
    </div>
</div>
