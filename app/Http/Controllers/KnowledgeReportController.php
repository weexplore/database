<?php

namespace App\Http\Controllers;

use App\Models\KnowledgeCategory;
use Illuminate\Http\Request;

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

        $categoriesQuery = $this->baseCategoryReferenceQuery($reviewOnly)
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
            'returnTo' => $request->input('return_to', url()->previous()),
        ]);
    }

    public function domainReferenceBook(Request $request)
{
    $domainId = (int) $request->input('domainid');

    abort_if($domainId <= 0, 404, 'No domain selected.');

    $reviewOnly = $request->boolean('review_only');

    // IMPORTANT:
    // Replace this with the SAME method used by your domain tree / sidebar.
    $selectedCategoryIds = $this->collectDomainTreeIdsInDisplayOrder($domainId);

    $categoriesQuery = $this->baseCategoryReferenceQuery($reviewOnly)
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

        $selectedCategory = KnowledgeCategory::query()
            ->with(['domain', 'parentCategory'])
            ->findOrFail($categoryId);

        $selectedCategoryIds = $this->collectCategoryTreeIds($selectedCategory->id);

        $categories = $this->prepareCategoriesForReport(
            $this->baseCategoryReferenceQuery($reviewOnly)
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
            'reviewOnly' => $reviewOnly,
            'returnTo' => $request->input('return_to', url()->previous()),
            'reportTitle' => 'Knowledge Category Tree Report – ' . $selectedCategory->categoryname,
            'reportSubtitle' => 'Compiled reference report for this category and all categories beneath it',
        ]);
    }

    protected function baseCategoryReferenceQuery(bool $reviewOnly = false)
    {
        return KnowledgeCategory::query()
            ->with([
                'domain',
                'parentCategory',
                'knowledgeItems' => function ($query) use ($reviewOnly) {
                    $query
                        ->when($reviewOnly, fn ($q) => $q->whereNotNull('nextreviewdate'))
                        ->orderBy('sortorder')
                        ->orderBy('itemname');
                },
                'knowledgeItems.primaryCategory',
                'knowledgeItems.parentItem',
                'knowledgeItems.place',
                'knowledgeItems.itemType',
                'knowledgeItems.notes' => function ($query) {
                    $query->orderBy('sortorder')
                        ->orderByDesc('reviewdate')
                        ->orderByDesc('id');
                },
                'knowledgeItems.sources' => function ($query) {
                    $query->orderByDesc('retrievedon')
                        ->orderByDesc('id');
                },
                'knowledgeItems.reviewLogs' => function ($query) {
                    $query->orderByDesc('reviewdate')
                        ->orderByDesc('id');
                },
                'knowledgeItems.outgoingRelationships' => function ($query) {
                    $query->orderBy('sortorder')
                        ->orderBy('id');
                },
                'knowledgeItems.outgoingRelationships.toItem.primaryCategory',
                'knowledgeItems.incomingRelationships' => function ($query) {
                    $query->orderBy('sortorder')
                        ->orderBy('id');
                },
                'knowledgeItems.incomingRelationships.fromItem.primaryCategory',
                'knowledgeItems.attachments' => function ($query) {
                    $query->orderByDesc('isprimary')
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
                'knowledgeItems.instrument.priceObservations' => function ($query) {
                    $query->orderByDesc('observedon')
                        ->orderByDesc('id');
                },
                'knowledgeItems.instrument.corporateActions' => function ($query) {
                    $query->orderByDesc('actiondate')
                        ->orderByDesc('id');
                },
                'knowledgeItems.instrument.corporateActions.source',
                'knowledgeItems.instrument.transactions' => function ($query) {
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
                    $displayRelationships = $item->outgoingRelationships
                        ->toBase()
                        ->map(function ($relationship) {
                            return [
                                'relationship' => $relationship,
                                'direction' => 'outgoing',
                                'relatedItem' => $relationship->toItem,
                                'displayTypeLabel' => $relationship->relationshipTypeLabel(),
                                'sortorder' => $relationship->sortorder ?? 0,
                                'relatedSortName' => mb_strtolower($relationship->toItem?->itemname ?? 'zzzz'),
                            ];
                        })
                        ->merge(
                            $item->incomingRelationships
                                ->toBase()
                                ->map(function ($relationship) {
                                    return [
                                        'relationship' => $relationship,
                                        'direction' => 'incoming',
                                        'relatedItem' => $relationship->fromItem,
                                        'displayTypeLabel' => $relationship->inverseRelationshipTypeLabel(),
                                        'sortorder' => $relationship->sortorder ?? 0,
                                        'relatedSortName' => mb_strtolower($relationship->fromItem?->itemname ?? 'zzzz'),
                                    ];
                                })
                        )
                        ->sortBy([
                            ['sortorder', 'asc'],
                            ['relatedSortName', 'asc'],
                        ])
                        ->values();

                    $item->setRelation('displayRelationships', $displayRelationships);

                    return $item;
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

}