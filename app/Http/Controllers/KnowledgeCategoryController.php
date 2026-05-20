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
    $items = collect();
    $globalParentOptions = collect();
    $rowParentOptions = [];

    if ($filters['domainid']) {
        $allCategories = KnowledgeCategory::query()
            ->where('domainid', $filters['domainid'])
            ->orderBy('sortorder')
            ->orderBy('categoryname')
            ->get();

        $categoryTree = $this->buildTree($allCategories);

        $selectedCategory = $filters['categoryid']
            ? $allCategories->firstWhere('id', $filters['categoryid'])
            : $allCategories->firstWhere('parentcategoryid', null) ?? $allCategories->first();

        if ($selectedCategory) {
            $filters['categoryid'] = (int) $selectedCategory->id;

            $itemQuery = KnowledgeItem::query()
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

            $items = $itemQuery->get();

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

        $globalParentOptions = $this->buildParentOptions($categoryTree);

        foreach ($editableCategories as $category) {
            $excludedIds = $this->collectDescendantIds($allCategories, (int) $category->id);
            $excludedIds[] = (int) $category->id;

            $rowParentOptions[$category->id] = $this->buildParentOptions($categoryTree, $excludedIds);
        }
    }

    $expandedIds = collect($request->input('expanded', []))
        ->map(fn ($id) => (int) $id)
        ->filter()
        ->values()
        ->all();

    if ($selectedCategory) {
        $ancestorIds = [];
        $current = $selectedCategory;

        while ($current && $current->parentcategoryid) {
            $ancestorIds[] = (int) $current->parentcategoryid;
            $current = $allCategories->firstWhere('id', $current->parentcategoryid);
        }

        $expandedIds = array_values(array_unique(array_merge($expandedIds, $ancestorIds)));
    }

    $itemTypes = KnowledgeItemType::query()
        ->where('isactive', 1)
        ->orderBy('sortorder')
        ->orderBy('typename')
        ->get();
        $parentOptions = collect();

    if (!empty($filters['domainid'])) {
        $parentOptions = KnowledgeCategory::query()
            ->where('domainid', $filters['domainid'])
            ->orderBy('categoryname')
            ->get()
            ->map(function ($category) {
                return [
                    'id' => $category->id,
                    'label' => $category->categoryname,
                ];
            })
            ->values();
    }

        

    $itemStatuses = KnowledgeItem::query()
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
        'rowParentOptions' => $rowParentOptions,
        'categoryTypeOptions' => [
            'folder' => 'Folder',
            'theme' => 'Theme',
            'subtheme' => 'Subtheme',
            'topic' => 'Topic',
            'stream' => 'Stream',
        ],
        'expandedIds' => $expandedIds,
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
        'existing.*.categorytype' => ['nullable', 'string', Rule::in($categoryTypeOptions)],
        'existing.*.sortorder' => ['nullable', 'integer', 'min:0'],
        'existing.*.nextreviewdate' => ['nullable', 'date'],
        'existing.*.isactive' => ['nullable', 'boolean'],
        'existing.*.isfeatured' => ['nullable', 'boolean'],

        'new' => ['nullable', 'array'],
        'new.categoryname' => ['nullable', 'string', 'max:200'],
        'new.parentcategoryid' => ['nullable', 'integer', Rule::exists('knowledgecategories', 'id')],
        'new.categorytype' => ['nullable', 'string', Rule::in($categoryTypeOptions)],
        'new.sortorder' => ['nullable', 'integer', 'min:0'],
        'new.nextreviewdate' => ['nullable', 'date'],
        'new.isactive' => ['nullable', 'boolean'],
        'new.isfeatured' => ['nullable', 'boolean'],

        'domainid' => ['required', 'integer', Rule::exists('knowledgedomains', 'id')],
        'categoryid' => ['nullable', 'integer'],
        'search' => ['nullable', 'string'],
        'knowledgeitemtypeid' => ['nullable', 'integer'],
        'itemstatus' => ['nullable', 'string'],
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
                'categorytype' => $row['categorytype'] ?? null,
                'sortorder' => $row['sortorder'] ?? 0,
                'nextreviewdate' => $row['nextreviewdate'] ?? null,
                'isactive' => (bool) ($row['isactive'] ?? false),
                'isfeatured' => (bool) ($row['isfeatured'] ?? false),
            ]);
        }

        $new = $validated['new'] ?? [];
        $hasNewRow = trim((string) ($new['categoryname'] ?? '')) !== '';

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
                ->where('categoryname', trim((string) $new['categoryname']));

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

            KnowledgeCategory::create([
                'domainid' => $validated['domainid'],
                'categoryname' => trim((string) $new['categoryname']),
                'parentcategoryid' => $parentId,
                'categorytype' => $new['categorytype'] ?? null,
                'sortorder' => $new['sortorder'] ?? 0,
                'nextreviewdate' => $new['nextreviewdate'] ?? null,
                'isactive' => array_key_exists('isactive', $new) ? (bool) $new['isactive'] : true,
                'isfeatured' => (bool) ($new['isfeatured'] ?? false),
                'slug' => Str::slug(trim((string) $new['categoryname'])),
            ]);
        }
    });

    return redirect()->route('knowledge-categories.index', [
        'domainid' => $request->input('domainid'),
        'categoryid' => $request->input('categoryid'),
        'search' => $request->input('search'),
        'knowledgeitemtypeid' => $request->input('knowledgeitemtypeid'),
        'itemstatus' => $request->input('itemstatus'),
        'show_child_categories' => $request->boolean('show_child_categories') ? 1 : 0,
    ])->with('success', 'Knowledge categories saved successfully.');
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

            $categoryTree = $this->buildTree($categories);
            $parentOptions = $this->buildParentOptions($categoryTree);
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
            'categorytype' => ['nullable', 'string', 'max:50'],
            'slug' => [
                'nullable',
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
        ]);

        if (!empty($validated['parentcategoryid'])) {
            $parent = KnowledgeCategory::findOrFail($validated['parentcategoryid']);

            if ((int) $parent->domainid !== (int) $validated['domainid']) {
                return back()
                    ->withInput()
                    ->withErrors(['parentcategoryid' => 'Parent category must be in the same domain.']);
            }
        }

        $slug = trim((string) ($validated['slug'] ?? ''));
        if ($slug === '') {
            $slug = Str::slug($validated['categoryname']);
        }

        $category = KnowledgeCategory::create([
            'domainid' => $validated['domainid'],
            'parentcategoryid' => $validated['parentcategoryid'] ?? null,
            'categoryname' => trim($validated['categoryname']),
            'categorytype' => $validated['categorytype'] ?? null,
            'slug' => $slug !== '' ? $slug : null,
            'description' => $validated['description'] ?? null,
            'sortorder' => $validated['sortorder'] ?? 0,
            'isfeatured' => (bool) ($validated['isfeatured'] ?? false),
            'isactive' => (bool) ($validated['isactive'] ?? true),
            'nextreviewdate' => $validated['nextreviewdate'] ?? null,
        ]);

        return redirect()->route('knowledge-categories.index', [
            'domainid' => $validated['domainid'],
            'categoryid' => $category->id,
        ])->with('success', 'Category added successfully.');
    }

    public function update(Request $request, KnowledgeCategory $knowledgeCategory): RedirectResponse
{
    $request->merge([
        'slug' => trim((string) $request->input('slug', '')),
        'categoryname' => trim((string) $request->input('categoryname', '')),
    ]);

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
        'categorytype' => ['nullable', 'string', 'max:50'],
        'slug' => [
            'nullable',
            'string',
            'max:220',
            Rule::unique('knowledgecategories', 'slug')
                ->where(fn ($q) => $q->where('domainid', $request->integer('domainid')))
                ->ignore($knowledgeCategory->id ?? null, 'id'),
        ],
        'description' => ['nullable', 'string'],
        'sortorder' => ['nullable', 'integer', 'min:0'],
        'isfeatured' => ['nullable', 'boolean'],
        'isactive' => ['nullable', 'boolean'],
    ]);

    if ((int) ($validated['parentcategoryid'] ?? 0) === (int) $knowledgeCategory->id) {
        return back()->withErrors([
            'parentcategoryid' => 'A category cannot be its own parent.',
        ])->withInput();
    }

    if (!empty($validated['parentcategoryid'])) {
        $parent = KnowledgeCategory::findOrFail($validated['parentcategoryid']);

        if ((int) $parent->domainid !== (int) $validated['domainid']) {
            return back()
                ->withInput()
                ->withErrors(['parentcategoryid' => 'Parent category must be in the same domain.']);
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
                ->withErrors(['parentcategoryid' => 'A category cannot be moved under one of its descendants.']);
        }
    }

    $slug = $validated['slug'];
    if ($slug === '') {
        $slug = Str::slug($validated['categoryname']);
    }

    $knowledgeCategory->update([
        'domainid' => $validated['domainid'],
        'parentcategoryid' => $validated['parentcategoryid'] ?? null,
        'categoryname' => $validated['categoryname'],
        'categorytype' => $validated['categorytype'] ?? null,
        'slug' => $slug !== '' ? $slug : null,
        'description' => $validated['description'] ?? null,
        'sortorder' => $validated['sortorder'] ?? 0,
        'isfeatured' => (bool) ($validated['isfeatured'] ?? false),
        'isactive' => (bool) ($validated['isactive'] ?? true),
    ]);

    return redirect()
        ->route('knowledge-categories.index', [
            'domainid' => $validated['domainid'],
            'categoryid' => $knowledgeCategory->id,
        ])
        ->with('success', 'Knowledge category saved.');
}

    protected function buildTree(Collection $categories, $parentId = null): Collection
    {
        return $categories
            ->where('parentcategoryid', $parentId)
            ->map(function ($category) use ($categories) {
                $category->children = $this->buildTree($categories, $category->id);
                return $category;
            })
            ->values();
    }

    protected function buildParentOptions(Collection $nodes, array $excludedIds = [], int $depth = 0): Collection
    {
        $options = collect();

        foreach ($nodes as $node) {
            if (!in_array((int) $node->id, $excludedIds, true)) {
                $options->push([
                    'id' => $node->id,
                    'label' => str_repeat('— ', $depth) . $node->categoryname,
                ]);
            }

            if ($node->children->isNotEmpty()) {
                $options = $options->merge(
                    $this->buildParentOptions($node->children, $excludedIds, $depth + 1)
                );
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