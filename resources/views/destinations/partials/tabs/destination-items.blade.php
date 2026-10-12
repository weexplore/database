<div class="bg-white shadow-sm sm:rounded-lg">
    <div class="px-6 py-4 border-b border-gray-200 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h3 class="text-lg font-medium text-gray-900">Destination Items</h3>
            <p class="mt-1 text-xs text-gray-500">{{ $destination->items->count() }} linked item{{ $destination->items->count() === 1 ? '' : 's' }}</p>
        </div>
        <a href="{{ route('destinations.destination-items.create-from-destination', [
            'destination' => $destination,
            'return_to' => $tabReturnUrls['destination-items'],
        ]) }}" class="px-3 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-sm">Add Destination Item</a>
    </div>
    <div class="p-6">
        @if ($destination->items->isNotEmpty())
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4 gap-3">
                @foreach ($destination->items as $item)
                    <a href="{{ route('destination-items.edit', [
                        'destinationItem' => $item,
                        'return_to' => $tabReturnUrls['destination-items'],
                    ]) }}" class="block rounded-md border border-gray-200 bg-gray-50 px-3 py-3 hover:bg-gray-100 hover:border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <div class="text-sm font-medium text-gray-900">{{ $item->itemname }}</div>
                        <div class="mt-1 text-xs text-gray-500">Type: {{ $item->itemTypes->pluck('typename')->filter()->join(', ') ?: 'Not specified' }}</div>
                    </a>
                @endforeach
            </div>
        @else
            <p class="text-sm text-gray-500">No destination items are linked yet.</p>
        @endif
    </div>
</div>
