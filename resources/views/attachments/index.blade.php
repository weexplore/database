<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Attachments
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="w-full max-w-none mx-auto px-4 sm:px-6 lg:px-8 xl:px-10 2xl:px-12 space-y-6">

            @if(session('success'))
                <div class="rounded-md bg-green-50 p-4 text-green-800 border border-green-200">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="rounded-md bg-red-50 p-4 text-red-800 border border-red-200">
                    {{ session('error') }}
                </div>
            @endif

            @if($errors->any())
                <div class="rounded-md bg-red-50 p-4 text-red-800 border border-red-200">
                    <div class="font-medium mb-2">Please fix the following:</div>
                    <ul class="list-disc pl-5 space-y-1 text-sm">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 border-b border-gray-200">
                    <form method="GET" action="{{ route('attachments.index') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                        <div>
                            <label for="linkedtype" class="block text-sm font-medium text-gray-700">Linked type</label>
                            <select id="linkedtype" name="linkedtype" class="mt-1 w-full rounded-md border-gray-300 shadow-sm text-sm">
                                <option value="">All</option>
                                @foreach($linkedTypeOptions as $value => $label)
                                    <option value="{{ $value }}" @selected(request('linkedtype') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="linkedid" class="block text-sm font-medium text-gray-700 mb-1">Linked record</label>

                            @if (!empty($selectedLinkedType) && !empty($linkedRecordOptions))
                                <select id="linkedid" name="linkedid" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                    <option value="">All</option>
                                    @foreach ($linkedRecordOptions as $id => $label)
                                        <option value="{{ $id }}" @selected((string) request('linkedid') === (string) $id)>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            @else
                                <input type="text"
                                    id="linkedid_display"
                                    value=""
                                    placeholder="Select linked type first"
                                    class="w-full rounded-md border-gray-300 bg-gray-50 shadow-sm text-sm"
                                    disabled>
                                <input type="hidden" name="linkedid" value="{{ request('linkedid') }}">
                            @endif
                        </div>

                        <div>
                            <label for="attachmenttype" class="block text-sm font-medium text-gray-700">Attachment type</label>
                            <select id="attachmenttype" name="attachmenttype" class="mt-1 w-full rounded-md border-gray-300 shadow-sm text-sm">
                                <option value="">All</option>
                                @foreach($attachmentTypeOptions as $value => $label)
                                    <option value="{{ $value }}" @selected(request('attachmenttype') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="search" class="block text-sm font-medium text-gray-700">Search</label>
                            <input type="text" id="search" name="search" value="{{ request('search') }}" class="mt-1 w-full rounded-md border-gray-300 shadow-sm text-sm">
                        </div>

                        <div class="md:col-span-5 flex flex-wrap items-center justify-between gap-3 pt-2">
                            <div class="flex flex-wrap items-center gap-2">
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 text-white rounded hover:bg-gray-900 text-sm">
                                    Filter
                                </button>
                                <a href="{{ route('attachments.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 text-sm">
                                    Reset
                                </a>
                                @if(!empty($returnTo))
                                    <a href="{{ $returnTo }}" class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 text-sm">
                                        Return
                                    </a>
                                @endif
                            </div>

                            <a href="{{ route('attachments.index', array_merge(request()->query(), ['show_create' => 1, 'return_to' => $returnTo])) }}"
                            class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-sm">
                                Add Attachment
                            </a>
                        </div>
                    </form>
                </div>

                @if($showCreate)
                    <div class="p-6 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Add Attachment</h3>

                        <form method="POST" action="{{ route('attachments.store') }}" enctype="multipart/form-data" class="space-y-6">
                            @csrf

                            <input type="hidden" name="return_to" value="{{ url()->full() }}">

                            @include('attachments._form', [
                                'attachment' => null,
                                'selectedTripId' => request('trip_id'),
                                'selectedLinkedType' => request('linkedtype'),
                                'selectedLinkedId' => request('linkedid'),
                                'selectedLinkedLabel' => request('linked_label'),
                            ])

                            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
                                <a href="{{ route('attachments.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 text-sm">
                                    Cancel
                                </a>

                                <button type="submit" class="inline-flex items-center px-5 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-sm">
                                    Upload Attachment
                                </button>
                            </div>
                        </form>
                    </div>
                @endif

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium text-gray-700">File</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-700">Linked To</th>
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
                                        <div class="text-gray-900">{{ $linkedTypeOptions[$attachment->linkedtype] ?? $attachment->linkedtype }}</div>
                                        <div class="text-xs text-gray-500 mt-1">ID {{ $attachment->linkedid }}</div>
                                        @if($attachment->tripid)
                                            <div class="text-xs text-gray-500 mt-1">Trip ID {{ $attachment->tripid }}</div>
                                        @endif
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
                                            <a href="{{ route('attachments.view', $attachment) }}"
                                               target="_blank"
                                               rel="noopener noreferrer"
                                               class="inline-flex items-center px-3 py-1.5 bg-gray-50 text-gray-700 rounded hover:bg-gray-100 text-xs">
                                                View
                                            </a>
                                            <a href="{{ route('attachments.download', $attachment) }}"
                                               class="inline-flex items-center px-3 py-1.5 bg-gray-50 text-gray-700 rounded hover:bg-gray-100 text-xs">
                                                Download
                                            </a>
                                            <a href="{{ route('attachments.edit', ['attachment' => $attachment, 'return_to' => url()->full()]) }}"
                                               class="inline-flex items-center px-3 py-1.5 bg-blue-50 text-blue-700 rounded hover:bg-blue-100 text-xs">
                                                Edit
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                                        No attachments found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="p-4 border-t border-gray-200">
                    {{ $attachments->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>