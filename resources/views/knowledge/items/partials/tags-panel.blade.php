<div class="border border-gray-200 rounded-lg p-5">
    <div class="flex items-start justify-between gap-4">
        <div>
            <h3 class="text-sm font-semibold text-gray-900">Tags</h3>
            <p class="mt-1 text-sm text-gray-500">
                Assign one or more tags to help classify this knowledge item.
            </p>
        </div>
    </div>

    @if(($knowledgeTags ?? collect())->isEmpty())
        <div class="mt-4 rounded-md bg-gray-50 px-4 py-3 text-sm text-gray-500">
            No active tags are available.
        </div>
    @else
        @php
            $selectedTagIds = collect(old('tagids', $knowledgeItem->tags->pluck('id')->all()))
                ->map(fn ($id) => (int) $id)
                ->all();

            $hasSelectedTags = count($selectedTagIds) > 0;
        @endphp

        <div class="mt-4 flex items-start justify-between gap-4">
            <div>
                <p class="text-xs text-gray-500">
                    Show selected tags by default. Open the full list only when you want to add or change them.
                </p>
            </div>

            <button type="button"
                    id="toggle-knowledge-tags-panel"
                    class="inline-flex items-center px-3 py-2 bg-gray-100 text-gray-800 rounded hover:bg-gray-200 text-xs sm:text-sm">
                {{ $hasSelectedTags ? 'Add or change tags' : 'Hide tags' }}
            </button>
        </div>

        <div id="selected-knowledge-tags-summary"
             class="mt-3 flex flex-wrap gap-2 {{ $hasSelectedTags ? '' : 'hidden' }}">
            @foreach($knowledgeTags as $tag)
                @if(in_array((int) $tag->id, $selectedTagIds, true))
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-blue-50 text-blue-700 text-xs font-medium border border-blue-200">
                        {{ $tag->tagname }}
                    </span>
                @endif
            @endforeach
        </div>

        <div id="knowledge-tags-panel" class="mt-4 {{ $hasSelectedTags ? 'hidden' : '' }}">
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3">
                @foreach($knowledgeTags as $tag)
                    <label class="flex items-start gap-3 rounded-lg border border-gray-200 px-4 py-3 hover:bg-gray-50">
                        <input type="checkbox"
                               name="tagids[]"
                               value="{{ $tag->id }}"
                               class="knowledge-tag-checkbox mt-1 rounded border-gray-300 text-blue-600 shadow-sm"
                               @checked(in_array((int) $tag->id, $selectedTagIds, true))>

                        <span class="min-w-0">
                            <span class="block text-sm font-medium text-gray-900">
                                {{ $tag->tagname }}
                            </span>

                            @if($tag->tagtype || $tag->description)
                                <span class="block mt-1 text-xs text-gray-500">
                                    {{ $tag->tagtype ?: 'General' }}
                                    @if($tag->description)
                                        · {{ $tag->description }}
                                    @endif
                                </span>
                            @endif
                        </span>
                    </label>
                @endforeach
            </div>
        </div>
    @endif
</div>