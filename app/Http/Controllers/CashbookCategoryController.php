<?php

namespace App\Http\Controllers;

use App\Models\CashbookCategory;
use App\Models\CashbookCategoryType;
use App\Models\LegalEntity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class CashbookCategoryController extends Controller
{

    public function index(Request $request): View
    {
        $legalEntities = LegalEntity::orderBy('entityname')->get();
        $categoryTypes = CashbookCategoryType::where('isactive', 1)
            ->orderBy('sortorder')
            ->orderBy('typename')
            ->get();

        $allCategories = CashbookCategory::query()
            ->select('cashbook_categories.*')
            ->leftJoin('legal_entities', 'cashbook_categories.legalentityid', '=', 'legal_entities.id')
            ->leftJoin('cashbook_category_types', 'cashbook_categories.categorytypeid', '=', 'cashbook_category_types.id')
            ->with(['legalEntity', 'categoryType', 'parentCategory'])
            ->when($request->filled('legalentityid'), fn ($query) => $query->where('cashbook_categories.legalentityid', $request->integer('legalentityid')))
            ->when($request->filled('categorytypeid'), fn ($query) => $query->where('cashbook_categories.categorytypeid', $request->integer('categorytypeid')))
            ->when($request->filled('isactive'), fn ($query) => $query->where('cashbook_categories.isactive', $request->boolean('isactive')))
            ->orderBy('legal_entities.entityname')
            ->orderBy('cashbook_category_types.typename')
            ->orderBy('cashbook_categories.sortorder')
            ->orderBy('cashbook_categories.categoryname')
            ->get();

        $categoriesByParent = $allCategories->groupBy(function ($category) {
            return $category->parentcategoryid ?: 0;
        });

        $flattenHierarchy = function ($parentId = 0, $depth = 0) use (&$flattenHierarchy, $categoriesByParent): Collection {
            $items = collect();

            foreach ($categoriesByParent->get($parentId, collect()) as $category) {
                $category->depth = $depth;
                $items->push($category);
                $items = $items->concat($flattenHierarchy($category->id, $depth + 1));
            }

            return $items;
        };

        $topLevelCategories = $allCategories->filter(function ($category) use ($allCategories) {
            return empty($category->parentcategoryid)
                || ! $allCategories->contains('id', $category->parentcategoryid);
        });

        $hierarchy = collect();

        foreach ($topLevelCategories as $topLevelCategory) {
            $topLevelCategory->depth = 0;
            $hierarchy->push($topLevelCategory);
            $hierarchy = $hierarchy->concat($flattenHierarchy($topLevelCategory->id, 1));
        }

        $perPage = 25;
        $page = LengthAwarePaginator::resolveCurrentPage();
        $pagedItems = $hierarchy->forPage($page, $perPage)->values();

        $cashbookCategories = new LengthAwarePaginator(
            $pagedItems,
            $hierarchy->count(),
            $perPage,
            $page,
            [
                'path' => LengthAwarePaginator::resolveCurrentPath(),
                'query' => $request->query(),
            ]
        );

        return view('cashbookcategories.index', compact('cashbookCategories', 'legalEntities', 'categoryTypes'));
    }

    public function create(): View
    {
        return view('cashbookcategories.edit', $this->editViewData(new CashbookCategory()));
    }

    public function bulkUpdate(Request $request): RedirectResponse
{
    $validated = $request->validate([
        'rows' => ['required', 'array'],
        'rows.*.id' => ['required', 'integer', 'exists:cashbook_categories,id'],
        'rows.*.sortorder' => ['nullable', 'integer'],
        'rows.*.categorycode' => ['nullable', 'string', 'max:50'],
        'rows.*.allowposting' => ['nullable', 'boolean'],
        'rows.*.isactive' => ['required', 'boolean'],
    ]);

    foreach ($validated['rows'] as $row) {
        $category = CashbookCategory::find($row['id']);

        if (! $category) {
            continue;
        }

        $category->update([
            'sortorder' => $row['sortorder'] ?? null,
            'categorycode' => $row['categorycode'] ?? null,
            'allowposting' => (bool) ($row['allowposting'] ?? 0),
            'isactive' => (bool) ($row['isactive'] ?? 0),
        ]);
    }

    return redirect()
        ->route('cashbook-categories.index', $request->query())
        ->with('success', 'Cashbook categories updated successfully.');
}

    public function store(Request $request): RedirectResponse
{
    $validated = $this->validateCategory($request);

    CashbookCategory::create($validated);

    return redirect()
        ->route('cashbook-categories.index')
        ->with('success', 'Cashbook category added successfully.');
}

    public function edit(CashbookCategory $cashbookCategory): View
    {
        return view('cashbookcategories.edit', $this->editViewData($cashbookCategory));
    }

public function update(Request $request, CashbookCategory $cashbookCategory): RedirectResponse
{
    $validated = $this->validateCategory($request, $cashbookCategory);

    $cashbookCategory->update($validated);

    return redirect()
        ->route('cashbook-categories.index')
        ->with('success', 'Cashbook category updated successfully.');
}

    public function destroy(CashbookCategory $cashbookCategory): RedirectResponse
    {
        $cashbookCategory->delete();

        return redirect()
            ->route('cashbook-categories.index')
            ->with('success', 'Cashbook category deleted successfully.');
    }

    private function editViewData(CashbookCategory $cashbookCategory): array
    {
        return [
            'cashbookCategory' => $cashbookCategory,
            'legalEntities' => LegalEntity::where('isactive', 1)->orderBy('entityname')->get(),
            'categoryTypes' => CashbookCategoryType::where('isactive', 1)->orderBy('sortorder')->orderBy('typename')->get(),
            'parentCategories' => CashbookCategory::where('isactive', 1)
                ->when($cashbookCategory->exists, fn ($query) => $query->where('id', '<>', $cashbookCategory->id))
                ->orderBy('categoryname')
                ->get(),
        ];
    }

    private function validateCategory(Request $request, ?CashbookCategory $cashbookCategory = null): array
{
    $categoryId = $cashbookCategory?->id;
    $legalEntityId = $request->filled('legalentityid') ? $request->integer('legalentityid') : null;

    $validated = $request->validate([
        'legalentityid' => ['nullable', 'integer', 'exists:legal_entities,id'],
        'categorytypeid' => ['required', 'integer', 'exists:cashbook_category_types,id'],
        'parentcategoryid' => ['nullable', 'integer', 'exists:cashbook_categories,id'],
        'categorycode' => [
            'nullable',
            'string',
            'max:30',
            Rule::unique('cashbook_categories', 'categorycode')
                ->where(fn ($query) => $query->where('legalentityid', $legalEntityId))
                ->ignore($categoryId),
        ],
        'categoryname' => [
            'required',
            'string',
            'max:150',
            Rule::unique('cashbook_categories', 'categoryname')
                ->where(fn ($query) => $query
                    ->where('legalentityid', $legalEntityId)
                    ->where('categorytypeid', $request->integer('categorytypeid'))
                    ->where('parentcategoryid', $request->input('parentcategoryid')))
                ->ignore($categoryId),
        ],
        'allowposting' => ['nullable', 'boolean'],
        'issystem' => ['nullable', 'boolean'],
        'sortorder' => ['nullable', 'integer', 'min:0'],
        'isactive' => ['nullable', 'boolean'],
        'notes' => ['nullable', 'string'],
    ]);

    $validated['allowposting'] = (bool) $request->input('allowposting', 0);
    $validated['issystem'] = (bool) $request->input('issystem', 0);
    $validated['isactive'] = (bool) $request->input('isactive', 0);

    if (! empty($validated['parentcategoryid'])) {
        $parentCategory = CashbookCategory::find($validated['parentcategoryid']);

        if (
            $parentCategory
            && $parentCategory->legalentityid !== null
            && (int) $parentCategory->legalentityid !== (int) $validated['legalentityid']
        ) {
            abort(422, 'Parent category must belong to the same legal entity scope.');
        }
    }

    return $validated;
}
}
