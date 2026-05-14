<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $pageTitle ?? 'Exchanges' }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="w-full max-w-none mx-auto px-4 sm:px-6 lg:px-8 xl:px-10 2xl:px-12 space-y-6">

            @include('partials.admin.flash-messages')
            @include('partials.admin.validation-summary')

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 border-b border-gray-200">
                    <form method="GET"
                          action="{{ route('exchanges.index') }}"
                          id="exchanges-filter-form"
                          class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label for="search" class="block text-sm font-medium text-gray-700 mb-1">
                                Search
                            </label>
                            <input type="text"
                                   name="search"
                                   id="search"
                                   value="{{ $filters['search'] ?? '' }}"
                                   class="w-full rounded-md border-gray-300 shadow-sm"
                                   placeholder="Code, name, country, currency">
                        </div>

                        <div>
                            <label for="countrycode" class="block text-sm font-medium text-gray-700 mb-1">
                                Country
                            </label>
                            <input type="text"
                                   name="countrycode"
                                   id="countrycode"
                                   value="{{ $filters['countrycode'] ?? '' }}"
                                   class="w-full rounded-md border-gray-300 shadow-sm"
                                   maxlength="2"
                                   placeholder="AU">
                        </div>

                        <div>
                            <label for="active" class="block text-sm font-medium text-gray-700 mb-1">
                                Status
                            </label>
                            <select name="active"
                                    id="active"
                                    class="w-full rounded-md border-gray-300 shadow-sm">
                                <option value="">All</option>
                                <option value="1" @selected(($filters['active'] ?? '') === '1')>Active</option>
                                <option value="0" @selected(($filters['active'] ?? '') === '0')>Inactive</option>
                            </select>
                        </div>

                        <div class="flex items-end gap-2">
                            <button type="submit"
                                    class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                                Filter
                            </button>

                            <a href="{{ route('exchanges.index') }}"
                               class="px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300">
                                Reset
                            </a>
                        </div>
                    </form>
                </div>

                <form method="POST"
                      action="{{ route('exchanges.bulk-save') }}"
                      id="exchanges-form">
                    @csrf

                    <input type="hidden" name="search" value="{{ $filters['search'] ?? '' }}">
                    <input type="hidden" name="countrycode" value="{{ $filters['countrycode'] ?? '' }}">
                    <input type="hidden" name="active" value="{{ $filters['active'] ?? '' }}">

                    <div class="overflow-x-auto">
                        <table class="w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Code</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Country</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Currency</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Website</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Timezone</th>
                                    <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase">Active</th>
                                    <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase">Actions</th>
                                </tr>
                            </thead>

                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($rows as $row)
                                    <tr>
                                        <td class="px-3 py-2">
                                            <input type="text"
                                                   name="existing[{{ $row->id }}][exchangecode]"
                                                   value="{{ old("existing.{$row->id}.exchangecode", $row->exchangecode) }}"
                                                   class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                                                   maxlength="20"
                                                   required>
                                        </td>

                                        <td class="px-3 py-2">
                                            <input type="text"
                                                   name="existing[{{ $row->id }}][exchangename]"
                                                   value="{{ old("existing.{$row->id}.exchangename", $row->exchangename) }}"
                                                   class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                                                   maxlength="150"
                                                   required>
                                        </td>

                                        <td class="px-3 py-2">
                                            <input type="text"
                                                   name="existing[{{ $row->id }}][countrycode]"
                                                   value="{{ old("existing.{$row->id}.countrycode", $row->countrycode) }}"
                                                   class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                                                   maxlength="2"
                                                   placeholder="AU">
                                        </td>

                                        <td class="px-3 py-2">
                                            <input type="text"
                                                   name="existing[{{ $row->id }}][defaultcurrencycode]"
                                                   value="{{ old("existing.{$row->id}.defaultcurrencycode", $row->defaultcurrencycode) }}"
                                                   class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                                                   maxlength="3"
                                                   placeholder="AUD">
                                        </td>

                                        <td class="px-3 py-2">
                                            <input type="url"
                                                   name="existing[{{ $row->id }}][marketwebsite]"
                                                   value="{{ old("existing.{$row->id}.marketwebsite", $row->marketwebsite) }}"
                                                   class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                                                   maxlength="255"
                                                   placeholder="https://...">
                                        </td>

                                        <td class="px-3 py-2">
                                            <input type="text"
                                                   name="existing[{{ $row->id }}][timezone]"
                                                   value="{{ old("existing.{$row->id}.timezone", $row->timezone) }}"
                                                   class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                                                   maxlength="100"
                                                   placeholder="Australia/Melbourne">
                                        </td>

                                        <td class="px-3 py-2 text-center">
                                            <input type="hidden" name="existing[{{ $row->id }}][isactive]" value="0">
                                            <input type="checkbox"
                                                   name="existing[{{ $row->id }}][isactive]"
                                                   value="1"
                                                   class="rounded border-gray-300 text-blue-600 shadow-sm"
                                                   @checked(old("existing.{$row->id}.isactive", $row->isactive))>
                                        </td>

                                        <td class="px-3 py-2 text-center whitespace-nowrap">
                                            <button type="button"
                                                    class="px-3 py-1.5 bg-red-100 text-red-700 rounded hover:bg-red-200 text-sm delete-exchange-btn"
                                                    data-id="{{ $row->id }}"
                                                    data-name="{{ $row->exchangecode }} - {{ $row->exchangename }}"
                                                    data-action="{{ route('exchanges.destroy', $row->id) }}">
                                                Delete
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="px-3 py-6 text-center text-sm text-gray-500">
                                            No exchanges found.
                                        </td>
                                    </tr>
                                @endforelse

                                <tr class="bg-blue-50">
                                    <td class="px-3 py-2">
                                        <input type="text"
                                               name="new[exchangecode]"
                                               value="{{ old('new.exchangecode') }}"
                                               class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                                               maxlength="20"
                                               placeholder="ASX">
                                    </td>

                                    <td class="px-3 py-2">
                                        <input type="text"
                                               name="new[exchangename]"
                                               value="{{ old('new.exchangename') }}"
                                               class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                                               maxlength="150"
                                               placeholder="Australian Securities Exchange">
                                    </td>

                                    <td class="px-3 py-2">
                                        <input type="text"
                                               name="new[countrycode]"
                                               value="{{ old('new.countrycode') }}"
                                               class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                                               maxlength="2"
                                               placeholder="AU">
                                    </td>

                                    <td class="px-3 py-2">
                                        <input type="text"
                                               name="new[defaultcurrencycode]"
                                               value="{{ old('new.defaultcurrencycode') }}"
                                               class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                                               maxlength="3"
                                               placeholder="AUD">
                                    </td>

                                    <td class="px-3 py-2">
                                        <input type="url"
                                               name="new[marketwebsite]"
                                               value="{{ old('new.marketwebsite') }}"
                                               class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                                               maxlength="255"
                                               placeholder="https://...">
                                    </td>

                                    <td class="px-3 py-2">
                                        <input type="text"
                                               name="new[timezone]"
                                               value="{{ old('new.timezone') }}"
                                               class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                                               maxlength="100"
                                               placeholder="Australia/Melbourne">
                                    </td>

                                    <td class="px-3 py-2 text-center">
                                        <input type="hidden" name="new[isactive]" value="0">
                                        <input type="checkbox"
                                               name="new[isactive]"
                                               value="1"
                                               class="rounded border-gray-300 text-blue-600 shadow-sm"
                                               @checked(old('new.isactive', true))>
                                    </td>

                                    <td class="px-3 py-2 text-center text-sm text-gray-400 whitespace-nowrap">
                                        New row
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="p-6 border-t border-gray-200 flex items-center justify-between">
                        <p class="text-sm text-gray-500">
                            Edit rows above, add a new exchange at the bottom, then save once.
                        </p>

                        <button type="submit"
                                class="px-5 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                            Save Exchanges
                        </button>
                    </div>
                </form>

                @include('partials.admin.compact-delete-form', [
                    'formId' => 'delete-exchange-form',
                    'query' => request()->only(['search', 'countrycode', 'active']),
                ])
            </div>
        </div>
    </div>

    @include('partials.admin.dirty-form-script', [
        'formId' => 'exchanges-form',
        'filterFormId' => 'exchanges-filter-form',
        'deleteFormId' => 'delete-exchange-form',
        'deleteButtonSelector' => '.delete-exchange-btn',
        'dirtyMessage' => 'You have unsaved changes in the Exchanges table. Continue and lose those changes?',
        'deleteDirtyMessage' => 'You have unsaved changes in the Exchanges table. Delete anyway and lose those changes?',
        'deleteConfirmPrefix' => 'Delete exchange',
        'deleteConfirmSuffix' => 'This cannot be undone.',
    ])
</x-app-layout>