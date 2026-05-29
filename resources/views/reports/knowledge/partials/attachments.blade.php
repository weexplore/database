@props([
    'attachments' => collect(),
    'heading' => 'Attachments',
    'printMinimal' => false,
])

@if($attachments->isNotEmpty())
    <section class="rounded-lg border border-gray-200 p-4 space-y-3 break-inside-avoid">
        <div class="flex items-center justify-between gap-2">
            <h5 class="text-sm font-semibold text-gray-900">
                {{ $heading }}
            </h5>

            <span class="text-xs text-gray-500">
                {{ $attachments->count() }} file{{ $attachments->count() === 1 ? '' : 's' }}
            </span>
        </div>

        @php
            $primary = $attachments->firstWhere('pivot.isprimary', true) ?? $attachments->first();
        @endphp

        @if($primary)
            <div class="text-xs text-green-700 bg-green-50 border border-green-200 px-2.5 py-1 rounded-full inline-flex items-center gap-1">
                <span class="font-medium">Primary:</span>
                <span class="truncate max-w-xs">
                    {{ $primary->originalfilename ?? $primary->filename }}
                </span>
            </div>
        @endif

        <div class="space-y-3">
            @foreach($attachments as $attachment)
                @php
                    $isImage = str_starts_with((string) $attachment->mimetype, 'image/');
                    $isPdf = $attachment->mimetype === 'application/pdf';
                    $filename = $attachment->originalfilename ?? $attachment->filename;
                @endphp

                <div class="rounded-md border border-gray-200 bg-gray-50 p-3 text-xs text-gray-700 space-y-2">
                    <div class="flex items-start justify-between gap-2">
                        <div class="min-w-0">
                            <div class="font-medium text-gray-900 break-all">
                                {{ $filename }}
                            </div>

                            <div class="mt-0.5 text-[11px] text-gray-500 flex flex-wrap gap-x-3 gap-y-1">
                                @if($attachment->attachmenttype)
                                    <span>Type: {{ ucfirst($attachment->attachmenttype) }}</span>
                                @endif

                                @if($attachment->mimetype)
                                    <span>MIME: {{ $attachment->mimetype }}</span>
                                @endif

                                @if($attachment->filesizebytes)
                                    <span>
                                        Size: {{ number_format($attachment->filesizebytes / 1024, 1) }} KB
                                    </span>
                                @endif

                                @if(!is_null($attachment->pivot?->sortorder))
                                    <span>Sort: {{ $attachment->pivot->sortorder }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="flex flex-wrap gap-1">
                            @if($attachment->pivot?->isprimary)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 text-[11px]">
                                    Primary
                                </span>
                            @endif
                        </div>
                    </div>

                    @if($attachment->pivot?->description)
                        <div class="text-[11px] text-gray-700 whitespace-pre-line">
                            {{ $attachment->pivot->description }}
                        </div>
                    @endif

                    @if($attachment->uploadedat || $attachment->uploadedby)
                        <div class="text-[11px] text-gray-500 flex flex-wrap gap-x-3 gap-y-1">
                            @if($attachment->uploadedat)
                                <span>
                                    Uploaded {{ \Illuminate\Support\Carbon::parse($attachment->uploadedat)->format('j M Y') }}
                                </span>
                            @endif

                            @if($attachment->uploadedby)
                                <span>By {{ $attachment->uploadedby }}</span>
                            @endif
                        </div>
                    @endif

                    @unless($printMinimal)
                        @if($isImage)
                            <div class="pt-1">
                                <a href="{{ route('knowledge.attachments.view', $attachment) }}"
                                   target="_blank"
                                   rel="noopener noreferrer"
                                   class="inline-block border border-gray-200 rounded overflow-hidden bg-white">
                                    <img
                                        src="{{ route('knowledge.attachments.view', $attachment) }}"
                                        alt="{{ $filename }}"
                                        class="h-24 w-24 object-cover"
                                    >
                                </a>
                            </div>
                        @elseif($isPdf)
                            <div class="pt-1">
                                <span class="inline-flex items-center px-2 py-1 rounded bg-red-50 text-red-700 border border-red-200 text-[11px]">
                                    PDF document
                                </span>
                            </div>
                        @endif

                        <div class="flex flex-wrap items-center gap-2 pt-1">
                            <a href="{{ route('knowledge.attachments.view', $attachment) }}"
                               target="_blank"
                               rel="noopener noreferrer"
                               class="inline-flex items-center px-2 py-1 rounded bg-white text-gray-700 border border-gray-300 hover:bg-gray-50 text-[11px]">
                                View
                            </a>

                            <a href="{{ route('knowledge.attachments.download', $attachment) }}"
                               class="inline-flex items-center px-2 py-1 rounded bg-white text-gray-700 border border-gray-300 hover:bg-gray-50 text-[11px]">
                                Download
                            </a>
                        </div>
                    @else
                        <div class="text-[11px] text-gray-500">
                            @if($isImage)
                                Image attachment
                            @elseif($isPdf)
                                PDF attachment
                            @else
                                File attachment
                            @endif
                        </div>
                    @endunless
                </div>
            @endforeach
        </div>
    </section>
@endif