<?php

namespace App\Http\Controllers;

use App\Models\KnowledgeCategory;
use App\Models\KnowledgeItem;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\SimpleType\JcTable;
use PhpOffice\PhpWord\Style\Table;

class KnowledgeReportController extends Controller
{
    public function categoryReferenceBook(Request $request)
    {
        $selectedCategoryIds = collect($request->input('category_ids', []))
            ->filter(fn ($id) => filled($id))
            ->map(fn ($id) => (int) $id)
            ->values();

        if ($selectedCategoryIds->isEmpty() && $request->filled('categoryid')) {
            $selectedCategoryIds = collect([(int) $request->input('categoryid')]);
        }

        $selectedCategoryIds = $selectedCategoryIds
            ->unique()
            ->values()
            ->all();

        $reviewOnly = $request->boolean('review_only');
        $itemStatus = trim((string) $request->query('itemstatus', ''));

        if (!array_key_exists(
            $itemStatus,
            $this->reportItemStatusOptions()
        )) {
            $itemStatus = '';
        }

        $agendaMode = $request->boolean('agenda_mode');

        $categoryOptions = KnowledgeCategory::query()
            ->with('domain')
            ->orderBy('domainid')
            ->orderBy('sortorder')
            ->orderBy('categoryname')
            ->get([
                'id',
                'categoryname',
                'domainid',
                'parentcategoryid',
                'categorytype',
                'sortorder',
                'isactive',
            ]);

        $categoriesQuery = $this->baseCategoryReferenceQuery(
                $reviewOnly,
                $itemStatus
            )
            ->orderBy('domainid')
            ->orderBy('sortorder')
            ->orderBy('categoryname');

        if (!empty($selectedCategoryIds)) {
            $categoriesQuery->whereIn('id', $selectedCategoryIds);
        }

        $categories = $this->prepareCategoriesForReport($categoriesQuery->get(), $reviewOnly);

        return view('reports.knowledge.categories.reference-book', [
            'categories' => $categories,
            'categoryOptions' => $categoryOptions,
            'selectedCategoryIds' => $selectedCategoryIds,
            'reviewOnly' => $reviewOnly,
            'itemStatus' => $itemStatus,
            'itemStatusOptions' => [
                '' => 'All items',
                'active' => 'Active items only',
                'draft' => 'Draft',
                'archived' => 'Archived',
                'reference' => 'Reference',
                'review' => 'Review',
            ],
            'agendaMode' => $agendaMode,
            'returnTo' => $request->input('return_to', url()->previous()),
        ]);
    }

    public function domainReferenceBook(Request $request)
{
    $domainId = (int) $request->input('domainid');

    abort_if($domainId <= 0, 404, 'No domain selected.');

    $reviewOnly = $request->boolean('review_only');
    $itemStatus = trim((string) $request->query('itemstatus', ''));

    if (!array_key_exists(
        $itemStatus,
        $this->reportItemStatusOptions()
    )) {
        $itemStatus = '';
    }

    $agendaMode = $request->boolean('agenda_mode');

    // IMPORTANT:
    // Replace this with the SAME method used by your domain tree / sidebar.
    $selectedCategoryIds = $this->collectDomainTreeIdsInDisplayOrder($domainId);

    $categoriesQuery = $this->baseCategoryReferenceQuery(
            $reviewOnly,
            $itemStatus
        )
        ->whereIn('id', $selectedCategoryIds);

    if (!empty($selectedCategoryIds)) {
        $categoriesQuery->orderByRaw(
            'FIELD(id, ' . implode(',', $selectedCategoryIds) . ')'
        );
    }

    $categories = $this->prepareCategoriesForReport(
        $categoriesQuery->get(),
        $reviewOnly
    );

    $domain = KnowledgeCategory::query()
        ->where('domainid', $domainId)
        ->with('domain')
        ->first()?->domain;

    return view('reports.knowledge.categories.reference-book', [
        'categories' => $categories,
        'selectedCategoryIds' => $selectedCategoryIds,
        'reviewOnly' => $reviewOnly,
        'returnTo' => $request->input('return_to', url()->previous()),
        'reportTitle' => $domain?->domainname
            ? 'Knowledge Domain Report – ' . $domain->domainname
            : 'Knowledge Domain Report',
        'reportSubtitle' => $domain?->domainname
            ? 'Compiled reference report for all categories in ' . $domain->domainname
            : 'Compiled reference report by domain',
        'itemStatus' => $itemStatus,
        'itemStatusOptions' => [
            '' => 'All items',
            'active' => 'Active items only',
            'draft' => 'Draft',
            'archived' => 'Archived',
            'reference' => 'Reference',
            'review' => 'Review',
        'agendaMode' => $agendaMode,
        ],
    ]);
}

    protected function collectCategoryTreeIds(int $rootCategoryId): array
    {
        $allCategories = KnowledgeCategory::query()
            ->select('id', 'parentcategoryid')
            ->orderBy('sortorder')
            ->orderBy('categoryname')
            ->get();

        $treeIds = [$rootCategoryId];

        $appendChildren = function ($parentId) use (&$appendChildren, $allCategories, &$treeIds) {
            $children = $allCategories->where('parentcategoryid', $parentId);

            foreach ($children as $child) {
                $treeIds[] = (int) $child->id;
                $appendChildren((int) $child->id);
            }
        };

        $appendChildren($rootCategoryId);

        return collect($treeIds)
            ->unique()
            ->values()
            ->all();
    }

