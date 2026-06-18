<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Legal Entities
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="w-full max-w-none mx-auto px-4 sm:px-6 lg:px-8 xl:px-10 2xl:px-12 space-y-6">
            @if (session('success'))
                <div class="rounded-md bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-800">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="rounded-md bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-800">
                    {{ session('error') }}
                </div>
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
                <div class="p-4 sm:p-6 space-y-6">
                    <form method="GET" action="{{ route('legal-entities.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label for="isactive" class="block text-sm font-medium text-gray-700">Active status</label>
                            <select id="isactive" name="isactive" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                                <option value="">All</option>
                                <option value="1" @selected(request('isactive') === '1')>Active</option>
                                <option value="0" @selected(request('isactive') === '0')>Inactive</option>
                            </select>
                        </div>

                        <div class="md:col-span-3 flex items-end gap-2">
                            <button type="submit" class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                                Filter
                            </button>
                            <a href="{{ route('legal-entities.index') }}" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                                Reset
                            </a>
                        </div>
                    </form>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Code</th>
                                    <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Name</th>
                                    <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Type</th>
                                    <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">ABN</th>
                                    <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">ACN</th>
                                    <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Active</th>
                                    <th class="px-3 py-2 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                @foreach ($legalEntities as $legalEntity)
                                    <tr>
                                        <td class="px-3 py-2 text-sm text-gray-900">{{ $legalEntity->entitycode }}</td>
                                        <td class="px-3 py-2 text-sm text-gray-900">{{ $legalEntity->entityname }}</td>
                                        <td class="px-3 py-2 text-sm text-gray-700">{{ $legalEntity->entitytype }}</td>
                                        <td class="px-3 py-2 text-sm text-gray-700">{{ $legalEntity->abn ?: '—' }}</td>
                                        <td class="px-3 py-2 text-sm text-gray-700">{{ $legalEntity->acn ?: '—' }}</td>
                                        <td class="px-3 py-2 text-sm text-gray-700">{{ $legalEntity->isactive ? 'Yes' : 'No' }}</td>
                                        <td class="px-3 py-2 text-right text-sm">
                                            <a href="{{ route('legal-entities.edit', $legalEntity) }}" class="text-indigo-600 hover:text-indigo-800">Edit</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div>
                        {{ $legalEntities->links() }}
                    </div>

                    <div class="border-t border-gray-200 pt-6">
                        <h3 class="text-lg font-semibold text-gray-900">Add Legal Entity</h3>

                        <form method="POST" action="{{ route('legal-entities.store') }}" class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                            @csrf

                            <div>
                                <label for="entitycode" class="block text-sm font-medium text-gray-700">Code</label>
                                <input type="text" id="entitycode" name="entitycode" value="{{ old('entitycode') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                            </div>

                            <div>
                                <label for="entityname" class="block text-sm font-medium text-gray-700">Name</label>
                                <input type="text" id="entityname" name="entityname" value="{{ old('entityname') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                            </div>

                            <div>
                                <label for="entitytype" class="block text-sm font-medium text-gray-700">Type</label>
                                <input type="text" id="entitytype" name="entitytype" value="{{ old('entitytype') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm" placeholder="individual, company, trust">
                            </div>

                            <div>
                                <label for="abn" class="block text-sm font-medium text-gray-700">ABN</label>
                                <input type="text" id="abn" name="abn" value="{{ old('abn') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                            </div>

                            <div>
                                <label for="acn" class="block text-sm font-medium text-gray-700">ACN</label>
                                <input type="text" id="acn" name="acn" value="{{ old('acn') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                            </div>

                            <div>
                                <label for="sortorder" class="block text-sm font-medium text-gray-700">Sort order</label>
                                <input type="number" id="sortorder" name="sortorder" value="{{ old('sortorder') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                            </div>

                            <div class="md:col-span-3">
                                <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
                                <textarea id="notes" name="notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">{{ old('notes') }}</textarea>
                            </div>

                            <div class="md:col-span-3 flex items-center justify-between">
                                <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                    <input type="checkbox" name="isactive" value="1" class="rounded border-gray-300" @checked(old('isactive', '1') == '1')>
                                    <span>Active</span>
                                </label>

                                <button type="submit" class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                                    Add Legal Entity
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
