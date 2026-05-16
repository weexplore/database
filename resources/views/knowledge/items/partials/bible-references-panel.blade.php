@php
    $books = \App\Models\BibleBook::query()
        ->orderBy('sortorder')
        ->orderBy('bookname')
        ->get();

    $versions = \App\Models\BibleVersion::query()
        ->where('isactive', 1)
        ->orderBy('versionname')
        ->get();

    $references = $knowledgeItem->bibleReferences
        ->sortBy([
            ['book.sortorder', 'asc'],
            ['chapterfrom', 'asc'],
            ['versefrom', 'asc'],
            ['id', 'asc'],
        ]);
@endphp

<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
    <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h3 class="text-lg font-semibold text-gray-900">Bible References</h3>
                <p class="mt-1 text-sm text-gray-500">
                    Link scripture references to this knowledge item.
                </p>
            </div>
        </div>
    </div>

    <div class="p-6 border-b border-gray-200 bg-gray-50">
        <form method="POST"
              action="{{ route('knowledge.items.bible-references.store', $knowledgeItem) }}"
              class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
                <div>
                    <label for="versionid" class="block text-sm font-medium text-gray-700 mb-1">Version</label>
                    <select id="versionid" name="versionid" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                        <option value="">Select</option>
                        @foreach($versions as $version)
                            <option value="{{ $version->id }}" @selected(old('versionid') == $version->id)>
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
                            <option value="{{ $book->id }}" @selected(old('bookid') == $book->id)>
                                {{ $book->bookname }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="chapterfrom" class="block text-sm font-medium text-gray-700 mb-1">Chapter from</label>
                    <input type="number" id="chapterfrom" name="chapterfrom" value="{{ old('chapterfrom') }}" min="1" class="w-full rounded-md border-gray-300 shadow-sm text-sm" required>
                </div>

                <div>
                    <label for="versefrom" class="block text-sm font-medium text-gray-700 mb-1">Verse from</label>
                    <input type="number" id="versefrom" name="versefrom" value="{{ old('versefrom') }}" min="1" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                </div>

                <div>
                    <label for="chapterto" class="block text-sm font-medium text-gray-700 mb-1">Chapter to</label>
                    <input type="number" id="chapterto" name="chapterto" value="{{ old('chapterto') }}" min="1" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                </div>

                <div>
                    <label for="verseto" class="block text-sm font-medium text-gray-700 mb-1">Verse to</label>
                    <input type="number" id="verseto" name="verseto" value="{{ old('verseto') }}" min="1" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                </div>

                <div class="md:col-span-2">
                    <label for="referencelabel" class="block text-sm font-medium text-gray-700 mb-1">Reference label</label>
                    <input type="text" id="referencelabel" name="referencelabel" value="{{ old('referencelabel') }}" class="w-full rounded-md border-gray-300 shadow-sm text-sm" placeholder="Optional, auto-built if blank">
                </div>
            </div>

            <div>
                <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                <textarea id="notes" name="notes" rows="3" class="w-full rounded-md border-gray-300 shadow-sm text-sm">{{ old('notes') }}</textarea>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
                <button type="submit" class="inline-flex items-center px-5 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-sm">
                    Add Bible Reference
                </button>
            </div>
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left font-medium text-gray-700">Reference</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-700">Version</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-700">Notes</th>
                    <th class="px-4 py-3 text-right font-medium text-gray-700">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @forelse($references as $reference)
                    <tr>
                        <td class="px-4 py-3 align-top">
                            <div class="font-medium text-gray-900">
                                {{ $reference->referencelabel ?: (($reference->book->bookname ?? 'Book') . ' ' . $reference->chapterfrom) }}
                            </div>
                            <div class="text-xs text-gray-500 mt-1">
                                {{ $reference->book->bookname ?? '—' }}
                                {{ $reference->chapterfrom }}@if($reference->versefrom):{{ $reference->versefrom }}@endif
                                @if($reference->chapterto || $reference->verseto)
                                    -
                                    {{ $reference->chapterto ?: $reference->chapterfrom }}@if($reference->verseto):{{ $reference->verseto }}@endif
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-3 align-top">
                            <div class="text-gray-900">{{ $reference->version->versionname ?? '—' }}</div>
                        </td>
                        <td class="px-4 py-3 align-top">
                            <div class="text-gray-700">{{ $reference->notes ?: '—' }}</div>
                        </td>
                        <td class="px-4 py-3 align-top text-right">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('knowledge.items.bible-references.edit', $reference) }}"
                                   class="inline-flex items-center px-3 py-1.5 bg-blue-50 text-blue-700 rounded hover:bg-blue-100 text-xs">
                                    Edit
                                </a>

                                <form method="POST"
                                      action="{{ route('knowledge.items.bible-references.destroy', $reference) }}"
                                      onsubmit="return confirm('Delete this Bible reference?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="inline-flex items-center px-3 py-1.5 bg-red-50 text-red-700 rounded hover:bg-red-100 text-xs">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-8 text-center text-gray-500">
                            No Bible references found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>