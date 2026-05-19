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
        abort_unless(
          (int) $knowledgeRelationship->fromitemid === (int) $knowledgeItem->id
            || (int) $knowledgeRelationship->toitemid === (int) $knowledgeItem->id,
            404
        );

        $validated = $request->validate([
            'toitemid' => [
                'required',
                'integer',
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

        $knowledgeRelationship->update([
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