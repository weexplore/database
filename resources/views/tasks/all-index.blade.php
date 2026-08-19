<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            All Tasks
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="w-full max-w-none mx-auto px-4 sm:px-6 lg:px-8 xl:px-10 2xl:px-12 space-y-6">

            @include('partials.admin.flash-messages')
            @include('partials.admin.validation-summary')

            <div class="bg-white shadow-sm rounded-lg">
                <div class="px-4 py-3 border-b border-gray-200 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900">All Tasks</h3>
                        <p class="mt-1 text-xs text-gray-600">
                            List of all tasks with start &amp; due dates, grouped by project. Click a row to view the task.
                        </p>
                    </div>
                </div>

                {{-- Filters --}}
                <div class="px-4 pt-4 pb-2 border-b border-gray-100">
                    <form method="GET"
                        action="{{ route('tasksall.all') }}"
                        class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">

                        <div>
                            <label class="block text-xs font-medium text-gray-600">Search</label>
                            <input type="text" name="search" value="{{ $search }}"
                                placeholder="Search title or description…"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-600">Project</label>
                            <select name="projectid" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                                <option value="">All projects</option>
                                @foreach ($projects as $proj)
                                    <option value="{{ $proj->id }}" @selected((string) $projectId === (string) $proj->id)>
                                        {{ $proj->projectname }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-600">Label</label>
                            <select name="labelid" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                                <option value="">All labels</option>
                                @foreach ($labels as $label)
                                    <option value="{{ $label->id }}" @selected((string) $labelId === (string) $label->id)>
                                        {{ $label->labelname }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="flex items-center gap-2">
                            <input type="hidden" name="hideclosed" value="0">
                            <input type="checkbox" name="hideclosed" value="1"
                                id="hideclosed"
                                @checked($hideClosed)
                                class="rounded border-gray-300 text-blue-600 shadow-sm">
                            <label for="hideclosed" class="text-xs font-medium text-gray-600">
                                Hide closed tasks
                            </label>
                        </div>

                        <div class="md:col-span-4 flex gap-2 justify-end">
                            <button type="submit"
                                    class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-xs font-semibold rounded hover:bg-blue-700">
                                Apply filters
                            </button>
                            <a href="{{ route('tasksall.all') }}"
                            class="inline-flex items-center px-3 py-2 bg-gray-100 text-gray-700 text-xs font-medium rounded border border-gray-300 hover:bg-gray-200">
                                Clear
                            </a>
                        </div>
                    </form>
                </div>

                {{-- Tasks table --}}
                <div class="px-4 pb-4 overflow-x-auto">
                    @php
                        $dir = $currentDir === 'asc' ? 'desc' : 'asc';

                        function sort_link(string $column, string $label, string $currentSort, string $currentDir) {
                            $params = array_merge(request()->all(), [
                                'sort' => $column,
                                'dir'  => ($currentSort === $column && $currentDir === 'asc') ? 'desc' : 'asc',
                            ]);

                            $icon = '';
                            if ($currentSort === $column) {
                                $icon = $currentDir === 'asc' ? '↑' : '↓';
                            }

                            return '<a href="'.e(route('tasksall.all', $params)).'" class="inline-flex items-center gap-1 text-xs font-semibold text-gray-700 hover:text-gray-900">'
                                    .e($label).' <span class="text-[10px] text-gray-400">'.e($icon).'</span></a>';
                        }
                    @endphp

                    <form method="POST" action="{{ route('tasks.bulk-update') }}">
                        @csrf

                        {{-- keep current filters/sort so we can redirect back --}}
                        <input type="hidden" name="projectid" value="{{ $projectId }}">
                        <input type="hidden" name="labelid" value="{{ $labelId }}">
                        <input type="hidden" name="sort" value="{{ $currentSort }}">
                        <input type="hidden" name="dir" value="{{ $currentDir }}">

                        <table class="min-w-full table-auto text-sm">
                            <thead>
                            <tr class="border-b border-gray-200 bg-gray-50">
                                <th class="px-3 py-2 text-left">
                                    {!! sort_link('tasktitle', 'Task', $currentSort, $currentDir) !!}
                                </th>
                                <th class="px-3 py-2 text-left">
                                    {!! sort_link('projectname', 'Project', $currentSort, $currentDir) !!}
                                </th>
                                <th class="px-3 py-2 text-left">
                                    {!! sort_link('startdate', 'Start date', $currentSort, $currentDir) !!}
                                </th>
                                <th class="px-3 py-2 text-left">
                                    {!! sort_link('duedate', 'Due date', $currentSort, $currentDir) !!}
                                </th>
                                <th class="px-3 py-2 text-left">Labels</th>
                                <th class="px-3 py-2 text-left">Status</th>
                                <th class="px-3 py-2"></th>
                            </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                            @forelse ($tasks as $task)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-3 py-2">
                                        <a href="{{ route('tasks.show', ['task' => $task, 'from' => 'alltasks']) }}"
                                        class="text-sm text-blue-700 hover:underline">
                                            {{ $task->tasktitle }}
                                        </a>
                                    </td>
                                    <td class="px-3 py-2 text-xs text-gray-700">
                                        {{ $task->project->projectname ?? '—' }}
                                    </td>
                                    <td class="px-3 py-2 text-xs text-gray-700">
                                        <input type="date"
                                            name="tasks[{{ $task->id }}][startdate]"
                                            value="{{ $task->startdate?->format('Y-m-d') }}"
                                            class="w-full border-gray-300 rounded-md shadow-sm text-xs">
                                    </td>
                                    <td class="px-3 py-2 text-xs text-gray-700">
                                        <input type="date"
                                            name="tasks[{{ $task->id }}][duedate]"
                                            value="{{ $task->duedate?->format('Y-m-d') }}"
                                            class="w-full border-gray-300 rounded-md shadow-sm text-xs">
                                    </td>
                                    <td class="px-3 py-2">
                                        <div class="flex flex-wrap gap-1">
                                            @foreach ($task->labels as $label)
                                                <span class="px-2 py-0.5 rounded-full text-white text-[11px]"
                                                    style="background: {{ $label->colourhex }}">
                                                    {{ $label->labelname }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td class="px-3 py-2 text-xs text-gray-700">
                                        <select name="tasks[{{ $task->id }}][statusid]"
                                                class="w-full border-gray-300 rounded-md shadow-sm text-xs">
                                            @foreach ($task->project->taskStatuses()->orderBy('sortorder')->get() as $status)
                                                <option value="{{ $status->id }}" @selected($task->statusid === $status->id)>
                                                    {{ $status->statuslabel }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="px-3 py-2 text-right">
                                        {{-- optional per-row button if you ever want row-only submits --}}
                                        <a href="{{ route('tasks.show', ['task' => $task, 'from' => 'alltasks']) }}"
                                        class="inline-flex items-center px-2.5 py-1.5 border border-gray-300 rounded text-xs text-gray-700 bg-white hover:bg-gray-100">
                                            View
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-3 py-4 text-center text-xs text-gray-500">
                                        No tasks found for the current filters.
                                    </td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>

                        <div class="mt-3 flex justify-end">
                            <button type="submit"
                                    class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-xs font-semibold rounded hover:bg-green-700">
                                Save changes
                            </button>
                        </div>
                    </form>

                    <div class="mt-3">
                        {{ $tasks->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>