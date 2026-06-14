<?php

namespace App\Http\Controllers;

use App\Models\KnowledgeCategory;
use App\Models\KnowledgeDomain;
use App\Models\KnowledgeItem;
use App\Models\KnowledgeItemType;
use App\Models\KnowledgeNote;
use App\Models\KnowledgePersonFact;
use App\Models\KnowledgeSource;
use App\Models\KnowledgeReviewLog;
use App\Models\KnowledgeRelationship;
use App\Models\KnowledgeRelationshipFact;
use App\Models\KnowledgeTag;
use App\Models\Exchange;
use App\Models\InstrumentType;
use App\Models\Place;
use App\Http\Controllers\InstrumentTransactionController;
use App\Models\Portfolio;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;


class KnowledgeItemController extends Controller
{
    public function index(Request $request): View
    {
        // dd($request->all());
        $filters = [
            'domainid' => $request->integer('domainid') ?: null,
            'categoryid' => $request->integer('categoryid') ?: null,
            'search' => trim((string) $request->query('search', '')),
            'itemtype' => $request->integer('itemtype') ?: null,
            'itemstatus' => trim((string) $request->query('itemstatus', '')),
            'active' => (string) $request->query('active', ''),
        ];

        $domains = KnowledgeDomain::query()
            ->where('isactive', 1)
            ->orderBy('sortorder')
            ->orderBy('domainname')
            ->get();

        if (!$filters['domainid'] && $domains->isNotEmpty()) {
            $filters['domainid'] = (int) $domains->first()->id;
        }

        $categories = collect();
        $items = collect();

        if ($filters['domainid']) {
            $categories = KnowledgeCategory::query()
                ->where('domainid', $filters['domainid'])
                ->orderBy('sortorder')
                ->orderBy('categoryname')
                ->get();

            $items = KnowledgeItem::query()
                ->with(['primaryCategory', 'parentItem', 'itemType'])
                ->whereHas('primaryCategory', function ($q) use ($filters) {
                    $q->where('domainid', $filters['domainid']);
                })
                ->when($filters['categoryid'], fn ($q) => $q->where('primarycategoryid', $filters['categoryid']))
                ->when($filters['search'] !== '', function ($q) use ($filters) {
                    $q->where(function ($sub) use ($filters) {
                        $sub->where('itemname', 'like', '%' . $filters['search'] . '%')
                            ->orWhere('summary', 'like', '%' . $filters['search'] . '%')
                            ->orWhere('detailednotes', 'like', '%' . $filters['search'] . '%')
                            ->orWhere('significance', 'like', '%' . $filters['search'] . '%')
                            ->orWhere('reviewnotes', 'like', '%' . $filters['search'] . '%');
                    });
                })
                ->when($filters['itemtype'], fn ($q) => $q->where('itemtype', $filters['itemtype']))
                ->when($filters['itemstatus'] !== '', fn ($q) => $q->where('itemstatus', $filters['itemstatus']))
                ->when($filters['active'] !== '', fn ($q) => $q->where('isactive', (int) $filters['active']))
                ->orderBy('sortorder')
                ->orderBy('itemname')
                ->get();
        }

        $itemTypes = KnowledgeItemType::query()
            ->where('isactive', true)
            ->orderBy('sortorder')
            ->orderBy('typename')
            ->get();

        $itemStatuses = KnowledgeItem::query()
            ->whereNotNull('itemstatus')
            ->where('itemstatus', '!=', '')
            ->distinct()
            ->orderBy('itemstatus')
            ->pluck('itemstatus');

        $itemStatusOptions = [
            'active' => 'Active',
            'draft' => 'Draft',
            'archived' => 'Archived',
            'reference' => 'Reference',
            'review' => 'Review',
        ];

        $selectedCategory = null;

        if (!empty($filters['categoryid'])) {
            $selectedCategory = KnowledgeCategory::query()
                ->with(['domain', 'parentCategory'])
                ->find($filters['categoryid']);
        }

        
        return view('knowledge.items.index', [
            'pageTitle' => 'Knowledge Items',
            'filters' => $filters,
            'domains' => $domains,
            'selectedCategory' => $selectedCategory,
            'categories' => $categories,
            'items' => $items,
            'itemTypes' => $itemTypes,
            'itemStatuses' => $itemStatuses,
            'itemStatusOptions' => $itemStatusOptions,
        ]);
    }

