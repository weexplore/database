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
        ->sortByDesc(fn ($attachment) => [
            (int) $attachment->isprimary,
            optional($attachment->uploadedat)?->timestamp ?? 0,
            $attachment->id,
        ]);
@endphp

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
              action="{{ route('knowledge.attachments.store', $knowledgeItem) }}"
              enctype="multipart/form-data"
              class="space-y-6">
            @csrf

            <input type="hidden" name="return_to" value="{{ route('knowledge.items.edit', ['knowledgeItem' => $knowledgeItem, 'tab' => 'attachments']) }}">

            @include('knowledge-attachments._form', [
                'knowledgeAttachment' => null,
                'knowledgeItem' => $knowledgeItem,
                'attachmentTypeOptions' => $attachmentTypeOptions,
            ])

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
                <button type="submit" class="inline-flex items-center px-5 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-sm">
                    Upload Attachment
                </button>
            </div>
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left font-medium text-gray-700">File</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-700">Type</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-700">Uploaded</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-700">Size</th>
                    <th class="px-4 py-3 text-right font-medium text-gray-700">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @forelse($attachments as $attachment)
                    <tr>
                        <td class="px-4 py-3 align-top">
                            <div class="font-medium text-gray-900">{{ $attachment->originalfilename }}</div>
                            @if($attachment->description)
                                <div class="text-xs text-gray-500 mt-1">{{ $attachment->description }}</div>
                            @endif
                            <div class="text-xs text-gray-500 mt-1">{{ $attachment->mimetype }}</div>
                        </td>
                        <td class="px-4 py-3 align-top">
                            <div class="text-gray-900">{{ $attachmentTypeOptions[$attachment->attachmenttype] ?? $attachment->attachmenttype }}</div>
                            @if($attachment->isprimary)
                                <div class="text-xs text-blue-600 mt-1">Primary</div>
                            @endif
                        </td>
                        <td class="px-4 py-3 align-top">
                            <div class="text-gray-900">{{ optional($attachment->uploadedat)->format('d M Y H:i') }}</div>
                            @if($attachment->uploadedby)
                                <div class="text-xs text-gray-500 mt-1">{{ $attachment->uploadedby }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3 align-top">
                            <div class="text-gray-900">{{ number_format(((int) $attachment->filesizebytes) / 1024, 1) }} KB</div>
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
                                        'knowledgeAttachment' => $attachment,
                                        'return_to' => route('knowledge.items.edit', ['knowledgeItem' => $knowledgeItem, 'tab' => 'attachments']),
                                   ]) }}"
                                   class="inline-flex items-center px-3 py-1.5 bg-blue-50 text-blue-700 rounded hover:bg-blue-100 text-xs">
                                    Edit
                                </a>
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