    public function categoryTreeReferenceBook(Request $request)
    {
        $categoryId = (int) $request->input('categoryid');

        abort_if($categoryId <= 0, 404, 'No category selected.');

        $reviewOnly = $request->boolean('review_only');
        $itemStatus = trim((string) $request->query('itemstatus', ''));

        if (!array_key_exists(
            $itemStatus,
            $this->reportItemStatusOptions()
        )) {
            $itemStatus = '';
        }

        $agendaMode = $request->boolean('agenda_mode');

        $selectedCategory = KnowledgeCategory::query()
            ->with(['domain', 'parentCategory'])
            ->findOrFail($categoryId);

        $selectedCategoryIds = $this->collectCategoryTreeIds($selectedCategory->id);

        $categories = $this->prepareCategoriesForReport(
            $this->baseCategoryReferenceQuery(
                    $reviewOnly,
                    $itemStatus
                )
                ->whereIn('id', $selectedCategoryIds)
                ->orderBy('domainid')
                ->orderBy('sortorder')
                ->orderBy('categoryname')
                ->get(),
            $reviewOnly
        );

        return view('reports.knowledge.categories.reference-book', [
            'categories' => $categories,
            'selectedCategoryIds' => $selectedCategoryIds,
            'selectedCategoryId' => $selectedCategory->id,
            'reviewOnly' => $reviewOnly,
            'returnTo' => $request->input('return_to', url()->previous()),
            'reportTitle' => 'Knowledge Category Tree Report – ' . $selectedCategory->categoryname,
            'reportSubtitle' => 'Compiled reference report for this category and all categories beneath it',
            'itemStatus' => $itemStatus,
            'itemStatusOptions' => [
                '' => 'All items',
                'active' => 'Active items only',
                'draft' => 'Draft',
                'archived' => 'Archived',
                'reference' => 'Reference',
                'review' => 'Review',
            ],
            'agendaMode' => $agendaMode,
        ]);
    }

    protected function baseCategoryReferenceQuery(
    bool $reviewOnly = false,
    string $itemStatus = ''
) {
    $activeOnly = $itemStatus === 'active';

    return KnowledgeCategory::query()
        ->with([
            'domain',
            'parentCategory',

            /*
             * When itemstatus is "active", inactive knowledge items
             * are excluded completely from the report.
             */
            'knowledgeItems' => function ($query) use (
                $reviewOnly,
                $itemStatus
            ) {
                $query
                    ->when(
                        $reviewOnly,
                        fn ($query) => $query
                            ->whereNotNull('nextreviewdate')
                    )
                    ->when(
                        $itemStatus !== '',
                        fn ($query) => $query->where(
                            'itemstatus',
                            $itemStatus
                        )
                    )
                    ->orderBy('sortorder')
                    ->orderBy('itemname');
            },

            'knowledgeItems.primaryCategory',
            'knowledgeItems.parentItem',
            'knowledgeItems.place',
            'knowledgeItems.itemType',

            'knowledgeItems.personFacts' => function ($query) {
                $query->with('place')
                    ->orderBy('sortorder')
                    ->orderBy('datefrom')
                    ->orderBy('id');
            },

            'knowledgeItems.personFacts.place',

            /*
             * Active Only:
             * load only active notes belonging to active items.
             */
            'knowledgeItems.notes' => function ($query) use (
                $activeOnly
            ) {
                $query
                    ->when(
                        $activeOnly,
                        fn ($query) => $query->active()
                    )
                    ->orderBy('sortorder')
                    ->orderByDesc('reviewdate')
                    ->orderByDesc('id');
            },

            'knowledgeItems.sources' => function ($query) {
                $query->orderByDesc('retrievedon')
                    ->orderByDesc('id');
            },

            /*
             * Active Only:
             * load only active review logs belonging to active items.
             */
            'knowledgeItems.reviewLogs' => function ($query) use (
                $activeOnly
            ) {
                $query
                    ->when(
                        $activeOnly,
                        fn ($query) => $query->active()
                    )
                    ->orderByDesc('reviewdate')
                    ->orderByDesc('id');
            },

            'knowledgeItems.outgoingRelationships',
            'knowledgeItems.outgoingRelationships.toItem.primaryCategory',

            'knowledgeItems.outgoingRelationships.relationshipFacts'
                => function ($query) {
                    $query->with('place')
                        ->orderBy('sortorder')
                        ->orderBy('datefrom')
                        ->orderBy('id');
                },

            'knowledgeItems.outgoingRelationships.relationshipFacts.place',

            'knowledgeItems.incomingRelationships',
            'knowledgeItems.incomingRelationships.fromItem.primaryCategory',

            'knowledgeItems.incomingRelationships.relationshipFacts'
                => function ($query) {
                    $query->with('place')
                        ->orderBy('sortorder')
                        ->orderBy('datefrom')
                        ->orderBy('id');
                },

            'knowledgeItems.incomingRelationships.relationshipFacts.place',

            'knowledgeItems.attachments' => function ($query) {
                $query->orderByPivot('isprimary', 'desc')
                    ->orderByPivot('sortorder')
                    ->orderBy('originalfilename')
                    ->orderBy('filename');
            },

            'knowledgeItems.bibleReferences' => function ($query) {
                $query->orderBy('bookid')
                    ->orderBy('chapterfrom')
                    ->orderBy('versefrom');
            },

            'knowledgeItems.bibleReferences.book',
            'knowledgeItems.bibleReferences.version',

            'knowledgeItems.instrument',
            'knowledgeItems.instrument.instrumentType',
            'knowledgeItems.instrument.exchange',

            'knowledgeItems.instrument.aliases' => function ($query) {
                $query->orderBy('aliastype')
                    ->orderBy('aliasvalue');
            },

            'knowledgeItems.instrument.priceObservations'
                => function ($query) {
                    $query->orderByDesc('observedon')
                        ->orderByDesc('id');
                },

            'knowledgeItems.instrument.corporateActions'
                => function ($query) {
                    $query->orderByDesc('actiondate')
                        ->orderByDesc('id');
                },

            'knowledgeItems.instrument.corporateActions.source',

            'knowledgeItems.instrument.transactions'
                => function ($query) {
                    $query->orderByDesc('transactiondate')
                        ->orderByDesc('id');
                },

            'knowledgeItems.instrument.transactions.portfolio',
        ]);
}

