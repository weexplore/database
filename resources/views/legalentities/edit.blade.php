<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Edit Legal Entity
            </h2>
            <a href="{{ route('legal-entities.index') }}" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
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
                    <form method="POST" action="{{ route('legal-entities.update', $legalEntity) }}" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="entitycode" class="block text-sm font-medium text-gray-700">Code</label>
                                <input type="text" id="entitycode" name="entitycode" value="{{ old('entitycode', $legalEntity->entitycode) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                            </div>

                            <div>
                                <label for="entityname" class="block text-sm font-medium text-gray-700">Name</label>
                                <input type="text" id="entityname" name="entityname" value="{{ old('entityname', $legalEntity->entityname) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                            </div>

                            <div>
                                <label for="entitytype" class="block text-sm font-medium text-gray-700">Type</label>
                                <input type="text" id="entitytype" name="entitytype" value="{{ old('entitytype', $legalEntity->entitytype) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                            </div>

                            <div>
                                <label for="sortorder" class="block text-sm font-medium text-gray-700">Sort order</label>
                                <input type="number" id="sortorder" name="sortorder" value="{{ old('sortorder', $legalEntity->sortorder) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                            </div>

                            <div>
                                <label for="abn" class="block text-sm font-medium text-gray-700">ABN</label>
                                <input type="text" id="abn" name="abn" value="{{ old('abn', $legalEntity->abn) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                            </div>

                            <div>
                                <label for="acn" class="block text-sm font-medium text-gray-700">ACN</label>
                                <input type="text" id="acn" name="acn" value="{{ old('acn', $legalEntity->acn) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                            </div>
                        </div>

                        <div>
                            <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
                            <textarea id="notes" name="notes" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">{{ old('notes', $legalEntity->notes) }}</textarea>
                        </div>

                        <div class="flex items-center justify-between">
                            <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                <input type="checkbox" name="isactive" value="1" class="rounded border-gray-300" @checked(old('isactive', $legalEntity->isactive))>
                                <span>Active</span>
                            </label>

                            <div class="flex items-center gap-2">
                                <button type="submit" class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                                    Save Changes
                                </button>
                            </div>
                        </div>
                    </form>

                    <div class="mt-6 border-t border-gray-200 pt-6">
                        <form method="POST" action="{{ route('legal-entities.destroy', $legalEntity) }}" onsubmit="return confirm('Delete this legal entity?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex items-center rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700">
                                Delete Legal Entity
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
