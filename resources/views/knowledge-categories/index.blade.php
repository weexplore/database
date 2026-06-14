<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $pageTitle ?? 'Knowledge Categories' }}
        </h2>
    </x-slot>

    <style>
        @media (min-width: 1280px) {
            .knowledge-categories-layout {
                grid-template-columns: 320px 400px minmax(0, 1fr);
            }

            .knowledge-categories-layout.knowledge-categories-layout--category-hidden {
                grid-template-columns: 320px 0 minmax(0, 1fr);
            }
        }
        .knowledge-categories-middle-panel {
            min-width: 0;
            overflow: hidden;
        }

        @media (min-width: 1280px) {
            .knowledge-categories-layout.knowledge-categories-layout--category-hidden .knowledge-categories-middle-panel {
                border-right-width: 0;
            }

            .knowledge-categories-layout.knowledge-categories-layout--category-hidden .knowledge-categories-middle-panel .knowledge-categories-middle-panel-inner {
                opacity: 0;
                pointer-events: none;
            }
        }
    </style>

    @php
        $showSelectedCategoryPanel = (bool) old(
            'show_selected_category_panel',
            request()->boolean('show_selected_category_panel', false)
        );

        $showChildCategories = (bool) old(
            'show_child_categories',
            request()->boolean('show_child_categories', false)
        ) || collect($errors->keys())->contains(
            fn ($key) => str_starts_with($key, 'existing.') || str_starts_with($key, 'new.')
        );
    @endphp

    <div class="py-6">
        <div class="w-full max-w-none mx-auto px-4 sm:px-6 lg:px-8 xl:px-10 2xl:px-12 space-y-6">

            @if(session('success'))
                <div class="rounded-md bg-green-50 border border-green-200 text-green-800 px-4 py-3">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="rounded-md bg-red-50 border border-red-200 text-red-800 px-4 py-3">
                    {{ session('error') }}
                </div>
            @endif

            @if($errors->any())
                <div class="rounded-md bg-red-50 border border-red-200 text-red-800 px-4 py-3">
                    <div class="font-semibold mb-2">Please fix the following:</div>
                    <ul class="list-disc pl-5 space-y-1 text-sm">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div
                    id="knowledge-categories-layout"
                    class="grid grid-cols-1 knowledge-categories-layout min-h-[720px] {{ $showSelectedCategoryPanel ? '' : 'knowledge-categories-layout--category-hidden' }}"
                >

                    <aside class="bg-slate-950 text-slate-100 border-r border-slate-800">
                        <div class="p-4 border-b border-slate-800 space-y-4">
                            <div>
                                <h3 class="text-base font-semibold">Knowledge</h3>
                                <p class="text-xs text-slate-400">Folder tree navigation</p>
                            </div>

                            <form method="GET" action="{{ route('knowledge-categories.index') }}" class="space-y-3" id="knowledge-domain-form">
                                <div>
                                    <label for="domainid" class="block text-xs font-medium text-slate-300 mb-1">Domain</label>
                                    <select
                                        name="domainid"
                                        id="domainid"
                                        class="w-full rounded-md border-slate-700 bg-slate-900 text-slate-100 text-sm"
                                        onchange="this.form.submit()">
                                        @foreach($domains as $domain)
                                            <option value="{{ $domain->id }}" @selected((int) ($filters['domainid'] ?? 0) === (int) $domain->id)>
                                                {{ $domain->domainname }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <input type="hidden" name="categoryid" value="{{ $filters['categoryid'] ?? '' }}">
                                <input type="hidden" name="search" value="{{ $filters['search'] ?? '' }}">
                                <input type="hidden" name="knowledgeitemtypeid" value="{{ $filters['knowledgeitemtypeid'] ?? '' }}">
                                <input type="hidden" name="itemstatus" value="{{ $filters['itemstatus'] ?? '' }}">
                                <input type="hidden" name="show_selected_category_panel" id="show-selected-category-panel-domain" value="{{ $showSelectedCategoryPanel ? 1 : 0 }}">

                                <div class="space-y-2">
                                    <button
                                        type="button"
                                        id="toggle-selected-category-panel"
                                        class="inline-flex w-full items-center justify-center rounded-md {{ $showSelectedCategoryPanel ? 'bg-gray-200 text-gray-900 hover:bg-gray-300' : 'bg-slate-100 text-slate-900 hover:bg-white border border-slate-300' }} px-3 py-2 text-sm font-medium"
                                        aria-expanded="{{ $showSelectedCategoryPanel ? 'true' : 'false' }}"
                                        aria-controls="selected-category-panel"
                                    >
                                        {{ $showSelectedCategoryPanel ? 'Hide category panel' : 'Show category panel' }}
                                    </button>

                                    <a href="{{ route('knowledge-categories.create', ['domainid' => $filters['domainid']]) }}"
                                    class="inline-flex w-full items-center justify-center rounded-md bg-sky-600 px-3 py-2 text-sm font-medium text-white hover:bg-sky-700">
                                        Add root category
                                    </a>

                                    @if($selectedCategory)
                                        <a href="{{ route('knowledge-categories.create', [
                                                'domainid' => $filters['domainid'],
                                                'parentcategoryid' => $selectedCategory->id,
                                            ]) }}"
                                        class="inline-flex w-full items-center justify-center rounded-md bg-slate-700 px-3 py-2 text-sm font-medium text-white hover:bg-slate-600">
                                            Add child under {{ $selectedCategory->categoryname }}
                                        </a>
                                    @endif
                                </div>

                                <div class="rounded-md border border-slate-800 bg-slate-900/70">
                                    <button
                                        type="button"
                                        id="toggle-sidebar-tools"
                                        class="flex w-full items-center justify-between px-3 py-2 text-xs font-semibold uppercase tracking-[0.08em] text-slate-300 hover:text-white"
                                        aria-expanded="false"
                                        aria-controls="sidebar-tools-panel"
                                    >
                                        <span>Reports and tools</span>
                                        <span id="toggle-sidebar-tools-icon">+</span>
                                    </button>

                                    <div id="sidebar-tools-panel" class="hidden px-3 pb-3 space-y-2">
                                        @if(!empty($filters['domainid']))
                                            <a href="{{ route('reports.knowledge.domains.reference-book', [
                                                    'domainid' => $filters['domainid'],
                                                    'return_to' => url()->full(),
                                                ]) }}"
                                            class="block rounded-md px-2.5 py-2 text-sm font-medium bg-indigo-500/15 text-indigo-200 hover:bg-indigo-500/25 hover:text-indigo-100">
                                                Domain Report
                                            </a>
                                        @endif

                                        @if($selectedCategory)
                                            <a href="{{ route('reports.knowledge.categories.reference-book', [
                                                    'category_ids' => [$selectedCategory->id],
                                                    'return_to' => url()->full(),
                                                ]) }}"
                                            class="block rounded-md px-2.5 py-2 text-sm font-medium bg-blue-500/15 text-blue-200 hover:bg-blue-500/25 hover:text-blue-100">
                                                Category Report
                                            </a>

                                            <a href="{{ route('reports.knowledge.categories.tree-reference-book', [
                                                    'categoryid' => $selectedCategory->id,
                                                    'return_to' => url()->full(),
                                                ]) }}"
                                            class="block rounded-md px-2.5 py-2 text-sm font-medium bg-violet-500/15 text-violet-200 hover:bg-violet-500/25 hover:text-violet-100">
                                                Category Tree Report
                                            </a>
                                        @endif

                                        @if(!empty($filters['domainid']))
                                            <a href="{{ route('knowledge.search', [
                                                    'domainid' => $filters['domainid'],
                                                    'return_to' => url()->full(),
                                                ]) }}"
                                            class="block rounded-md px-2.5 py-2 text-sm font-medium bg-emerald-500/15 text-emerald-200 hover:bg-emerald-500/25 hover:text-emerald-100">
                                                Domain Search
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </form>
                        </div>

                        <div class="p-3 overflow-y-auto h-[calc(100%-220px)]">
                            <div class="text-[11px] uppercase tracking-[0.12em] text-slate-500 px-2 py-2">Folders</div>

                            @if($categoryTree->isNotEmpty())
                                @include('knowledge-categories.partials.tree-nodes', [
                                    'nodes' => $categoryTree,
                                    'selectedId' => $selectedCategory?->id,
                                    'depth' => 0,
                                    'domainId' => $filters['domainid'],
                                    'expandedIds' => $expandedIds,
                                    'expandedIdLookup' => $expandedIdLookup,
                                    'baseFilters' => [
                                        'search' => $filters['search'],
                                        'knowledgeitemtypeid' => $filters['knowledgeitemtypeid'],
                                        'itemstatus' => $filters['itemstatus'],
                                        'show_selected_category_panel' => $showSelectedCategoryPanel ? 1 : 0,
                                    ],
                                ])
                            @else
                                <div class="px-2 py-4 text-sm text-slate-400">
                                    No categories exist yet for this domain.
                                </div>
                            @endif
                        </div>
                    </aside>

                    <section
                        id="selected-category-panel"
                        class="knowledge-categories-middle-panel border-r border-gray-200 bg-gray-50/40"
                    >
                        <div class="knowledge-categories-middle-panel-inner">
                            <div class="p-5">
                                @if($selectedCategory)
                                    <form method="POST"
                                          action="{{ route('knowledge-categories.update', $selectedCategory) }}"
                                          id="knowledge-category-form"
                                          class="space-y-4">
                                        @csrf
                                        @method('PUT')

                                        <input type="hidden" name="domainid" value="{{ old('domainid', $selectedCategory->domainid) }}">

                                        <div class="flex items-start justify-between gap-3">
                                            <div>
                                                <h3 class="text-sm font-semibold text-gray-900">Edit category</h3>
                                                <div class="text-sm text-gray-500 mt-1">
                                                    Domain /
                                                    {{ optional($domains->firstWhere('id', $selectedCategory->domainid))->domainname ?? 'Unknown' }}
                                                    /
                                                    <span class="font-semibold text-gray-800">{{ $selectedCategory->categoryname }}</span>
                                                </div>
                                            </div>

                                            <div>
                                                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium {{ $selectedCategory->isactive ? 'bg-green-100 text-green-800' : 'bg-gray-200 text-gray-700' }}">
                                                    {{ $selectedCategory->isactive ? 'Active' : 'Inactive' }}
                                                </span>
                                            </div>
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Category name</label>
                                            <input type="text"
                                                   name="categoryname"
                                                   value="{{ old('categoryname', $selectedCategory->categoryname) }}"
                                                   class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                                                   maxlength="200"
                                                   required>
                                        </div>

                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Parent category</label>
                                                <select name="parentcategoryid" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                                    <option value="">No parent</option>
                                                    @foreach(($parentOptions ?? []) as $option)
                                                        @if((int) $option['id'] !== (int) $selectedCategory->id)
                                                            <option value="{{ $option['id'] }}"
                                                                @selected((string) old('parentcategoryid', $selectedCategory->parentcategoryid) === (string) $option['id'])>
                                                                {{ $option['label'] }}
                                                            </option>
                                                        @endif
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Category type</label>
                                                <select name="categorytype" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                                    <option value="">Select type</option>
                                                    @foreach($categoryTypeOptions as $value => $label)
                                                        <option value="{{ $value }}" @selected(old('categorytype', $selectedCategory->categorytype) === $value)>
                                                            {{ $label }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Slug</label>
                                                <input type="text"
                                                       name="slug"
                                                       value="{{ old('slug', $selectedCategory->slug) }}"
                                                       class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                                                       maxlength="220">
                                            </div>

                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Sort order</label>
                                                <input type="number"
                                                       name="sortorder"
                                                       value="{{ old('sortorder', $selectedCategory->sortorder) }}"
                                                       class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                                                       min="0">
                                            </div>
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                                            <textarea name="description"
                                                      rows="6"
                                                      class="w-full rounded-md border-gray-300 shadow-sm text-sm">{{ old('description', $selectedCategory->description) }}</textarea>
                                        </div>

                                        <div class="flex flex-wrap gap-4">
                                            <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                                <input type="hidden" name="isfeatured" value="0">
                                                <input type="checkbox"
                                                       name="isfeatured"
                                                       value="1"
                                                       class="rounded border-gray-300 text-blue-600"
                                                       @checked(old('isfeatured', $selectedCategory->isfeatured))>
                                                Featured
                                            </label>

                                            <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                                <input type="hidden" name="isactive" value="0">
                                                <input type="checkbox"
                                                       name="isactive"
                                                       value="1"
                                                       class="rounded border-gray-300 text-blue-600"
                                                       @checked(old('isactive', $selectedCategory->isactive))>
                                                Active
                                            </label>
                                        </div>

                                        <div class="flex flex-wrap gap-3 pt-2">
                                            <button type="submit"
                                                    class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm">
                                                Save category
                                            </button>

                                            <a href="{{ route('knowledge-categories.create', [
                                                    'domainid' => $selectedCategory->domainid,
                                                    'parentcategoryid' => $selectedCategory->id,
                                                ]) }}"
                                               class="px-4 py-2 bg-slate-700 text-white rounded-md hover:bg-slate-600 text-sm">
                                                Add child
                                            </a>
                                        </div>
                                    </form>
                                @else
                                    <div class="rounded-md border border-dashed border-gray-300 bg-white px-4 py-6 text-sm text-gray-500">
                                        Select a category from the tree, or create a new root category.
                                    </div>
                                @endif
                            </div>

                            @if($selectedCategory)
                                <div class="px-5 pb-5">
                                    <div class="rounded-lg border border-gray-200 bg-white overflow-hidden">
                                        <form method="POST"
                                              action="{{ route('knowledge-categories.bulk-save') }}"
                                              id="knowledge-child-categories-form">
                                            @csrf

                                            <input type="hidden" name="domainid" value="{{ $selectedCategory->domainid }}">
                                            <input type="hidden" name="categoryid" value="{{ $selectedCategory->id }}">
                                            <input type="hidden" name="search" value="{{ $filters['search'] ?? '' }}">
                                            <input type="hidden" name="knowledgeitemtypeid" value="{{ $filters['knowledgeitemtypeid'] ?? '' }}">
                                            <input type="hidden" name="itemstatus" value="{{ $filters['itemstatus'] ?? '' }}">
                                            <input type="hidden" name="show_child_categories" value="0">
                                            <input type="hidden" name="show_selected_category_panel" id="show-selected-category-panel-child" value="{{ $showSelectedCategoryPanel ? 1 : 0 }}">

                                            <div class="px-4 py-3 border-b border-gray-200 flex items-center justify-between gap-3">
                                                <div>
                                                    <h3 class="text-sm font-semibold text-gray-900">
                                                        Child categories under {{ $selectedCategory->categoryname }}
                                                    </h3>
                                                    <p class="text-xs text-gray-500">
                                                        Show only when you want to add or quickly edit immediate child folders
                                                    </p>
                                                </div>

                                                <label class="inline-flex items-center gap-2 text-sm text-gray-700 whitespace-nowrap">
                                                    <input type="checkbox"
                                                           id="toggle-child-categories"
                                                           name="show_child_categories"
                                                           value="1"
                                                           class="rounded border-gray-300 text-blue-600 shadow-sm"
                                                           @checked($showChildCategories)>
                                                    Show child categories
                                                </label>
                                            </div>

                                            <div id="child-categories-panel" class="{{ $showChildCategories ? '' : 'hidden' }}">
                                                <div class="px-4 py-3 border-b border-gray-200 flex items-start justify-between gap-3 bg-gray-50">
                                                    <div>
                                                        <p class="text-xs text-gray-500">
                                                            Quick entry and maintenance for immediate child folders
                                                        </p>
                                                    </div>

                                                    <a href="{{ route('knowledge-categories.create', [
                                                            'domainid' => $selectedCategory->domainid,
                                                            'parentcategoryid' => $selectedCategory->id,
                                                        ]) }}"
                                                       class="inline-flex items-center px-3 py-2 bg-slate-700 text-white rounded-md hover:bg-slate-600 text-sm whitespace-nowrap">
                                                        Open full form
                                                    </a>
                                                </div>

                                                <div class="overflow-x-auto">
                                                    <table class="w-full divide-y divide-gray-200">
                                                        <thead class="bg-gray-50">
                                                            <tr>
                                                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Category name</th>
                                                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                                                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Sort order</th>
                                                            </tr>
                                                        </thead>

                                                        <tbody class="bg-white divide-y divide-gray-200">
                                                            @forelse($editableCategories as $category)
                                                                <tr>
                                                                    <td class="px-3 py-2 min-w-[220px]">
                                                                        <input type="text"
                                                                               name="existing[{{ $category->id }}][categoryname]"
                                                                               value="{{ old("existing.{$category->id}.categoryname", $category->categoryname) }}"
                                                                               class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                                                                               required>

                                                                        <input type="hidden"
                                                                               name="existing[{{ $category->id }}][parentcategoryid]"
                                                                               value="{{ old("existing.{$category->id}.parentcategoryid", $category->parentcategoryid) }}">

                                                                        <input type="hidden"
                                                                               name="existing[{{ $category->id }}][nextreviewdate]"
                                                                               value="{{ old("existing.{$category->id}.nextreviewdate", $category->nextreviewdate ? \Illuminate\Support\Carbon::parse($category->nextreviewdate)->format('Y-m-d') : '') }}">

                                                                        <input type="hidden"
                                                                               name="existing[{{ $category->id }}][isfeatured]"
                                                                               value="{{ old("existing.{$category->id}.isfeatured", $category->isfeatured ? 1 : 0) }}">

                                                                        <input type="hidden"
                                                                               name="existing[{{ $category->id }}][isactive]"
                                                                               value="{{ old("existing.{$category->id}.isactive", $category->isactive ? 1 : 0) }}">
                                                                    </td>

                                                                    <td class="px-3 py-2 min-w-[160px]">
                                                                        <select name="existing[{{ $category->id }}][categorytype]"
                                                                                class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                                                            <option value="">Select type</option>
                                                                            @foreach($categoryTypeOptions as $value => $label)
                                                                                <option value="{{ $value }}"
                                                                                    @selected(old("existing.{$category->id}.categorytype", $category->categorytype) === $value)>
                                                                                    {{ $label }}
                                                                                </option>
                                                                            @endforeach
                                                                        </select>
                                                                    </td>

                                                                    <td class="px-3 py-2 w-[8ch]">
                                                                        <input type="number"
                                                                               name="existing[{{ $category->id }}][sortorder]"
                                                                               value="{{ old("existing.{$category->id}.sortorder", $category->sortorder) }}"
                                                                               class="w-[8ch] rounded-md border-gray-300 shadow-sm text-sm"
                                                                               min="0">
                                                                    </td>
                                                                </tr>
                                                            @empty
                                                                <tr>
                                                                    <td colspan="3" class="px-3 py-6 text-center text-sm text-gray-500">
                                                                        No child categories found. Add the first child row below.
                                                                    </td>
                                                                </tr>
                                                            @endforelse

                                                            <tr class="bg-blue-50">
                                                                <td class="px-3 py-2">
                                                                    <input type="text"
                                                                           name="new[categoryname]"
                                                                           value="{{ old('new.categoryname') }}"
                                                                           class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                                                                           placeholder="New child category">

                                                                    <input type="hidden" name="new[parentcategoryid]" value="{{ $selectedCategory->id }}">
                                                                    <input type="hidden" name="new[isfeatured]" value="0">
                                                                    <input type="hidden" name="new[isactive]" value="1">
                                                                </td>

                                                                <td class="px-3 py-2 min-w-[160px]">
                                                                    <select name="new[categorytype]"
                                                                            class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                                                        <option value="">Select type</option>
                                                                        @foreach($categoryTypeOptions as $value => $label)
                                                                            <option value="{{ $value }}"
                                                                                @selected(old('new.categorytype') === $value)>
                                                                                {{ $label }}
                                                                            </option>
                                                                        @endforeach
                                                                    </select>
                                                                </td>

                                                                <td class="px-3 py-2 w-[8ch]">
                                                                    <input type="number"
                                                                           name="new[sortorder]"
                                                                           value="{{ old('new.sortorder', 0) }}"
                                                                           class="w-[8ch] rounded-md border-gray-300 shadow-sm text-sm"
                                                                           min="0">
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>

                                                <div class="px-4 py-3 border-t border-gray-200 flex items-center justify-between gap-3">
                                                    <p class="text-xs text-gray-500">
                                                        Parent is inherited from the selected category above.
                                                    </p>

                                                    <button type="submit"
                                                            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm">
                                                        Save child categories
                                                    </button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                                <div class="mt-1 mx-5 mb-5 bg-white overflow-hidden shadow-sm sm:rounded-lg border border-red-200">
                                    <div class="px-5 py-4 border-b border-red-200">
                                        <h3 class="text-sm font-semibold text-red-800">Delete Category</h3>
                                    </div>

                                    <div class="p-5 space-y-4">
                                        <p class="text-sm text-gray-600">
                                            Delete this category only if it is no longer needed and has no child categories or knowledge items attached.
                                        </p>

                                        <form method="POST"
                                              action="{{ route('knowledge-categories.destroy', $selectedCategory) }}"
                                              onsubmit="return confirm('Delete category {{ addslashes($selectedCategory->categoryname) }}? This cannot be undone.');">
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit"
                                                    class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 text-sm">
                                                Delete Category
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </section>

                    <section class="min-w-0">
                        <div class="p-5 border-b border-gray-200 flex items-start justify-between gap-3">
                            <div>
                                <h3 class="text-base font-semibold text-gray-900">
                                    Items in {{ $selectedCategory?->categoryname ?? 'selected category' }}
                                </h3>
                                <p class="text-sm text-gray-500">Register view filtered by the selected folder</p>
                            </div>
                        </div>

                        <div class="p-5 space-y-5">
                            <form method="GET"
                                  action="{{ route('knowledge-categories.index') }}"
                                  class="grid grid-cols-1 md:grid-cols-4 gap-4"
                                  id="knowledge-category-items-filter-form">
                                <input type="hidden" name="domainid" value="{{ $filters['domainid'] }}">
                                <input type="hidden" name="categoryid" value="{{ $filters['categoryid'] }}">
                                <input type="hidden" name="show_selected_category_panel" id="show-selected-category-panel-filter" value="{{ $showSelectedCategoryPanel ? 1 : 0 }}">

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                                    <input type="text"
                                           name="search"
                                           value="{{ $filters['search'] ?? '' }}"
                                           class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                                           placeholder="Name, summary, notes">
                                </div>

                                <div>
                                    <label for="knowledgeitemtypeid" class="block text-sm font-medium text-gray-700 mb-1">
                                        Type
                                    </label>
                                    <select name="knowledgeitemtypeid"
                                            id="knowledgeitemtypeid"
                                            class="w-full rounded-md border-gray-300 shadow-sm">
                                        <option value="">All item types</option>
                                        @foreach($itemTypes as $itemType)
                                            <option value="{{ $itemType->id }}"
                                                @selected((string) ($filters['knowledgeitemtypeid'] ?? '') === (string) $itemType->id)>
                                                {{ $itemType->typename }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                    <select name="itemstatus" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                        <option value="">All statuses</option>
                                        @foreach($itemStatuses as $itemStatus)
                                            <option value="{{ $itemStatus }}" @selected(($filters['itemstatus'] ?? '') === $itemStatus)>
                                                {{ $itemStatus }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="flex items-end gap-2">
                                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm">
                                        Apply
                                    </button>

                                    <a href="{{ route('knowledge-categories.index', [
                                            'domainid' => $filters['domainid'],
                                            'categoryid' => $filters['categoryid'],
                                            'show_selected_category_panel' => $showSelectedCategoryPanel ? 1 : 0,
                                        ]) }}"
                                       class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md text-sm">
                                        Reset
                                    </a>
                                </div>
                            </form>

                            @if($selectedCategory)
                                <form method="POST"
                                      action="{{ route('knowledge.items.bulk-save') }}"
                                      id="knowledge-category-items-form">
                                    @csrf

                                    <input type="hidden" name="domainid" value="{{ $filters['domainid'] ?? '' }}">
                                    <input type="hidden" name="categoryid" value="{{ $filters['categoryid'] ?? '' }}">
                                    <input type="hidden" name="search" value="{{ $filters['search'] ?? '' }}">
                                    <input type="hidden" name="itemtype" value="{{ $filters['knowledgeitemtypeid'] ?? '' }}">
                                    <input type="hidden" name="itemstatus" value="{{ $filters['itemstatus'] ?? '' }}">
                                    <input type="hidden" name="active" value="{{ $filters['active'] ?? '' }}">
                                    <input type="hidden" name="page" value="{{ request('page', 1) }}">
                                    <input type="hidden" name="return_to" value="{{ url()->full() }}">
                                    <input type="hidden" name="show_selected_category_panel" id="show-selected-category-panel-items" value="{{ $showSelectedCategoryPanel ? 1 : 0 }}">

                                    <div class="overflow-x-auto border border-gray-200 rounded-lg">
                                        <table class="w-full divide-y divide-gray-200">
                                            <thead class="bg-gray-50">
                                                <tr>
                                                    <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase w-12"></th>
                                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Item</th>
                                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Summary</th>
                                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Sort</th>
                                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Start Date</th>
                                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">End Date</th>
                                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Next Review</th>
                                                    <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase">Featured</th>
                                                    <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase">Active</th>
                                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                                </tr>
                                            </thead>

                                            <tbody class="bg-white divide-y divide-gray-200">
                                                @forelse($items as $item)
                                                    <tr class="knowledge-item-row" data-item-id="{{ $item->id }}">
                                                        <td class="px-3 py-2 text-center align-top">
                                                            <button type="button"
                                                                    class="knowledge-item-drag-handle inline-flex items-center justify-center text-gray-400 hover:text-gray-600 cursor-move"
                                                                    title="Drag to reorder"
                                                                    aria-label="Drag to reorder">
                                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                                    class="w-5 h-5"
                                                                    viewBox="0 0 20 20"
                                                                    fill="currentColor"
                                                                    aria-hidden="true">
                                                                    <path d="M3 5h14a1 1 0 110 2H3a1 1 0 110-2Zm0 4h14a1 1 0 110 2H3a1 1 0 110-2Zm0 4h14a1 1 0 110 2H3a1 1 0 110-2Z" />
                                                                </svg>
                                                            </button>
                                                        </td>

