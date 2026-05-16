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
        @endphp

        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3">
            @foreach($knowledgeTags as $tag)
                <label class="flex items-start gap-3 rounded-lg border border-gray-200 px-4 py-3 hover:bg-gray-50">
                    <input type="checkbox"
                           name="tagids[]"
                           value="{{ $tag->id }}"
                           class="mt-1 rounded border-gray-300 text-blue-600 shadow-sm"
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
    @endif
</div>