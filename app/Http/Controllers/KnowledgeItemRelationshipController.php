<?php

namespace App\Http\Controllers;

use App\Models\KnowledgeItem;
use App\Models\KnowledgeRelationship;
use Illuminate\Http\Request;
use Illuminate\Mail\Markdown;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class KnowledgeItemRelationshipController extends Controller
{
    public function store(Request $request, KnowledgeItem $knowledgeItem)
    {
        $validated = $request->validate([
            'toitemid' => [
                'required',
                'integer',
                Rule::exists('knowledgeitems', 'id'),
            ],
            'relationshiptype' => [
                'required',
                'string',
                Rule::in(KnowledgeRelationship::typeValues()),
            ],
            'effective_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
            'sortorder' => ['nullable', 'integer', 'min:0'],
        ]);

        if ((int) $validated['toitemid'] === (int) $knowledgeItem->id) {
            return response()->json([
                'message' => 'A knowledge item cannot relate to itself.',
                'errors' => [
                    'toitemid' => ['A knowledge item cannot relate to itself.'],
                ],
            ], 422);
        }

        $relationship = KnowledgeRelationship::create([
            'fromitemid' => $knowledgeItem->id,
            'toitemid' => $validated['toitemid'],
            'relationshiptype' => $validated['relationshiptype'],
            'effective_date' => $validated['effective_date'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'outboundsortorder' => (int) ($validated['sortorder'] ?? 0),
            'inboundsortorder' => 0,
        ]);

        $relationship->load('toItem.primaryCategory');

        return response()->json([
            'relationship' => $this->relationshipPayload(
                $relationship,
                $knowledgeItem
            ),
        ], 201);
    }

    public function update(
        Request $request,
        KnowledgeItem $knowledgeItem,
        KnowledgeRelationship $knowledgeRelationship
    ) {
        $isOutgoing = $knowledgeRelationship->isOutgoingFor($knowledgeItem);
        $isIncoming = $knowledgeRelationship->isIncomingFor($knowledgeItem);

        abort_unless($isOutgoing || $isIncoming, 404);

        $validated = $request->validate([
            'relateditemid' => [
                'required',
                'integer',
                Rule::exists('knowledgeitems', 'id'),
            ],
            'relationshiptype' => [
                'required',
                'string',
                Rule::in(KnowledgeRelationship::typeValues()),
            ],
            'effective_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
            'sortorder' => ['nullable', 'integer', 'min:0'],
        ]);

        $relatedItemId = (int) $validated['relateditemid'];

        if ($relatedItemId === (int) $knowledgeItem->id) {
            return response()->json([
                'message' => 'A knowledge item cannot relate to itself.',
                'errors' => [
                    'relateditemid' => ['A knowledge item cannot relate to itself.'],
                ],
            ], 422);
        }

        if ($isOutgoing) {
            $knowledgeRelationship->toitemid = $relatedItemId;
        }

        if ($isIncoming) {
            $knowledgeRelationship->fromitemid = $relatedItemId;
        }

        $knowledgeRelationship->relationshiptype = $validated['relationshiptype'];
        $knowledgeRelationship->effective_date = $validated['effective_date'] ?? null;
        $knowledgeRelationship->notes = $validated['notes'] ?? null;
        $knowledgeRelationship->setSortOrderFor(
            $knowledgeItem,
            (int) ($validated['sortorder'] ?? 0)
        );
        $knowledgeRelationship->save();

        $knowledgeRelationship->load([
            'fromItem.primaryCategory',
            'toItem.primaryCategory',
        ]);

        return response()->json([
            'relationship' => $this->relationshipPayload(
                $knowledgeRelationship,
                $knowledgeItem
            ),
        ]);
    }

    public function destroy(
        KnowledgeItem $knowledgeItem,
        KnowledgeRelationship $knowledgeRelationship
    ) {
        abort_unless(
            $knowledgeRelationship->isOutgoingFor($knowledgeItem)
            || $knowledgeRelationship->isIncomingFor($knowledgeItem),
            404
        );

        $knowledgeRelationship->delete();

        return response()->noContent();
    }

    public function reorder(Request $request, KnowledgeItem $knowledgeItem)
    {
        $validated = $request->validate([
            'relationship_order' => ['required', 'array'],
            'relationship_order.*' => ['required', 'integer', 'min:1'],
        ]);

        $relationshipIds = array_map(
            'intval',
            array_keys($validated['relationship_order'])
        );

        $relationships = KnowledgeRelationship::query()
            ->where(function ($query) use ($knowledgeItem) {
                $query->where('fromitemid', $knowledgeItem->id)
                    ->orWhere('toitemid', $knowledgeItem->id);
            })
            ->whereIn('id', $relationshipIds)
            ->get()
            ->keyBy('id');

        DB::transaction(function () use (
            $validated,
            $relationships,
            $knowledgeItem
        ) {
            foreach ($validated['relationship_order'] as $relationshipId => $sortOrder) {
                $relationship = $relationships->get((int) $relationshipId);

                if (! $relationship) {
                    continue;
                }

                $relationship->setSortOrderFor(
                    $knowledgeItem,
                    (int) $sortOrder
                );

                $relationship->save();
            }
        });

        return response()->noContent();
    }

    private function relationshipPayload(
        KnowledgeRelationship $relationship,
        KnowledgeItem $knowledgeItem
    ): array {
        $isOutgoing = $relationship->isOutgoingFor($knowledgeItem);
        $relatedItem = $isOutgoing
            ? $relationship->toItem
            : $relationship->fromItem;

        $relationshipTypeLabel = $isOutgoing
            ? $relationship->relationshipTypeLabel()
            : $relationship->inverseRelationshipTypeLabel();

        return [
            'id' => $relationship->id,
            'direction' => $isOutgoing ? 'outgoing' : 'incoming',
            'relateditemid' => $relatedItem?->id,
            'relateditemname' => $relatedItem?->itemname ?? 'Missing related item',
            'relateditemcategory' => $relatedItem?->primaryCategory?->categoryname
                ?? 'Uncategorised',
            'relationshiptype' => $relationship->relationshiptype,
            'relationshiptype_label' => $relationshipTypeLabel,
            'effective_date' => $relationship->effective_date?->format('Y-m-d'),
            'effective_date_display' => $relationship->effective_date?->format('d M Y'),
            'notes' => $relationship->notes ?? '',
            'notes_html' => app(Markdown::class)
                ->parse($relationship->notes ?? '')
                ->toHtml(),
            'sortorder' => (int) ($relationship->sortOrderFor($knowledgeItem) ?? 0),
        ];
    }
}