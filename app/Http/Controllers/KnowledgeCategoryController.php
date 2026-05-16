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
    $items = collect();
    $parentOptions = collect();

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

            $excludedIds = $this->collectDescendantIds($allCategories, $selectedCategory->id);
            $excludedIds[] = (int) $selectedCategory->id;

            $parentOptions = $this->buildParentOptions(
                $categoryTree,
                $excludedIds
            );

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
        } else {
            $parentOptions = $this->buildParentOptions($categoryTree);
        }
    }

    $itemTypes = KnowledgeItemType::query()
        ->where('isactive', 1)
        ->orderBy('sortorder')
        ->orderBy('typename')
        ->get();

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
        'items' => $items,
        'itemTypes' => $itemTypes,
        'itemStatuses' => $itemStatuses,
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
            'slug' => ['nullable', 'string', 'max:220', 'unique:knowledgecategories,slug'],
            'description' => ['nullable', 'string'],
            'sortorder' => ['nullable', 'integer', 'min:0'],
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
        ]);

        return redirect()->route('knowledge-categories.index', [
            'domainid' => $validated['domainid'],
            'categoryid' => $category->id,
        ])->with('success', 'Category added successfully.');
    }

    public function update(Request $request, KnowledgeCategory $knowledgeCategory): RedirectResponse
    {
        $categoryNameUnique = Rule::unique('knowledgecategories', 'categoryname')
            ->ignore($knowledgeCategory->id)
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
                Rule::unique('knowledgecategories', 'slug')->ignore($knowledgeCategory->id),
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

        $slug = trim((string) ($validated['slug'] ?? ''));
        if ($slug === '') {
            $slug = Str::slug($validated['categoryname']);
        }

        $knowledgeCategory->update([
            'domainid' => $validated['domainid'],
            'parentcategoryid' => $validated['parentcategoryid'] ?? null,
            'categoryname' => trim($validated['categoryname']),
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