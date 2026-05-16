{{-- resources/views/knowledge/items/partials/sources-panel.blade.php --}}
<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
    <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h3 class="text-sm font-semibold text-gray-900">Sources</h3>
                <p class="mt-1 text-sm text-gray-500">
                    Track the articles, books, and links backing this knowledge item.
                </p>
            </div>

            <div class="flex items-center gap-2">
                <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-slate-100 text-slate-700 text-xs font-medium">
                    {{ $knowledgeItem->sources->count() }} total
                </span>

                @if(!($showAddSource ?? false))
                    <a href="{{ route('knowledge.items.edit', [
                            'knowledgeItem' => $knowledgeItem,
                            'tab' => 'sources',
                            'show_add_source' => 1,
                        ]) }}"
                       class="inline-flex items-center px-3 py-1.5 bg-blue-600 text-white rounded text-sm hover:bg-blue-700">
                        Add Source
                    </a>
                @endif
            </div>
        </div>
    </div>

    @if($showAddSource ?? false)
        <div class="p-6 border-b border-gray-200 space-y-4">
            <div class="flex items-center justify-between gap-4">
                <h4 class="text-sm font-semibold text-gray-900">Add Source</h4>

                <a href="{{ route('knowledge.items.edit', [
                    'knowledgeItem' => $knowledgeItem,
                    'tab' => 'sources',
                ]) }}"
                   class="inline-flex items-center px-3 py-1.5 bg-gray-200 text-gray-800 rounded text-sm hover:bg-gray-300">
                    Cancel
                </a>
            </div>

            <form method="POST"
                  action="{{ route('knowledge.items.sources.store', $knowledgeItem) }}"
                  class="space-y-4">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="source_sourcetype" class="block text-sm font-medium text-gray-700 mb-1">
                            Source Type
                        </label>
                        <select name="sourcetype"
                                id="source_sourcetype"
                                class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                                required>
                            <option value="">Select source type</option>
                            @foreach($sourceTypeOptions as $value => $label)
                                <option value="{{ $value }}" @selected(old('sourcetype') === $value)>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label for="source_sourcetitle" class="block text-sm font-medium text-gray-700 mb-1">
                            Title
                        </label>
                        <input type="text"
                               name="sourcetitle"
                               id="source_sourcetitle"
                               value="{{ old('sourcetitle') }}"
                               class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                               maxlength="255"
                               required>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="md:col-span-2">
                        <label for="source_sourceurl" class="block text-sm font-medium text-gray-700 mb-1">
                            URL
                        </label>
                        <input type="url"
                               name="sourceurl"
                               id="source_sourceurl"
                               value="{{ old('sourceurl') }}"
                               class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                               placeholder="https://">
                    </div>

                    <div>
                        <label for="source_sourcepublisher" class="block text-sm font-medium text-gray-700 mb-1">
                            Publisher / Source
                        </label>
                        <input type="text"
                               name="sourcepublisher"
                               id="source_sourcepublisher"
                               value="{{ old('sourcepublisher') }}"
                               class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="source_retrievedon" class="block text-sm font-medium text-gray-700 mb-1">
                            Retrieved On
                        </label>
                        <input type="date"
                               name="retrievedon"
                               id="source_retrievedon"
                               value="{{ old('retrievedon') }}"
                               class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                    </div>

                    <div>
                        <label for="source_importstatus" class="block text-sm font-medium text-gray-700 mb-1">
                            Import Status
                        </label>
                        <input type="text"
                               name="importstatus"
                               id="source_importstatus"
                               value="{{ old('importstatus') }}"
                               class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                               placeholder="new, reviewed, rejected">
                    </div>

                    <div>
                        <label for="source_reviewedon" class="block text-sm font-medium text-gray-700 mb-1">
                            Reviewed On
                        </label>
                        <input type="date"
                               name="reviewedon"
                               id="source_reviewedon"
                               value="{{ old('reviewedon') }}"
                               class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                    </div>
                </div>

                <div>
                    <label for="source_importedsummary" class="block text-sm font-medium text-gray-700 mb-1">
                        Imported Summary / Notes
                    </label>
                    <textarea name="importedsummary"
                              id="source_importedsummary"
                              rows="3"
                              class="w-full rounded-md border-gray-300 shadow-sm text-sm">{{ old('importedsummary') }}</textarea>
                </div>

                <div class="flex items-center justify-end">
                    <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">
                        Save New Source
                    </button>
                </div>
            </form>
        </div>
    @endif

    <div class="divide-y divide-gray-200">
        @forelse($knowledgeItem->sources->sortBy('sourcetitle') as $source)
            <div class="p-4 space-y-3">
                <div class="flex items-start justify-between gap-4">
                    <div class="space-y-1 min-w-0">
                        <div class="text-sm font-semibold text-gray-900">
                            {{ $source->sourcetitle ?: 'Untitled source' }}
                        </div>

                        <div class="text-xs text-gray-500">
                            Type: {{ $source->sourcetype ?: '—' }}
                            · Publisher: {{ $source->sourcepublisher ?: '—' }}
                            · Status: {{ $source->importstatus ?: '—' }}
                        </div>

                        @if($source->sourceurl)
                            <div class="text-xs">
                                <a href="{{ $source->sourceurl }}"
                                   target="_blank"
                                   rel="noopener noreferrer"
                                   class="text-blue-600 hover:underline break-all">
                                    {{ $source->sourceurl }}
                                </a>
                            </div>
                        @endif

                        @if($source->importedsummary)
                            <div class="text-sm text-gray-700 line-clamp-2">
                                {{ $source->importedsummary }}
                            </div>
                        @endif
                    </div>

                    <div class="flex flex-col items-end gap-2 text-xs text-gray-500 whitespace-nowrap">
                        <div>ID: {{ $source->id }}</div>
                        <div>Retrieved: {{ $source->retrievedon?->format('d M Y') ?? '—' }}</div>
                        <div>Reviewed: {{ $source->reviewedon?->format('d M Y') ?? '—' }}</div>

                        <div class="flex items-center gap-2 mt-1">
                            <a href="{{ route('knowledge.items.edit', [
                                    'knowledgeItem' => $knowledgeItem,
                                    'tab' => 'sources',
                                    'editing_source_id' => $source->id,
                                ]) }}"
                               class="inline-flex items-center px-3 py-1.5 bg-gray-200 text-gray-800 rounded text-xs hover:bg-gray-300">
                                Edit
                            </a>

                            <form method="POST"
                                  action="{{ route('knowledge.items.sources.destroy', [$knowledgeItem, $source]) }}"
                                  onsubmit="return confirm('Delete this source?');">
                                @csrf
                                @method('DELETE')

                                <button type="submit"
                                        class="inline-flex items-center px-3 py-1.5 bg-red-600 text-white rounded text-xs hover:bg-red-700">
                                    Delete
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                @if(isset($editingSourceId) && (int) $editingSourceId === $source->id)
                    <div class="mt-4 border-t border-gray-200 pt-4">
                        <form method="POST"
                              action="{{ route('knowledge.items.sources.update', [$knowledgeItem, $source]) }}"
                              class="space-y-4">
                            @csrf
                            @method('PUT')

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Source Type</label>
                                    <select name="sourcetype"
                                            class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                                            required>
                                        <option value="">Select source type</option>
                                        @foreach($sourceTypeOptions as $value => $label)
                                            <option value="{{ $value }}" @selected(old('sourcetype', $source->sourcetype) === $value)>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                                    <input type="text"
                                           name="sourcetitle"
                                           value="{{ old('sourcetitle', $source->sourcetitle) }}"
                                           class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                                           required>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">URL</label>
                                    <input type="url"
                                           name="sourceurl"
                                           value="{{ old('sourceurl', $source->sourceurl) }}"
                                           class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Publisher / Source</label>
                                    <input type="text"
                                           name="sourcepublisher"
                                           value="{{ old('sourcepublisher', $source->sourcepublisher) }}"
                                           class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Retrieved On</label>
                                    <input type="date"
                                           name="retrievedon"
                                           value="{{ optional($source->retrievedon)->format('Y-m-d') }}"
                                           class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Import Status</label>
                                    <input type="text"
                                           name="importstatus"
                                           value="{{ old('importstatus', $source->importstatus) }}"
                                           class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Reviewed On</label>
                                    <input type="date"
                                           name="reviewedon"
                                           value="{{ optional($source->reviewedon)->format('Y-m-d') }}"
                                           class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Imported Summary / Notes</label>
                                <textarea name="importedsummary"
                                          rows="3"
                                          class="w-full rounded-md border-gray-300 shadow-sm text-sm">{{ old('importedsummary', $source->importedsummary) }}</textarea>
                            </div>

                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('knowledge.items.edit', [
                                    'knowledgeItem' => $knowledgeItem,
                                    'tab' => 'sources',
                                ]) }}"
                                   class="inline-flex items-center px-3 py-1.5 bg-gray-200 text-gray-800 rounded text-xs hover:bg-gray-300">
                                    Cancel
                                </a>

                                <button type="submit"
                                        class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-sm">
                                    Save Source
                                </button>
                            </div>
                        </form>
                        <form method="POST"
                            action="{{ route('knowledge.items.sources.destroy', [$knowledgeItem, $knowledgeSource]) }}"
                            class="inline">
                            @csrf
                            @method('DELETE')

                            <button type="submit"
                                    class="inline-flex items-center px-3 py-1.5 bg-red-100 text-red-700 rounded hover:bg-red-200 text-xs"
                                    onclick="return confirm('Delete this source? This cannot be undone.');">
                                Delete
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        @empty
            <div class="p-6 text-sm text-gray-500">
                No sources recorded for this knowledge item yet.
            </div>
        @endforelse
    </div>
</div>