<div class="bg-white shadow-sm sm:rounded-lg">
    <div class="px-6 py-4 border-b border-gray-200 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h3 class="text-lg font-medium text-gray-900">Attachments</h3>
            <p class="mt-1 text-xs text-gray-500">{{ $destination->attachments->count() }} linked record{{ $destination->attachments->count() === 1 ? '' : 's' }}</p>
        </div>
        @if (Route::has('attachments.index'))
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('attachments.index', [
                    'linkedtype' => 'destination', 'linkedid' => $destination->id,
                    'return_to' => $tabReturnUrls['attachments'],
                ]) }}" class="px-3 py-2 bg-gray-100 text-gray-800 rounded hover:bg-gray-200 text-sm">View Attachments</a>
                <a href="{{ route('attachments.index', [
                    'linkedtype' => 'destination', 'linkedid' => $destination->id,
                    'show_create' => 1, 'return_to' => $tabReturnUrls['attachments'],
                ]) }}" class="px-3 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-sm">Add Attachment</a>
            </div>
        @endif
    </div>
    <div class="p-6">
        @if ($destination->attachments->isNotEmpty())
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3">
                @foreach ($destination->attachments as $attachment)
                    <div class="border border-gray-200 rounded-md px-4 py-3">
                        <div class="text-sm font-medium text-gray-900">{{ $attachment->description ?: $attachment->originalfilename ?: 'Attachment' }}</div>
                        <div class="mt-1 text-xs text-gray-500">
                            {{ $attachment->attachmenttype ?: 'File' }}
                            @if ($attachment->uploadedat)
                                · {{ \Illuminate\Support\Carbon::parse($attachment->uploadedat)->format('d M Y') }}
                            @endif
                            @if ($attachment->isprimary) · Primary @endif
                        </div>
                        <div class="mt-3 flex flex-wrap gap-2">
                            @if (Route::has('attachments.view'))
                                <a href="{{ route('attachments.view', $attachment) }}" target="_blank" rel="noopener noreferrer"
                                   class="px-2.5 py-1.5 bg-gray-100 text-gray-800 rounded hover:bg-gray-200 text-xs">View</a>
                            @endif
                            @if (Route::has('attachments.download'))
                                <a href="{{ route('attachments.download', $attachment) }}"
                                   class="px-2.5 py-1.5 bg-gray-100 text-gray-800 rounded hover:bg-gray-200 text-xs">Download</a>
                            @endif
                            @if (Route::has('attachments.edit'))
                                <a href="{{ route('attachments.edit', [
                                    'attachment' => $attachment,
                                    'return_to' => $tabReturnUrls['attachments'],
                                ]) }}" class="px-2.5 py-1.5 bg-gray-100 text-gray-800 rounded hover:bg-gray-200 text-xs">Edit</a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-sm text-gray-500">No attachments are linked to this destination.</p>
        @endif
    </div>
</div>
