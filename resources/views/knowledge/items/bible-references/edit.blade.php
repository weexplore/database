{{-- resources/views/knowledge/items/bible-references/edit.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Edit Bible Reference
        </h2>
    </x-slot>

    @php
        $returnTo = $returnTo ?? route('knowledge.items.edit', [
            'knowledgeItem' => $knowledgeItem,
            'tab' => 'bible-references',
        ]);

        $hasCachedPassage = filled($bibleReference->cachedpassagetext);
    @endphp

    <div class="py-6">
        <div class="w-full max-w-none mx-auto px-4 sm:px-6 lg:px-8 xl:px-10 2xl:px-12 space-y-6">

            @include('partials.admin.flash-messages')
            @include('partials.admin.validation-summary')

            <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
                <div class="xl:col-span-2 space-y-6">
                    <div class="bg-white shadow-sm sm:rounded-lg p-6">
                        <form method="POST"
                              action="{{ route('knowledge.items.bible-references.update', $bibleReference) }}"
                              class="space-y-6">
                            @csrf
                            @method('PUT')

                            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
                                <div>
                                    <label for="versionid" class="block text-sm font-medium text-gray-700 mb-1">Version</label>
                                    <select id="versionid" name="versionid" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                        <option value="">Select</option>
                                        @foreach($versions as $version)
                                            <option value="{{ $version->id }}" @selected((string) old('versionid', $bibleReference->versionid) === (string) $version->id)>
                                                {{ $version->versionname }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label for="bookid" class="block text-sm font-medium text-gray-700 mb-1">Book</label>
                                    <select id="bookid" name="bookid" class="w-full rounded-md border-gray-300 shadow-sm text-sm" required>
                                        <option value="">Select</option>
                                        @foreach($books as $book)
                                            <option value="{{ $book->id }}" @selected((string) old('bookid', $bibleReference->bookid) === (string) $book->id)>
                                                {{ $book->bookname }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label for="chapterfrom" class="block text-sm font-medium text-gray-700 mb-1">Chapter from</label>
                                    <input type="number" id="chapterfrom" name="chapterfrom" value="{{ old('chapterfrom', $bibleReference->chapterfrom) }}" min="1" class="w-full rounded-md border-gray-300 shadow-sm text-sm" required>
                                </div>

                                <div>
                                    <label for="versefrom" class="block text-sm font-medium text-gray-700 mb-1">Verse from</label>
                                    <input type="number" id="versefrom" name="versefrom" value="{{ old('versefrom', $bibleReference->versefrom) }}" min="1" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                </div>

                                <div>
                                    <label for="chapterto" class="block text-sm font-medium text-gray-700 mb-1">Chapter to</label>
                                    <input type="number" id="chapterto" name="chapterto" value="{{ old('chapterto', $bibleReference->chapterto) }}" min="1" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                </div>

                                <div>
                                    <label for="verseto" class="block text-sm font-medium text-gray-700 mb-1">Verse to</label>
                                    <input type="number" id="verseto" name="verseto" value="{{ old('verseto', $bibleReference->verseto) }}" min="1" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                </div>

                                <div class="md:col-span-2">
                                    <label for="referencelabel" class="block text-sm font-medium text-gray-700 mb-1">Reference label</label>
                                    <input type="text" id="referencelabel" name="referencelabel" value="{{ old('referencelabel', $bibleReference->referencelabel) }}" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                </div>
                            </div>

                            <div>
                                <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                                <textarea id="notes" name="notes" rows="4" class="w-full rounded-md border-gray-300 shadow-sm text-sm">{{ old('notes', $bibleReference->notes) }}</textarea>
                            </div>

                            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
                                <a href="{{ $returnTo }}" class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 text-sm">
                                    Cancel
                                </a>

                                <button type="submit" class="inline-flex items-center px-5 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-sm">
                                    Save Bible Reference
                                </button>
                            </div>
                        </form>
                    </div>

                    <div class="bg-white shadow-sm sm:rounded-lg p-6">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h3 class="text-sm font-semibold text-gray-900">Passage Cache</h3>
                                <p class="mt-1 text-sm text-gray-600">
                                    Fetch and store the passage text for this reference.
                                </p>
                            </div>

                            <form method="POST"
                                  action="{{ route('knowledge.items.bible-references.fetch-passage', $bibleReference) }}">
                                @csrf

                                <button type="submit"
                                        class="inline-flex items-center px-4 py-2 {{ $hasCachedPassage ? 'bg-amber-600 hover:bg-amber-700' : 'bg-blue-600 hover:bg-blue-700' }} text-white rounded text-sm">
                                    {{ $hasCachedPassage ? 'Refresh Passage' : 'Fetch Passage' }}
                                </button>
                            </form>
                        </div>

                        @if($hasCachedPassage)
                            <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                                    <div class="text-xs font-medium uppercase tracking-wide text-gray-500">Stored reference</div>
                                    <div class="mt-1 text-sm text-gray-900">
                                        {{ $bibleReference->cachedreferencetext ?: $bibleReference->referencelabel }}
                                    </div>
                                </div>

                                <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                                    <div class="text-xs font-medium uppercase tracking-wide text-gray-500">Last fetched</div>
                                    <div class="mt-1 text-sm text-gray-900">
                                        {{ $bibleReference->passagefetchedat?->format('d M Y H:i') ?? 'Not recorded' }}
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4">
                                <div class="text-xs font-medium uppercase tracking-wide text-gray-500 mb-2">Cached passage text</div>
                                <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 text-sm text-gray-800 whitespace-pre-line leading-6">
                                    {{ $bibleReference->cachedpassagetext }}
                                </div>
                            </div>
                        @else
                            <div class="mt-4 rounded-lg border border-dashed border-gray-300 bg-gray-50 p-4 text-sm text-gray-600">
                                No cached passage has been stored yet.
                            </div>
                        @endif
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="bg-white shadow-sm sm:rounded-lg p-4">
                        <h3 class="text-sm font-semibold text-gray-900">Delete Bible Reference</h3>
                        <form method="POST"
                              action="{{ route('knowledge.items.bible-references.destroy', $bibleReference) }}"
                              class="mt-4"
                              onsubmit="return confirm('Delete this Bible reference?');">
                            @csrf
                            @method('DELETE')

                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 text-sm">
                                Delete
                            </button>
                        </form>
                    </div>

                    <div class="bg-white shadow-sm sm:rounded-lg p-4">
                        <h3 class="text-sm font-semibold text-gray-900">Linked Item</h3>
                        <div class="mt-3 text-sm text-gray-700">
                            {{ $knowledgeItem->itemname }}
                        </div>
                    </div>

                    <div class="bg-white shadow-sm sm:rounded-lg p-4">
                        <h3 class="text-sm font-semibold text-gray-900">Reference Summary</h3>
                        <dl class="mt-3 space-y-3 text-sm">
                            <div>
                                <dt class="text-gray-500">Reference label</dt>
                                <dd class="text-gray-900">{{ $bibleReference->referencelabel }}</dd>
                            </div>

                            <div>
                                <dt class="text-gray-500">Version</dt>
                                <dd class="text-gray-900">{{ $bibleReference->version?->versionname ?? 'Not selected' }}</dd>
                            </div>

                            <div>
                                <dt class="text-gray-500">Book</dt>
                                <dd class="text-gray-900">{{ $bibleReference->book?->bookname ?? 'Not selected' }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>