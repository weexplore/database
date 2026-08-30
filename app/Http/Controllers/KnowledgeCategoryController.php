<?php

namespace App\Http\Controllers;

use App\Models\KnowledgeCategory;
use App\Models\KnowledgeDomain;
use App\Models\KnowledgeItem;
use App\Models\KnowledgeItemType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Pagination\LengthAwarePaginator;

class KnowledgeCategoryController extends Controller
{
    public function index(Request $request): View
{
    $filters = [
        'domainid' => $request->integer('domainid') ?: null,
        'categoryid' => $request->integer('categoryid') ?: null,
        'search' => trim((string) $request->query('search', '')),
        'knowledgeitemtypeid' => $request->integer('knowledgeitemtypeid') ?: null,
        'itemstatus' => trim((string) $request->query('itemstatus', '')),
    ];

    $domains = KnowledgeDomain::query()
        ->where('isactive', 1)
        ->orderBy('sortorder')
        ->orderBy('domainname')
        ->get();

    if (!$filters['domainid'] && $domains->isNotEmpty()) {
        $filters['domainid'] = (int) $domains->first()->id;
    }

    $allCategories = collect();
    $categoryTree = collect();
    $selectedCategory = null;
    $editableCategories = collect();
    $items = new LengthAwarePaginator(
        collect(),
        0,
        50,
        1,
        [
            'path' => $request->url(),
            'query' => $request->query(),
        ]
    );$items = collect();
    $globalParentOptions = collect();

    // Expanded ids from request
    $expandedIds = collect($request->input('expanded', []))
        ->map(fn ($id) => (int) $id)
        ->filter()
        ->values()
        ->all();

    if ($filters['domainid']) {
        $allCategories = KnowledgeCategory::query()
            ->where('domainid', $filters['domainid'])
            ->orderBy('sortorder')
            ->orderBy('categoryname')
            ->get();

        // Pre-group by parent for efficient tree building
        $groupedByParent = $allCategories->groupBy('parentcategoryid');

        $categoryTree = $this->buildTreeFromGroups($groupedByParent, null);

        // Choose selected category
        $selectedCategory = $filters['categoryid']
            ? $allCategories->firstWhere('id', $filters['categoryid'])
            : $allCategories->firstWhere('parentcategoryid', null) ?? $allCategories->first();

        if ($selectedCategory) {
            $filters['categoryid'] = (int) $selectedCategory->id;

            // Items under selected category
            $itemColumns = [
                'id',
                'primarycategoryid',
                'itemname',
                'itemtype',
                'itemstatus',
                'summary',
                'startdate',
                'enddate',
                'nextreviewdate',
                'sortorder',
                'isfeatured',
                'isactive',
            ];

            /*
            * detailednotes is only selected when it is needed for the current
            * full-text-style LIKE search. This avoids loading large Bible study
            * content during normal category browsing.
            */
            if ($filters['search'] !== '') {
                $itemColumns[] = 'detailednotes';
            }

            $itemQuery = KnowledgeItem::query()
                ->select($itemColumns)
                ->where('primarycategoryid', $selectedCategory->id)
                ->with([
                    'tagLinks.tag',
                    'itemType',
                ])
                ->orderBy('sortorder')
                ->orderBy('itemname');

            if ($filters['search'] !== '') {
                $itemQuery->where(function ($query) use ($filters) {
                    $query->where('itemname', 'like', '%' . $filters['search'] . '%')
                        ->orWhere('summary', 'like', '%' . $filters['search'] . '%')
                        ->orWhere('detailednotes', 'like', '%' . $filters['search'] . '%');
                });
            }

            if ($filters['knowledgeitemtypeid']) {
                $itemQuery->where('itemtype', $filters['knowledgeitemtypeid']);
            }

            if ($filters['itemstatus'] !== '') {
                $itemQuery->where('itemstatus', $filters['itemstatus']);
            }

            $items = $itemQuery
                ->paginate(50)
                ->withQueryString();

            // Immediate children for bulk-edit block
            $editableCategories = $allCategories
                ->where('parentcategoryid', $selectedCategory->id)
                ->sortBy([
                    ['sortorder', 'asc'],
                    ['categoryname', 'asc'],
                ])
                ->values();
        } else {
            $editableCategories = $allCategories
                ->whereNull('parentcategoryid')
                ->sortBy([
                    ['sortorder', 'asc'],
                    ['categoryname', 'asc'],
                ])
                ->values();
        }

        // Flattened parent options for the "Parent category" select
        $globalParentOptions = $this->buildParentOptionsFromGroups($groupedByParent);

        // Note: rowParentOptions removed – the index page does not use per-row parent dropdowns
    }

    // Ensure ancestors of selected category are expanded
    if ($selectedCategory && $allCategories->isNotEmpty()) {
        $ancestorIds = [];
        $current = $selectedCategory;

        while ($current && $current->parentcategoryid) {
            $ancestorIds[] = (int) $current->parentcategoryid;
            $current = $allCategories->firstWhere('id', $current->parentcategoryid);
        }

        $expandedIds = array_values(array_unique(array_merge($expandedIds, $ancestorIds)));
    }

    // Build a lookup array for quick checks in Blade
    $expandedIdLookup = [];
    foreach ($expandedIds as $id) {
        $expandedIdLookup[(int) $id] = true;
    }

    $itemTypes = KnowledgeItemType::query()
        ->where('isactive', 1)
        ->orderBy('sortorder')
        ->orderBy('typename')
        ->get();

    $parentOptions = collect();
    if (!empty($filters['domainid'])) {
        $parentOptions = $allCategories
            ->sortBy([
                ['categoryname', 'asc'],
            ])
            ->map(function ($category) {
                return [
                    'id' => $category->id,
                    'label' => $category->categoryname,
                ];
            })
            ->values();
    }

    $itemStatuses = KnowledgeItem::query()
        ->when(
            $selectedCategory,
            fn ($query) => $query->where(
                'primarycategoryid',
                $selectedCategory->id
            )
        )
        ->whereNotNull('itemstatus')
        ->where('itemstatus', '!=', '')
        ->distinct()
        ->orderBy('itemstatus')
        ->pluck('itemstatus');

    return view('knowledge-categories.index', [
        'pageTitle' => 'Knowledge Categories',
        'filters' => $filters,
        'domains' => $domains,
        'categoryTree' => $categoryTree,
        'selectedCategory' => $selectedCategory,
        'editableCategories' => $editableCategories,
        'parentOptions' => $parentOptions,
        'items' => $items,
        'itemTypes' => $itemTypes,
        'itemStatuses' => $itemStatuses,
        'globalParentOptions' => $globalParentOptions,
        // rowParentOptions removed
        'categoryTypeOptions' => [
            'folder' => 'Folder',
            'theme' => 'Theme',
            'subtheme' => 'Subtheme',
            'topic' => 'Topic',
            'stream' => 'Stream',
        ],
        'expandedIds' => $expandedIds,
        'expandedIdLookup' => $expandedIdLookup,
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
    $categoryTypeOptions = ['folder', 'theme', 'subtheme', 'topic', 'stream'];

    $validated = $request->validate([
        'existing' => ['nullable', 'array'],
        'existing.*.categoryname' => ['required', 'string', 'max:200'],
        'existing.*.parentcategoryid' => ['nullable', 'integer', Rule::exists('knowledgecategories', 'id')],
        'existing.*.categorytype' => ['required', 'string', Rule::in($categoryTypeOptions)],
        'existing.*.sortorder' => ['nullable', 'integer', 'min:0'],
        'existing.*.nextreviewdate' => ['nullable', 'date'],
        'existing.*.isactive' => ['nullable', 'boolean'],
        'existing.*.isfeatured' => ['nullable', 'boolean'],

        'new' => ['nullable', 'array'],
        'new.categoryname' => ['nullable', 'string', 'max:200'],
        'new.parentcategoryid' => ['nullable', 'integer', Rule::exists('knowledgecategories', 'id')],
        'new.categorytype' => ['required_with:new.categoryname', 'string', Rule::in($categoryTypeOptions)],
        'new.sortorder' => ['nullable', 'integer', 'min:0'],
        'new.nextreviewdate' => ['nullable', 'date'],
        'new.isactive' => ['nullable', 'boolean'],
        'new.isfeatured' => ['nullable', 'boolean'],

        'domainid' => ['required', 'integer', Rule::exists('knowledgedomains', 'id')],
        'categoryid' => ['nullable', 'integer'],
        'search' => ['nullable', 'string'],
        'knowledgeitemtypeid' => ['nullable', 'integer'],
        'itemstatus' => ['nullable', 'string'],
    ], [
        'existing.*.categorytype.required' => 'Please select a category type.',
        'existing.*.categorytype.in' => 'Please select a valid category type.',
        'new.categorytype.required_with' => 'Please select a category type for the new category.',
        'new.categorytype.in' => 'Please select a valid category type for the new category.',
    ]);

    $allCategories = KnowledgeCategory::query()
        ->where('domainid', $validated['domainid'])
        ->get();

    DB::transaction(function () use ($validated, $allCategories) {
        foreach ($validated['existing'] ?? [] as $id => $row) {
            $category = $allCategories->firstWhere('id', (int) $id);

            if (!$category) {
                abort(404, 'Category not found.');
            }

            $parentId = !empty($row['parentcategoryid']) ? (int) $row['parentcategoryid'] : null;

            if ($parentId === (int) $category->id) {
                throw ValidationException::withMessages([
                    "existing.$id.parentcategoryid" => 'A category cannot be its own parent.',
                ]);
            }

            if ($parentId) {
                $parent = $allCategories->firstWhere('id', $parentId);

                if (!$parent || (int) $parent->domainid !== (int) $validated['domainid']) {
                    throw ValidationException::withMessages([
                        "existing.$id.parentcategoryid" => 'Parent category must be in the same domain.',
                    ]);
                }

                $descendantIds = $this->collectDescendantIds($allCategories, (int) $category->id);

                if (in_array($parentId, $descendantIds, true)) {
                    throw ValidationException::withMessages([
                        "existing.$id.parentcategoryid" => 'A category cannot be moved under one of its descendants.',
                    ]);
                }
            }

            $duplicateQuery = KnowledgeCategory::query()
                ->where('domainid', $validated['domainid'])
                ->where('categoryname', trim((string) $row['categoryname']))
                ->where('id', '<>', $category->id);

            if ($parentId) {
                $duplicateQuery->where('parentcategoryid', $parentId);
            } else {
                $duplicateQuery->whereNull('parentcategoryid');
            }

            if ($duplicateQuery->exists()) {
                throw ValidationException::withMessages([
                    "existing.$id.categoryname" => 'Category name must be unique within the selected parent.',
                ]);
            }

            $category->update([
                'categoryname' => trim((string) $row['categoryname']),
                'parentcategoryid' => $parentId,
                'categorytype' => $row['categorytype'],
                'sortorder' => $row['sortorder'] ?? 0,
                'nextreviewdate' => $row['nextreviewdate'] ?? null,
                'isactive' => (bool) ($row['isactive'] ?? false),
                'isfeatured' => (bool) ($row['isfeatured'] ?? false),
            ]);
        }

        $new = $validated['new'] ?? [];
        $newCategoryName = trim((string) ($new['categoryname'] ?? ''));
        $hasNewRow = $newCategoryName !== '';

        if ($hasNewRow) {
            $parentId = !empty($new['parentcategoryid']) ? (int) $new['parentcategoryid'] : null;

            if ($parentId) {
                $parent = $allCategories->firstWhere('id', $parentId);

                if (!$parent || (int) $parent->domainid !== (int) $validated['domainid']) {
                    throw ValidationException::withMessages([
                        'new.parentcategoryid' => 'Parent category must be in the same domain.',
                    ]);
                }
            }

            $duplicateQuery = KnowledgeCategory::query()
                ->where('domainid', $validated['domainid'])
                ->where('categoryname', $newCategoryName);

            if ($parentId) {
                $duplicateQuery->where('parentcategoryid', $parentId);
            } else {
                $duplicateQuery->whereNull('parentcategoryid');
            }

            if ($duplicateQuery->exists()) {
                throw ValidationException::withMessages([
                    'new.categoryname' => 'Category name must be unique within the selected parent.',
                ]);
            }

            $slug = Str::slug($newCategoryName);

            $slugExists = KnowledgeCategory::query()
                ->where('domainid', $validated['domainid'])
                ->where('slug', $slug)
                ->exists();

            if ($slugExists) {
                throw ValidationException::withMessages([
                    'new.categoryname' => 'The generated slug already exists in this domain. Please use a different category name.',
                ]);
            }

            KnowledgeCategory::create([
                'domainid' => $validated['domainid'],
                'categoryname' => $newCategoryName,
                'parentcategoryid' => $parentId,
                'categorytype' => $new['categorytype'],
                'sortorder' => $new['sortorder'] ?? 0,
                'nextreviewdate' => $new['nextreviewdate'] ?? null,
                'isactive' => array_key_exists('isactive', $new) ? (bool) $new['isactive'] : true,
                'isfeatured' => (bool) ($new['isfeatured'] ?? false),
                'slug' => $slug,
            ]);
        }
    });

    return redirect()
        ->route('knowledge-categories.index', [
            'domainid' => (int) $validated['domainid'],
            'categoryid' => (int) $request->input('categoryid'),
            'search' => $request->input('search'),
            'knowledgeitemtypeid' => $request->input('knowledgeitemtypeid'),
            'itemstatus' => $request->input('itemstatus'),

            // bulkSave is the child-category quick-entry workflow:
            // always return to the selected parent and keep its child table open.
            'showchildcategories' => 1,
            'showselectedcategorypanel' => 1,
        ])
        ->with('success', 'Child categories saved successfully.');
}
    public function create(Request $request): View
    {
        $domains = KnowledgeDomain::query()
            ->where('isactive', 1)
            ->orderBy('sortorder')
            ->orderBy('domainname')
            ->get();

        $domainId = $request->integer('domainid') ?: ($domains->first()->id ?? null);
        $parentCategoryId = $request->integer('parentcategoryid') ?: null;

        $categories = collect();
        $categoryTree = collect();
        $parentOptions = collect();

        if ($domainId) {
            $categories = KnowledgeCategory::query()
                ->where('domainid', $domainId)
                ->orderBy('sortorder')
                ->orderBy('categoryname')
                ->get();

            $groupedByParent = $categories->groupBy('parentcategoryid');

            $categoryTree = $this->buildTreeFromGroups($groupedByParent, null);
            $parentOptions = $this->buildParentOptionsFromGroups($groupedByParent);
        }

        return view('knowledge-categories.create', [
            'pageTitle' => 'Add Knowledge Category',
            'domains' => $domains,
            'domainId' => $domainId,
            'parentCategoryId' => $parentCategoryId,
            'parentOptions' => $parentOptions,
            'categoryTypeOptions' => [
                'folder' => 'Folder',
                'theme' => 'Theme',
                'subtheme' => 'Subtheme',
                'topic' => 'Topic',
                'stream' => 'Stream',
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
{
    $categoryTypeOptions = ['folder', 'theme', 'subtheme', 'topic', 'stream'];

    $request->merge([
        'categoryname' => trim((string) $request->input('categoryname', '')),
        'slug' => trim((string) $request->input('slug', '')),
    ]);

    if ($request->input('slug') === '') {
        $request->merge([
            'slug' => Str::slug($request->input('categoryname')),
        ]);
    }

    $categoryNameUnique = Rule::unique('knowledgecategories', 'categoryname')
        ->where(function ($q) use ($request) {
            $q->where('domainid', $request->integer('domainid'));

            if ($request->filled('parentcategoryid')) {
                $q->where('parentcategoryid', $request->integer('parentcategoryid'));
            } else {
                $q->whereNull('parentcategoryid');
            }
        });

    $validated = $request->validate([
        'domainid' => ['required', 'integer', Rule::exists('knowledgedomains', 'id')],
        'parentcategoryid' => ['nullable', 'integer', Rule::exists('knowledgecategories', 'id')],
        'categoryname' => [
            'required',
            'string',
            'max:200',
            $categoryNameUnique,
        ],
        'categorytype' => ['required', 'string', Rule::in($categoryTypeOptions)],
        'slug' => [
            'required',
            'string',
            'max:220',
            Rule::unique('knowledgecategories', 'slug')
                ->where(fn ($q) => $q->where('domainid', $request->integer('domainid'))),
        ],
        'description' => ['nullable', 'string'],
        'sortorder' => ['nullable', 'integer', 'min:0'],
        'nextreviewdate' => ['nullable', 'date'],
        'isfeatured' => ['nullable', 'boolean'],
        'isactive' => ['nullable', 'boolean'],
    ], [
        'categorytype.required' => 'Please select a category type.',
        'categorytype.in' => 'Please select a valid category type.',
        'slug.unique' => 'Slug must be unique within the selected domain.',
    ]);

    if (!empty($validated['parentcategoryid'])) {
        $parent = KnowledgeCategory::findOrFail($validated['parentcategoryid']);

        if ((int) $parent->domainid !== (int) $validated['domainid']) {
            return back()
                ->withInput()
                ->withErrors([
                    'parentcategoryid' => 'Parent category must be in the same domain.',
                ]);
        }
    }

    $category = KnowledgeCategory::create([
        'domainid' => $validated['domainid'],
        'parentcategoryid' => $validated['parentcategoryid'] ?? null,
        'categoryname' => $validated['categoryname'],
        'categorytype' => $validated['categorytype'],
        'slug' => $validated['slug'],
        'description' => $validated['description'] ?? null,
        'sortorder' => $validated['sortorder'] ?? 0,
        'isfeatured' => (bool) ($validated['isfeatured'] ?? false),
        'isactive' => array_key_exists('isactive', $validated)
            ? (bool) $validated['isactive']
            : true,
        'nextreviewdate' => $validated['nextreviewdate'] ?? null,
    ]);

    return redirect()->route('knowledge-categories.index', [
        'domainid' => $validated['domainid'],
        'categoryid' => $category->id,
    ])->with('success', 'Category added successfully.');
}

    public function update(Request $request, KnowledgeCategory $knowledgeCategory): RedirectResponse
{
    $categoryTypeOptions = ['folder', 'theme', 'subtheme', 'topic', 'stream'];

    $request->merge([
        'categoryname' => trim((string) $request->input('categoryname', '')),
        'slug' => trim((string) $request->input('slug', '')),
    ]);

    if ($request->input('slug') === '') {
        $request->merge([
            'slug' => Str::slug($request->input('categoryname')),
        ]);
    }

    $categoryNameUnique = Rule::unique('knowledgecategories', 'categoryname')
        ->ignore($knowledgeCategory->id, 'id')
        ->where(function ($q) use ($request) {
            $q->where('domainid', $request->integer('domainid'));

            if ($request->filled('parentcategoryid')) {
                $q->where('parentcategoryid', $request->integer('parentcategoryid'));
            } else {
                $q->whereNull('parentcategoryid');
            }
        });

    $validated = $request->validate([
        'domainid' => ['required', 'integer', Rule::exists('knowledgedomains', 'id')],
        'parentcategoryid' => ['nullable', 'integer', Rule::exists('knowledgecategories', 'id')],
        'categoryname' => [
            'required',
            'string',
            'max:200',
            $categoryNameUnique,
        ],
        'categorytype' => ['required', 'string', Rule::in($categoryTypeOptions)],
        'slug' => [
            'required',
            'string',
            'max:220',
            Rule::unique('knowledgecategories', 'slug')
                ->where(fn ($q) => $q->where('domainid', $request->integer('domainid')))
                ->ignore($knowledgeCategory->id, 'id'),
        ],
        'description' => ['nullable', 'string'],
        'sortorder' => ['nullable', 'integer', 'min:0'],
        'nextreviewdate' => ['nullable', 'date'],
        'isfeatured' => ['nullable', 'boolean'],
        'isactive' => ['nullable', 'boolean'],
    ], [
        'categorytype.required' => 'Please select a category type.',
        'categorytype.in' => 'Please select a valid category type.',
        'slug.unique' => 'Slug must be unique within the selected domain.',
    ]);

    if ((int) ($validated['parentcategoryid'] ?? 0) === (int) $knowledgeCategory->id) {
        return back()
            ->withErrors([
                'parentcategoryid' => 'A category cannot be its own parent.',
            ])
            ->withInput();
    }

    if (!empty($validated['parentcategoryid'])) {
        $parent = KnowledgeCategory::findOrFail($validated['parentcategoryid']);

        if ((int) $parent->domainid !== (int) $validated['domainid']) {
            return back()
                ->withInput()
                ->withErrors([
                    'parentcategoryid' => 'Parent category must be in the same domain.',
                ]);
        }

        $descendantIds = $this->collectDescendantIds(
            KnowledgeCategory::query()
                ->where('domainid', $validated['domainid'])
                ->get(),
            $knowledgeCategory->id
        );

        if (in_array((int) $validated['parentcategoryid'], $descendantIds, true)) {
            return back()
                ->withInput()
                ->withErrors([
                    'parentcategoryid' => 'A category cannot be moved under one of its descendants.',
                ]);
        }
    }

    $knowledgeCategory->update([
        'domainid' => $validated['domainid'],
        'parentcategoryid' => $validated['parentcategoryid'] ?? null,
        'categoryname' => $validated['categoryname'],
        'categorytype' => $validated['categorytype'],
        'slug' => $validated['slug'],
        'description' => $validated['description'] ?? null,
        'sortorder' => $validated['sortorder'] ?? 0,
        'isfeatured' => (bool) ($validated['isfeatured'] ?? false),
        'isactive' => array_key_exists('isactive', $validated)
            ? (bool) $validated['isactive']
            : true,
        'nextreviewdate' => $validated['nextreviewdate'] ?? null,
    ]);

    return redirect()
        ->route('knowledge-categories.index', [
            'domainid' => $validated['domainid'],
            'categoryid' => $knowledgeCategory->id,
        ])
        ->with('success', 'Knowledge category saved.');
}

    /**
 * Build a nested tree using a grouped-by-parent map.
 *
 * @param \Illuminate\Support\Collection $groupedByParent  // key: parentcategoryid, value: Collection<KnowledgeCategory>
 * @param int|null $parentId
 * @return \Illuminate\Support\Collection
 */
protected function buildTreeFromGroups(Collection $groupedByParent, $parentId = null): Collection
{
    $children = $groupedByParent->get($parentId, collect());

    return $children->map(function ($category) use ($groupedByParent) {
        $category->children = $this->buildTreeFromGroups($groupedByParent, $category->id);
        return $category;
    })->values();
}

/**
 * Build flattened parent options using the grouped tree once.
 */
protected function buildParentOptionsFromGroups(Collection $groupedByParent, array $excludedIds = [], int $depth = 0): Collection
{
    $options = collect();

    $roots = $groupedByParent->get(null, collect());

    $stack = $roots->map(fn ($node) => [$node, $depth])->all();

    while (!empty($stack)) {
        [$node, $level] = array_shift($stack);

        if (!in_array((int) $node->id, $excludedIds, true)) {
            $options->push([
                'id' => $node->id,
                'label' => str_repeat('— ', $level) . $node->categoryname,
            ]);
        }

        $children = $groupedByParent->get($node->id, collect());
        foreach ($children->reverse() as $child) {
            $stack[] = [$child, $level + 1];
        }
    }

    return $options->values();
}

    protected function collectDescendantIds(Collection $categories, int $categoryId): array
    {
        $childIds = $categories
            ->where('parentcategoryid', $categoryId)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        $all = [];

        foreach ($childIds as $childId) {
            $all[] = $childId;
            $all = array_merge($all, $this->collectDescendantIds($categories, $childId));
        }

        return array_values(array_unique($all));
    }
    public function destroy(KnowledgeCategory $knowledgeCategory): RedirectResponse
    {
        $knowledgeCategory->loadCount(['children', 'knowledgeItems']);

        if ($knowledgeCategory->children_count > 0 || $knowledgeCategory->knowledge_items_count > 0) {
            return redirect()
                ->route('knowledge-categories.index', [
                    'domainid' => $knowledgeCategory->domainid,
                    'categoryid' => $knowledgeCategory->id,
                ])
                ->with('error', 'This category cannot be deleted because it has child categories or knowledge items attached.');
        }

        $domainId = $knowledgeCategory->domainid;
        $knowledgeCategory->delete();

        return redirect()
            ->route('knowledge-categories.index', ['domainid' => $domainId])
            ->with('success', 'Knowledge category deleted.');
    }
}