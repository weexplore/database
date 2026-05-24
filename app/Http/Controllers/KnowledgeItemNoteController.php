<?php

namespace App\Http\Controllers;

use App\Models\KnowledgeItem;
use App\Models\KnowledgeNote;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;


class KnowledgeItemNoteController extends Controller
{
    public function store(Request $request, KnowledgeItem $knowledgeItem): RedirectResponse
    {
        $validated = $request->validate([
            'notetype' => ['required', 'string', Rule::in(KnowledgeNote::typeValues())],
            'title' => ['nullable', 'string', 'max:255'],
            'notecontent' => ['required', 'string'],
            'stance' => ['nullable', 'string', 'max:30'],
            'convictionlevel' => ['nullable', 'integer', 'min:1', 'max:5'],
            'reviewdate' => ['nullable', 'date'],
            'isprivate' => ['nullable', 'boolean'],
            'sortorder' => ['nullable', 'integer', 'min:0'],
        ]);

        KnowledgeNote::create([
            'knowledgeitemid' => $knowledgeItem->getKey(),
            'notetype' => $validated['notetype'],
            'title' => $validated['title'] ?? null,
            'notecontent' => $validated['notecontent'],
            'stance' => $validated['stance'] ?? null,
            'convictionlevel' => $validated['convictionlevel'] ?? null,
            'reviewdate' => $validated['reviewdate'] ?? null,
            'isprivate' => (bool) ($validated['isprivate'] ?? false),
            'sortorder' => $validated['sortorder'] ?? 0,
        ]);

        return redirect()
            ->route('knowledge.items.edit', [
                'knowledgeItem' => $knowledgeItem,
                'tab' => 'notes',
            ])
            ->with('success', 'Note added successfully.');
    }

    public function update(Request $request, KnowledgeItem $knowledgeItem, KnowledgeNote $knowledgeNote): RedirectResponse
    {
        $validated = $request->validate([
            'notetype' => ['required', 'string', Rule::in(KnowledgeNote::typeValues())],
            'title' => ['nullable', 'string', 'max:255'],
            'notecontent' => ['required', 'string'],
            'stance' => ['nullable', 'string', 'max:30'],
            'convictionlevel' => ['nullable', 'integer', 'min:1', 'max:5'],
            'reviewdate' => ['nullable', 'date'],
            'isprivate' => ['nullable', 'boolean'],
            'sortorder' => ['nullable', 'integer', 'min:0'],
        ]);

        $knowledgeNote->update([
            'notetype' => $validated['notetype'],
            'title' => $validated['title'] ?? null,
            'notecontent' => $validated['notecontent'],
            'stance' => $validated['stance'] ?? null,
            'convictionlevel' => $validated['convictionlevel'] ?? null,
            'reviewdate' => $validated['reviewdate'] ?? null,
            'isprivate' => (bool) ($validated['isprivate'] ?? false),
            'sortorder' => $validated['sortorder'] ?? 0,
        ]);

        return redirect()
            ->route('knowledge.items.edit', [
                'knowledgeItem' => $knowledgeItem,
                'tab' => 'notes',
            ])
            ->with('success', 'Note updated successfully.');
    }

    public function destroy(KnowledgeItem $knowledgeItem, KnowledgeNote $knowledgeNote): RedirectResponse
    {
        $knowledgeNote->delete();

    return redirect()
        ->route('knowledge.items.edit', [
            'knowledgeItem' => $knowledgeItem,
            'tab' => 'notes',
        ])
        ->with('success', 'Note deleted successfully.');
        }

        
public function reorder(Request $request, KnowledgeItem $knowledgeItem)
{
        $validated = $request->validate([
            'note_order' => ['required', 'array'],
            'note_order.*' => ['required', 'integer', 'min:1'],
        ]);

    $noteOrder = $validated['note_order'];

    $noteIds = $knowledgeItem->notes()
        ->whereIn('id', array_keys($noteOrder))
        ->pluck('id')
        ->map(fn ($id) => (int) $id)
        ->all();

    DB::transaction(function () use ($knowledgeItem, $noteOrder, $noteIds) {
        foreach ($noteOrder as $noteId => $sortOrder) {
            $noteId = (int) $noteId;

            if (!in_array($noteId, $noteIds, true)) {
                continue;
            }

            $knowledgeItem->notes()
                ->where('id', $noteId)
                ->update([
                    'sortorder' => (int) $sortOrder,
                ]);
        }
    });

    return redirect()
        ->route('knowledge.items.edit', [
            'knowledgeItem' => $knowledgeItem,
            'tab' => 'notes',
        ])
        ->with('success', 'Note order updated.');
}
}