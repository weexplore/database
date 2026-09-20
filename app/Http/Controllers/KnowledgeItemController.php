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
use App\Models\BibleBook;
use App\Models\BibleVersion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;


class KnowledgeItemController extends Controller
{
    public function index(Request $request): View
    {
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
            ->get(['id', 'domainname', 'sortorder']);

        if (!$filters['domainid'] && $domains->isNotEmpty()) {
            $filters['domainid'] = (int) $domains->first()->id;
        }

        $categories = collect();
        $items = KnowledgeItem::query()
            ->whereRaw('1 = 0')
            ->paginate(50)
            ->withQueryString();

        if ($filters['domainid']) {
            $categories = KnowledgeCategory::query()
                ->where('domainid', $filters['domainid'])
                ->orderBy('sortorder')
                ->orderBy('categoryname')
                ->get([
                    'id',
                    'domainid',
                    'parentcategoryid',
                    'categoryname',
                    'sortorder',
                ]);

            $items = KnowledgeItem::query()
                ->select([
                    'id',
                    'primarycategoryid',
                    'parentitemid',
                    'itemtype',
                    'itemname',
                    'itemstatus',
                    'summary',
                    'sortorder',
                    'startdate',
                    'enddate',
                    'nextreviewdate',
                    'isfeatured',
                    'iswatchlist',
                    'isactive',
                ])
                ->with([
                    'primaryCategory:id,domainid,categoryname',
                    'parentItem:id,itemname',
                    'itemType:id,typename',
                ])
                ->whereHas(
                    'primaryCategory',
                    fn ($query) => $query->where(
                        'domainid',
                        $filters['domainid']
                    )
                )
                ->when(
                    $filters['categoryid'],
                    fn ($query) => $query->where(
                        'primarycategoryid',
                        $filters['categoryid']
                    )
                )
                ->when($filters['search'] !== '', function ($query) use ($filters) {
                    $search = $filters['search'];

                    $query->where(function ($subQuery) use ($search) {
                        $subQuery
                            ->where('itemname', 'like', '%' . $search . '%')
                            ->orWhere('summary', 'like', '%' . $search . '%')
                            ->orWhere('detailednotes', 'like', '%' . $search . '%')
                            ->orWhere('significance', 'like', '%' . $search . '%')
                            ->orWhere('reviewnotes', 'like', '%' . $search . '%');
                    });
                })
                ->when(
                    $filters['itemtype'],
                    fn ($query) => $query->where('itemtype', $filters['itemtype'])
                )
                ->when(
                    $filters['itemstatus'] !== '',
                    fn ($query) => $query->where(
                        'itemstatus',
                        $filters['itemstatus']
                    )
                )
                ->when(
                    $filters['active'] !== '',
                    fn ($query) => $query->where(
                        'isactive',
                        (int) $filters['active']
                    )
                )
                ->orderBy('sortorder')
                ->orderBy('itemname')
                ->paginate(50)
                ->withQueryString();
        }

        $itemTypes = KnowledgeItemType::query()
            ->where('isactive', true)
            ->orderBy('sortorder')
            ->orderBy('typename')
            ->get(['id', 'typename', 'sortorder']);

        $itemStatuses = KnowledgeItem::query()
            ->whereNotNull('itemstatus')
            ->where('itemstatus', '!=', '')
            ->distinct()
            ->orderBy('itemstatus')
            ->pluck('itemstatus');

        $selectedCategory = $filters['categoryid']
            ? KnowledgeCategory::query()
                ->with([
                    'domain:id,domainname',
                    'parentCategory:id,categoryname',
                ])
                ->find($filters['categoryid'])
            : null;

        return view('knowledge.items.index', [
            'pageTitle' => 'Knowledge Items',
            'filters' => $filters,
            'domains' => $domains,
            'selectedCategory' => $selectedCategory,
            'categories' => $categories,
            'items' => $items,
            'itemTypes' => $itemTypes,
            'itemStatuses' => $itemStatuses,
            'itemStatusOptions' => [
                'active' => 'Active',
                'draft' => 'Draft',
                'archived' => 'Archived',
                'reference' => 'Reference',
                'review' => 'Review',
            ],
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
            'existing.*.iswatchlist' => ['nullable', 'boolean'],
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
            'new.iswatchlist' => ['nullable', 'boolean'],
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
            $affectedCategoryIds = [];

            foreach ($validated['existing'] ?? [] as $id => $row) {
                $item = KnowledgeItem::query()
                    ->lockForUpdate()
                    ->findOrFail($id);

                $oldCategoryId = (int) $item->primarycategoryid;
                $newCategoryId = (int) $row['primarycategoryid'];

                if ($oldCategoryId !== $newCategoryId) {
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
                }

                $newValues = [
                    'primarycategoryid' => $newCategoryId,
                    'itemtype' => $row['itemtype'] ?? null,
                    'itemstatus' => $row['itemstatus'] ?? null,
                    'summary' => $row['summary'] ?? null,
                    'sortorder' => (int) ($row['sortorder'] ?? 0),
                    'startdate' => $row['startdate'] ?? null,
                    'enddate' => $row['enddate'] ?? null,
                    'nextreviewdate' => $row['nextreviewdate'] ?? null,
                    'isfeatured' => (bool) ($row['isfeatured'] ?? false),
                    'iswatchlist' => (bool) ($row['iswatchlist'] ?? false),
                    'isactive' => (bool) ($row['isactive'] ?? false),
                ];

                $item->fill($newValues);

                if (!$item->isDirty()) {
                    continue;
                }

                if (
                    $oldCategoryId !== $newCategoryId
                    || (int) $item->getOriginal('sortorder') !== (int) $newValues['sortorder']
                ) {
                    $affectedCategoryIds[] = $oldCategoryId;
                    $affectedCategoryIds[] = $newCategoryId;
                }

                $item->save();
            }

            $new = $validated['new'] ?? [];
            $hasNewRow = trim((string) ($new['itemname'] ?? '')) !== '';

            if ($hasNewRow) {
                $newCategoryId = (int) ($new['primarycategoryid'] ?? 0);
                $newItemName = trim((string) ($new['itemname'] ?? ''));

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
                    'iswatchlist' => ['nullable', 'boolean'],
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
                $newRequestedSortOrder = (int) (
                    $newValidated['sortorder'] ?? 0
                );

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
                    'itemname' => trim((string) $newValidated['itemname']),
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
                    'iswatchlist' => (bool) (
                        $newValidated['iswatchlist'] ?? false
                    ),
                    'isactive' => array_key_exists('isactive', $newValidated)
                        ? (bool) $newValidated['isactive']
                        : true,
                ]);

                $affectedCategoryIds[] = $newCategoryId;
            }

            $affectedCategoryIds = array_values(array_unique(
                array_filter(array_map('intval', $affectedCategoryIds))
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
                    ->get(['id', 'sortorder']);

                foreach ($items as $index => $item) {
                    $normalisedSortOrder = $index + 1;

                    if ((int) $item->sortorder !== $normalisedSortOrder) {
                        $item->update(['sortorder' => $normalisedSortOrder]);
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
        /*
        * Always load only the small core graph required to establish the domain,
        * populate the Details tab, and render the tab controls.
        */
        $knowledgeItem->load([
            'primaryCategory.domain',
            'primaryCategory.parentCategory',
            'parentItem',
            'itemType',
            'tags',
        ]);

        $domainId = $knowledgeItem->primaryCategory?->domainid;
        $domain = $knowledgeItem->primaryCategory?->domain;

        $hasBibleTools = (bool) ($domain?->hasbibletools ?? false);
        $hasInvestmentTools = (bool) ($domain?->hasinvestmenttools ?? false);
        $hasFamilyHistoryTools = (bool) ($domain?->hasfamilyhistorytools ?? false);

        $allowedTabs = [
            'details',
            'info',
            'notes',
            'sources',
            'review-logs',
            'relationships',
            'attachments',
        ];

        if ($hasBibleTools) {
            $allowedTabs[] = 'bible-references';
        }

        if ($hasInvestmentTools) {
            $allowedTabs[] = 'investments';
        }

        if ($hasFamilyHistoryTools) {
            $allowedTabs[] = 'family-history';
        }

        $activeTab = $request->string('tab')->value() ?: 'details';

        if (!in_array($activeTab, $allowedTabs, true)) {
            $activeTab = 'details';
        }

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
        $showAddRelationshipFactFor = $request->integer(
            'show_add_relationship_fact_for'
        );
        $editingRelationshipFactId = $request->integer(
            'editing_relationship_fact_id'
        );

        $categories = collect();
        $parentItems = collect();
        $relationshipItems = collect();
        $displayRelationships = collect();
        $allRelationships = collect();
        $places = collect();
        $knowledgeTags = collect();
        $instrumentTypes = collect();
        $exchanges = collect();
        $portfolios = collect();
        $books = collect();
        $versions = collect();
        $editingPersonFact = null;
        $editingRelationshipFact = null;

        /* Details: only data needed by the general edit form. */
        if ($activeTab === 'details') {
            $categories = KnowledgeCategory::query()
                ->with([
                    'domain:id,domainname,sortorder',
                    'parentCategory:id,categoryname',
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

            /*
            * Temporary compatibility collection for an existing parent <select>.
            * Replace this with server-side autocomplete next. The cap prevents a
            * 6,000-item domain from being loaded into every Details request.
            */
            $parentItems = KnowledgeItem::query()
                ->select(['id', 'itemname', 'primarycategoryid'])
                ->where('id', '!=', $knowledgeItem->id)
                ->where('isactive', 1)
                ->whereHas(
                    'primaryCategory',
                    fn ($query) => $query->where('domainid', $domainId)
                )
                ->orderBy('itemname')
                ->limit(500)
                ->get();

            $knowledgeTags = KnowledgeTag::query()
                ->where('isactive', 1)
                ->orderByRaw('COALESCE(sortorder, 999999), tagname')
                ->get();
        }

        if ($activeTab === 'notes') {
            $knowledgeItem->load([
                'notes' => fn ($query) => $query
                    ->orderByRaw('COALESCE(sortorder, 999999)')
                    ->orderBy('id'),
            ]);
        }

        if ($activeTab === 'sources') {
            $knowledgeItem->load([
                'sources' => fn ($query) => $query
                    ->orderByDesc('retrievedon')
                    ->orderByDesc('id'),
            ]);
        }

        if ($activeTab === 'review-logs') {
            $knowledgeItem->load([
                'reviewLogs' => fn ($query) => $query
                    ->orderByDesc('reviewdate')
                    ->orderByDesc('id'),
            ]);
        }

        if ($activeTab === 'attachments') {
            $knowledgeItem->load([
                'attachments' => fn ($query) => $query
                    ->orderByDesc('isprimary')
                    ->orderBy('sortorder')
                    ->orderBy('id'),
            ]);
        }

        if ($activeTab === 'relationships') {
            $knowledgeItem->load([
                'outgoingRelationships.toItem.primaryCategory',
                'incomingRelationships.fromItem.primaryCategory',
                'outgoingRelationships.relationshipFacts.place',
                'incomingRelationships.relationshipFacts.place',
            ]);

            $displayRelationships = collect(
                $knowledgeItem->outgoingRelationships->map(
                    function ($relationship) use ($knowledgeItem) {
                        return [
                            'relationship' => $relationship,
                            'direction' => 'outgoing',
                            'relatedItem' => $relationship->toItem,
                            'displayTypeLabel' => $relationship->relationshipTypeLabel(),
                            'sortorder' => $relationship->sortOrderFor($knowledgeItem),
                            'relatedSortName' => mb_strtolower(
                                $relationship->toItem?->itemname ?? 'zzzz'
                            ),
                        ];
                    }
                )->all()
            )->merge(
                collect(
                    $knowledgeItem->incomingRelationships->map(
                        function ($relationship) use ($knowledgeItem) {
                            return [
                                'relationship' => $relationship,
                                'direction' => 'incoming',
                                'relatedItem' => $relationship->fromItem,
                                'displayTypeLabel' =>
                                    $relationship->inverseRelationshipTypeLabel(),
                                'sortorder' => $relationship->sortOrderFor($knowledgeItem),
                                'relatedSortName' => mb_strtolower(
                                    $relationship->fromItem?->itemname ?? 'zzzz'
                                ),
                            ];
                        }
                    )->all()
                )
            )->sortBy([
                ['sortorder', 'asc'],
                ['relatedSortName', 'asc'],
            ])->values();

            $allRelationships = $knowledgeItem->outgoingRelationships
                ->merge($knowledgeItem->incomingRelationships)
                ->map(function ($relationship) use ($knowledgeItem) {
                    $relationship->display_sortorder = $relationship->sortOrderFor(
                        $knowledgeItem
                    );

                    return $relationship;
                })
                ->sortBy([
                    ['display_sortorder', 'asc'],
                    ['id', 'asc'],
                ])
                ->values();

            /*
            * Temporary compatibility collection for an existing relationship
            * target <select>. Replace with autocomplete; do not remove the limit
            * unless you deliberately accept loading all domain items again.
            */
            $relationshipItems = KnowledgeItem::query()
                ->select([
                    'knowledgeitems.id',
                    'knowledgeitems.itemname',
                    'knowledgeitems.primarycategoryid',
                ])
                ->join(
                    'knowledgecategories',
                    'knowledgecategories.id',
                    '=',
                    'knowledgeitems.primarycategoryid'
                )
                ->where('knowledgecategories.domainid', $domainId)
                ->where('knowledgeitems.id', '<>', $knowledgeItem->id)
                ->where('knowledgeitems.isactive', 1)
                ->with('primaryCategory:id,categoryname,parentcategoryid')
                ->orderBy('knowledgecategories.categoryname')
                ->orderBy('knowledgeitems.itemname')
                ->limit(500)
                ->get();
        }

        if ($activeTab === 'bible-references' && $hasBibleTools) {
            $knowledgeItem->load([
                'bibleReferences.book',
                'bibleReferences.version',
            ]);

            $books = BibleBook::query()
                ->orderBy('sortorder')
                ->orderBy('bookname')
                ->get();

            $versions = BibleVersion::query()
                ->where('isactive', 1)
                ->orderBy('versionname')
                ->get();
        }

        if ($activeTab === 'investments' && $hasInvestmentTools) {
            $knowledgeItem->load([
                'instrument.instrumentType',
                'instrument.exchange',
                'instrument.aliases',
                'instrument.corporateActions',
                /*
                * Do not load priceObservations or transactions here unless the
                * Investments Blade needs every row. Those should use their own
                * paginated query/controller when they become substantial.
                */
            ]);

            $instrumentTypes = InstrumentType::query()
                ->where('isactive', 1)
                ->orderBy('typename')
                ->get();

            $exchanges = Exchange::query()
                ->where('isactive', 1)
                ->orderBy('exchangename')
                ->get();

            $portfolios = Portfolio::query()
                ->where('isactive', 1)
                ->orderBy('portfolioname')
                ->get();
        }

        if ($activeTab === 'family-history' && $hasFamilyHistoryTools) {
            $knowledgeItem->load([
                'personFacts.place',
                'outgoingRelationships.relationshipFacts.place',
                'incomingRelationships.relationshipFacts.place',
                'outgoingRelationships.toItem.primaryCategory',
                'incomingRelationships.fromItem.primaryCategory',
            ]);

            $places = Place::query()
                ->where('isactive', true)
                ->orderBy('placename')
                ->orderBy('locality')
                ->get(['id', 'placename', 'locality', 'placetype']);

            $editingPersonFact = $editingPersonFactId
                ? $knowledgeItem->personFacts->firstWhere(
                    'id',
                    $editingPersonFactId
                )
                : null;

            $editingRelationshipFact = $editingRelationshipFactId
                ? $knowledgeItem->outgoingRelationships
                    ->merge($knowledgeItem->incomingRelationships)
                    ->flatMap->relationshipFacts
                    ->firstWhere('id', $editingRelationshipFactId)
                : null;
        }

        $itemTypes = KnowledgeItemType::query()
            ->where('isactive', true)
            ->orderBy('sortorder')
            ->orderBy('typename')
            ->get();

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
            'allRelationships' => $allRelationships,
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
            'knowledgeTags' => $knowledgeTags,
            'hasBibleTools' => $hasBibleTools,
            'hasInvestmentTools' => $hasInvestmentTools,
            'instrumentTypes' => $instrumentTypes,
            'exchanges' => $exchanges,
            'corporateActionTypeOptions' =>
                InstrumentCorporateActionController::actionTypeOptions(),
            'transactionTypeOptions' =>
                InstrumentTransactionController::transactionTypeOptions(),
            'portfolios' => $portfolios,
            'hasFamilyHistoryTools' => $hasFamilyHistoryTools,
            'showAddPersonFact' => $showAddPersonFact,
            'editingPersonFactId' => $editingPersonFactId,
            'editingPersonFact' => $editingPersonFact,
            'showAddRelationshipFactFor' => $showAddRelationshipFactFor,
            'editingRelationshipFactId' => $editingRelationshipFactId,
            'editingRelationshipFact' => $editingRelationshipFact,
            'personFactTypeOptions' => KnowledgePersonFact::factTypeOptions(),
            'relationshipFactTypeOptions' =>
                KnowledgeRelationshipFact::factTypeOptions(),
            'dateQualifierOptions' => KnowledgePersonFact::dateQualifierOptions(),
            'proofStatusOptions' => KnowledgePersonFact::proofStatusOptions(),
            'books' => $books,
            'versions' => $versions,
        ]);
    }

    public function update(Request $request, KnowledgeItem $knowledgeItem): RedirectResponse
    {
        $validated = $request->validate([
            'primarycategoryid' => [
                'required',
                'integer',
                Rule::exists('knowledgecategories', 'id'),
            ],
            'itemname' => ['required', 'string', 'max:255'],
            'itemtype' => [
                'nullable',
                'integer',
                Rule::exists('knowledgeitemtypes', 'id')
                    ->where(fn ($query) => $query->where('isactive', 1)),
            ],
            'itemstatus' => ['nullable', 'string', 'max:30'],
            'summary' => ['nullable', 'string'],
            'detailednotes' => ['nullable', 'string'],
            'significance' => ['nullable', 'string'],
            'reviewnotes' => ['nullable', 'string'],
            'parentitemid' => [
                'nullable',
                'integer',
                Rule::exists('knowledgeitems', 'id'),
            ],
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
                ->withErrors([
                    'parentitemid' => 'A knowledge item cannot be its own parent.',
                ]);
        }

        $selectedCategory = KnowledgeCategory::query()
            ->select(['id', 'domainid'])
            ->findOrFail($validated['primarycategoryid']);

        if (!empty($validated['parentitemid'])) {
            $selectedParentItem = KnowledgeItem::query()
                ->select(['id', 'primarycategoryid'])
                ->with('primaryCategory:id,domainid')
                ->findOrFail($validated['parentitemid']);

            if (
                (int) $selectedParentItem->primaryCategory?->domainid
                !== (int) $selectedCategory->domainid
            ) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'parentitemid' =>
                            'The parent item must be in the same domain as the selected primary category.',
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

        DB::transaction(function () use (
            $knowledgeItem,
            $validated,
            $tagIds
        ) {
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

            $knowledgeItem->tags()->sync($tagIds);
        });

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

    public function destroy(
        Request $request,
        KnowledgeItem $knowledgeItem
    ): RedirectResponse {
        $knowledgeItem->load('primaryCategory:id,domainid');

        $returnTo = $this->safeReturnUrl(
            $request,
            $request->input('return_to')
        );

        if ($knowledgeItem->childItems()->exists()) {
            return redirect()->to(
                $returnTo ?: route('knowledge.items.edit', $knowledgeItem)
            )->with(
                'error',
                'This knowledge item cannot be deleted because it has child items.'
            );
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

    public function reorder(
        Request $request,
        KnowledgeItem $knowledgeItem
    ): RedirectResponse {
        $validated = $request->validate([
            'notes' => ['required', 'array'],
            'notes.*.sortorder' => ['required', 'integer', 'min:1'],
        ]);

        $noteSortOrders = collect($validated['notes'])
            ->mapWithKeys(
                fn (array $row, $noteId) => [
                    (int) $noteId => (int) $row['sortorder'],
                ]
            )
            ->all();

        $notes = $knowledgeItem->notes()
            ->whereIn('id', array_keys($noteSortOrders))
            ->get(['id', 'sortorder']);

        foreach ($notes as $note) {
            $sortOrder = $noteSortOrders[$note->id];

            if ((int) $note->sortorder !== $sortOrder) {
                $note->update(['sortorder' => $sortOrder]);
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