    public function bulkSave(Request $request): RedirectResponse
{
    $itemTypeRule = [
        'nullable',
        'integer',
        Rule::exists('knowledgeitemtypes', 'id')->where(fn ($query) => $query->where('isactive', 1)),
    ];

    $rules = [
        'existing' => ['nullable', 'array'],
        // 'existing.*.itemname' => ['required', 'string', 'max:255'],
        'existing.*.primarycategoryid' => ['required', 'integer', Rule::exists('knowledgecategories', 'id')],
        'existing.*.itemtype' => $itemTypeRule,
        'existing.*.itemstatus' => ['nullable', 'string', 'max:30'],
        'existing.*.summary' => ['nullable', 'string'],
        'existing.*.sortorder' => ['nullable', 'integer', 'min:0'],
        'existing.*.startdate' => ['nullable', 'date'],
        'existing.*.enddate' => ['nullable', 'date'],
        'existing.*.nextreviewdate' => ['nullable', 'date'],
        'existing.*.isfeatured' => ['nullable', 'boolean'],
        'existing.*.isactive' => ['nullable', 'boolean'],

        'new' => ['nullable', 'array'],
        'new.itemname' => ['nullable', 'string', 'max:255'],
        'new.primarycategoryid' => ['nullable', 'integer', Rule::exists('knowledgecategories', 'id')],
        'new.itemtype' => $itemTypeRule,
        'new.itemstatus' => ['nullable', 'string', 'max:30'],
        'new.summary' => ['nullable', 'string'],
        'new.sortorder' => ['nullable', 'integer', 'min:0'],
        'new.startdate' => ['nullable', 'date'],
        'new.enddate' => ['nullable', 'date'],
        'new.nextreviewdate' => ['nullable', 'date'],
        'new.isfeatured' => ['nullable', 'boolean'],
        'new.isactive' => ['nullable', 'boolean'],

        'domainid' => ['nullable', 'integer'],
        'categoryid' => ['nullable', 'integer'],
        'search' => ['nullable', 'string'],
        'itemtype' => $itemTypeRule,
        'itemstatus' => ['nullable', 'string'],
        'active' => ['nullable', 'in:0,1'],
        'page' => ['nullable', 'integer', 'min:1'],
        'show_selected_category_panel' => ['nullable', 'in:0,1'],
    ];

    $validated = $request->validate($rules, [], [
        'itemtype' => 'item type',
        'existing.*.itemtype' => 'item type',
        'new.itemtype' => 'item type',
    ]);

    DB::transaction(function () use ($validated) {
        foreach ($validated['existing'] ?? [] as $id => $row) {
            $item = KnowledgeItem::findOrFail($id);

            $item->update([
                'primarycategoryid' => $row['primarycategoryid'],
                // 'itemname' => trim((string) $row['itemname']),
                'itemtype' => $row['itemtype'] ?? null,
                'itemstatus' => $row['itemstatus'] ?? null,
                'summary' => $row['summary'] ?? null,
                'sortorder' => $row['sortorder'] ?? 0,
                'startdate' => $row['startdate'] ?? null,
                'enddate' => $row['enddate'] ?? null,
                'nextreviewdate' => $row['nextreviewdate'] ?? null,
                'isfeatured' => (bool) ($row['isfeatured'] ?? false),
                'isactive' => (bool) ($row['isactive'] ?? false),
            ]);
        }

        $new = $validated['new'] ?? [];
        $hasNewRow = trim((string) ($new['itemname'] ?? '')) !== '';

        if ($hasNewRow) {
            $validator = Validator::make($new, [
                'itemname' => ['required', 'string', 'max:255'],
                'primarycategoryid' => ['required', 'integer', Rule::exists('knowledgecategories', 'id')],
                'itemtype' => [
                    'nullable',
                    'integer',
                    Rule::exists('knowledgeitemtypes', 'id')->where(fn ($query) => $query->where('isactive', 1)),
                ],
                'itemstatus' => ['nullable', 'string', 'max:30'],
                'summary' => ['nullable', 'string'],
                'sortorder' => ['nullable', 'integer', 'min:0'],
                'startdate' => ['nullable', 'date'],
                'enddate' => ['nullable', 'date'],
                'nextreviewdate' => ['nullable', 'date'],
                'isfeatured' => ['nullable', 'boolean'],
                'isactive' => ['nullable', 'boolean'],
            ], [], [
                'itemtype' => 'item type',
            ]);

            if ($validator->fails()) {
                throw ValidationException::withMessages($validator->errors()->toArray());
            }

            KnowledgeItem::create([
                'primarycategoryid' => $new['primarycategoryid'],
                'itemname' => trim((string) $new['itemname']),
                'itemtype' => $new['itemtype'] ?? null,
                'itemstatus' => $new['itemstatus'] ?? 'active',
                'summary' => $new['summary'] ?? null,
                'sortorder' => $new['sortorder'] ?? 0,
                'startdate' => $new['startdate'] ?? null,
                'enddate' => $new['enddate'] ?? null,
                'nextreviewdate' => $new['nextreviewdate'] ?? null,
                'isfeatured' => (bool) ($new['isfeatured'] ?? false),
                'isactive' => array_key_exists('isactive', $new) ? (bool) $new['isactive'] : true,
            ]);
        }
    });

    return redirect()->route('knowledge-categories.index', [
        'domainid' => $request->input('domainid'),
        'categoryid' => $request->input('categoryid'),
        'search' => $request->input('search'),
        'knowledgeitemtypeid' => $request->input('itemtype'),
        'itemstatus' => $request->input('itemstatus'),
        'active' => $request->input('active'),
        'page' => $request->input('page'),
        'show_selected_category_panel' => $request->input('show_selected_category_panel'),
    ])->with('success', 'Knowledge items saved successfully.');
}

