<?php

namespace App\Http\Controllers;

use App\Models\KnowledgeItem;
use App\Models\KnowledgeRelationship;
use App\Models\KnowledgeRelationshipFact;
use App\Models\Place;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class KnowledgeRelationshipFactController extends Controller
{
    protected function rules(): array
    {
        return [
            'facttype' => ['required', 'string', Rule::in(array_keys(KnowledgeRelationshipFact::factTypeOptions()))],
            'factlabel' => ['nullable', 'string', 'max:150'],
            'datetext' => ['nullable', 'string', 'max:100'],
            'datefrom' => ['nullable', 'date'],
            'dateto' => ['nullable', 'date'],
            'datequalifier' => ['nullable', 'string', Rule::in(array_keys(KnowledgeRelationshipFact::dateQualifierOptions()))],
            'placeid' => ['nullable', 'integer', Rule::exists('places', 'id')],
            'valuetext' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'proofstatus' => ['nullable', 'string', Rule::in(array_keys(KnowledgeRelationshipFact::proofStatusOptions()))],
            'ispreferred' => ['nullable', 'boolean'],
            'sortorder' => ['nullable', 'integer', 'min:0'],
        ];
    }

    protected function ensureOwnership(KnowledgeItem $knowledgeItem, KnowledgeRelationship $knowledgeRelationship): void
    {
        abort_unless(
            (int) $knowledgeRelationship->fromitemid === (int) $knowledgeItem->id
            || (int) $knowledgeRelationship->toitemid === (int) $knowledgeItem->id,
            404
        );
    }

    protected function ensureFactOwnership(KnowledgeRelationship $knowledgeRelationship, KnowledgeRelationshipFact $knowledgeRelationshipFact): void
    {
        abort_unless((int) $knowledgeRelationshipFact->knowledgerelationshipid === (int) $knowledgeRelationship->id, 404);
    }

    public function store(Request $request, KnowledgeItem $knowledgeItem, KnowledgeRelationship $knowledgeRelationship): RedirectResponse
    {
        $this->ensureOwnership($knowledgeItem, $knowledgeRelationship);

        $validated = $request->validate($this->rules());

        if (!empty($validated['ispreferred'])) {
            $knowledgeRelationship->relationshipFacts()
                ->where('facttype', $validated['facttype'])
                ->update(['ispreferred' => 0]);
        }

        KnowledgeRelationshipFact::create([
            'knowledgerelationshipid' => $knowledgeRelationship->id,
            'facttype' => $validated['facttype'],
            'factlabel' => $validated['factlabel'] ?? null,
            'datetext' => $validated['datetext'] ?? null,
            'datefrom' => $validated['datefrom'] ?? null,
            'dateto' => $validated['dateto'] ?? null,
            'datequalifier' => $validated['datequalifier'] ?? null,
            'placeid' => $validated['placeid'] ?? null,
            'valuetext' => $validated['valuetext'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'proofstatus' => $validated['proofstatus'] ?? null,
            'ispreferred' => (bool) ($validated['ispreferred'] ?? false),
            'sortorder' => $validated['sortorder'] ?? 0,
        ]);

        return redirect()->route('knowledge.items.edit', [
            'knowledgeItem' => $knowledgeItem,
            'tab' => 'family-history',
        ])->with('success', 'Relationship fact added.');
    }

    public function edit(KnowledgeItem $knowledgeItem, KnowledgeRelationship $knowledgeRelationship, KnowledgeRelationshipFact $knowledgeRelationshipFact): View
    {
        $this->ensureOwnership($knowledgeItem, $knowledgeRelationship);
        $this->ensureFactOwnership($knowledgeRelationship, $knowledgeRelationshipFact);

        $knowledgeItem->load([
            'primaryCategory.domain',
            'primaryCategory.parentCategory',
            'personFacts.place',
            'outgoingRelationships.toItem.primaryCategory',
            'outgoingRelationships.relationshipFacts.place',
            'incomingRelationships.fromItem.primaryCategory',
            'incomingRelationships.relationshipFacts.place',
        ]);

        return view('knowledge.items.edit', [
            'pageTitle' => 'Edit Knowledge Item',
            'knowledgeItem' => $knowledgeItem,
            'activeTab' => 'family-history',
            'showAddPersonFact' => false,
            'editingPersonFactId' => null,
            'editingPersonFact' => null,
            'editingRelationshipFactId' => $knowledgeRelationshipFact->id,
            'editingRelationshipFact' => $knowledgeRelationshipFact,
            'showAddRelationshipFactFor' => null,
            'places' => Place::query()
                ->where('isactive', true)
                ->orderBy('placename')
                ->orderBy('locality')
                ->get(['id', 'placename', 'locality', 'placetype']),
            'hasFamilyHistoryTools' => (bool) ($knowledgeItem->primaryCategory?->domain?->hasfamilyhistorytools ?? false),
            'hasBibleTools' => (bool) ($knowledgeItem->primaryCategory?->domain?->hasbibletools ?? false),
            'hasInvestmentTools' => (bool) ($knowledgeItem->primaryCategory?->domain?->hasinvestmenttools ?? false),
            'personFactTypeOptions' => \App\Models\KnowledgePersonFact::factTypeOptions(),
            'relationshipFactTypeOptions' => KnowledgeRelationshipFact::factTypeOptions(),
            'dateQualifierOptions' => KnowledgeRelationshipFact::dateQualifierOptions(),
            'proofStatusOptions' => KnowledgeRelationshipFact::proofStatusOptions(),
        ]);
    }

    public function update(Request $request, KnowledgeItem $knowledgeItem, KnowledgeRelationship $knowledgeRelationship, KnowledgeRelationshipFact $knowledgeRelationshipFact): RedirectResponse
    {
        $this->ensureOwnership($knowledgeItem, $knowledgeRelationship);
        $this->ensureFactOwnership($knowledgeRelationship, $knowledgeRelationshipFact);

        $validated = $request->validate($this->rules());

        if (!empty($validated['ispreferred'])) {
            $knowledgeRelationship->relationshipFacts()
                ->where('facttype', $validated['facttype'])
                ->where('id', '<>', $knowledgeRelationshipFact->id)
                ->update(['ispreferred' => 0]);
        }

        $knowledgeRelationshipFact->update([
            'facttype' => $validated['facttype'],
            'factlabel' => $validated['factlabel'] ?? null,
            'datetext' => $validated['datetext'] ?? null,
            'datefrom' => $validated['datefrom'] ?? null,
            'dateto' => $validated['dateto'] ?? null,
            'datequalifier' => $validated['datequalifier'] ?? null,
            'placeid' => $validated['placeid'] ?? null,
            'valuetext' => $validated['valuetext'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'proofstatus' => $validated['proofstatus'] ?? null,
            'ispreferred' => (bool) ($validated['ispreferred'] ?? false),
            'sortorder' => $validated['sortorder'] ?? 0,
        ]);

        return redirect()->route('knowledge.items.edit', [
            'knowledgeItem' => $knowledgeItem,
            'tab' => 'family-history',
        ])->with('success', 'Relationship fact updated.');
    }

    public function destroy(KnowledgeItem $knowledgeItem, KnowledgeRelationship $knowledgeRelationship, KnowledgeRelationshipFact $knowledgeRelationshipFact): RedirectResponse
    {
        $this->ensureOwnership($knowledgeItem, $knowledgeRelationship);
        $this->ensureFactOwnership($knowledgeRelationship, $knowledgeRelationshipFact);

        $knowledgeRelationshipFact->delete();

        return redirect()->route('knowledge.items.edit', [
            'knowledgeItem' => $knowledgeItem,
            'tab' => 'family-history',
        ])->with('success', 'Relationship fact deleted.');
    }

    public function reorder(Request $request, KnowledgeItem $knowledgeItem, KnowledgeRelationship $knowledgeRelationship): RedirectResponse
    {
        $validated = $request->validate([
            'ordered_ids' => ['required', 'array'],
            'ordered_ids.*' => ['integer'],
        ]);

        foreach ($validated['ordered_ids'] as $index => $id) {
            KnowledgeRelationshipFact::query()
                ->where('knowledgerelationshipid', $knowledgeRelationship->id)
                ->where('id', $id)
                ->update(['sortorder' => $index + 1]);
        }

        return redirect()->route('knowledge.items.edit', [
            'knowledgeItem' => $knowledgeItem,
            'tab' => 'family-history',
        ])->with('success', 'Relationship facts reordered.');
    }
}