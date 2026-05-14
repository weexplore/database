<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $pageTitle ?? 'Instrument Types' }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="w-full max-w-none mx-auto px-4 sm:px-6 lg:px-8 xl:px-10 2xl:px-12 space-y-6">

            @include('partials.admin.flash-messages')
            @include('partials.admin.validation-summary')

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 border-b border-gray-200">
                    <form method="GET"
                          action="{{ route('instrument-types.index') }}"
                          id="instrument-types-filter-form"
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
                                   placeholder="Code, name or notes">
                        </div>

                        <div>
                            <label for="income" class="block text-sm font-medium text-gray-700 mb-1">
                                Income type
                            </label>
                            <select name="income"
                                    id="income"
                                    class="w-full rounded-md border-gray-300 shadow-sm">
                                <option value="">All</option>
                                <option value="dividends" @selected(($filters['income'] ?? '') === 'dividends')>Dividends</option>
                                <option value="distributions" @selected(($filters['income'] ?? '') === 'distributions')>Distributions</option>
                                <option value="both" @selected(($filters['income'] ?? '') === 'both')>Both</option>
                                <option value="none" @selected(($filters['income'] ?? '') === 'none')>None</option>
                            </select>
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

                            <a href="{{ route('instrument-types.index') }}"
                               class="px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300">
                                Reset
                            </a>
                        </div>
                    </form>
                </div>

                <form method="POST"
                      action="{{ route('instrument-types.bulk-save') }}"
                      id="instrument-types-form">
                    @csrf

                    <input type="hidden" name="search" value="{{ $filters['search'] ?? '' }}">
                    <input type="hidden" name="income" value="{{ $filters['income'] ?? '' }}">
                    <input type="hidden" name="active" value="{{ $filters['active'] ?? '' }}">

                    <div class="overflow-x-auto">
                        <table class="w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Code</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                                    <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase">Has Units</th>
                                    <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase">Has Dividends</th>
                                    <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase">Has Distributions</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Notes</th>
                                    <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase">Active</th>
                                    <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase">Actions</th>
                                </tr>
                            </thead>

                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($rows as $row)
                                    <tr>
                                        <td class="px-3 py-2">
                                            <input type="text"
                                                   name="existing[{{ $row->id }}][typecode]"
                                                   value="{{ old("existing.{$row->id}.typecode", $row->typecode) }}"
                                                   class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                                                   maxlength="30"
                                                   required>
                                        </td>

                                        <td class="px-3 py-2">
                                            <input type="text"
                                                   name="existing[{{ $row->id }}][typename]"
                                                   value="{{ old("existing.{$row->id}.typename", $row->typename) }}"
                                                   class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                                                   maxlength="100"
                                                   required>
                                        </td>

                                        <td class="px-3 py-2 text-center">
                                            <input type="hidden" name="existing[{{ $row->id }}][hasunits]" value="0">
                                            <input type="checkbox"
                                                   name="existing[{{ $row->id }}][hasunits]"
                                                   value="1"
                                                   class="rounded border-gray-300 text-blue-600 shadow-sm"
                                                   @checked(old("existing.{$row->id}.hasunits", $row->hasunits))>
                                        </td>

                                        <td class="px-3 py-2 text-center">
                                            <input type="hidden" name="existing[{{ $row->id }}][hasdividends]" value="0">
                                            <input type="checkbox"
                                                   name="existing[{{ $row->id }}][hasdividends]"
                                                   value="1"
                                                   class="rounded border-gray-300 text-blue-600 shadow-sm"
                                                   @checked(old("existing.{$row->id}.hasdividends", $row->hasdividends))>
                                        </td>

                                        <td class="px-3 py-2 text-center">
                                            <input type="hidden" name="existing[{{ $row->id }}][hasdistributions]" value="0">
                                            <input type="checkbox"
                                                   name="existing[{{ $row->id }}][hasdistributions]"
                                                   value="1"
                                                   class="rounded border-gray-300 text-blue-600 shadow-sm"
                                                   @checked(old("existing.{$row->id}.hasdistributions", $row->hasdistributions))>
                                        </td>

                                        <td class="px-3 py-2">
                                            <textarea name="existing[{{ $row->id }}][notes]"
                                                      rows="2"
                                                      class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                                                      placeholder="Optional notes">{{ old("existing.{$row->id}.notes", $row->notes) }}</textarea>
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
                                                    class="px-3 py-1.5 bg-red-100 text-red-700 rounded hover:bg-red-200 text-sm delete-instrument-type-btn"
                                                    data-id="{{ $row->id }}"
                                                    data-name="{{ $row->typecode }} - {{ $row->typename }}"
                                                    data-action="{{ route('instrument-types.destroy', $row->id) }}">
                                                Delete
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="px-3 py-6 text-center text-sm text-gray-500">
                                            No instrument types found.
                                        </td>
                                    </tr>
                                @endforelse

                                <tr class="bg-blue-50">
                                    <td class="px-3 py-2">
                                        <input type="text"
                                               name="new[typecode]"
                                               value="{{ old('new.typecode') }}"
                                               class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                                               maxlength="30"
                                               placeholder="share">
                                    </td>

                                    <td class="px-3 py-2">
                                        <input type="text"
                                               name="new[typename]"
                                               value="{{ old('new.typename') }}"
                                               class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                                               maxlength="100"
                                               placeholder="Ordinary Share">
                                    </td>

                                    <td class="px-3 py-2 text-center">
                                        <input type="hidden" name="new[hasunits]" value="0">
                                        <input type="checkbox"
                                               name="new[hasunits]"
                                               value="1"
                                               class="rounded border-gray-300 text-blue-600 shadow-sm"
                                               @checked(old('new.hasunits', true))>
                                    </td>

                                    <td class="px-3 py-2 text-center">
                                        <input type="hidden" name="new[hasdividends]" value="0">
                                        <input type="checkbox"
                                               name="new[hasdividends]"
                                               value="1"
                                               class="rounded border-gray-300 text-blue-600 shadow-sm"
                                               @checked(old('new.hasdividends', false))>
                                    </td>

                                    <td class="px-3 py-2 text-center">
                                        <input type="hidden" name="new[hasdistributions]" value="0">
                                        <input type="checkbox"
                                               name="new[hasdistributions]"
                                               value="1"
                                               class="rounded border-gray-300 text-blue-600 shadow-sm"
                                               @checked(old('new.hasdistributions', false))>
                                    </td>

                                    <td class="px-3 py-2">
                                        <textarea name="new[notes]"
                                                  rows="2"
                                                  class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                                                  placeholder="Optional notes">{{ old('new.notes') }}</textarea>
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
                            Edit rows above, add a new instrument type at the bottom, then save once.
                        </p>

                        <button type="submit"
                                class="px-5 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                            Save Instrument Types
                        </button>
                    </div>
                </form>

                @include('partials.admin.compact-delete-form', [
                    'formId' => 'delete-instrument-type-form',
                    'query' => request()->only(['search', 'income', 'active']),
                ])
            </div>
        </div>
    </div>

    @include('partials.admin.dirty-form-script', [
        'formId' => 'instrument-types-form',
        'filterFormId' => 'instrument-types-filter-form',
        'deleteFormId' => 'delete-instrument-type-form',
        'deleteButtonSelector' => '.delete-instrument-type-btn',
        'dirtyMessage' => 'You have unsaved changes in the Instrument Types table. Continue and lose those changes?',
        'deleteDirtyMessage' => 'You have unsaved changes in the Instrument Types table. Delete anyway and lose those changes?',
        'deleteConfirmPrefix' => 'Delete instrument type',
        'deleteConfirmSuffix' => 'This cannot be undone.',
    ])
</x-app-layout>