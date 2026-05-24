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
            'outboundsortorder' => (int) ($validated['sortorder'] ?? 0),
            'inboundsortorder' => 0,
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
        $isOutgoing = $knowledgeRelationship->isOutgoingFor($knowledgeItem);
        $isIncoming = $knowledgeRelationship->isIncomingFor($knowledgeItem);

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

            $knowledgeRelationship->toitemid = $newToItemId;
        }

        if ($isIncoming) {
            $newFromItemId = (int) ($validated['fromitemid'] ?? 0);

            if ($newFromItemId <= 0 || $newFromItemId === (int) $knowledgeItem->id) {
                return back()
                    ->withErrors(['fromitemid' => 'A knowledge item cannot relate to itself.'])
                    ->withInput();
            }

            $knowledgeRelationship->fromitemid = $newFromItemId;
        }

        $knowledgeRelationship->relationshiptype = $validated['relationshiptype'];
        $knowledgeRelationship->effective_date = $validated['effective_date'] ?? null;
        $knowledgeRelationship->notes = $validated['notes'] ?? null;
        $knowledgeRelationship->setSortOrderFor($knowledgeItem, (int) ($validated['sortorder'] ?? 0));
        $knowledgeRelationship->save();

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
            $knowledgeRelationship->isOutgoingFor($knowledgeItem)
            || $knowledgeRelationship->isIncomingFor($knowledgeItem),
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

    public function reorder(Request $request, KnowledgeItem $knowledgeItem): RedirectResponse
    {
        $validated = $request->validate([
            'relationship_order' => ['required', 'array'],
            'relationship_order.*' => ['required', 'integer', 'min:1'],
        ]);

        $relationshipIds = array_map('intval', array_keys($validated['relationship_order']));

        $relationships = KnowledgeRelationship::query()
            ->where(function ($query) use ($knowledgeItem) {
                $query->where('fromitemid', $knowledgeItem->id)
                    ->orWhere('toitemid', $knowledgeItem->id);
            })
            ->whereIn('id', $relationshipIds)
            ->get()
            ->keyBy('id');

        foreach ($validated['relationship_order'] as $relationshipId => $sortOrder) {
            $relationshipId = (int) $relationshipId;
            $relationship = $relationships->get($relationshipId);

            if (!$relationship) {
                continue;
            }

            $relationship->setSortOrderFor($knowledgeItem, (int) $sortOrder);
            $relationship->save();
        }

        return redirect()
            ->route('knowledge.items.edit', [
                'knowledgeItem' => $knowledgeItem,
                'tab' => 'relationships',
            ])
            ->with('success', 'Relationship order updated successfully.');
    }
}