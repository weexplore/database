@php
    $attachmentTypeOptions = [
        'document' => 'Document',
        'image' => 'Image',
        'invoice' => 'Invoice',
        'receipt' => 'Receipt',
        'map' => 'Map',
        'other' => 'Other',
    ];

    $attachments = $knowledgeItem->attachments
        ->sortBy([
            fn ($attachment) => -(int) ($attachment->pivot->isprimary ?? 0),
            fn ($attachment) => (int) ($attachment->pivot->sortorder ?? 0),
            fn ($attachment) => -(optional($attachment->uploadedat)?->timestamp ?? 0),
            fn ($attachment) => -(int) $attachment->id,
        ])
        ->values();

    $linkedAttachmentIds = $knowledgeItem->attachments->pluck('id')->all();

    $availableAttachments = \App\Models\KnowledgeAttachment::query()
        ->when(!empty($linkedAttachmentIds), fn ($query) => $query->whereNotIn('id', $linkedAttachmentIds))
        ->orderByDesc('uploadedat')
        ->orderByDesc('id')
        ->get();

    $returnToUrl = route('knowledge.items.edit', [
        'knowledgeItem' => $knowledgeItem,
        'tab' => 'attachments',
    ]);
@endphp

<link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>

<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
    <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h3 class="text-lg font-semibold text-gray-900">Attachments</h3>
                <p class="mt-1 text-sm text-gray-500">
                    Upload and manage files linked to this knowledge item.
                </p>
            </div>
        </div>
    </div>

    <div class="p-6 border-b border-gray-200 bg-gray-50">
        <form method="POST"
              action="{{ route('knowledge.attachments.store', ['knowledgeItem' => $knowledgeItem]) }}"
              enctype="multipart/form-data"
              class="space-y-6">
            @csrf

            <input type="hidden" name="return_to" value="{{ $returnToUrl }}">

            @include('knowledge-attachments._form', [
                'knowledgeAttachment' => null,
                'knowledgeItem' => $knowledgeItem,
                'attachmentTypeOptions' => $attachmentTypeOptions,
            ])

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
                <button type="submit"
                        class="inline-flex items-center px-5 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-sm">
                    Upload Attachment
                </button>
            </div>
        </form>
    </div>

    <div class="p-6 border-b border-gray-200 bg-white">
        <form method="POST"
              action="{{ route('knowledge.attachments.attach-existing', ['knowledgeItem' => $knowledgeItem]) }}"
              class="space-y-6">
            @csrf

            <input type="hidden" name="return_to" value="{{ $returnToUrl }}">

            <div>
                <h4 class="text-sm font-semibold text-gray-900">Link Existing Attachment</h4>
                <p class="mt-1 text-sm text-gray-500">
                    Reuse a file already uploaded elsewhere and link it to this knowledge item.
                </p>
            </div>

            @if($availableAttachments->isEmpty())
                <div class="rounded-md border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-500">
                    No unlinked attachments are currently available.
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label for="existing_knowledgeattachmentid" class="block text-sm font-medium text-gray-700 mb-1">
                            Existing Attachment
                        </label>
                        <select name="knowledgeattachmentid"
                                id="existing_knowledgeattachmentid"
                                class="js-searchable-select w-full rounded-md border-gray-300 shadow-sm text-sm"
                                required>
                            <option value="">Select attachment</option>
                            @foreach($availableAttachments as $existingAttachment)
                                <option value="{{ $existingAttachment->id }}"
                                    @selected((string) old('knowledgeattachmentid') === (string) $existingAttachment->id)>
                                    {{ $existingAttachment->originalfilename }}
                                    @if($existingAttachment->attachmenttype)
                                        — {{ $attachmentTypeOptions[$existingAttachment->attachmenttype] ?? $existingAttachment->attachmenttype }}
                                    @endif
                                    @if($existingAttachment->uploadedat)
                                        — {{ optional($existingAttachment->uploadedat)->format('d M Y') }}
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label for="existing_description" class="block text-sm font-medium text-gray-700 mb-1">
                            Description for this knowledge item
                        </label>
                        <input type="text"
                               name="description"
                               id="existing_description"
                               value="{{ old('description') }}"
                               class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                    </div>

                    <div>
                        <label for="existing_sortorder" class="block text-sm font-medium text-gray-700 mb-1">
                            Sort Order
                        </label>
                        <input type="number"
                               name="sortorder"
                               id="existing_sortorder"
                               value="{{ old('sortorder', 0) }}"
                               min="0"
                               class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                    </div>

                    <div class="flex items-center pt-6">
                        <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                            <input type="checkbox"
                                   name="isprimary"
                                   value="1"
                                   @checked(old('isprimary'))
                                   class="rounded border-gray-300 text-blue-600 shadow-sm">
                            Set as primary for this item
                        </label>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
                    <button type="submit"
                            class="inline-flex items-center px-5 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">
                        Link Existing Attachment
                    </button>
                </div>
            @endif
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                        Attachment
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                        Type
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                        Uploaded
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                        Expiry
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                        Size
                    </th>
                    <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">
                        Actions
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @forelse($attachments as $attachment)
                    @php
                        $pivot = $attachment->pivot;
                    @endphp

                    <tr>
                        <td class="px-4 py-3 align-top">
                            <div class="font-medium text-gray-900">
                                {{ $attachment->originalfilename }}
                            </div>

                            @if(!empty($pivot?->description))
                                <div class="text-xs text-gray-500 mt-1">
                                    {{ $pivot->description }}
                                </div>
                            @endif

                            <div class="text-xs text-gray-500 mt-1">
                                {{ $attachment->mimetype }}
                            </div>
                        </td>

                        <td class="px-4 py-3 align-top">
                            <div class="text-gray-900">
                                {{ $attachmentTypeOptions[$attachment->attachmenttype] ?? $attachment->attachmenttype }}
                            </div>

                            @if(!empty($pivot?->isprimary))
                                <div class="text-xs text-blue-600 mt-1">Primary</div>
                            @endif

                            @if(!is_null($pivot?->sortorder))
                                <div class="text-xs text-gray-500 mt-1">Sort: {{ $pivot->sortorder }}</div>
                            @endif
                        </td>

                        <td class="px-4 py-3 align-top">
                            <div class="text-gray-900">
                                {{ optional($attachment->uploadedat)->format('d M Y H:i') }}
                            </div>

                            @if($attachment->uploadedby)
                                <div class="text-xs text-gray-500 mt-1">
                                    {{ $attachment->uploadedby }}
                                </div>
                            @endif
                        </td>

                        <td class="px-4 py-3 align-top">
                            @if($pivot?->expirydate)
                                <div class="text-gray-900">
                                    {{ $pivot->expirydate->format('d M Y') }}
                                </div>

                                @if($pivot->expirydate->isPast() && !$pivot->expirydate->isToday())
                                    <div class="mt-1 text-xs text-red-600">
                                        Expired
                                    </div>
                                @elseif($pivot->expirydate->isToday())
                                    <div class="mt-1 text-xs text-amber-600">
                                        Expires today
                                    </div>
                                @elseif($pivot->expirydate->lte(today()->addDays(14)))
                                    <div class="mt-1 text-xs text-amber-600">
                                        Expires soon
                                    </div>
                                @endif
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </td>

                        <td class="px-4 py-3 align-top">
                            <div class="text-gray-900">
                                {{ number_format(((int) $attachment->filesizebytes) / 1024, 1) }} KB
                            </div>
                        </td>

                        <td class="px-4 py-3 align-top text-right">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('knowledge.attachments.view', $attachment) }}"
                                   target="_blank"
                                   rel="noopener noreferrer"
                                   class="inline-flex items-center px-3 py-1.5 bg-gray-50 text-gray-700 rounded hover:bg-gray-100 text-xs">
                                    View
                                </a>

                                <a href="{{ route('knowledge.attachments.download', $attachment) }}"
                                   class="inline-flex items-center px-3 py-1.5 bg-gray-50 text-gray-700 rounded hover:bg-gray-100 text-xs">
                                    Download
                                </a>

                                <a href="{{ route('knowledge.attachments.edit', [
                                        'knowledgeItem' => $knowledgeItem,
                                        'knowledgeAttachment' => $attachment,
                                        'return_to' => $returnToUrl,
                                   ]) }}"
                                   class="inline-flex items-center px-3 py-1.5 bg-blue-50 text-blue-700 rounded hover:bg-blue-100 text-xs">
                                    Edit
                                </a>

                                <form method="POST"
                                      action="{{ route('knowledge.attachments.destroy', [
                                            'knowledgeItem' => $knowledgeItem,
                                            'knowledgeAttachment' => $attachment,
                                      ]) }}"
                                      onsubmit="return confirm('Remove this attachment from this knowledge item?');">
                                    @csrf
                                    @method('DELETE')

                                    <input type="hidden" name="return_to" value="{{ $returnToUrl }}">

                                    <button type="submit"
                                            class="inline-flex items-center px-3 py-1.5 bg-red-50 text-red-700 rounded hover:bg-red-100 text-xs">
                                        Remove
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                            No attachments found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.js-searchable-select').forEach(function (element) {
            if (element.tomselect) {
                return;
            }

            new TomSelect(element, {
                create: false,
                maxOptions: 500,
                placeholder: 'Search attachments...',
                allowEmptyOption: true,
                searchField: ['text'],
                sortField: [
                    { field: 'text', direction: 'asc' }
                ],
            });
        });
    });
</script>