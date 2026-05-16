<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Add Knowledge Category
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="w-full max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

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

            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-base font-semibold text-gray-900">New category</h3>
                    <p class="text-sm text-gray-500 mt-1">
                        Create a root category or assign a parent category within the same domain.
                    </p>
                </div>

                <form method="POST" action="{{ route('knowledge-categories.store') }}" class="p-6 space-y-5">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="domainid" class="block text-sm font-medium text-gray-700 mb-1">Domain</label>
                            <select name="domainid" id="domainid" class="w-full rounded-md border-gray-300 shadow-sm text-sm" required>
                                @foreach($domains as $domain)
                                    <option value="{{ $domain->id }}" @selected((string) old('domainid', $domainId) === (string) $domain->id)>
                                        {{ $domain->domainname }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="parentcategoryid" class="block text-sm font-medium text-gray-700 mb-1">Parent category</label>
                            <select name="parentcategoryid" id="parentcategoryid" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                <option value="">No parent</option>
                                @foreach($parentOptions as $option)
                                    <option value="{{ $option['id'] }}" @selected((string) old('parentcategoryid', $parentCategoryId) === (string) $option['id'])>
                                        {{ $option['label'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <label for="categoryname" class="block text-sm font-medium text-gray-700 mb-1">Category name</label>
                        <input type="text"
                               name="categoryname"
                               id="categoryname"
                               value="{{ old('categoryname') }}"
                               class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                               maxlength="200"
                               required>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="categorytype" class="block text-sm font-medium text-gray-700 mb-1">Category type</label>
                            <select name="categorytype" id="categorytype" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                <option value="">Select type</option>
                                @foreach($categoryTypeOptions as $value => $label)
                                    <option value="{{ $value }}" @selected(old('categorytype') === $value)>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="sortorder" class="block text-sm font-medium text-gray-700 mb-1">Sort order</label>
                            <input type="number"
                                   name="sortorder"
                                   id="sortorder"
                                   value="{{ old('sortorder', 0) }}"
                                   class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                                   min="0">
                        </div>
                    </div>

                    <div>
                        <label for="slug" class="block text-sm font-medium text-gray-700 mb-1">Slug</label>
                        <input type="text"
                               name="slug"
                               id="slug"
                               value="{{ old('slug') }}"
                               class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                               maxlength="220">
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea name="description"
                                  id="description"
                                  rows="6"
                                  class="w-full rounded-md border-gray-300 shadow-sm text-sm">{{ old('description') }}</textarea>
                    </div>

                    <div class="flex flex-wrap gap-4">
                        <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                            <input type="hidden" name="isfeatured" value="0">
                            <input type="checkbox" name="isfeatured" value="1" class="rounded border-gray-300 text-blue-600" @checked(old('isfeatured'))>
                            Featured
                        </label>

                        <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                            <input type="hidden" name="isactive" value="0">
                            <input type="checkbox" name="isactive" value="1" class="rounded border-gray-300 text-blue-600" @checked(old('isactive', true))>
                            Active
                        </label>
                    </div>

                    <div class="flex flex-wrap gap-3 pt-2">
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm">
                            Create category
                        </button>

                        <a href="{{ route('knowledge-categories.index', ['domainid' => old('domainid', $domainId), 'categoryid' => old('parentcategoryid', $parentCategoryId)]) }}"
                           class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 text-sm">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>