    protected function prepareCategoriesForReport($categories, bool $reviewOnly = false)
{
    return $categories
        ->values()
        ->map(function ($category) use ($reviewOnly) {
            $items = $category->knowledgeItems
                ->when(
                    $reviewOnly,
                    fn ($collection) => $collection->filter(
                        fn ($item) => !empty($item->nextreviewdate)
                    )
                )
                ->sortBy([
                    ['sortorder', 'asc'],
                    ['itemname', 'asc'],
                ])
                ->values()
                ->map(function ($item) {
                    return $this->prepareKnowledgeItemForReport($item);
                });

            $category->setRelation('knowledgeItems', $items);

            return $category;
        })
        ->filter(fn ($category) => $category->knowledgeItems->isNotEmpty())
        ->values();
}
protected function collectDomainTreeIdsInDisplayOrder(int $domainId): array
{
    $categories = KnowledgeCategory::query()
        ->where('domainid', $domainId)
        ->orderBy('sortorder')
        ->orderBy('categoryname')
        ->get([
            'id',
            'parentcategoryid',
            'sortorder',
            'categoryname',
        ]);

    $orderedIds = [];

    $appendChildren = function ($parentId) use (&$appendChildren, $categories, &$orderedIds) {
        $children = $categories
            ->where('parentcategoryid', $parentId)
            ->sortBy([
                ['sortorder', 'asc'],
                ['categoryname', 'asc'],
            ])
            ->values();

        foreach ($children as $child) {
            $orderedIds[] = (int) $child->id;
            $appendChildren((int) $child->id);
        }
    };

    $rootCategories = $categories
        ->whereNull('parentcategoryid')
        ->sortBy([
            ['sortorder', 'asc'],
            ['categoryname', 'asc'],
        ])
        ->values();

    foreach ($rootCategories as $rootCategory) {
        $orderedIds[] = (int) $rootCategory->id;
        $appendChildren((int) $rootCategory->id);
    }

    return collect($orderedIds)
        ->unique()
        ->values()
        ->all();
}

public function knowledgeItemReferenceBook(
    Request $request,
    int $knowledgeItemId
) {
    $reviewOnly = $request->boolean('review_only');
    $agendaMode = $request->boolean('agenda_mode');

    $item = KnowledgeItem::query()
        ->with([
            'primaryCategory.domain',
            'primaryCategory.parentCategory',
            'parentItem',
            'place',
            'itemType',

            'personFacts' => function ($query) {
                $query->with('place')
                    ->orderBy('sortorder')
                    ->orderBy('datefrom')
                    ->orderBy('id');
            },

            'personFacts.place',

            'notes' => function ($query) {
                $query->orderBy('sortorder')
                    ->orderByDesc('reviewdate')
                    ->orderByDesc('id');
            },

            'sources' => function ($query) {
                $query->orderByDesc('retrievedon')
                    ->orderByDesc('id');
            },

            'reviewLogs' => function ($query) {
                $query->orderByDesc('reviewdate')
                    ->orderByDesc('id');
            },

            'outgoingRelationships',
            'outgoingRelationships.toItem.primaryCategory',
            'outgoingRelationships.relationshipFacts' => function ($query) {
                $query->with('place')
                    ->orderBy('sortorder')
                    ->orderBy('datefrom')
                    ->orderBy('id');
            },
            'outgoingRelationships.relationshipFacts.place',

            'incomingRelationships',
            'incomingRelationships.fromItem.primaryCategory',
            'incomingRelationships.relationshipFacts' => function ($query) {
                $query->with('place')
                    ->orderBy('sortorder')
                    ->orderBy('datefrom')
                    ->orderBy('id');
            },
            'incomingRelationships.relationshipFacts.place',

            'attachments' => function ($query) {
                $query->orderByDesc('isprimary')
                    ->orderBy('originalfilename')
                    ->orderBy('filename');
            },

            'bibleReferences' => function ($query) {
                $query->orderBy('bookid')
                    ->orderBy('chapterfrom')
                    ->orderBy('versefrom');
            },
            'bibleReferences.book',
            'bibleReferences.version',

            'instrument',
            'instrument.instrumentType',
            'instrument.exchange',

            'instrument.aliases' => function ($query) {
                $query->orderBy('aliastype')
                    ->orderBy('aliasvalue');
            },

            'instrument.priceObservations' => function ($query) {
                $query->orderByDesc('observedon')
                    ->orderByDesc('id');
            },

            'instrument.corporateActions' => function ($query) {
                $query->orderByDesc('actiondate')
                    ->orderByDesc('id');
            },
            'instrument.corporateActions.source',

            'instrument.transactions' => function ($query) {
                $query->orderByDesc('transactiondate')
                    ->orderByDesc('id');
            },
            'instrument.transactions.portfolio',
        ])
        ->findOrFail($knowledgeItemId);

    if ($reviewOnly && empty($item->nextreviewdate)) {
        abort(404, 'This knowledge item does not have a review date.');
    }

    $item = $this->prepareKnowledgeItemForReport($item);
    $reviewOnly = $request->boolean('review_only');
    $agendaMode = $request->boolean('agenda_mode');

    return view('reports.knowledge.items.reference-book', [
        'knowledgeItem' => $item,
        'reviewOnly' => $reviewOnly,
        'agendaMode' => $agendaMode,
        'returnTo' => $request->input('return_to', url()->previous()),
        'reportTitle' => 'Knowledge Item Report – ' . $item->itemname,
        'reportSubtitle' => 'Compiled reference report for a single knowledge item',
    ]);
}

protected function prepareKnowledgeItemForReport($item)
{
    $displayRelationships = $item->outgoingRelationships
        ->toBase()
        ->map(function ($relationship) use ($item) {
            return [
                'relationship' => $relationship,
                'direction' => 'outgoing',
                'relatedItem' => $relationship->toItem,
                'displayTypeLabel' => $relationship->relationshipTypeLabel(),
                'sortorder' => $relationship->sortOrderFor($item) ?? 0,
                'relatedSortName' => mb_strtolower($relationship->toItem?->itemname ?? 'zzzz'),
                'effectiveDate' => $relationship->effective_date,
            ];
        })
        ->merge(
            $item->incomingRelationships
                ->toBase()
                ->map(function ($relationship) use ($item) {
                    return [
                        'relationship' => $relationship,
                        'direction' => 'incoming',
                        'relatedItem' => $relationship->fromItem,
                        'displayTypeLabel' => $relationship->inverseRelationshipTypeLabel(),
                        'sortorder' => $relationship->sortOrderFor($item) ?? 0,
                        'relatedSortName' => mb_strtolower($relationship->fromItem?->itemname ?? 'zzzz'),
                        'effectiveDate' => $relationship->effective_date,
                    ];
                })
        )
        ->sortBy([
            ['sortorder', 'asc'],
            ['relatedSortName', 'asc'],
        ])
        ->values();

    $reportRelationships = $item->outgoingRelationships
        ->toBase()
        ->map(function ($relationship) use ($item) {
            return [
                'relationship' => $relationship,
                'direction' => 'outgoing',
                'relatedItem' => $relationship->toItem,
                'displayTypeLabel' => $relationship->relationshipTypeLabel(),
                'sortorder' => $relationship->sortOrderFor($item) ?? 0,
                'relatedSortName' => mb_strtolower($relationship->toItem?->itemname ?? 'zzzz'),
                'effectiveDate' => $relationship->effective_date,
                'relationshipFacts' => $relationship->relationshipFacts
                    ->sortBy([
                        ['sortorder', 'asc'],
                        ['datefrom', 'asc'],
                        ['id', 'asc'],
                    ])
                    ->values(),
            ];
        })
        ->merge(
            $item->incomingRelationships
                ->toBase()
                ->map(function ($relationship) use ($item) {
                    return [
                        'relationship' => $relationship,
                        'direction' => 'incoming',
                        'relatedItem' => $relationship->fromItem,
                        'displayTypeLabel' => $relationship->inverseRelationshipTypeLabel(),
                        'sortorder' => $relationship->sortOrderFor($item) ?? 0,
                        'relatedSortName' => mb_strtolower($relationship->fromItem?->itemname ?? 'zzzz'),
                        'effectiveDate' => $relationship->effective_date,
                        'relationshipFacts' => $relationship->relationshipFacts
                            ->sortBy([
                                ['sortorder', 'asc'],
                                ['datefrom', 'asc'],
                                ['id', 'asc'],
                            ])
                            ->values(),
                    ];
                })
        )
        ->sortBy([
            ['sortorder', 'asc'],
            ['relatedSortName', 'asc'],
        ])
        ->values();

    $item->setRelation(
        'personFacts',
        $item->personFacts
            ->sortBy([
                ['sortorder', 'asc'],
                ['datefrom', 'asc'],
                ['id', 'asc'],
            ])
            ->values()
    );

    $item->setRelation('displayRelationships', $displayRelationships);
    $item->setRelation('reportRelationships', $reportRelationships);

    return $item;
}

