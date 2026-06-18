<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $cashbookCategory->exists ? 'Edit Cashbook Category' : 'Add Cashbook Category' }}
            </h2>
            <a href="{{ route('cashbook-categories.index') }}" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                Back to list
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="w-full max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            @if (session('success'))
                <div class="rounded-md bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-800">{{ session('success') }}</div>
            @endif

            @if ($errors->any())
                <div class="rounded-md bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-800">
                    <div class="font-semibold">Please fix the following:</div>
                    <ul class="mt-2 list-disc pl-5 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ $cashbookCategory->exists ? route('cashbook-categories.update', $cashbookCategory) : route('cashbook-categories.store') }}" class="space-y-6">
                        @csrf
                        @if ($cashbookCategory->exists)
                            @method('PUT')
                        @endif

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="legalentityid" class="block text-sm font-medium text-gray-700">Legal entity scope</label>
                                <select id="legalentityid" name="legalentityid" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                                    <option value="">Shared category</option>
                                    @foreach ($legalEntities as $legalEntity)
                                        <option value="{{ $legalEntity->id }}" @selected(old('legalentityid', $cashbookCategory->legalentityid) == $legalEntity->id)>
                                            {{ $legalEntity->entityname }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="categorytypeid" class="block text-sm font-medium text-gray-700">Category type</label>
                                <select id="categorytypeid" name="categorytypeid" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                                    <option value="">Select category type</option>
                                    @foreach ($categoryTypes as $categoryType)
                                        <option value="{{ $categoryType->id }}" @selected(old('categorytypeid', $cashbookCategory->categorytypeid) == $categoryType->id)>
                                            {{ $categoryType->typename }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="parentcategoryid" class="block text-sm font-medium text-gray-700">Parent category</label>
                                <select id="parentcategoryid" name="parentcategoryid" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                                    <option value="">No parent</option>
                                    @foreach ($parentCategories as $parentCategory)
                                        <option value="{{ $parentCategory->id }}" @selected(old('parentcategoryid', $cashbookCategory->parentcategoryid) == $parentCategory->id)>
                                            {{ $parentCategory->categoryname }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="sortorder" class="block text-sm font-medium text-gray-700">Sort order</label>
                                <input type="number" id="sortorder" name="sortorder" value="{{ old('sortorder', $cashbookCategory->sortorder) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                            </div>

                            <div>
                                <label for="categorycode" class="block text-sm font-medium text-gray-700">Category code</label>
                                <input type="text" id="categorycode" name="categorycode" value="{{ old('categorycode', $cashbookCategory->categorycode) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                            </div>

                            <div>
                                <label for="categoryname" class="block text-sm font-medium text-gray-700">Category name</label>
                                <input type="text" id="categoryname" name="categoryname" value="{{ old('categoryname', $cashbookCategory->categoryname) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                            </div>
                        </div>

                        <div>
                            <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
                            <textarea id="notes" name="notes" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">{{ old('notes', $cashbookCategory->notes) }}</textarea>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                <input type="checkbox" name="allowposting" value="1" class="rounded border-gray-300" @checked(old('allowposting', $cashbookCategory->allowposting ?? true))>
                                <span>Allow posting</span>
                            </label>

                            <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                <input type="checkbox" name="issystem" value="1" class="rounded border-gray-300" @checked(old('issystem', $cashbookCategory->issystem ?? false))>
                                <span>System category</span>
                            </label>

                            <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                <input type="checkbox" name="isactive" value="1" class="rounded border-gray-300" @checked(old('isactive', $cashbookCategory->isactive ?? true))>
                                <span>Active</span>
                            </label>
                        </div>

                        <div class="flex items-center justify-end gap-3 border-t border-gray-200 pt-6">
                            <button type="submit" class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                                Save Category
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            @if ($cashbookCategory->exists)
                <div class="bg-white shadow-sm sm:rounded-lg border border-red-200">
                    <div class="p-6 space-y-4">
                        <div>
                            <h3 class="text-lg font-semibold text-red-700">Delete Category</h3>
                            <p class="mt-1 text-sm text-gray-600">
                                This permanently deletes the cashbook category. Only do this if you are sure it is no longer needed.
                            </p>
                        </div>

                        <form method="POST" action="{{ route('cashbook-categories.destroy', $cashbookCategory) }}" onsubmit="return confirm('Delete this cashbook category?');">
                            @csrf
                            @method('DELETE')

                            <button type="submit" class="inline-flex items-center rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700">
                                Delete Category
                            </button>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>