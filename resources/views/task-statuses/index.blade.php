<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $project->projectname ?? 'Global' }} Task Statuses
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="w-full max-w-none mx-auto px-4 sm:px-6 lg:px-8 xl:px-10 2xl:px-12 space-y-6">

            @include('partials.admin.flash-messages')
            @include('partials.admin.validation-summary')

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 border-b border-gray-200">
                    <p class="text-sm text-gray-600">
                        Edit existing statuses inline and add a new status at the bottom, then save once.
                        {{ $project ? 'These statuses apply only to this project.' : 'These are global defaults used when new projects are created.' }}
                    </p>
                </div>

                <form method="POST"
                      action="{{ $project ? route('projects.statuses.update', $project) : route('task-statuses.defaults.update') }}"
                      id="task-statuses-form">
                    @csrf
                    @method('PATCH')

                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[900px] divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase w-[26%]">Label</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase w-[18%]">Code</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase w-[16%]">Colour</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase w-[10%]">Completed?</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase w-[10%]">Sort</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase w-[10%]">Active</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase w-[10%]">Actions</th>
                                </tr>
                            </thead>

                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($statuses as $status)
                                    <tr>
                                        <td class="px-4 py-3">
                                            <input type="text"
                                                   name="statuses[{{ $status->id }}][statuslabel]"
                                                   value="{{ old("statuses.{$status->id}.statuslabel", $status->statuslabel) }}"
                                                   class="w-full min-w-[220px] rounded-md border-gray-300 shadow-sm"
                                                   required>
                                        </td>

                                        <td class="px-4 py-3">
                                            <input type="text"
                                                   name="statuses[{{ $status->id }}][statuscode]"
                                                   value="{{ old("statuses.{$status->id}.statuscode", $status->statuscode) }}"
                                                   class="w-full min-w-[140px] rounded-md border-gray-300 shadow-sm"
                                                   required>
                                        </td>

                                        <td class="px-4 py-3">
                                            <input type="color"
                                                   name="statuses[{{ $status->id }}][colourhex]"
                                                   value="{{ old("statuses.{$status->id}.colourhex", $status->colourhex) }}"
                                                   class="h-9 w-16 rounded-md border-gray-300 shadow-sm">
                                        </td>

                                        <td class="px-4 py-3 text-center">
                                            <input type="hidden" name="statuses[{{ $status->id }}][iscompletedstatus]" value="0">
                                            <input type="checkbox"
                                                   name="statuses[{{ $status->id }}][iscompletedstatus]"
                                                   value="1"
                                                   class="rounded border-gray-300 text-blue-600 shadow-sm"
                                                   @checked(old("statuses.{$status->id}.iscompletedstatus", $status->iscompletedstatus))>
                                        </td>

                                        <td class="px-4 py-3">
                                            <input type="number"
                                                   name="statuses[{{ $status->id }}][sortorder]"
                                                   value="{{ old("statuses.{$status->id}.sortorder", $status->sortorder) }}"
                                                   class="w-20 rounded-md border-gray-300 shadow-sm">
                                        </td>

                                        <td class="px-4 py-3 text-center">
                                            <input type="hidden" name="statuses[{{ $status->id }}][isactive]" value="0">
                                            <input type="checkbox"
                                                   name="statuses[{{ $status->id }}][isactive]"
                                                   value="1"
                                                   class="rounded border-gray-300 text-blue-600 shadow-sm"
                                                   @checked(old("statuses.{$status->id}.isactive", $status->isactive))>
                                        </td>

                                        <td class="px-4 py-3 text-center whitespace-nowrap">
                                            <button type="button"
                                                    class="px-3 py-1.5 bg-red-100 text-red-700 rounded hover:bg-red-200 text-xs delete-status-btn"
                                                    data-id="{{ $status->id }}"
                                                    data-name="{{ $status->statuslabel }}"
                                                    data-action="{{ route('task-statuses.destroy', $status->id) }}">
                                                Delete
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-4 py-6 text-center text-gray-500">
                                            No statuses defined.
                                        </td>
                                    </tr>
                                @endforelse

                                {{-- New row at the bottom --}}
                                <tr class="bg-blue-50">
                                    <td class="px-4 py-3">
                                        <input type="text"
                                               name="new[statuslabel]"
                                               value="{{ old('new.statuslabel') }}"
                                               class="w-full min-w-[220px] rounded-md border-gray-300 shadow-sm"
                                               placeholder="New status label">
                                    </td>

                                    <td class="px-4 py-3">
                                        <input type="text"
                                               name="new[statuscode]"
                                               value="{{ old('new.statuscode') }}"
                                               class="w-full min-w-[140px] rounded-md border-gray-300 shadow-sm"
                                               placeholder="New code">
                                    </td>

                                    <td class="px-4 py-3">
                                        <input type="color"
                                               name="new[colourhex]"
                                               value="{{ old('new.colourhex', '#94A3B8') }}"
                                               class="h-9 w-16 rounded-md border-gray-300 shadow-sm">
                                    </td>

                                    <td class="px-4 py-3 text-center">
                                        <input type="hidden" name="new[iscompletedstatus]" value="0">
                                        <input type="checkbox"
                                               name="new[iscompletedstatus]"
                                               value="1"
                                               class="rounded border-gray-300 text-blue-600 shadow-sm"
                                               @checked(old('new.iscompletedstatus', false))>
                                    </td>

                                    <td class="px-4 py-3">
                                        <input type="number"
                                               name="new[sortorder]"
                                               value="{{ old('new.sortorder') }}"
                                               class="w-20 rounded-md border-gray-300 shadow-sm"
                                               placeholder="Auto">
                                    </td>

                                    <td class="px-4 py-3 text-center">
                                        <input type="hidden" name="new[isactive]" value="0">
                                        <input type="checkbox"
                                               name="new[isactive]"
                                               value="1"
                                               class="rounded border-gray-300 text-blue-600 shadow-sm"
                                               @checked(old('new.isactive', true))>
                                    </td>

                                    <td class="px-4 py-3 text-center text-xs text-gray-400 whitespace-nowrap">
                                        New row
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="p-6 border-t border-gray-200 flex items-center justify-between">
                        <p class="text-sm text-gray-500">
                            Edit rows above, add a new status at the bottom, then save once.
                        </p>

                        <button type="submit"
                                class="px-5 py-2 bg-green-600 text-white rounded hover:bg-green-700"
                                id="save-statuses-button">
                            Save Statuses
                        </button>
                    </div>
                </form>

                @include('partials.admin.compact-delete-form', [
                    'formId' => 'delete-task-status-form',
                    'query' => $project ? ['project' => $project->id] : [],
                ])
            </div>
        </div>
    </div>

    @include('partials.admin.dirty-form-script', [
        'formId' => 'task-statuses-form',
        'filterFormId' => null,
        'deleteFormId' => 'delete-task-status-form',
        'deleteButtonSelector' => '.delete-status-btn',
        'dirtyMessage' => 'You have unsaved changes in the Task Statuses table. Continue and lose those changes?',
        'deleteDirtyMessage' => 'You have unsaved changes in the Task Statuses table. Delete anyway and lose those changes?',
        'deleteConfirmPrefix' => 'Delete status',
        'deleteConfirmSuffix' => 'This cannot be undone.',
    ])
</x-app-layout>