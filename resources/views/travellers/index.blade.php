<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Travellers
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="w-full max-w-none mx-auto px-4 sm:px-6 lg:px-8 xl:px-10 2xl:px-12 space-y-6">

            @include('partials.admin.flash-messages')
            @include('partials.admin.validation-summary')

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 border-b border-gray-200">
                    <form method="GET" action="{{ route('travellers.index') }}" id="travellers-filter-form" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                            <input type="text"
                                   name="search"
                                   id="search"
                                   value="{{ request('search') }}"
                                   class="w-full rounded-md border-gray-300 shadow-sm"
                                   placeholder="Search by name">
                        </div>

                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <select name="status" id="status" class="w-full rounded-md border-gray-300 shadow-sm">
                                <option value="">All</option>
                                <option value="1" @selected(request('status') === '1')>Active</option>
                                <option value="0" @selected(request('status') === '0')>Inactive</option>
                            </select>
                        </div>

                        <div>
                            <label for="primary" class="block text-sm font-medium text-gray-700 mb-1">Primary</label>
                            <select name="primary" id="primary" class="w-full rounded-md border-gray-300 shadow-sm">
                                <option value="">All</option>
                                <option value="1" @selected(request('primary') === '1')>Primary only</option>
                                <option value="0" @selected(request('primary') === '0')>Non-primary only</option>
                            </select>
                        </div>

                        <div class="flex items-end gap-2">
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                                Filter
                            </button>

                            <a href="{{ route('travellers.index') }}"
                               class="px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300"
                               id="travellers-reset-link">
                                Reset
                            </a>
                        </div>
                    </form>
                </div>

                <form method="POST" action="{{ route('travellers.bulk-save') }}" id="travellers-form">
                    @csrf

                    <input type="hidden" name="search" value="{{ request('search') }}">
                    <input type="hidden" name="status" value="{{ request('status') }}">
                    <input type="hidden" name="primary" value="{{ request('primary') }}">
                    <input type="hidden" name="primary_type" id="primary_type">
                    <input type="hidden" name="primary_id" id="primary_id">

                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[1100px] divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase w-[22%]">First Name</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase w-[22%]">Last Name</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase w-[34%]">Display Name</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase w-[8%]">Primary</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase w-[8%]">Active</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase w-[6%]">Actions</th>
                                </tr>
                            </thead>

                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($travellers as $traveller)
                                    <tr>
                                        <td class="px-4 py-3">
                                            <input type="text"
                                                   name="existing[{{ $traveller->id }}][firstname]"
                                                   value="{{ old("existing.{$traveller->id}.firstname", $traveller->firstname) }}"
                                                   class="w-full min-w-[170px] rounded-md border-gray-300 shadow-sm"
                                                   required>
                                        </td>

                                        <td class="px-4 py-3">
                                            <input type="text"
                                                   name="existing[{{ $traveller->id }}][lastname]"
                                                   value="{{ old("existing.{$traveller->id}.lastname", $traveller->lastname) }}"
                                                   class="w-full min-w-[170px] rounded-md border-gray-300 shadow-sm">
                                        </td>

                                        <td class="px-4 py-3">
                                            <input type="text"
                                                   name="existing[{{ $traveller->id }}][displayname]"
                                                   value="{{ old("existing.{$traveller->id}.displayname", $traveller->displayname) }}"
                                                   class="w-full min-w-[240px] rounded-md border-gray-300 shadow-sm"
                                                   required>
                                        </td>

                                        <td class="px-4 py-3 text-center">
                                            <input type="radio"
                                                   name="primary_choice"
                                                   value="existing:{{ $traveller->id }}"
                                                   class="border-gray-300 text-blue-600 shadow-sm"
                                                   @checked(old('primary_choice', $traveller->isprimarytraveller ? 'existing:'.$traveller->id : '') === 'existing:'.$traveller->id)>
                                        </td>

                                        <td class="px-4 py-3 text-center">
                                            <input type="hidden" name="existing[{{ $traveller->id }}][isactive]" value="0">
                                            <input type="checkbox"
                                                   name="existing[{{ $traveller->id }}][isactive]"
                                                   value="1"
                                                   class="rounded border-gray-300 text-blue-600 shadow-sm"
                                                   @checked(old("existing.{$traveller->id}.isactive", $traveller->isactive))>
                                        </td>

                                        <td class="px-4 py-3 text-center whitespace-nowrap">
                                            <button type="button"
                                                    class="px-3 py-1.5 bg-red-100 text-red-700 rounded hover:bg-red-200 text-sm delete-traveller-btn"
                                                    data-id="{{ $traveller->id }}"
                                                    data-name="{{ $traveller->displayname }}"
                                                    data-action="{{ route('travellers.destroy', $traveller->id) }}">
                                                Delete
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-4 py-6 text-center text-gray-500">
                                            No travellers found.
                                        </td>
                                    </tr>
                                @endforelse

                                <tr class="bg-blue-50">
                                    <td class="px-4 py-3">
                                        <input type="text"
                                               name="new[firstname]"
                                               value="{{ old('new.firstname') }}"
                                               class="w-full min-w-[170px] rounded-md border-gray-300 shadow-sm"
                                               placeholder="New first name">
                                    </td>

                                    <td class="px-4 py-3">
                                        <input type="text"
                                               name="new[lastname]"
                                               value="{{ old('new.lastname') }}"
                                               class="w-full min-w-[170px] rounded-md border-gray-300 shadow-sm"
                                               placeholder="New last name">
                                    </td>

                                    <td class="px-4 py-3">
                                        <input type="text"
                                               name="new[displayname]"
                                               value="{{ old('new.displayname') }}"
                                               class="w-full min-w-[240px] rounded-md border-gray-300 shadow-sm"
                                               placeholder="New display name">
                                    </td>

                                    <td class="px-4 py-3 text-center">
                                        <input type="radio"
                                               name="primary_choice"
                                               value="new"
                                               class="border-gray-300 text-blue-600 shadow-sm"
                                               @checked(old('primary_choice') === 'new')>
                                    </td>

                                    <td class="px-4 py-3 text-center">
                                        <input type="hidden" name="new[isactive]" value="0">
                                        <input type="checkbox"
                                               name="new[isactive]"
                                               value="1"
                                               class="rounded border-gray-300 text-blue-600 shadow-sm"
                                               @checked(old('new.isactive', true))>
                                    </td>

                                    <td class="px-4 py-3 text-center text-sm text-gray-400 whitespace-nowrap">
                                        New row
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="p-6 border-t border-gray-200 flex items-center justify-between">
                        <p class="text-sm text-gray-500">
                            Edit rows above, add a new traveller at the bottom, then save once.
                        </p>

                        <button type="submit"
                                class="px-5 py-2 bg-green-600 text-white rounded hover:bg-green-700"
                                id="save-button">
                            Save Travellers
                        </button>
                    </div>
                </form>

                @include('partials.admin.compact-delete-form', [
                    'formId' => 'delete-traveller-form',
                    'query' => request()->only(['search', 'status', 'primary']),
                ])
            </div>
        </div>
    </div>

    <script>
        (() => {
            const form = document.getElementById('travellers-form');
            const primaryTypeInput = document.getElementById('primary_type');
            const primaryIdInput = document.getElementById('primary_id');

            if (!form) return;

            form.addEventListener('submit', function () {
                const selectedPrimary = form.querySelector('input[name="primary_choice"]:checked');

                if (selectedPrimary) {
                    if (selectedPrimary.value === 'new') {
                        primaryTypeInput.value = 'new';
                        primaryIdInput.value = '';
                    } else if (selectedPrimary.value.startsWith('existing:')) {
                        primaryTypeInput.value = 'existing';
                        primaryIdInput.value = selectedPrimary.value.split(':')[1];
                    }
                } else {
                    primaryTypeInput.value = '';
                    primaryIdInput.value = '';
                }
            });
        })();
    </script>

    @include('partials.admin.dirty-form-script', [
        'formId' => 'travellers-form',
        'filterFormId' => 'travellers-filter-form',
        'deleteFormId' => 'delete-traveller-form',
        'deleteButtonSelector' => '.delete-traveller-btn',
        'dirtyMessage' => 'You have unsaved changes in the Travellers table. Continue and lose those changes?',
        'deleteDirtyMessage' => 'You have unsaved changes in the Travellers table. Delete anyway and lose those changes?',
        'deleteConfirmPrefix' => 'Delete traveller',
        'deleteConfirmSuffix' => 'This cannot be undone.',
    ])
</x-app-layout>