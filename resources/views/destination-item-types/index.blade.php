<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $pageTitle ?? 'Destination Item Types' }}
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
                <div class="p-4 border-b border-gray-200">
                    <form method="GET" action="{{ route('destination-item-types.index') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                            <input
                                type="text"
                                name="search"
                                id="search"
                                value="{{ $filters['search'] ?? '' }}"
                                class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                                placeholder="Type name or slug">
                        </div>

                        <div>
                            <label for="active" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <select name="active" id="active" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                <option value="">All</option>
                                <option value="1" @selected(($filters['active'] ?? '') === '1')>Active</option>
                                <option value="0" @selected(($filters['active'] ?? '') === '0')>Inactive</option>
                            </select>
                        </div>

                        <div class="flex items-end gap-2">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm">
                                Apply
                            </button>
                            <a href="{{ route('destination-item-types.index') }}"
                               class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 text-sm">
                                Reset
                            </a>
                        </div>
                    </form>
                </div>

                <form method="POST" action="{{ route('destination-item-types.bulk-save') }}" id="destination-item-types-form">
                    @csrf

                    <input type="hidden" name="search" value="{{ $filters['search'] ?? '' }}">
                    <input type="hidden" name="active" value="{{ $filters['active'] ?? '' }}">

                    <div class="overflow-x-auto">
                        <table class="w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Type Name</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Slug</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase w-28">Sort Order</th>
                                    <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase w-24">Active</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase w-32">Actions</th>
                                </tr>
                            </thead>

                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($types as $type)
                                    <tr>
                                        <td class="px-3 py-2 min-w-[220px]">
                                            <input
                                                type="text"
                                                name="existing[{{ $type->id }}][typename]"
                                                value="{{ old("existing.$type->id.typename", $type->typename) }}"
                                                class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                                                maxlength="100"
                                                required>
                                        </td>

                                        <td class="px-3 py-2 min-w-[220px]">
                                            <input
                                                type="text"
                                                name="existing[{{ $type->id }}][slug]"
                                                value="{{ old("existing.$type->id.slug", $type->slug) }}"
                                                class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                                                maxlength="120">
                                        </td>

                                        <td class="px-3 py-2">
                                            <input
                                                type="number"
                                                name="existing[{{ $type->id }}][sortorder]"
                                                value="{{ old("existing.$type->id.sortorder", $type->sortorder ?? 0) }}"
                                                class="w-24 rounded-md border-gray-300 shadow-sm text-sm"
                                                min="0">
                                        </td>

                                        <td class="px-3 py-2 text-center">
                                            <input type="hidden" name="existing[{{ $type->id }}][isactive]" value="0">
                                            <input
                                                type="checkbox"
                                                name="existing[{{ $type->id }}][isactive]"
                                                value="1"
                                                class="rounded border-gray-300 text-blue-600 shadow-sm"
                                                @checked(old("existing.$type->id.isactive", $type->isactive))>
                                        </td>

                                        <td class="px-3 py-2 whitespace-nowrap">
                                            <button
                                                type="submit"
                                                form="delete-destination-item-type-{{ $type->id }}"
                                                class="inline-flex items-center px-3 py-1.5 bg-red-600 text-white rounded hover:bg-red-700 text-sm"
                                                onclick="return confirm('Delete destination item type {{ addslashes($type->typename) }}?');">
                                                Delete
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-3 py-6 text-center text-sm text-gray-500">
                                            No destination item types found.
                                        </td>
                                    </tr>
                                @endforelse

                                <tr class="bg-blue-50">
                                    <td class="px-3 py-2">
                                        <input
                                            type="text"
                                            name="new[typename]"
                                            value="{{ old('new.typename') }}"
                                            class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                                            maxlength="100"
                                            placeholder="New item type">
                                    </td>

                                    <td class="px-3 py-2">
                                        <input
                                            type="text"
                                            name="new[slug]"
                                            value="{{ old('new.slug') }}"
                                            class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                                            maxlength="120"
                                            placeholder="Auto from name if blank">
                                    </td>

                                    <td class="px-3 py-2">
                                        <input
                                            type="number"
                                            name="new[sortorder]"
                                            value="{{ old('new.sortorder', 0) }}"
                                            class="w-24 rounded-md border-gray-300 shadow-sm text-sm"
                                            min="0">
                                    </td>

                                    <td class="px-3 py-2 text-center">
                                        <input type="hidden" name="new[isactive]" value="0">
                                        <input
                                            type="checkbox"
                                            name="new[isactive]"
                                            value="1"
                                            class="rounded border-gray-300 text-blue-600 shadow-sm"
                                            @checked(old('new.isactive', true))>
                                    </td>

                                    <td class="px-3 py-2 text-sm text-gray-400 whitespace-nowrap">
                                        New row
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="px-4 py-3 border-t border-gray-200 flex items-center justify-between gap-3">
                        <p class="text-sm text-gray-500">
                            Maintain short reusable destination item type records here.
                        </p>

                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>

            @foreach($types as $type)
                <form
                    method="POST"
                    action="{{ route('destination-item-types.destroy', $type) }}"
                    id="delete-destination-item-type-{{ $type->id }}"
                    class="hidden">
                    @csrf
                    @method('DELETE')
                </form>
            @endforeach
        </div>
    </div>

    @include('partials.admin.dirty-form-script', [
        'formId' => 'destination-item-types-form',
        'filterFormId' => null,
        'dirtyMessage' => 'You have unsaved changes in destination item types. Continue and lose those changes?',
    ])
</x-app-layout>