    public function edit(Request $request, KnowledgeItem $knowledgeItem): View
{
    $knowledgeItem->load([
        'primaryCategory',
        'primaryCategory.domain',
        'primaryCategory.parentCategory',
        'parentItem',
        'childItems',
        'sources',
        'notes',
        'attachments',
        'reviewLogs',
        'itemType',
        'outgoingRelationships.toItem.primaryCategory',
        'incomingRelationships.fromItem.primaryCategory',
        'bibleReferences.book',
        'bibleReferences.version',
        'instrument.instrumentType',
        'instrument.exchange',
        'tags',
        'instrument.aliases',
        'instrument.priceObservations',
        'instrument.corporateActions',
        'instrument.transactions.portfolio',
        'personFacts.place',
        'outgoingRelationships.relationshipFacts.place',
        'incomingRelationships.relationshipFacts.place',
    ]);

    $domainId = optional($knowledgeItem->primaryCategory)->domainid;
    $domain = $knowledgeItem->primaryCategory?->domain;

    $hasBibleTools = (bool) ($domain?->hasbibletools ?? false);
    $hasInvestmentTools = (bool) ($domain?->hasinvestmenttools ?? false);
    $hasFamilyHistoryTools = (bool) ($domain?->hasfamilyhistorytools ?? false);

    $categories = KnowledgeCategory::query()
        ->where('domainid', $domainId)
        ->where('isactive', 1)
        ->orderBy('sortorder')
        ->orderBy('categoryname')
        ->get();

    $parentItems = KnowledgeItem::query()
        ->where('id', '!=', $knowledgeItem->id)
        ->whereHas('primaryCategory', fn ($q) => $q->where('domainid', $domainId))
        ->orderBy('itemname')
        ->get();

    $itemTypes = KnowledgeItemType::query()
        ->where('isactive', true)
        ->orderBy('sortorder')
        ->orderBy('typename')
        ->get();

    $relationshipItems = KnowledgeItem::query()
        ->with([
            'primaryCategory',
            'primaryCategory.parentCategory',
            'primaryCategory.domain',
        ])
        ->where('id', '<>', $knowledgeItem->id)
        ->whereHas('primaryCategory', function ($query) use ($knowledgeItem) {
            $query->where('domainid', $knowledgeItem->primaryCategory?->domainid);
        })
        ->get()
        ->sortBy(function ($item) {
            return sprintf(
                '%s %s',
                mb_strtolower($item->primaryCategory?->categoryname ?? 'zzzz'),
                mb_strtolower($item->itemname ?? '')
            );
        })
        ->values();

    // Relationships for the Relationships tab
        // Relationships for the Relationships tab
    $displayRelationships = collect(
        $knowledgeItem->outgoingRelationships->map(function ($relationship) use ($knowledgeItem) {
            return [
                'relationship' => $relationship,
                'direction' => 'outgoing',
                'relatedItem' => $relationship->toItem,
                'displayTypeLabel' => $relationship->relationshipTypeLabel(),
                'sortorder' => $relationship->sortOrderFor($knowledgeItem),
                'relatedSortName' => mb_strtolower($relationship->toItem?->itemname ?? 'zzzz'),
            ];
        })->all()
    )->merge(
        collect(
            $knowledgeItem->incomingRelationships->map(function ($relationship) use ($knowledgeItem) {
                return [
                    'relationship' => $relationship,
                    'direction' => 'incoming',
                    'relatedItem' => $relationship->fromItem,
                    'displayTypeLabel' => $relationship->inverseRelationshipTypeLabel(),
                    'sortorder' => $relationship->sortOrderFor($knowledgeItem),
                    'relatedSortName' => mb_strtolower($relationship->fromItem?->itemname ?? 'zzzz'),
                ];
            })->all()
        )
    )->sortBy([
        ['sortorder', 'asc'],
        ['relatedSortName', 'asc'],
    ])->values();

    // Combined relationships collection for timeline etc.
    $allRelationships = $knowledgeItem->outgoingRelationships
        ->merge($knowledgeItem->incomingRelationships)
        ->map(function ($relationship) use ($knowledgeItem) {
            $relationship->display_sortorder = $relationship->sortOrderFor($knowledgeItem);
            return $relationship;
        })
        ->sortBy([
            ['display_sortorder', 'asc'],
            ['id', 'asc'],
        ])
        ->values();


    // Query-state flags
    $editingNoteId = $request->integer('editing_note_id');
    $showAddNote = $request->boolean('show_add_note');

    $editingSourceId = $request->integer('editing_source_id');
    $showAddSource = $request->boolean('show_add_source');
    $showFetchSource = $request->boolean('show_fetch_source');

    $editingReviewLogId = $request->integer('editing_review_log_id');
    $showAddReviewLog = $request->boolean('show_add_review_log');

    $editingRelationshipId = $request->integer('editing_relationship_id');
    $showAddRelationship = $request->boolean('show_add_relationship');

    $showAddPersonFact = $request->boolean('show_add_person_fact');
    $editingPersonFactId = $request->integer('editing_person_fact_id');
    $showAddRelationshipFactFor = $request->integer('show_add_relationship_fact_for');
    $editingRelationshipFactId = $request->integer('editing_relationship_fact_id');

    // Tabs
    $allowedTabs = ['details', 'info', 'notes', 'sources', 'review-logs', 'relationships', 'attachments'];

    if (!empty($hasBibleTools)) {
        $allowedTabs[] = 'bible-references';
    }

    if (!empty($hasInvestmentTools)) {
        $allowedTabs[] = 'investments';
    }

    if (!empty($hasFamilyHistoryTools)) {
        $allowedTabs[] = 'family-history';
    }

    $activeTab = $request->string('tab')->value() ?: 'details';

    if (!in_array($activeTab, $allowedTabs, true)) {
        $activeTab = 'details';
    }

    $places = Place::query()
        ->where('isactive', true)
        ->orderBy('placename')
        ->orderBy('locality')
        ->get(['id', 'placename', 'locality', 'placetype']);

    // Option arrays for facts
    $personFactTypeOptions = KnowledgePersonFact::factTypeOptions();
    $relationshipFactTypeOptions = KnowledgeRelationshipFact::factTypeOptions();
    $dateQualifierOptions = KnowledgePersonFact::dateQualifierOptions();
    $proofStatusOptions = KnowledgePersonFact::proofStatusOptions();

    
    return view('knowledge.items.edit', [
        'pageTitle' => 'Edit Knowledge Item',
        'knowledgeItem' => $knowledgeItem,
        'editingNoteId' => $editingNoteId,
        'showAddNote' => $showAddNote,
        'editingSourceId' => $editingSourceId,
        'showAddSource' => $showAddSource,
        'showFetchSource' => $showFetchSource,
        'editingReviewLogId' => $editingReviewLogId,
        'showAddReviewLog' => $showAddReviewLog,
        'editingRelationshipId' => $editingRelationshipId,
        'showAddRelationship' => $showAddRelationship,
        'relationshipItems' => $relationshipItems,
        'displayRelationships' => $displayRelationships,
        'domainId' => $domainId,
        'categories' => $categories,
        'parentItems' => $parentItems,
        'itemTypes' => $itemTypes,
        'itemStatusOptions' => [
            'active' => 'Active',
            'draft' => 'Draft',
            'archived' => 'Archived',
            'reference' => 'Reference',
            'review' => 'Review',
        ],
        'noteTypeOptions' => KnowledgeNote::typeOptions(),
        'sourceTypeOptions' => KnowledgeSource::typeOptions(),
        'reviewTypeOptions' => KnowledgeReviewLog::typeOptions(),
        'relationshipTypeOptions' => KnowledgeRelationship::typeOptions(),
        'activeTab' => $activeTab,
        'places' => $places,
        'knowledgeTags' => KnowledgeTag::query()
            ->where('isactive', 1)
            ->orderByRaw('COALESCE(sortorder, 999999), tagname')
            ->get(),
        'hasBibleTools' => $hasBibleTools,
        'hasInvestmentTools' => $hasInvestmentTools,
        'instrumentTypes' => InstrumentType::query()
            ->where('isactive', 1)
            ->orderBy('typename')
            ->get(),
        'exchanges' => Exchange::query()
            ->where('isactive', 1)
            ->orderBy('exchangename')
            ->get(),
        'corporateActionTypeOptions' => InstrumentCorporateActionController::actionTypeOptions(),
        'transactionTypeOptions' => InstrumentTransactionController::transactionTypeOptions(),
        'portfolios' => Portfolio::query()
            ->where('isactive', 1)
            ->orderBy('portfolioname')
            ->get(),
        'hasFamilyHistoryTools' => $hasFamilyHistoryTools,
        'showAddPersonFact' => $showAddPersonFact,
        'editingPersonFactId' => $editingPersonFactId,
        'editingPersonFact' => $editingPersonFactId
            ? $knowledgeItem->personFacts->firstWhere('id', $editingPersonFactId)
            : null,
        'showAddRelationshipFactFor' => $showAddRelationshipFactFor,
        'editingRelationshipFactId' => $editingRelationshipFactId,
        'editingRelationshipFact' => $knowledgeItem->outgoingRelationships
            ->merge($knowledgeItem->incomingRelationships)
            ->flatMap->relationshipFacts
            ->firstWhere('id', $editingRelationshipFactId),
        'personFactTypeOptions' => $personFactTypeOptions,
        'relationshipFactTypeOptions' => $relationshipFactTypeOptions,
        'dateQualifierOptions' => $dateQualifierOptions,
        'proofStatusOptions' => $proofStatusOptions,
    ]);
}

