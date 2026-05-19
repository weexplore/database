<?php

namespace App\Http\Controllers;

use App\Models\KnowledgeItem;
use App\Models\KnowledgeRelationship;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class KnowledgeItemRelationshipController extends Controller
{
    
    public function store(Request $request, KnowledgeItem $knowledgeItem): RedirectResponse
    {
        $validated = $request->validate([
            'toitemid' => [
                'required',
                'integer',
                'different:fromitemid',
                Rule::exists('knowledgeitems', 'id'),
            ],
            'relationshiptype' => ['required', 'string', Rule::in(KnowledgeRelationship::typeValues())],
            'effective_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
            'sortorder' => ['nullable', 'integer', 'min:0'],
        ]);

        if ((int) $validated['toitemid'] === (int) $knowledgeItem->id) {
            return back()
                ->withErrors(['toitemid' => 'A knowledge item cannot relate to itself.'])
                ->withInput();
        }

        KnowledgeRelationship::create([
            'fromitemid' => $knowledgeItem->id,
            'toitemid' => $validated['toitemid'],
            'relationshiptype' => $validated['relationshiptype'],
            'effective_date' => $validated['effective_date'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'sortorder' => $validated['sortorder'] ?? 0,
        ]);

    return redirect()
        ->route('knowledge.items.edit', [
            'knowledgeItem' => $knowledgeItem,
            'tab' => 'relationships',
        ])
            ->with('success', 'Relationship added successfully.');
    }

    public function update(
    Request $request,
    KnowledgeItem $knowledgeItem,
    KnowledgeRelationship $knowledgeRelationship
): RedirectResponse {
    $isOutgoing = (int) $knowledgeRelationship->fromitemid === (int) $knowledgeItem->id;
    $isIncoming = (int) $knowledgeRelationship->toitemid === (int) $knowledgeItem->id;

    abort_unless($isOutgoing || $isIncoming, 404);

    $validated = $request->validate([
        'fromitemid' => ['nullable', 'integer', Rule::exists('knowledgeitems', 'id')],
        'toitemid' => ['nullable', 'integer', Rule::exists('knowledgeitems', 'id')],
        'relationshiptype' => ['required', 'string', Rule::in(KnowledgeRelationship::typeValues())],
        'effective_date' => ['nullable', 'date'],
        'notes' => ['nullable', 'string'],
        'sortorder' => ['nullable', 'integer', 'min:0'],
    ]);

    if ($isOutgoing) {
        $newToItemId = (int) ($validated['toitemid'] ?? 0);

        if ($newToItemId <= 0 || $newToItemId === (int) $knowledgeItem->id) {
            return back()
                ->withErrors(['toitemid' => 'A knowledge item cannot relate to itself.'])
                ->withInput();
        }

        $knowledgeRelationship->update([
            'toitemid' => $newToItemId,
            'relationshiptype' => $validated['relationshiptype'],
            'effective_date' => $validated['effective_date'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'sortorder' => $validated['sortorder'] ?? 0,
        ]);
    }

    if ($isIncoming) {
        $newFromItemId = (int) ($validated['fromitemid'] ?? 0);

        if ($newFromItemId <= 0 || $newFromItemId === (int) $knowledgeItem->id) {
            return back()
                ->withErrors(['fromitemid' => 'A knowledge item cannot relate to itself.'])
                ->withInput();
        }

        $knowledgeRelationship->update([
            'fromitemid' => $newFromItemId,
            'relationshiptype' => $validated['relationshiptype'],
            'effective_date' => $validated['effective_date'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'sortorder' => $validated['sortorder'] ?? 0,
        ]);
    }

    return redirect()
        ->route('knowledge.items.edit', [
            'knowledgeItem' => $knowledgeItem,
            'tab' => 'relationships',
        ])
        ->with('success', 'Relationship updated successfully.');
}

    public function destroy(
        KnowledgeItem $knowledgeItem,
        KnowledgeRelationship $knowledgeRelationship
    ): RedirectResponse {
        abort_unless(
            (int) $knowledgeRelationship->fromitemid === (int) $knowledgeItem->id
            || (int) $knowledgeRelationship->toitemid === (int) $knowledgeItem->id,
            404
        );

        $knowledgeRelationship->delete();

        return redirect()
            ->route('knowledge.items.edit', [
            'knowledgeItem' => $knowledgeItem,
            'tab' => 'relationships',
            ])
            ->with('success', 'Relationship deleted successfully.');
    }
}