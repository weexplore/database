<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Labels
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="w-full max-w-none mx-auto px-4 sm:px-6 lg:px-8 xl:px-10 2xl:px-12 space-y-6">

            @include('partials.admin.flash-messages')
            @include('partials.admin.validation-summary')

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                {{-- Filters --}}
                <div class="p-6 border-b border-gray-200">
                    <form method="GET"
                          action="{{ route('labels.index') }}"
                          id="labels-filter-form"
                          class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="md:col-span-2">
                            <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                            <input type="text"
                                   name="search"
                                   id="search"
                                   value="{{ request('search') }}"
                                   class="w-full rounded-md border-gray-300 shadow-sm"
                                   placeholder="Search by label name">
                        </div>

                        <div class="flex items-end gap-2">
                            <button type="submit"
                                    class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                                Filter
                            </button>

                            <a href="{{ route('labels.index') }}"
                               class="px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300"
                               id="labels-reset-link">
                                Reset
                            </a>
                        </div>
                    </form>
                </div>

                {{-- Bulk save form --}}
                <form method="POST"
                      action="{{ route('labels.bulk-update') }}"
                      id="labels-form">
                    @csrf

                    {{-- Preserve filter state --}}
                    <input type="hidden" name="search" value="{{ request('search') }}">

                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[800px] divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase w-[40%]">
                                        Label Name
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase w-[20%]">
                                        Colour
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase w-[20%]">
                                        Used in Tasks
                                    </th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase w-[20%]">
                                        Actions
                                    </th>
                                </tr>
                            </thead>

                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($labels as $label)
                                    <tr>
                                        <td class="px-4 py-3">
                                            <input type="text"
                                                   name="labels[{{ $label->id }}][labelname]"
                                                   value="{{ old("labels.{$label->id}.labelname", $label->labelname) }}"
                                                   class="w-full min-w-[240px] rounded-md border-gray-300 shadow-sm"
                                                   required>
                                        </td>

                                        <td class="px-4 py-3">
                                            <input type="color"
                                                   name="labels[{{ $label->id }}][colourhex]"
                                                   value="{{ old("labels.{$label->id}.colourhex", $label->colourhex) }}"
                                                   class="h-9 w-16 rounded-md border-gray-300 shadow-sm">
                                        </td>

                                        <td class="px-4 py-3 text-sm text-gray-600">
                                            {{ $label->task_labels_count }} task(s)
                                        </td>

                                        <td class="px-4 py-3 text-center whitespace-nowrap">
                                            <button type="button"
                                                    class="px-3 py-1.5 bg-red-100 text-red-700 rounded hover:bg-red-200 text-sm delete-label-btn"
                                                    data-id="{{ $label->id }}"
                                                    data-name="{{ $label->labelname }}"
                                                    data-action="{{ route('labels.destroy', $label->id) }}">
                                                Delete
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-4 py-6 text-center text-gray-500">
                                            No labels found.
                                        </td>
                                    </tr>
                                @endforelse

                                {{-- New row at the bottom --}}
                                <tr class="bg-blue-50">
                                    <td class="px-4 py-3">
                                        <input type="text"
                                               name="new[labelname]"
                                               value="{{ old('new.labelname') }}"
                                               class="w-full min-w-[240px] rounded-md border-gray-300 shadow-sm"
                                               placeholder="New label name">
                                    </td>

                                    <td class="px-4 py-3">
                                        <input type="color"
                                               name="new[colourhex]"
                                               value="{{ old('new.colourhex', '#6B7280') }}"
                                               class="h-9 w-16 rounded-md border-gray-300 shadow-sm">
                                    </td>

                                    <td class="px-4 py-3 text-sm text-gray-400">
                                        New row
                                    </td>

                                    <td class="px-4 py-3 text-center text-sm text-gray-400 whitespace-nowrap">
                                        &nbsp;
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="p-6 border-t border-gray-200 flex items-center justify-between">
                        <p class="text-sm text-gray-500">
                            Edit rows above, add a new label at the bottom, then save once.
                        </p>

                        <button type="submit"
                                class="px-5 py-2 bg-green-600 text-white rounded hover:bg-green-700"
                                id="save-labels-button">
                            Save Labels
                        </button>
                    </div>
                </form>

                {{-- Shared compact delete form, same pattern as Travellers --}}
                @include('partials.admin.compact-delete-form', [
                    'formId' => 'delete-label-form',
                    'query' => request()->only(['search']),
                ])
            </div>
        </div>
    </div>

    @include('partials.admin.dirty-form-script', [
        'formId' => 'labels-form',
        'filterFormId' => 'labels-filter-form',
        'deleteFormId' => 'delete-label-form',
        'deleteButtonSelector' => '.delete-label-btn',
        'dirtyMessage' => 'You have unsaved changes in the Labels table. Continue and lose those changes?',
        'deleteDirtyMessage' => 'You have unsaved changes in the Labels table. Delete anyway and lose those changes?',
        'deleteConfirmPrefix' => 'Delete label',
        'deleteConfirmSuffix' => 'This cannot be undone.',
    ])
</x-app-layout>