    public function update(Request $request, KnowledgeItem $knowledgeItem): RedirectResponse
    {
        $validated = $request->validate([
            'primarycategoryid' => ['required', 'integer', Rule::exists('knowledgecategories', 'id')],
            'itemname' => ['required', 'string', 'max:255'],
            'itemtype' => [
                'nullable',
                'integer',
                Rule::exists('knowledgeitemtypes', 'id')->where(fn ($query) => $query->where('isactive', 1)),
            ],
            'itemstatus' => ['nullable', 'string', 'max:30'],
            'summary' => ['nullable', 'string'],
            'detailednotes' => ['nullable', 'string'],
            'significance' => ['nullable', 'string'],
            'reviewnotes' => ['nullable', 'string'],
            'parentitemid' => ['nullable', 'integer', Rule::exists('knowledgeitems', 'id')],
            'placeid' => ['nullable', 'integer', Rule::exists('places', 'id')],
            'startdate' => ['nullable', 'date'],
            'enddate' => ['nullable', 'date'],
            'nextreviewdate' => ['nullable', 'date'],
            'sortorder' => ['nullable', 'integer', 'min:0'],
            'isfeatured' => ['nullable', 'boolean'],
            'isactive' => ['nullable', 'boolean'],
            
        ], [], [
            'itemtype' => 'item type',
        ]);

        if ((int) ($validated['parentitemid'] ?? 0) === (int) $knowledgeItem->id) {
            return back()
                ->withInput()
                ->withErrors(['parentitemid' => 'A knowledge item cannot be its own parent.']);
        }

        $tagIds = collect($request->input('tagids', []))
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        $knowledgeItem->tags()->sync($tagIds);

        $knowledgeItem->update([
            'primarycategoryid' => $validated['primarycategoryid'],
            'itemname' => trim((string) $validated['itemname']),
            'itemtype' => $validated['itemtype'] ?? null,
            'itemstatus' => $validated['itemstatus'] ?? null,
            'summary' => $validated['summary'] ?? null,
            'detailednotes' => $validated['detailednotes'] ?? null,
            'significance' => $validated['significance'] ?? null,
            'reviewnotes' => $validated['reviewnotes'] ?? null,
            'parentitemid' => $validated['parentitemid'] ?? null,
            'placeid' => $validated['placeid'] ?? null,
            'startdate' => $validated['startdate'] ?? null,
            'enddate' => $validated['enddate'] ?? null,
            'nextreviewdate' => $validated['nextreviewdate'] ?? null,
            'sortorder' => $validated['sortorder'] ?? 0,
            'isfeatured' => (bool) ($validated['isfeatured'] ?? false),
            'isactive' => (bool) ($validated['isactive'] ?? false),
        ]);

        $returnTo = $request->input('return_to');

        return redirect()->to(
            $returnTo ?: route('knowledge-categories.index', [
                'domainid' => $knowledgeItem->primaryCategory?->domainid,
                'categoryid' => $knowledgeItem->primarycategoryid,
            ])
        )->with('success', 'Knowledge item updated.');
    }

