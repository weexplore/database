<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Edit Knowledge Attachment
        </h2>
    </x-slot>

    @php
        $returnTo = $returnTo ?? route('knowledge.items.edit', [
            'knowledgeItem' => $knowledgeItem,
            'tab' => 'attachments',
        ]);
    @endphp

    <div class="py-6">
        <div class="w-full max-w-none mx-auto px-4 sm:px-6 lg:px-8 xl:px-10 2xl:px-12 space-y-6">

            @include('partials.admin.flash-messages')
            @include('partials.admin.validation-summary')

            <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
                <div class="xl:col-span-2">
                    <div class="bg-white shadow-sm sm:rounded-lg p-6">
                        <form method="POST"
                            action="{{ route('knowledge.attachments.update', [
                                'knowledgeItem' => $knowledgeItem,
                                'knowledgeAttachment' => $knowledgeAttachment,
                            ]) }}"
                            class="space-y-6">
                            @csrf
                            @method('PUT')

                            <input type="hidden" name="return_to" value="{{ $returnTo }}">

                            @include('knowledge-attachments._form', [
                                'knowledgeAttachment' => $knowledgeAttachment,
                                'knowledgeItem' => $knowledgeItem,
                                'attachmentTypeOptions' => $attachmentTypeOptions,
                            ])

                            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
                                <a href="{{ $returnTo }}" class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 text-sm">
                                    Cancel
                                </a>

                                <button type="submit" class="inline-flex items-center px-5 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-sm">
                                    Save Attachment
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="bg-white shadow-sm sm:rounded-lg p-4">
                        <h3 class="text-sm font-semibold text-gray-900">File Actions</h3>
                        <div class="mt-4 flex flex-wrap gap-2">
                            <a href="{{ route('knowledge.attachments.view', [
                                    'knowledgeItem' => $knowledgeItem,
                                    'knowledgeAttachment' => $knowledgeAttachment,
                                ]) }}"
                               target="_blank"
                               rel="noopener noreferrer"
                               class="inline-flex items-center px-3 py-2 bg-gray-100 text-gray-800 rounded hover:bg-gray-200 text-sm">
                                View
                            </a>
                            <a href="{{ route('knowledge.attachments.download', $knowledgeAttachment) }}"
                               class="inline-flex items-center px-3 py-2 bg-gray-100 text-gray-800 rounded hover:bg-gray-200 text-sm">
                                Download
                            </a>
                        </div>
                    </div>

                    <div class="bg-white shadow-sm sm:rounded-lg p-4">
                        <h3 class="text-sm font-semibold text-gray-900">Delete Attachment</h3>
                        <form method="POST" action="{{ route('knowledge.attachments.destroy', [
                            'knowledgeItem' => $knowledgeItem,
                            'knowledgeAttachment' => $knowledgeAttachment,
                        ]) }}" class="mt-4" onsubmit="return confirm('Delete this attachment?');">
                            @csrf
                            @method('DELETE')
                            <input type="hidden" name="return_to" value="{{ $returnTo }}">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 text-sm">
                                Delete
                            </button>
                        </form>
                    </div>

                    <div class="bg-white shadow-sm sm:rounded-lg p-4">
                        <h3 class="text-sm font-semibold text-gray-900">
                            This Item’s Attachments
                        </h3>

                        <div class="mt-3 space-y-2">
                            @forelse($attachments as $attachment)
                                @php
                                    $isCurrentAttachment = (int) $attachment->id === (int) $knowledgeAttachment->id;
                                    $expiryDate = $attachment->pivot?->expirydate;
                                @endphp

                                <div
                                    @class([
                                        'rounded border p-3 text-sm',
                                        'border-blue-300 bg-blue-50' => $isCurrentAttachment,
                                        'border-gray-200 bg-white' => !$isCurrentAttachment,
                                    ])
                                >
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0 flex-1">
                                            <div class="break-words font-medium text-gray-900">
                                                {{ $attachment->originalfilename }}
                                            </div>

                                            <div class="mt-1 text-xs text-gray-500">
                                                {{ $attachment->attachmenttype ?: 'document' }}

                                                @if($attachment->pivot?->isprimary)
                                                    · Primary
                                                @endif
                                            </div>

                                            @if(filled($attachment->pivot?->description))
                                                <div class="mt-1 text-xs text-gray-600">
                                                    {{ $attachment->pivot->description }}
                                                </div>
                                            @endif

                                            @if($expiryDate)
                                                <div
                                                    @class([
                                                        'mt-1 text-xs font-medium',
                                                        'text-red-700' => $expiryDate->isPast()
                                                            && !$expiryDate->isToday(),
                                                        'text-amber-700' => $expiryDate->isToday()
                                                            || $expiryDate->between(
                                                                today(),
                                                                today()->copy()->addDays(14)
                                                            ),
                                                        'text-gray-500' => !$expiryDate->isPast()
                                                            && !$expiryDate->isToday()
                                                            && !$expiryDate->between(
                                                                today(),
                                                                today()->copy()->addDays(14)
                                                            ),
                                                    ])
                                                >
                                                    Expires {{ $expiryDate->format('d M Y') }}

                                                    @if($expiryDate->isPast() && !$expiryDate->isToday())
                                                        · Expired
                                                    @elseif($expiryDate->isToday())
                                                        · Expires today
                                                    @elseif($expiryDate->between(
                                                        today(),
                                                        today()->copy()->addDays(14)
                                                    ))
                                                        · Expires soon
                                                    @endif
                                                </div>
                                            @endif
                                        </div>

                                        <div class="flex shrink-0 flex-wrap gap-1">
                                            <a
                                                href="{{ route('knowledge.attachments.view', $attachment) }}"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                class="inline-flex items-center rounded bg-gray-100 px-2 py-1 text-xs font-medium text-gray-700 hover:bg-gray-200"
                                            >
                                                View
                                            </a>

                                            @if(!$isCurrentAttachment)
                                                <a
                                                    href="{{ route('knowledge.attachments.edit', [
                                                        'knowledgeItem' => $knowledgeItem,
                                                        'knowledgeAttachment' => $attachment,
                                                        'return_to' => $returnTo,
                                                    ]) }}"
                                                    class="inline-flex items-center rounded bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700 hover:bg-blue-100"
                                                >
                                                    Edit
                                                </a>
                                            @else
                                                <span class="inline-flex items-center rounded bg-blue-600 px-2 py-1 text-xs font-medium text-white">
                                                    Editing
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-sm text-gray-500">
                                    No attachments found.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>