    protected function reportItemStatusOptions(): array
    {
        return [
            '' => 'All items',
            'active' => 'Active items only',
            'draft' => 'Draft',
            'archived' => 'Archived',
            'reference' => 'Reference',
            'review' => 'Review',
        ];
    }


    public function agendaDocx(Request $request)
    {
        $categoryId = $request->integer('categoryid');

        if ($categoryId <= 0) {
            $categoryId = collect($request->input('category_ids', []))
                ->filter(fn ($id) => filled($id))
                ->map(fn ($id) => (int) $id)
                ->first() ?? 0;
        }

        abort_if(
            $categoryId <= 0,
            404,
            'No category selected for Agenda DOCX export.'
        );

        $reviewOnly = $request->boolean('review_only');

        $itemStatus = trim((string) $request->query('itemstatus', ''));

        if (!array_key_exists(
            $itemStatus,
            $this->reportItemStatusOptions()
        )) {
            $itemStatus = '';
        }

        $selectedCategory = KnowledgeCategory::query()
            ->with(['domain', 'parentCategory'])
            ->findOrFail($categoryId);

        /*
        * Use the exact same category-tree selection as the current on-screen
        * Knowledge Category Tree Report.
        */
        $selectedCategoryIds = $this->collectCategoryTreeIds(
            $selectedCategory->id
        );

        $categories = $this->prepareCategoriesForReport(
            $this->baseCategoryReferenceQuery(
                $reviewOnly,
                $itemStatus
            )
                ->whereIn('id', $selectedCategoryIds)
                ->orderBy('domainid')
                ->orderBy('sortorder')
                ->orderBy('categoryname')
                ->get(),
            $reviewOnly
        );

        $phpWord = new PhpWord();

        $phpWord->setDefaultFontName('Arial');
        $phpWord->setDefaultFontSize(11);

        $phpWord->addTitleStyle(1, [
            'name' => 'Arial',
            'bold' => true,
            'size' => 17,
            'color' => '1F2937',
        ]);

        $phpWord->addTitleStyle(2, [
            'name' => 'Arial',
            'bold' => true,
            'size' => 14,
            'color' => '1F2937',
        ]);

        $phpWord->addTitleStyle(3, [
            'name' => 'Arial',
            'bold' => true,
            'size' => 11,
            'color' => '1F2937',
        ]);

        $section = $phpWord->addSection([
            'marginTop' => 900,
            'marginRight' => 900,
            'marginBottom' => 900,
            'marginLeft' => 900,
        ]);

        $section->addTitle(
            $selectedCategory->categoryname . ' Agenda',
            1
        );

        // Agenda DOCX deliberately omits report metadata.
        /* 
           $metaText = collect([
           $selectedCategory->domain?->domainname,
           'Generated ' . now()->format('j F Y'),
           $itemStatus !== ''
                ? 'Items: '
                   . (
                       $this->reportItemStatusOptions()[$itemStatus]
                       ?? ucfirst($itemStatus)
                   )
                : 'Items: All items',
            $reviewOnly ? 'Review dates only' : null,
            ])->filter()->implode(' · ');
        

        if ($metaText !== '') {
            $section->addText(
                $metaText,
                [
                    'name' => 'Arial',
                    'size' => 9,
                    'italic' => true,
                    'color' => '666666',
                ],
                [
                    'spaceAfter' => 200,
                ]
            );
        }
        */

        foreach ($categories as $category) {
            $section->addTitle($category->categoryname, 2);

            foreach ($category->knowledgeItems as $index => $item) {
                $section->addTitle(
                    ($index + 1) . '. ' . $item->itemname,
                    3
                );

                /*
                * Agenda-oriented content only. This mirrors the simplified
                * Agenda report and deliberately excludes technical metadata,
                * sources, attachments, relationships and reference material.
                */
                $agendaContent = [
                    $item->summary,
                    $item->detailednotes,
                    $item->reviewnotes,
                ];

                foreach ($agendaContent as $content) {
                    if (blank($content)) {
                        continue;
                    }

                    $this->addMarkdownContentToDocx(
                        $section,
                        $content
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | Knowledge Notes
                |--------------------------------------------------------------------------
                | Agenda mode displays note content but excludes note metadata such as
                | type, review status, private status, dates, and other badges.
                */
                $agendaNotes = $item->notes
                    ->filter(fn ($note) => filled($note->notecontent))
                    ->sortBy([
                        ['sortorder', 'asc'],
                        ['reviewdate', 'desc'],
                        ['id', 'asc'],
                    ])
                    ->values();

                foreach ($agendaNotes as $note) {
                    if (filled($note->title)) {
                        $section->addText(
                            $note->title,
                            [
                                'name' => 'Arial',
                                'size' => 10,
                                'bold' => true,
                                'color' => '374151',
                            ],
                            [
                                'spaceBefore' => 100,
                                'spaceAfter' => 50,
                            ]
                        );
                    }

                    $this->addMarkdownContentToDocx(
                        $section,
                        $note->notecontent
                    );
                }

                $reviewEntries = $item->reviewLogs
                    ->filter(
                        fn ($reviewLog) => filled($reviewLog->summary)
                    );

                if ($reviewEntries->isNotEmpty()) {
                    $section->addText(
                        'Previous decisions / reviews',
                        [
                            'name' => 'Arial',
                            'size' => 10,
                            'bold' => true,
                            'color' => '374151',
                        ],
                        [
                            'spaceBefore' => 120,
                            'spaceAfter' => 50,
                        ]
                    );

                    foreach ($reviewEntries as $reviewLog) {
                        $reviewText = $this->plainTextForDocx(
                            $reviewLog->summary
                        );

                        if ($reviewText === '') {
                            continue;
                        }

                        $prefix = $reviewLog->reviewdate
                            ? $reviewLog->reviewdate->format('d M Y') . ': '
                            : '';

                        $section->addListItem(
                            $prefix . $reviewText,
                            0,
                            [
                                'name' => 'Arial',
                                'size' => 10,
                            ]
                        );
                    }
                }

                /*
                * Space between agenda items, while preserving a clean editable
                * Word layout for recipients.
                */
                $section->addTextBreak(1);
            }
        }

        $filename = Str::slug(
            $selectedCategory->categoryname
        ) . '-agenda-' . now()->format('Y-m-d') . '.docx';

        return response()->streamDownload(
            function () use ($phpWord) {
                IOFactory::createWriter(
                    $phpWord,
                    'Word2007'
                )->save('php://output');
            },
            $filename,
            [
                'Content-Type' =>
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            ]
        );
    }

    public function knowledgeItemAgendaDocx(
        Request $request,
        int $knowledgeItemId
    ) {
        $reviewOnly = $request->boolean('review_only');

        $item = KnowledgeItem::query()
            ->with([
                'primaryCategory.domain',
                'primaryCategory.parentCategory',
                'notes' => function ($query) {
                    $query->orderBy('sortorder')
                        ->orderByDesc('reviewdate')
                        ->orderByDesc('id');
                },
                'reviewLogs' => function ($query) {
                    $query->orderByDesc('reviewdate')
                        ->orderByDesc('id');
                },
            ])
            ->findOrFail($knowledgeItemId);

        if ($reviewOnly && empty($item->nextreviewdate)) {
            abort(404, 'This knowledge item does not have a review date.');
        }

        $phpWord = new PhpWord();

        $phpWord->setDefaultFontName('Arial');
        $phpWord->setDefaultFontSize(11);

        $phpWord->addTitleStyle(1, [
            'name' => 'Arial',
            'bold' => true,
            'size' => 17,
            'color' => '1F2937',
        ]);

        $phpWord->addTitleStyle(2, [
            'name' => 'Arial',
            'bold' => true,
            'size' => 14,
            'color' => '1F2937',
        ]);

        $section = $phpWord->addSection([
            'marginTop' => 900,
            'marginRight' => 900,
            'marginBottom' => 900,
            'marginLeft' => 900,
        ]);

        $section->addTitle($item->itemname, 1);

        $agendaContent = [
            $item->summary,
            $item->detailednotes,
            $item->reviewnotes,
        ];

        foreach ($agendaContent as $content) {
            if (blank($content)) {
                continue;
            }

            $this->addMarkdownContentToDocx($section, $content);
        }

        $agendaNotes = $item->notes
            ->filter(fn ($note) => filled($note->notecontent))
            ->sortBy([
                ['sortorder', 'asc'],
                ['reviewdate', 'desc'],
                ['id', 'asc'],
            ])
            ->values();

        foreach ($agendaNotes as $note) {
            if (filled($note->title)) {
                $section->addText(
                    $note->title,
                    [
                        'name' => 'Arial',
                        'size' => 10,
                        'bold' => true,
                        'color' => '374151',
                    ],
                    [
                        'spaceBefore' => 120,
                        'spaceAfter' => 50,
                    ]
                );
            }

            $this->addMarkdownContentToDocx(
                $section,
                $note->notecontent
            );
        }

        $reviewEntries = $item->reviewLogs
            ->filter(fn ($reviewLog) => filled($reviewLog->summary));

        if ($reviewEntries->isNotEmpty()) {
            $section->addText(
                'Previous decisions / reviews',
                [
                    'name' => 'Arial',
                    'size' => 10,
                    'bold' => true,
                    'color' => '374151',
                ],
                [
                    'spaceBefore' => 120,
                    'spaceAfter' => 50,
                ]
            );

            foreach ($reviewEntries as $reviewLog) {
                $reviewText = $this->plainTextForDocx(
                    $reviewLog->summary
                );

                if ($reviewText === '') {
                    continue;
                }

                $prefix = $reviewLog->reviewdate
                    ? $reviewLog->reviewdate->format('d M Y') . ': '
                    : '';

                $section->addListItem(
                    $prefix . $reviewText,
                    0,
                    [
                        'name' => 'Arial',
                        'size' => 10,
                    ]
                );
            }
        }

        $filename = Str::slug($item->itemname)
            . '-agenda-'
            . now()->format('Y-m-d')
            . '.docx';

        return response()->streamDownload(
            function () use ($phpWord) {
                IOFactory::createWriter($phpWord, 'Word2007')
                    ->save('php://output');
            },
            $filename,
            [
                'Content-Type' =>
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            ]
        );
    }
    public function testDocx()
    {
        $phpWord = new PhpWord();

        $phpWord->setDefaultFontName('Arial');
        $phpWord->setDefaultFontSize(11);

        $phpWord->addTitleStyle(1, [
            'name' => 'Arial',
            'bold' => true,
            'size' => 16,
            'color' => '1F2937',
        ]);

        $section = $phpWord->addSection([
            'marginTop' => 900,
            'marginRight' => 900,
            'marginBottom' => 900,
            'marginLeft' => 900,
        ]);

        $section->addTitle(
            'Knowledge Agenda DOCX Test',
            1
        );

        $section->addText(
            'This document confirms that PHPWord DOCX export is working.',
            [
                'name' => 'Arial',
                'size' => 11,
            ]
        );

        $section->addTextBreak(1);

        $section->addListItem(
            'This is an editable Word document.',
            0,
            [
                'name' => 'Arial',
                'size' => 11,
            ]
        );

        $section->addListItem(
            'The Agenda DOCX export can now be implemented safely.',
            0,
            [
                'name' => 'Arial',
                'size' => 11,
            ]
        );

        return response()->streamDownload(
            function () use ($phpWord) {
                IOFactory::createWriter(
                    $phpWord,
                    'Word2007'
                )->save('php://output');
            },
            'knowledge-agenda-docx-test.docx',
            [
                'Content-Type' =>
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            ]
        );
    }
    protected function plainTextForDocx(?string $content): string
    {
        if (blank($content)) {
            return '';
        }

        /*
        * Preserve readable paragraph separation before stripping HTML.
        */
        $content = str_replace(
            [
                '<br>',
                '<br/>',
                '<br />',
                '</p>',
                '</li>',
                '</div>',
                '</h1>',
                '</h2>',
                '</h3>',
                '</h4>',
                '</h5>',
                '</h6>',
            ],
            "\n",
            $content
        );

        $content = strip_tags($content);

        $content = html_entity_decode(
            $content,
            ENT_QUOTES | ENT_HTML5,
            'UTF-8'
        );

        $content = preg_replace("/\r\n|\r/", "\n", $content);

        $content = preg_replace("/[ \t]+\n/", "\n", $content);

        $content = preg_replace("/\n{3,}/", "\n\n", $content);

        return trim($content);
    }

    protected function isMarkdownTableLine(string $line): bool
    {
        if ($line === '') {
            return false;
        }

        /*
        * Markdown table rows always contain pipe delimiters. This also permits
        * tables that omit leading/trailing pipes, although your stored content
        * normally uses them.
        */
        return str_contains($line, '|');
    }

    protected function addMarkdownContentToDocx(
    $section,
    ?string $content
): void {
    if (blank($content)) {
        return;
    }

    $lines = preg_split('/\R/', trim((string) $content));

    $paragraphLines = [];
    $tableLines = [];
    $listItems = [];
    $listType = null;

    $flushParagraph = function () use (&$paragraphLines, $section): void {
        if (empty($paragraphLines)) {
            return;
        }

        $paragraph = trim(implode(' ', $paragraphLines));

        if ($paragraph !== '') {
            $this->addMarkdownParagraphToDocx($section, $paragraph);
        }

        $paragraphLines = [];
    };

    $flushTable = function () use (&$tableLines, $section): void {
        if (empty($tableLines)) {
            return;
        }

        $this->addMarkdownTableToDocx($section, $tableLines);

        $tableLines = [];
    };

    $flushList = function () use (&$listItems, &$listType, $section): void {
        if (empty($listItems)) {
            return;
        }

        foreach ($listItems as $listItem) {
            $this->addMarkdownListItemToDocx(
                $section,
                $listItem['text'],
                $listType === 'ordered',
                $listItem['level']
            );
        }

        $listItems = [];
        $listType = null;
    };

    foreach ($lines as $line) {
        $trimmedLine = trim($line);

        if ($this->isMarkdownTableLine($trimmedLine)) {
            $flushParagraph();
            $flushList();

            $tableLines[] = $trimmedLine;

            continue;
        }

        if (!empty($tableLines)) {
            $flushTable();
        }

        if ($trimmedLine === '') {
            $flushParagraph();
            $flushList();

            continue;
        }

        /*
        |--------------------------------------------------------------------------
        | Nested bullet list: >- Item
        |--------------------------------------------------------------------------
        | Accepts:
        | >- Item
        | > - Item
        | >>- Item
        | >> - Item
        */
        if (preg_match('/^(>+)\s*-\s+(.+)$/', $trimmedLine, $matches)) {
            $flushParagraph();
            $flushList();

            $indentLevel = strlen($matches[1]);

            $section->addText(
                $matches[2],
                [
                    'name' => 'Arial',
                    'size' => 11,
                ],
                [
                    'indentation' => [
                        'left' => 720 * $indentLevel,
                        'hanging' => 0,
                    ],
                    'spaceAfter' => 50,
                ]
            );

            continue;
        }

        /*
        |--------------------------------------------------------------------------
        | Standard bullet list: - Item, * Item, + Item
        |--------------------------------------------------------------------------
        */
        if (preg_match('/^[-*+]\s+(.+)$/', $trimmedLine, $matches)) {
            $flushParagraph();

            if ($listType !== null && $listType !== 'bullet') {
                $flushList();
            }

            $listType = 'bullet';

            $listItems[] = [
                'text' => $matches[1],
                'level' => 0,
            ];

            continue;
        }

        /*
        |--------------------------------------------------------------------------
        | Ordered list: 1. Item
        |--------------------------------------------------------------------------
        */
        if (preg_match('/^\d+\.\s+(.+)$/', $trimmedLine, $matches)) {
            $flushParagraph();

            if ($listType !== null && $listType !== 'ordered') {
                $flushList();
            }

            $listType = 'ordered';

            $listItems[] = [
                'text' => $matches[1],
                'level' => 0,
            ];

            continue;
        }

        if (!empty($listItems)) {
            $flushList();
        }

        $paragraphLines[] = $line;
    }

    $flushTable();
    $flushList();
    $flushParagraph();
}

    protected function addMarkdownTableToDocx(
        \PhpOffice\PhpWord\Element\Section $section,
        array $tableLines
    ): void {
        $rows = collect($tableLines)
            ->map(fn (string $line) => $this->parseMarkdownTableRow($line))
            ->filter(fn (array $cells) => $cells !== [])
            ->values();

        if ($rows->count() < 2) {
            /*
            * It was not a useful table. Preserve the source as ordinary text.
            */
            foreach ($tableLines as $line) {
                $section->addText(
                    $line,
                    [
                        'name' => 'Arial',
                        'size' => 10,
                    ]
                );
            }

            return;
        }

        /*
        * The second row is the Markdown divider:
        *
        * | --- | ---: | :--- |
        *
        * It is formatting syntax, not data.
        */
        if ($rows->count() >= 2 && $this->isMarkdownTableDivider($rows->get(1))) {
            $headerRow = $rows->first();
            $dataRows = $rows->slice(2)->values();
        } else {
            $headerRow = null;
            $dataRows = $rows;
        }

        $columnCount = max(
            $headerRow ? count($headerRow) : 0,
            $dataRows->max(fn (array $row) => count($row)) ?? 0
        );

        if ($columnCount === 0) {
            return;
        }

        $table = $section->addTable([
            'borderSize' => 6,
            'borderColor' => 'A6A6A6',
            'cellMargin' => 80,
            'alignment' => \PhpOffice\PhpWord\SimpleType\JcTable::CENTER,
            'layout' => \PhpOffice\PhpWord\Style\Table::LAYOUT_FIXED,
        ]);

        if ($headerRow !== null) {
            $table->addRow();

            for ($columnIndex = 0; $columnIndex < $columnCount; $columnIndex++) {
                $cell = $table->addCell(
                    9000 / $columnCount,
                    [
                        'bgColor' => 'E5E7EB',
                        'valign' => 'center',
                    ]
                );

                $cell->addText(
                    $headerRow[$columnIndex] ?? '',
                    [
                        'name' => 'Arial',
                        'size' => 8.5,
                        'bold' => true,
                    ]
                );
            }
        }

        foreach ($dataRows as $row) {
            $table->addRow();

            for ($columnIndex = 0; $columnIndex < $columnCount; $columnIndex++) {
                $cell = $table->addCell(
                    9000 / $columnCount,
                    [
                        'valign' => 'center',
                    ]
                );

                $cell->addText(
                    $row[$columnIndex] ?? '',
                    [
                        'name' => 'Arial',
                        'size' => 8.5,
                    ]
                );
            }
        }

        $section->addTextBreak(1);
    }

    protected function parseMarkdownTableRow(string $line): array
    {
        $line = trim($line);

        $line = preg_replace('/^\|/', '', $line);
        $line = preg_replace('/\|$/', '', $line);

        if ($line === '') {
            return [];
        }

        return collect(explode('|', $line))
            ->map(function (string $cell) {
                $cell = trim($cell);

                /*
                * Preserve a literal pipe entered in Markdown as \|.
                */
                $cell = str_replace('\|', '|', $cell);

                return $this->plainTextForDocx($cell);
            })
            ->all();
    }

    protected function isMarkdownTableDivider(array $cells): bool
    {
        if ($cells === []) {
            return false;
        }

        return collect($cells)
            ->every(function (string $cell) {
                return (bool) preg_match(
                    '/^:?-{3,}:?$/',
                    trim($cell)
                );
            });
    }

    protected function addMarkdownParagraphToDocx(
        \PhpOffice\PhpWord\Element\Section $section,
        string $markdown
    ): void {
        $markdown = trim($markdown);

        if ($markdown === '') {
            return;
        }

        /*
        * Join physical lines inside the same paragraph. This preserves paragraph
        * structure while avoiding awkward Word line breaks from wrapped Markdown.
        */
        $markdown = preg_replace('/\s*\n\s*/', ' ', $markdown);

        $textRun = $section->addTextRun([
            'spaceAfter' => 120,
        ]);

        $this->addInlineMarkdownToTextRun(
            $textRun,
            $markdown,
            [
                'name' => 'Arial',
                'size' => 11,
            ]
        );
    } 
    protected function addMarkdownListItemToDocx(
        \PhpOffice\PhpWord\Element\Section $section,
        string $markdown,
        bool $ordered = false
    ): void {
        $textRun = $section->addListItemRun(
            0,
            $ordered
                ? [
                    'listType' => \PhpOffice\PhpWord\Style\ListItem::TYPE_NUMBER,
                ]
                : [
                    'listType' => \PhpOffice\PhpWord\Style\ListItem::TYPE_BULLET_FILLED,
                ],
            [
                'spaceAfter' => 50,
            ]
        );

        $this->addInlineMarkdownToTextRun(
            $textRun,
            $markdown,
            [
                'name' => 'Arial',
                'size' => 11,
            ]
        );
    }  
    protected function addInlineMarkdownToTextRun(
        \PhpOffice\PhpWord\Element\TextRun $textRun,
        string $markdown,
        array $baseFontStyle = []
    ): void {
        /*
        * Supports:
        *
        * **bold**
        * __bold__
        * *italic*
        * _italic_
        * `code`
        *
        * This is intentionally a practical Markdown subset for agenda material.
        */
        $pattern = '/(\*\*.+?\*\*|__.+?__|\*.+?\*|_.+?_|`.+?`)/u';

        $parts = preg_split(
            $pattern,
            $markdown,
            -1,
            PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY
        );

        foreach ($parts as $part) {
            $style = $baseFontStyle;
            $text = $part;

            if (
                str_starts_with($part, '**')
                && str_ends_with($part, '**')
            ) {
                $text = mb_substr($part, 2, -2);
                $style['bold'] = true;
            } elseif (
                str_starts_with($part, '__')
                && str_ends_with($part, '__')
            ) {
                $text = mb_substr($part, 2, -2);
                $style['bold'] = true;
            } elseif (
                str_starts_with($part, '*')
                && str_ends_with($part, '*')
            ) {
                $text = mb_substr($part, 1, -1);
                $style['italic'] = true;
            } elseif (
                str_starts_with($part, '_')
                && str_ends_with($part, '_')
            ) {
                $text = mb_substr($part, 1, -1);
                $style['italic'] = true;
            } elseif (
                str_starts_with($part, '`')
                && str_ends_with($part, '`')
            ) {
                $text = mb_substr($part, 1, -1);
                $style['name'] = 'Courier New';
            }

            if ($text !== '') {
                $textRun->addText(
                    html_entity_decode(
                        $text,
                        ENT_QUOTES | ENT_HTML5,
                        'UTF-8'
                    ),
                    $style
                );
            }
        }
    }
}