    public function destroy(Request $request, KnowledgeItem $knowledgeItem): RedirectResponse
{
    $knowledgeItem->load(['primaryCategory', 'childItems']);

    $returnTo = $request->input('return_to');

    if ($knowledgeItem->childItems()->exists()) {
        return redirect()->to(
            $returnTo ?: route('knowledge.items.edit', $knowledgeItem)
        )->with('error', 'This knowledge item cannot be deleted because it has child items.');
    }

    $domainId = $knowledgeItem->primaryCategory?->domainid;
    $categoryId = $knowledgeItem->primarycategoryid;

    $knowledgeItem->delete();

    return redirect()->to(
        $returnTo ?: route('knowledge-categories.index', [
            'domainid' => $domainId,
            'categoryid' => $categoryId,
        ])
    )->with('success', 'Knowledge item deleted.');
}

public function reorder(Request $request, KnowledgeItem $knowledgeItem): RedirectResponse
{
    $validated = $request->validate([
        'notes' => ['required', 'array'],
        'notes.*.sortorder' => ['required', 'integer', 'min:1'],
    ]);

    foreach ($validated['notes'] as $noteId => $row) {
        $note = $knowledgeItem->notes()->whereKey($noteId)->first();

        if ($note) {
            $note->update([
                'sortorder' => $row['sortorder'],
            ]);
        }
    }

    return redirect()->route('knowledge.items.edit', [
        'knowledgeItem' => $knowledgeItem,
        'tab' => 'notes',
    ])->with('success', 'Note order saved.');
}
}