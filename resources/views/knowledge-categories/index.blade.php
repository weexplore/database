<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $pageTitle ?? 'Knowledge Categories' }}
        </h2>
    </x-slot>

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
                <div class="grid grid-cols-1 xl:grid-cols-[320px_minmax(380px,460px)_minmax(0,1fr)] min-h-[720px]">

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
                                <input type="hidden" name="itemtype" value="{{ $filters['itemtype'] ?? '' }}">
                                <input type="hidden" name="itemstatus" value="{{ $filters['itemstatus'] ?? '' }}">

                                <div class="space-y-2">
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
                                    @if(!empty($filters['domainid']))
                                        <a href="{{ route('reports.knowledge.domains.reference-book', [
                                                'domainid' => $filters['domainid'],
                                                'return_to' => url()->full(),
                                            ]) }}"
                                        class="inline-flex w-full items-center justify-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                                            Domain Report
                                        </a>
                                    @endif
                                    @if($selectedCategory)
                                        <a href="{{ route('reports.knowledge.categories.reference-book', [
                                                'category_ids' => [$selectedCategory->id],
                                                'return_to' => url()->full(),
                                            ]) }}"
                                        class="inline-flex w-full items-center justify-center rounded-md bg-blue-600 px-3 py-2 text-sm font-medium text-white hover:bg-blue-700">
                                            Category Report
                                        </a>
                                            <a href="{{ route('reports.knowledge.categories.tree-reference-book', [
                                                'categoryid' => $selectedCategory->id,
                                                'return_to' => url()->full(),
                                            ]) }}"
                                        class="inline-flex w-full items-center justify-center rounded-md bg-violet-600 px-3 py-2 text-sm font-medium text-white hover:bg-violet-700">
                                            Category Tree Report
                                        </a>
                                    @endif
                                </div>
                            </form>
                        </div>

                        <div class="p-3 overflow-y-auto h-[calc(100%-190px)]">
                            <div class="text-[11px] uppercase tracking-[0.12em] text-slate-500 px-2 py-2">Folders</div>

                            @if($categoryTree->isNotEmpty())
                                @include('knowledge-categories.partials.tree-nodes', [
                                    'nodes' => $categoryTree,
                                    'selectedId' => $selectedCategory?->id,
                                    'depth' => 0,
                                    'domainId' => $filters['domainid'],
                                    'expandedIds' => $expandedIds ?? [],
                                ])
                            @else
                                <div class="px-2 py-4 text-sm text-slate-400">
                                    No categories exist yet for this domain.
                                </div>
                            @endif
                        </div>
                    </aside>

                    <section class="border-r border-gray-200 bg-gray-50/40">
                        <div class="p-5 border-b border-gray-200 flex items-start justify-between gap-3">
                            <div>
                                <h3 class="text-base font-semibold text-gray-900">Selected category</h3>
                                <p class="text-sm text-gray-500">Maintenance for the highlighted folder</p>
                            </div>

                            @if($selectedCategory)
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium {{ $selectedCategory->isactive ? 'bg-green-100 text-green-800' : 'bg-gray-200 text-gray-700' }}">
                                        {{ $selectedCategory->isactive ? 'Active' : 'Inactive' }}
                                    </span>
                                </div>
                            @endif
                        </div>

                        <div class="p-5">
                            @if($selectedCategory)
                                <form method="POST"
                                      action="{{ route('knowledge-categories.update', $selectedCategory) }}"
                                      id="knowledge-category-form"
                                      class="space-y-4">
                                    @csrf
                                    @method('PUT')

                                    <input type="hidden" name="domainid" value="{{ old('domainid', $selectedCategory->domainid) }}">

                                    <div class="text-sm text-gray-500">
                                        Domain /
                                        {{ optional($domains->firstWhere('id', $selectedCategory->domainid))->domainname ?? 'Unknown' }}
                                        /
                                        <span class="font-semibold text-gray-800">{{ $selectedCategory->categoryname }}</span>
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
                                                @foreach($parentOptions as $option)
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
                        <div class="mt-6 bg-white overflow-hidden shadow-sm sm:rounded-lg border border-red-200">
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
                    </section>

                    <section>
                        <div class="p-5 border-b border-gray-200 flex items-start justify-between gap-3">
                            <div>
                                <h3 class="text-base font-semibold text-gray-900">
                                    Items in {{ $selectedCategory?->categoryname ?? 'selected category' }}
                                </h3>
                                <p class="text-sm text-gray-500">Register view filtered by the selected folder</p>
                            </div>

                            @if($selectedCategory)
                                <a href="{{ route('knowledge.items.index', [
                                        'domainid' => $selectedCategory->domainid ?? ($filters['domainid'] ?? null),
                                        'categoryid' => $selectedCategory->id,
                                        'show_create' => 1,
                                    ]) }}"
                                class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 text-sm whitespace-nowrap">
                                    New knowledge item
                                </a>
                            @else
                                <button type="button"
                                        disabled
                                        class="px-4 py-2 bg-gray-300 text-gray-500 rounded-md text-sm whitespace-nowrap cursor-not-allowed">
                                    New knowledge item
                                </button>
                            @endif
                        </div>

                        <div class="p-5 space-y-5">
                            <form method="GET" action="{{ route('knowledge-categories.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <input type="hidden" name="domainid" value="{{ $filters['domainid'] }}">
                                <input type="hidden" name="categoryid" value="{{ $filters['categoryid'] }}">

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
                                        ]) }}"
                                       class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md text-sm">
                                        Reset
                                    </a>
                                </div>
                            </form>

                            <div class="overflow-x-auto border border-gray-200 rounded-lg">
                                <table class="w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Item</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Next Review</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Summary</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @forelse($items as $item)
                                            <tr>
                                                <td class="px-4 py-3 align-top">
                                                    <div class="font-medium text-gray-900">{{ $item->itemname }}</div>
                                                    <div class="text-xs text-gray-500 mt-1">
                                                        Primary category: {{ $selectedCategory?->categoryname }}
                                                    </div>
                                                </td>

                                                <td class="px-4 py-3 text-sm text-gray-700">
                                                    {{ $item->itemtype ?: '—' }}
                                                </td>

                                                <td class="px-4 py-3">
                                                    <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium {{ $item->itemstatus === 'active' ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-800' }}">
                                                        {{ $item->itemstatus ?: 'n/a' }}
                                                    </span>
                                                </td>

                                                <td class="px-4 py-3 text-sm text-gray-700">
                                                    {{ $item->nextreviewdate ? \Illuminate\Support\Carbon::parse($item->nextreviewdate)->format('d M Y') : '—' }}
                                                </td>

                                                <td class="px-4 py-3 text-sm text-gray-600">
                                                    {{ $item->summary }}
                                                </td>

                                                <td class="px-4 py-3 whitespace-nowrap">
                                                    <a href="{{ route('knowledge.items.edit', $item) }}"
                                                    class="inline-flex items-center px-3 py-1.5 bg-slate-700 text-white rounded hover:bg-slate-600 text-sm">
                                                        Open
                                                    </a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-500">
                                                    No knowledge items found for this category and filter set.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </section>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>