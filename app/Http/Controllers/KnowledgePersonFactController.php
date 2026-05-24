<?php

namespace App\Http\Controllers;

use App\Models\KnowledgeItem;
use App\Models\KnowledgePersonFact;
use App\Models\Place;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class KnowledgePersonFactController extends Controller
{
    protected function rules(): array
    {
        return [
            'facttype' => ['required', 'string', Rule::in(array_keys(KnowledgePersonFact::factTypeOptions()))],
            'factlabel' => ['nullable', 'string', 'max:150'],
            'datetext' => ['nullable', 'string', 'max:100'],
            'datefrom' => ['nullable', 'date'],
            'dateto' => ['nullable', 'date'],
            'datequalifier' => ['nullable', 'string', Rule::in(array_keys(KnowledgePersonFact::dateQualifierOptions()))],
            'placeid' => ['nullable', 'integer', Rule::exists('places', 'id')],
            'valuetext' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'proofstatus' => ['nullable', 'string', Rule::in(array_keys(KnowledgePersonFact::proofStatusOptions()))],
            'ispreferred' => ['nullable', 'boolean'],
            'sortorder' => ['nullable', 'integer', 'min:0'],
        ];
    }

    protected function ensureOwnership(KnowledgeItem $knowledgeItem, KnowledgePersonFact $knowledgePersonFact): void
    {
        abort_unless((int) $knowledgePersonFact->knowledgeitemid === (int) $knowledgeItem->id, 404);
    }

    public function store(Request $request, KnowledgeItem $knowledgeItem): RedirectResponse
    {
        $validated = $request->validate($this->rules());

        if (!empty($validated['ispreferred'])) {
            $knowledgeItem->personFacts()
                ->where('facttype', $validated['facttype'])
                ->update(['ispreferred' => 0]);
        }

        KnowledgePersonFact::create([
            'knowledgeitemid' => $knowledgeItem->id,
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
        ])->with('success', 'Person fact added.');
    }

    public function edit(KnowledgeItem $knowledgeItem, KnowledgePersonFact $knowledgePersonFact): View
    {
        $this->ensureOwnership($knowledgeItem, $knowledgePersonFact);

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
            'editingPersonFactId' => $knowledgePersonFact->id,
            'editingPersonFact' => $knowledgePersonFact,
            'places' => Place::query()
                ->where('isactive', true)
                ->orderBy('placename')
                ->orderBy('locality')
                ->get(['id', 'placename', 'locality', 'placetype']),
            'hasFamilyHistoryTools' => (bool) ($knowledgeItem->primaryCategory?->domain?->hasfamilyhistorytools ?? false),
            'hasBibleTools' => (bool) ($knowledgeItem->primaryCategory?->domain?->hasbibletools ?? false),
            'hasInvestmentTools' => (bool) ($knowledgeItem->primaryCategory?->domain?->hasinvestmenttools ?? false),
            'personFactTypeOptions' => KnowledgePersonFact::factTypeOptions(),
            'dateQualifierOptions' => KnowledgePersonFact::dateQualifierOptions(),
            'proofStatusOptions' => KnowledgePersonFact::proofStatusOptions(),
            'editingRelationshipFactId' => null,
            'showAddRelationshipFactFor' => null,
        ]);
    }

    public function update(Request $request, KnowledgeItem $knowledgeItem, KnowledgePersonFact $knowledgePersonFact): RedirectResponse
    {
        $this->ensureOwnership($knowledgeItem, $knowledgePersonFact);

        $validated = $request->validate($this->rules());

        if (!empty($validated['ispreferred'])) {
            $knowledgeItem->personFacts()
                ->where('facttype', $validated['facttype'])
                ->where('id', '<>', $knowledgePersonFact->id)
                ->update(['ispreferred' => 0]);
        }

        $knowledgePersonFact->update([
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
        ])->with('success', 'Person fact updated.');
    }

    public function destroy(KnowledgeItem $knowledgeItem, KnowledgePersonFact $knowledgePersonFact): RedirectResponse
    {
        $this->ensureOwnership($knowledgeItem, $knowledgePersonFact);

        $knowledgePersonFact->delete();

        return redirect()->route('knowledge.items.edit', [
            'knowledgeItem' => $knowledgeItem,
            'tab' => 'family-history',
        ])->with('success', 'Person fact deleted.');
    }

    public function reorder(Request $request, KnowledgeItem $knowledgeItem): RedirectResponse
{
    $validated = $request->validate([
        'facts' => ['required', 'array'],
        'facts.*.sortorder' => ['required', 'integer', 'min:1'],
    ]);

    foreach ($validated['facts'] as $factId => $row) {
        $fact = $knowledgeItem->personFacts()->whereKey($factId)->first();

        if ($fact) {
            $fact->update([
                'sortorder' => $row['sortorder'],
            ]);
        }
    }

    return redirect()->route('knowledge.items.edit', [
        'knowledgeItem' => $knowledgeItem,
        'tab' => 'family-history',
    ])->with('success', 'Person fact order updated successfully.');
}
}