<td class="px-3 py-2 min-w-[220px]">
    <a href="{{ route('knowledge.items.edit', $item) }}"
       class="block w-full min-w-[180px] rounded-md border border-gray-200 bg-gray-100 px-3 py-2 text-sm text-gray-700 hover:bg-gray-200 hover:text-gray-900"
       title="Open {{ $item->itemname }}">
        <span class="truncate">{{ $item->itemname }}</span>
    </a>
</td>

                                                        <td class="px-3 py-2 min-w-[150px]">
                                                            <select name="existing[{{ $item->id }}][itemtype]"
                                                                    class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                                                <option value="">Select type</option>
                                                                @foreach($itemTypes as $itemType)
                                                                    <option value="{{ $itemType->id }}"
                                                                        @selected((string) old("existing.{$item->id}.itemtype", $item->itemtype) === (string) $itemType->id)>
                                                                        {{ $itemType->typename }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                            <input type="hidden"
                                                                   name="existing[{{ $item->id }}][primarycategoryid]"
                                                                   value="{{ $selectedCategory->id }}">
                                                        </td>

                                                        <td class="px-3 py-2 min-w-[160px]">
                                                            <select name="existing[{{ $item->id }}][itemstatus]"
                                                                    class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                                                <option value="">Select status</option>
                                                                @foreach($itemStatusOptions as $value => $label)
                                                                    <option value="{{ $value }}"
                                                                        @selected(old("existing.{$item->id}.itemstatus", $item->itemstatus ?? 'active') === $value)>
                                                                        {{ $label }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </td>

                                                        <td class="px-3 py-2 min-w-[260px]">
                                                            <textarea name="existing[{{ $item->id }}][summary]"
                                                                      rows="2"
                                                                      class="w-full rounded-md border-gray-300 shadow-sm text-sm">{{ old("existing.{$item->id}.summary", $item->summary) }}</textarea>
                                                        </td>

                                                        <td class="px-3 py-2 w-[90px]">
                                                            <input type="number"
                                                                   name="existing[{{ $item->id }}][sortorder]"
                                                                   value="{{ old("existing.{$item->id}.sortorder", $item->sortorder) }}"
                                                                   class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                                                                   min="0">
                                                        </td>

                                                        <td class="px-3 py-2 min-w-[150px]">
                                                            <input type="date"
                                                                name="existing[{{ $item->id }}][startdate]"
                                                                value="{{ old("existing.{$item->id}.startdate", optional($item->startdate)->format('Y-m-d')) }}"
                                                                class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                                        </td>

                                                        <td class="px-3 py-2 min-w-[150px]">
                                                            <input type="date"
                                                                name="existing[{{ $item->id }}][enddate]"
                                                                value="{{ old("existing.{$item->id}.enddate", optional($item->enddate)->format('Y-m-d')) }}"
                                                                class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                                        </td>

                                                        <td class="px-3 py-2 min-w-[150px]">
                                                            <input type="date"
                                                                   name="existing[{{ $item->id }}][nextreviewdate]"
                                                                   value="{{ old("existing.{$item->id}.nextreviewdate", optional($item->nextreviewdate)->format('Y-m-d')) }}"
                                                                   class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                                        </td>

                                                        <td class="px-3 py-2 text-center">
                                                            <input type="hidden" name="existing[{{ $item->id }}][isfeatured]" value="0">
                                                            <input type="checkbox"
                                                                   name="existing[{{ $item->id }}][isfeatured]"
                                                                   value="1"
                                                                   class="rounded border-gray-300 text-blue-600 shadow-sm"
                                                                   @checked(old("existing.{$item->id}.isfeatured", $item->isfeatured))>
                                                        </td>

                                                        <td class="px-3 py-2 text-center">
                                                            <input type="hidden" name="existing[{{ $item->id }}][isactive]" value="0">
                                                            <input type="checkbox"
                                                                   name="existing[{{ $item->id }}][isactive]"
                                                                   value="1"
                                                                   class="rounded border-gray-300 text-blue-600 shadow-sm"
                                                                   @checked(old("existing.{$item->id}.isactive", $item->isactive))>
                                                        </td>

                                                        <td class="px-3 py-2 whitespace-nowrap">
                                                            <a href="{{ route('knowledge.items.edit', $item) }}"
                                                               class="inline-flex items-center px-3 py-1.5 bg-slate-700 text-white rounded hover:bg-slate-600 text-sm">
                                                                Open
                                                            </a>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="12" class="px-3 py-6 text-center text-sm text-gray-500">
                                                            No knowledge items found for this category and filter set.
                                                        </td>
                                                    </tr>
                                                @endforelse

                                                <tr class="bg-blue-50">
                                                    <td class="px-3 py-2 text-center align-top text-gray-400">
                                                        <span class="inline-flex w-5 h-5"></span>
                                                    </td>
                                                    <td class="px-3 py-2">
                                                        <input
                                                            type="text"
                                                            name="new[itemname]"
                                                            value="{{ old('new.itemname') }}"
                                                            class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                                                            placeholder="New knowledge item"
                                                        >
                                                    </td>
                                                    <td class="px-3 py-2 min-w-[150px]">
                                                        <select name="new[itemtype]" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                                            <option value="">Select type</option>
                                                            @foreach ($itemTypes as $itemType)
                                                                <option value="{{ $itemType->id }}" @selected((string) old('new.itemtype') === (string) $itemType->id)>
                                                                    {{ $itemType->typename }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        <input type="hidden" name="new[primarycategoryid]" value="{{ $selectedCategory->id }}">
                                                    </td>
                                                    <td class="px-3 py-2 min-w-[160px]">
                                                        <select name="new[itemstatus]" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                                            <option value="">Select status</option>
                                                            @foreach ($itemStatusOptions as $value => $label)
                                                                <option value="{{ $value }}" @selected(old('new.itemstatus', 'active') === $value)>
                                                                    {{ $label }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                    <td class="px-3 py-2">
                                                        <textarea
                                                            name="new[summary]"
                                                            rows="2"
                                                            class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                                                            placeholder="Short summary"
                                                        >{{ old('new.summary') }}</textarea>
                                                    </td>
                                                    <td class="px-3 py-2 w-[160px]">
                                                        <input
                                                            type="number"
                                                            name="new[sortorder]"
                                                            value="{{ old('new.sortorder', 0) }}"
                                                            class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                                                            min="0"
                                                        >
                                                    </td>
                                                    <td class="px-3 py-2">
                                                        <input
                                                            type="date"
                                                            name="new[startdate]"
                                                            value="{{ old('new.startdate') }}"
                                                            class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                                                        >
                                                    </td>
                                                    <td class="px-3 py-2">
                                                        <input
                                                            type="date"
                                                            name="new[enddate]"
                                                            value="{{ old('new.enddate') }}"
                                                            class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                                                        >
                                                    </td>
                                                    <td class="px-3 py-2">
                                                        <input
                                                            type="date"
                                                            name="new[nextreviewdate]"
                                                            value="{{ old('new.nextreviewdate') }}"
                                                            class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                                                        >
                                                    </td>
                                                    <td class="px-3 py-2 text-center">
                                                        <input type="hidden" name="new[isfeatured]" value="0">
                                                        <input
                                                            type="checkbox"
                                                            name="new[isfeatured]"
                                                            value="1"
                                                            class="rounded border-gray-300 text-blue-600 shadow-sm"
                                                            @checked(old('new.isfeatured', false))
                                                        >
                                                    </td>
                                                    <td class="px-3 py-2 text-center">
                                                        <input type="hidden" name="new[isactive]" value="0">
                                                        <input
                                                            type="checkbox"
                                                            name="new[isactive]"
                                                            value="1"
                                                            class="rounded border-gray-300 text-blue-600 shadow-sm"
                                                            @checked(old('new.isactive', true))
                                                        >
                                                    </td>
                                                    <td class="px-3 py-2 text-sm text-gray-400 whitespace-nowrap">
                                                        New row
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="pt-4 flex items-center justify-between">
                                        <p class="text-sm text-gray-500">
                                            Quick update key fields here, then open full edit for richer notes, relationships, and review content.
                                        </p>

                                        <div class="flex items-center gap-2">
                                            <a href="{{ route('knowledge-categories.index', array_filter([
                                                    'domainid' => $filters['domainid'] ?? request('domainid'),
                                                    'categoryid' => $filters['categoryid'] ?? request('categoryid'),
                                                    'search' => $filters['search'] ?? request('search'),
                                                    'knowledgeitemtypeid' => $filters['knowledgeitemtypeid'] ?? request('knowledgeitemtypeid'),
                                                    'itemstatus' => $filters['itemstatus'] ?? request('itemstatus'),
                                                    'show_selected_category_panel' => $showSelectedCategoryPanel ? 1 : 0,
                                                ], fn ($value) => $value !== null && $value !== '')) }}"
                                               class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md text-sm font-semibold text-gray-700 hover:bg-gray-50">
                                                Cancel
                                            </a>

                                            <button type="submit"
                                                    class="inline-flex items-center px-5 py-2 bg-green-600 border border-transparent rounded-md text-sm font-semibold text-white hover:bg-green-700">
                                                Save Changes
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            @else
                                <div class="rounded-md border border-dashed border-gray-300 bg-white px-4 py-6 text-sm text-gray-500">
                                    Select a category from the tree to edit knowledge items inline.
                                </div>
                            @endif
                        </div>
                    </section>

                </div>
            </div>
        </div>
    </div>

    @include('partials.admin.dirty-form-script', [
        'formId' => 'knowledge-category-items-form',
        'filterFormId' => 'knowledge-category-items-filter-form',
        'dirtyMessage' => 'You have unsaved changes in the category items table. Continue and lose those changes?',
    ])

    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.6/Sortable.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const toggleSelectedButton = document.getElementById('toggle-selected-category-panel');
            const selectedPanel = document.getElementById('selected-category-panel');
            const layout = document.getElementById('knowledge-categories-layout');

            const selectedDomainInput = document.getElementById('show-selected-category-panel-domain');
            const selectedFilterInput = document.getElementById('show-selected-category-panel-filter');
            const selectedItemsInput = document.getElementById('show-selected-category-panel-items');
            const selectedChildInput = document.getElementById('show-selected-category-panel-child');

            function setSelectedPanelState(isOpen) {
                if (layout) {
                    layout.classList.toggle('knowledge-categories-layout--category-hidden', !isOpen);
                }

                if (toggleSelectedButton) {
                    toggleSelectedButton.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
                    toggleSelectedButton.textContent = isOpen ? 'Hide category panel' : 'Show category panel';

                    toggleSelectedButton.classList.remove(
                        'bg-gray-200',
                        'text-gray-900',
                        'hover:bg-gray-300',
                        'bg-amber-500',
                        'text-slate-950',
                        'hover:bg-amber-400'
                    );

                    if (isOpen) {
                        toggleSelectedButton.classList.add('bg-gray-200', 'text-gray-900', 'hover:bg-gray-300');
                    } else {
                        toggleSelectedButton.classList.add('bg-amber-500', 'text-slate-950', 'hover:bg-amber-400');
                    }
                }

                if (selectedDomainInput) selectedDomainInput.value = isOpen ? '1' : '0';
                if (selectedFilterInput) selectedFilterInput.value = isOpen ? '1' : '0';
                if (selectedItemsInput) selectedItemsInput.value = isOpen ? '1' : '0';
                if (selectedChildInput) selectedChildInput.value = isOpen ? '1' : '0';
            }

            if (toggleSelectedButton && layout) {
                toggleSelectedButton.addEventListener('click', function () {
                    const isOpen = toggleSelectedButton.getAttribute('aria-expanded') === 'true';
                    setSelectedPanelState(!isOpen);
                });
            }

            const sidebarToolsToggle = document.getElementById('toggle-sidebar-tools');
            const sidebarToolsPanel = document.getElementById('sidebar-tools-panel');
            const sidebarToolsIcon = document.getElementById('toggle-sidebar-tools-icon');

            if (sidebarToolsToggle && sidebarToolsPanel) {
                sidebarToolsToggle.addEventListener('click', function () {
                    const isOpen = sidebarToolsToggle.getAttribute('aria-expanded') === 'true';
                    sidebarToolsToggle.setAttribute('aria-expanded', isOpen ? 'false' : 'true');
                    sidebarToolsPanel.classList.toggle('hidden', isOpen);
                    if (sidebarToolsIcon) {
                        sidebarToolsIcon.textContent = isOpen ? '+' : '−';
                    }
                });
            }

            const toggleChild = document.getElementById('toggle-child-categories');
            const childPanel = document.getElementById('child-categories-panel');

            if (toggleChild && childPanel) {
                toggleChild.addEventListener('change', function () {
                    childPanel.classList.toggle('hidden', !this.checked);
                });
            }

            const itemsForm = document.getElementById('knowledge-category-items-form');
            if (!itemsForm) return;

            const tbody = itemsForm.querySelector('table tbody');
            if (!tbody || typeof Sortable === 'undefined') return;

            let isSubmittingReorder = false;

            function syncKnowledgeItemSortOrder() {
                const rows = Array.from(tbody.querySelectorAll('tr.knowledge-item-row'));

                rows.forEach((row, index) => {
                    const itemId = row.dataset.itemId;
                    if (!itemId) return;

                    const sortInput = row.querySelector(`input[name="existing[${itemId}][sortorder]"]`);
                    if (sortInput) {
                        sortInput.value = index + 1;
                    }
                });
            }

            Sortable.create(tbody, {
                animation: 150,
                handle: '.knowledge-item-drag-handle',
                draggable: 'tr.knowledge-item-row',
                ghostClass: 'bg-blue-50',
                onEnd: function () {
                    if (isSubmittingReorder) return;

                    syncKnowledgeItemSortOrder();
                    isSubmittingReorder = true;
                    itemsForm.submit();
                }
            });

            syncKnowledgeItemSortOrder();
        });
    </script>
</x-app-layout>