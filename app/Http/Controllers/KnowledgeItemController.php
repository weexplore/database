<?php

namespace App\Http\Controllers;

use App\Models\KnowledgeCategory;
use App\Models\KnowledgeDomain;
use App\Models\KnowledgeItemAttachment;
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
use Illuminate\Support\Str;


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
            Rule::exists('knowledgeitemtypes', 'id')
                ->where(fn ($query) => $query->where('isactive', 1)),
        ];

        $rules = [
            'existing' => ['nullable', 'array'],
            'existing.*.primarycategoryid' => [
                'required',
                'integer',
                Rule::exists('knowledgecategories', 'id'),
            ],
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
            'new.primarycategoryid' => [
                'nullable',
                'integer',
                Rule::exists('knowledgecategories', 'id'),
            ],
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
            /*
            * Categories whose items must have their sort orders normalised.
            * This includes both source and destination categories when an
            * existing item is moved.
            */
            $affectedCategoryIds = [];

            /*
            * Existing rows.
            *
            * Item names are not editable from this bulk screen. However, an
            * item can be moved to a different category, so validate that its
            * unchanged name does not already exist in the target category.
            */
            foreach ($validated['existing'] ?? [] as $id => $row) {
                $item = KnowledgeItem::query()
                    ->lockForUpdate()
                    ->findOrFail($id);

                $oldCategoryId = (int) $item->primarycategoryid;
                $newCategoryId = (int) $row['primarycategoryid'];

                /*
                * Do not allow a move into a category that already contains an
                * item with the same item name. Exclude the current item so that
                * saving without changing categories remains valid.
                */
                $duplicateExists = KnowledgeItem::query()
                    ->where('primarycategoryid', $newCategoryId)
                    ->where('itemname', $item->itemname)
                    ->whereKeyNot($item->id)
                    ->exists();

                if ($duplicateExists) {
                    throw ValidationException::withMessages([
                        "existing.{$id}.primarycategoryid" =>
                            "Cannot move '{$item->itemname}' because an item with that name already exists in the selected category.",
                    ]);
                }

                $affectedCategoryIds[] = $oldCategoryId;
                $affectedCategoryIds[] = $newCategoryId;

                $item->update([
                    'primarycategoryid' => $newCategoryId,
                    'itemtype' => $row['itemtype'] ?? null,
                    'itemstatus' => $row['itemstatus'] ?? null,
                    'summary' => $row['summary'] ?? null,
                    'sortorder' => (int) ($row['sortorder'] ?? 0),
                    'startdate' => $row['startdate'] ?? null,
                    'enddate' => $row['enddate'] ?? null,
                    'nextreviewdate' => $row['nextreviewdate'] ?? null,
                    'isfeatured' => (bool) ($row['isfeatured'] ?? false),
                    'isactive' => (bool) ($row['isactive'] ?? false),
                ]);
            }

            /*
            * Optional single new row from the inline “Add item” controls.
            */
            $new = $validated['new'] ?? [];
            $hasNewRow = trim((string) ($new['itemname'] ?? '')) !== '';

            if ($hasNewRow) {
                $newCategoryId = (int) ($new['primarycategoryid'] ?? 0);
                $newItemName = trim((string) ($new['itemname'] ?? ''));

                /*
                * The first validation pass allows an empty new row. Once an
                * item name has been entered, the new row becomes mandatory and
                * must be unique within its selected category.
                */
                $newValidator = Validator::make([
                    ...$new,
                    'itemname' => $newItemName,
                    'primarycategoryid' => $newCategoryId,
                ], [
                    'itemname' => [
                        'required',
                        'string',
                        'max:255',
                        Rule::unique('knowledgeitems', 'itemname')
                            ->where(
                                fn ($query) => $query->where(
                                    'primarycategoryid',
                                    $newCategoryId
                                )
                            ),
                    ],
                    'primarycategoryid' => [
                        'required',
                        'integer',
                        Rule::exists('knowledgecategories', 'id'),
                    ],
                    'itemtype' => [
                        'nullable',
                        'integer',
                        Rule::exists('knowledgeitemtypes', 'id')
                            ->where(fn ($query) => $query->where('isactive', 1)),
                    ],
                    'itemstatus' => ['nullable', 'string', 'max:30'],
                    'summary' => ['nullable', 'string'],
                    'sortorder' => ['nullable', 'integer', 'min:0'],
                    'startdate' => ['nullable', 'date'],
                    'enddate' => ['nullable', 'date'],
                    'nextreviewdate' => ['nullable', 'date'],
                    'isfeatured' => ['nullable', 'boolean'],
                    'isactive' => ['nullable', 'boolean'],
                ], [
                    'itemname.unique' =>
                        'An item with this name already exists in the selected category.',
                ], [
                    'itemname' => 'item name',
                    'itemtype' => 'item type',
                    'primarycategoryid' => 'primary category',
                ]);

                if ($newValidator->fails()) {
                    throw ValidationException::withMessages(
                        $newValidator->errors()->toArray()
                    );
                }

                $newValidated = $newValidator->validated();

                $newCategoryId = (int) $newValidated['primarycategoryid'];
                $newItemName = trim((string) $newValidated['itemname']);
                $newRequestedSortOrder = (int) (
                    $newValidated['sortorder'] ?? 0
                );

                /*
                * Blank or zero means append. The later normalisation pass makes
                * sort order values sequential for all affected categories.
                */
                if ($newRequestedSortOrder <= 0) {
                    $currentMaximumSortOrder = KnowledgeItem::query()
                        ->where('primarycategoryid', $newCategoryId)
                        ->lockForUpdate()
                        ->max('sortorder');

                    $newRequestedSortOrder = max(
                        0,
                        (int) ($currentMaximumSortOrder ?? 0)
                    ) + 1;
                }

                KnowledgeItem::create([
                    'primarycategoryid' => $newCategoryId,
                    'itemname' => $newItemName,
                    'itemtype' => $newValidated['itemtype'] ?? null,
                    'itemstatus' => $newValidated['itemstatus'] ?? 'active',
                    'summary' => $newValidated['summary'] ?? null,
                    'sortorder' => $newRequestedSortOrder,
                    'startdate' => $newValidated['startdate'] ?? null,
                    'enddate' => $newValidated['enddate'] ?? null,
                    'nextreviewdate' => $newValidated['nextreviewdate'] ?? null,
                    'isfeatured' => (bool) (
                        $newValidated['isfeatured'] ?? false
                    ),
                    'isactive' => array_key_exists('isactive', $newValidated)
                        ? (bool) $newValidated['isactive']
                        : true,
                ]);

                $affectedCategoryIds[] = $newCategoryId;
            }

            /*
            * Ensure each affected category has a clean sequential order:
            * 1, 2, 3, ...
            *
            * Explicit positive order values are honoured first. Zero and NULL
            * values are placed after them in deterministic name/ID order.
            */
            $affectedCategoryIds = array_values(array_unique(
                array_filter(
                    array_map('intval', $affectedCategoryIds)
                )
            ));

            foreach ($affectedCategoryIds as $categoryId) {
                $items = KnowledgeItem::query()
                    ->where('primarycategoryid', $categoryId)
                    ->lockForUpdate()
                    ->orderByRaw(
                        'CASE WHEN sortorder IS NULL OR sortorder = 0 THEN 1 ELSE 0 END'
                    )
                    ->orderBy('sortorder')
                    ->orderBy('itemname')
                    ->orderBy('id')
                    ->get();

                foreach ($items as $index => $item) {
                    $normalisedSortOrder = $index + 1;

                    if ((int) $item->sortorder !== $normalisedSortOrder) {
                        $item->update([
                            'sortorder' => $normalisedSortOrder,
                        ]);
                    }
                }
            }
        });

        return redirect()->route('knowledge-categories.index', [
            'domainid' => $request->input('domainid'),
            'categoryid' => $request->input('categoryid'),
            'search' => $request->input('search'),
            'itemtype' => $request->input('itemtype'),
            'itemstatus' => $request->input('itemstatus'),
            'active' => $request->input('active'),
            'page' => $request->input('page'),
            'show_selected_category_panel' => $request->input(
                'show_selected_category_panel'
            ),
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
        ->with([
            'domain',
            'parentCategory',
        ])
        ->join(
            'knowledgedomains',
            'knowledgedomains.id',
            '=',
            'knowledgecategories.domainid'
        )
        ->where('knowledgecategories.isactive', 1)
        ->where('knowledgedomains.isactive', 1)
        ->select('knowledgecategories.*')
        ->orderByRaw('COALESCE(knowledgedomains.sortorder, 999999)')
        ->orderBy('knowledgedomains.domainname')
        ->orderByRaw('COALESCE(knowledgecategories.parentcategoryid, 0)')
        ->orderByRaw('COALESCE(knowledgecategories.sortorder, 999999)')
        ->orderBy('knowledgecategories.categoryname')
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
        'books' => \App\Models\BibleBook::query()
            ->orderBy('sortorder')
            ->orderBy('bookname')
            ->get(),

        'versions' => \App\Models\BibleVersion::query()
            ->where('isactive', 1)
            ->orderBy('versionname')
            ->get(),
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
            'iswatchlist' => ['nullable', 'boolean'],
            'isactive' => ['nullable', 'boolean'],
            
        ], [], [
            'itemtype' => 'item type',
        ]);

        if ((int) ($validated['parentitemid'] ?? 0) === (int) $knowledgeItem->id) {
            return back()
                ->withInput()
                ->withErrors(['parentitemid' => 'A knowledge item cannot be its own parent.']);
        }
        $selectedCategory = KnowledgeCategory::query()
            ->select(['id', 'domainid'])
            ->findOrFail($validated['primarycategoryid']);

        if (!empty($validated['parentitemid'])) {
            $selectedParentItem = KnowledgeItem::query()
                ->with('primaryCategory:id,domainid')
                ->findOrFail($validated['parentitemid']);

            if (
                (int) $selectedParentItem->primaryCategory?->domainid
                !== (int) $selectedCategory->domainid
            ) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'parentitemid' => 'The parent item must be in the same domain as the selected primary category.',
                    ]);
            }
        }

        foreach (['startdate', 'enddate', 'nextreviewdate'] as $field) {
            $validated[$field] = filled($validated[$field] ?? null)
                ? $validated[$field]
                : null;
        }
        
        $tagIds = collect($request->input('tagids', []))
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        $categoryChanged = (int) $knowledgeItem->primarycategoryid
            !== (int) $validated['primarycategoryid'];

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
            'iswatchlist' => (bool) ($validated['iswatchlist'] ?? false),
            'isactive' => (bool) ($validated['isactive'] ?? false),
        ]);

        $returnTo = $this->safeReturnUrl(
            $request,
            $request->input('return_to')
        );

        return redirect()->to(
            !$categoryChanged && filled($returnTo)
                ? $returnTo
                : route('knowledge-categories.index', [
                    'domainid' => $selectedCategory->domainid,
                    'categoryid' => $selectedCategory->id,
                ])
        )->with('success', 'Knowledge item updated.');
    }

    public function destroy(Request $request, KnowledgeItem $knowledgeItem): RedirectResponse
    {
        $knowledgeItem->load(['primaryCategory', 'childItems']);

        $returnTo = $this->safeReturnUrl(
            $request,
            $request->input('return_to')
        );

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

    private function safeReturnUrl(Request $request, ?string $returnUrl): ?string
    {
        if (blank($returnUrl)) {
            return null;
        }

        $parsedUrl = parse_url($returnUrl);

        if ($parsedUrl === false) {
            return null;
        }

        if (empty($parsedUrl['host'])) {
            return Str::startsWith($returnUrl, '/')
                ? $returnUrl
                : null;
        }

        $returnPort = isset($parsedUrl['port'])
            ? (int) $parsedUrl['port']
            : ($parsedUrl['scheme'] === 'https' ? 443 : 80);

        $requestPort = (int) $request->getPort();

        return $parsedUrl['host'] === $request->getHost()
            && $parsedUrl['scheme'] === $request->getScheme()
            && $returnPort === $requestPort
            ? $returnUrl
            : null;
    }
}