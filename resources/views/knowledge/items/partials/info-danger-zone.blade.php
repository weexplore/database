{{-- resources/views/knowledge/items/partials/info-danger-zone.blade.php --}}
<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-red-200">
    <div class="px-5 py-4 border-b border-red-200 bg-red-50">
        <h3 class="text-sm font-semibold text-red-800">Danger Zone</h3>
    </div>

    <div class="p-5 space-y-4 text-sm">
        <p class="text-red-700">
            Deleting this knowledge item will permanently remove it. This action cannot be undone.
        </p>

        @if($knowledgeItem->childItems->count() > 0)
            <div class="rounded-md bg-yellow-50 border border-yellow-200 px-3 py-2 text-yellow-800 text-sm">
                This item currently has child items and should not be deleted until those links are resolved.
            </div>
        @endif

        <form method="POST"
              action="{{ route('knowledge.items.destroy', $knowledgeItem) }}"
              id="delete-knowledge-item-form">
            @csrf
            @method('DELETE')

            <button type="submit"
                    class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed"
                    onclick="return confirm('Delete knowledge item {{ addslashes($knowledgeItem->itemname ?: ('#' . $knowledgeItem->id)) }}? This cannot be undone.');"
                    @disabled($knowledgeItem->childItems->count() > 0)>
                Delete Knowledge Item
            </button>
        </form>
    </div>
</div>