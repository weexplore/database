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

    $categoryOptions = KnowledgeCategory::query()
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

    $categoriesQuery = KnowledgeCategory::query()
        ->with([
            'domain',
            'parentCategory',
            'knowledgeItems' => function ($query) {
                $query->orderBy('sortorder')
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
            'knowledgeItems.relationships' => function ($query) {
                $query->orderBy('sortorder')
                    ->orderBy('id');
            },
            'knowledgeItems.relationships.toItem',
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
        ])
        ->orderBy('sortorder')
        ->orderBy('categoryname');

    if (!empty($selectedCategoryIds)) {
        $categoriesQuery->whereIn('id', $selectedCategoryIds);
    }

    $categories = $categoriesQuery->get();

    return view('reports.knowledge.categories.reference-book', [
        'categories' => $categories,
        'categoryOptions' => $categoryOptions,
        'selectedCategoryIds' => $selectedCategoryIds,
        'returnTo' => $request->input('return_to', url()->previous()),
    ]);
}

public function domainReferenceBook(Request $request)
{
    $domainId = (int) $request->input('domainid');

    abort_if($domainId <= 0, 404, 'No domain selected.');

    $selectedCategoryIds = KnowledgeCategory::query()
        ->where('domainid', $domainId)
        ->orderBy('sortorder')
        ->orderBy('categoryname')
        ->pluck('id')
        ->map(fn ($id) => (int) $id)
        ->all();

    $categories = KnowledgeCategory::query()
        ->with([
            'domain',
            'parentCategory',
            'knowledgeItems' => function ($query) {
                $query->orderBy('sortorder')
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
            'knowledgeItems.relationships' => function ($query) {
                $query->orderBy('sortorder')
                    ->orderBy('id');
            },
            'knowledgeItems.relationships.toItem',
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
        ])
        ->whereIn('id', $selectedCategoryIds)
        ->orderBy('sortorder')
        ->orderBy('categoryname')
        ->get();

    $domain = KnowledgeCategory::query()
        ->where('domainid', $domainId)
        ->with('domain')
        ->first()?->domain;

    return view('reports.knowledge.categories.reference-book', [
        'categories' => $categories,
        'selectedCategoryIds' => $selectedCategoryIds,
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

    $selectedCategory = KnowledgeCategory::query()
        ->with(['domain', 'parentCategory'])
        ->findOrFail($categoryId);

    $selectedCategoryIds = $this->collectCategoryTreeIds($selectedCategory->id);

    $categories = KnowledgeCategory::query()
        ->with([
            'domain',
            'parentCategory',
            'knowledgeItems' => function ($query) {
                $query->orderBy('sortorder')
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
            'knowledgeItems.relationships' => function ($query) {
                $query->orderBy('sortorder')
                    ->orderBy('id');
            },
            'knowledgeItems.relationships.toItem',
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
        ])
        ->whereIn('id', $selectedCategoryIds)
        ->orderBy('sortorder')
        ->orderBy('categoryname')
        ->get();

    return view('reports.knowledge.categories.reference-book', [
        'categories' => $categories,
        'selectedCategoryIds' => $selectedCategoryIds,
        'returnTo' => $request->input('return_to', url()->previous()),
        'reportTitle' => 'Knowledge Category Tree Report – ' . $selectedCategory->categoryname,
        'reportSubtitle' => 'Compiled reference report for this category and all categories beneath it',
    ]);
}
}