<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Projects</h2>
    </x-slot>

    <div class="py-6">
        <div class="w-full max-w-none mx-auto px-4 sm:px-6 lg:px-8 xl:px-10 2xl:px-12 space-y-6">

            @include('partials.admin.flash-messages')
            @include('partials.admin.validation-summary')

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                {{-- Filters --}}
                <div class="p-6 border-b border-gray-200">
                    <form method="GET"
                          action="{{ route('projects.index') }}"
                          id="projects-filter-form"
                          class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <select name="status" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                <option value="">All</option>
                                @foreach (['planning','active','onhold','completed','archived'] as $s)
                                    <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Owner</label>
                            <select name="ownerid" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                <option value="">All</option>
                                @foreach (\App\Models\User::orderBy('name')->get() as $user)
                                    <option value="{{ $user->id }}" @selected((string) request('ownerid') === (string) $user->id)>
                                        {{ $user->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="flex items-end gap-2">
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                                Filter
                            </button>

                            <a href="{{ route('projects.index') }}"
                               class="px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300"
                               id="projects-reset-link">
                                Reset
                            </a>
                        </div>
                    </form>
                </div>

                {{-- Bulk save form --}}
                <form method="POST"
                      action="{{ route('projects.update') }}"
                      id="projects-form">
                    @csrf
                    @method('PATCH')

                    {{-- Preserve filter state --}}
                    <input type="hidden" name="status" value="{{ request('status') }}">
                    <input type="hidden" name="ownerid" value="{{ request('ownerid') }}">

                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[1100px] divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase w-[30%]">Name</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase w-[12%]">Status</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase w-[12%]">Start</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase w-[12%]">Target</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase w-[10%]">Colour</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase w-[14%]">Owner</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase w-[10%]">Actions</th>
                                </tr>
                            </thead>

                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($projects as $project)
                                    <tr>
                                        <td class="px-4 py-3">
                                            <input type="text"
                                                   name="projects[{{ $project->id }}][projectname]"
                                                   value="{{ old("projects.{$project->id}.projectname", $project->projectname) }}"
                                                   class="w-full min-w-[260px] rounded-md border-gray-300 shadow-sm"
                                                   required>
                                        </td>

                                        <td class="px-4 py-3">
                                            <select name="projects[{{ $project->id }}][status]"
                                                    class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                                @foreach (['planning','active','onhold','completed','archived'] as $s)
                                                    <option value="{{ $s }}" @selected(old("projects.{$project->id}.status", $project->status) === $s)>
                                                        {{ ucfirst($s) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>

                                        <td class="px-4 py-3">
                                            <input type="date"
                                                   name="projects[{{ $project->id }}][startdate]"
                                                   value="{{ old("projects.{$project->id}.startdate", $project->startdate?->format('Y-m-d')) }}"
                                                   class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                        </td>

                                        <td class="px-4 py-3">
                                            <input type="date"
                                                   name="projects[{{ $project->id }}][targetdate]"
                                                   value="{{ old("projects.{$project->id}.targetdate", $project->targetdate?->format('Y-m-d')) }}"
                                                   class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                        </td>

                                        <td class="px-4 py-3">
                                            <input type="color"
                                                   name="projects[{{ $project->id }}][colourhex]"
                                                   value="{{ old("projects.{$project->id}.colourhex", $project->colourhex) }}"
                                                   class="h-9 w-16 rounded-md border-gray-300 shadow-sm">
                                        </td>

                                        <td class="px-4 py-3">
                                            <select name="projects[{{ $project->id }}][ownerid]"
                                                    class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                                <option value="">—</option>
                                                @foreach (\App\Models\User::orderBy('name')->get() as $user)
                                                    <option value="{{ $user->id }}"
                                                            @selected((string) old("projects.{$project->id}.ownerid", $project->ownerid) === (string) $user->id)>
                                                        {{ $user->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>

                                        <td class="px-4 py-3 text-center whitespace-nowrap">
                                            <button type="button"
                                                    class="inline-flex items-center px-3 py-1.5 bg-green-100 text-green-700 rounded hover:bg-green-200 text-xs mb-1"
                                                    onclick="window.location='{{ route('tasks.index', $project) }}'">
                                                View Tasks
                                            </button>

                                            <button type="button"
                                                    class="inline-flex items-center px-3 py-1.5 bg-indigo-100 text-indigo-700 rounded hover:bg-indigo-200 text-xs mb-1"
                                                    onclick="window.location='{{ route('projects.statuses.index', $project) }}'">
                                                Statuses
                                            </button>

                                            <button type="button"
                                                    class="inline-flex items-center px-3 py-1.5 bg-red-100 text-red-700 rounded hover:bg-red-200 text-xs delete-project-btn"
                                                    data-id="{{ $project->id }}"
                                                    data-name="{{ $project->projectname }}"
                                                    data-action="{{ route('projects.destroy', $project->id) }}">
                                                Delete
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-4 py-6 text-center text-gray-500">
                                            No projects found.
                                        </td>
                                    </tr>
                                @endforelse

                                {{-- New row at the bottom --}}
                                <tr class="bg-blue-50">
                                    <td class="px-4 py-3">
                                        <input type="text"
                                               name="new[projectname]"
                                               value="{{ old('new.projectname') }}"
                                               class="w-full min-w-[260px] rounded-md border-gray-300 shadow-sm"
                                               placeholder="New project name">
                                    </td>

                                    <td class="px-4 py-3">
                                        <select name="new[status]"
                                                class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                            @foreach (['planning','active','onhold','completed','archived'] as $s)
                                                <option value="{{ $s }}" @selected(old('new.status') === $s)>
                                                    {{ ucfirst($s) }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>

                                    <td class="px-4 py-3">
                                        <input type="date"
                                               name="new[startdate]"
                                               value="{{ old('new.startdate') }}"
                                               class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                    </td>

                                    <td class="px-4 py-3">
                                        <input type="date"
                                               name="new[targetdate]"
                                               value="{{ old('new.targetdate') }}"
                                               class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                    </td>

                                    <td class="px-4 py-3">
                                        <input type="color"
                                               name="new[colourhex]"
                                               value="{{ old('new.colourhex', '#6366F1') }}"
                                               class="h-9 w-16 rounded-md border-gray-300 shadow-sm">
                                    </td>

                                    <td class="px-4 py-3">
                                        <select name="new[ownerid]"
                                                class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                            <option value="">—</option>
                                            @foreach (\App\Models\User::orderBy('name')->get() as $user)
                                                <option value="{{ $user->id }}" @selected(old('new.ownerid') == $user->id)>
                                                    {{ $user->name }}
                                                </option>
                                            @endforeach
                                        </select>
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
                            Edit rows above, add a new project at the bottom, then save once.
                        </p>

                        <button type="submit"
                                class="px-5 py-2 bg-green-600 text-white rounded hover:bg-green-700"
                                id="save-projects-button">
                            Save Projects
                        </button>
                    </div>
                </form>

                @include('partials.admin.compact-delete-form', [
                    'formId' => 'delete-project-form',
                    'query' => request()->only(['status', 'ownerid']),
                ])
            </div>
        </div>
    </div>

    @include('partials.admin.dirty-form-script', [
        'formId' => 'projects-form',
        'filterFormId' => 'projects-filter-form',
        'deleteFormId' => 'delete-project-form',
        'deleteButtonSelector' => '.delete-project-btn',
        'dirtyMessage' => 'You have unsaved changes in the Projects table. Continue and lose those changes?',
        'deleteDirtyMessage' => 'You have unsaved changes in the Projects table. Delete anyway and lose those changes?',
        'deleteConfirmPrefix' => 'Delete project',
        'deleteConfirmSuffix' => 'This cannot be undone.',
    ])
</x-app-layout>