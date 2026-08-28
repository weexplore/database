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

                            <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                <input type="checkbox"
                                    name="templatesonly"
                                    value="1"
                                    class="rounded border-gray-300 text-indigo-600 shadow-sm"
                                    @checked($templatesOnly)>

                                <span>Show recurring tasks</span>
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
                        <input type="hidden" name="search" value="{{ $search }}">
                        <input type="hidden" name="hideclosed" value="{{ $hideClosed ? '1' : '0' }}">
                        <input type="hidden" name="templatesonly" value="{{ $templatesOnly ? '1' : '0' }}">
                        <input type="hidden" name="page" value="{{ request('page', 1) }}">

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
                                        <div class="flex flex-wrap items-center gap-2">
                                            <a href="{{ route('tasks.show', [
                                                    'task' => $task,
                                                    'from' => 'alltasks',
                                                    'return_url' => request()->fullUrl(),
                                                ]) }}"
                                            class="text-indigo-700 hover:underline">
                                                {{ $task->tasktitle }}
                                            </a>

                                            @if ($task->recurrence)
                                                <span class="inline-flex items-center gap-1 rounded-full border px-2 py-0.5 text-[11px] font-semibold shadow-sm"
                                                    style="border-color: #a78bfa; background-color: #f5f3ff; color: #5b21b6;"
                                                    title="Active recurring task">
                                                    <svg xmlns="http://www.w3.org/2000/svg"
                                                        class="h-3.5 w-3.5"
                                                        viewBox="0 0 20 20"
                                                        fill="currentColor"
                                                        aria-hidden="true">
                                                        <path fill-rule="evenodd"
                                                            d="M15.312 11.424a5.5 5.5 0 0 1-9.625 1.258.75.75 0 1 0-1.14.974 7 7 0 0 0 12.258-1.601.75.75 0 0 0-1.493-.2ZM4.688 8.576a5.5 5.5 0 0 1 9.625-1.258.75.75 0 1 0 1.14-.974A7 7 0 0 0 3.195 7.945a.75.75 0 0 0 1.493.2Z"
                                                            clip-rule="evenodd" />
                                                        <path d="M13.5 3.5a.75.75 0 0 1 .75-.75h2.5a.75.75 0 0 1 .75.75v2.5a.75.75 0 0 1-1.5 0V5.31l-1.22 1.22a.75.75 0 0 1-1.06-1.06l1.22-1.22h-.69a.75.75 0 0 1-.75-.75ZM6.5 16.5a.75.75 0 0 1-.75.75h-2.5a.75.75 0 0 1-.75-.75v-2.5a.75.75 0 0 1 1.5 0v.69l1.22-1.22a.75.75 0 0 1 1.06 1.06L5.06 15.75h.69a.75.75 0 0 1 .75.75Z" />
                                                    </svg>
                                                    Recurring
                                                </span>
                                            @endif

                                            @if ($task->parentTask)
                                                <a href="{{ route('tasks.show', [
                                                    'task' => $task->parentTask,
                                                    'from' => 'alltasks',
                                                    'return_url' => request()->fullUrl(),
                                                ]) }}"
                                                class="inline-flex items-center gap-1 rounded-full border px-2 py-0.5 text-[11px] font-semibold shadow-sm hover:opacity-80"
                                                style="border-color: #4ade80; background-color: #f0fdf4; color: #166534;"
                                                title="Child of: {{ $task->parentTask->tasktitle }}">
                                                    <svg xmlns="http://www.w3.org/2000/svg"
                                                        class="h-3.5 w-3.5"
                                                        viewBox="0 0 20 20"
                                                        fill="currentColor"
                                                        aria-hidden="true">
                                                        <path fill-rule="evenodd"
                                                            d="M3 4.75A1.75 1.75 0 0 1 4.75 3h3.5A1.75 1.75 0 0 1 10 4.75v3.5A1.75 1.75 0 0 1 8.25 10H6.5v2.25A1.75 1.75 0 0 0 8.25 14H12v1.25A1.75 1.75 0 0 0 13.75 17h1.5A1.75 1.75 0 0 0 17 15.25v-3.5A1.75 1.75 0 0 0 15.25 10h-1.5A1.75 1.75 0 0 0 12 11.75V12.5H8.25a.25.25 0 0 1-.25-.25V10h.25A1.75 1.75 0 0 0 10 8.25v-3.5A1.75 1.75 0 0 0 8.25 3h-3.5A1.75 1.75 0 0 0 3 4.75Zm1.75-.25a.25.25 0 0 0-.25.25v3.5c0 .138.112.25.25.25h3.5a.25.25 0 0 0 .25-.25v-3.5a.25.25 0 0 0-.25-.25h-3.5Zm9 7.75a.25.25 0 0 0-.25.25v3.5c0 .138.112.25.25.25h1.5a.25.25 0 0 0 .25-.25v-3.5a.25.25 0 0 0-.25-.25h-1.5Z"
                                                            clip-rule="evenodd" />
                                                    </svg>
                                                    Child task
                                                </a>
                                            @endif

                                            @if ($task->subtasks_count > 0)
                                                <span class="inline-flex items-center gap-1 rounded-full border px-2 py-0.5 text-[11px] font-semibold shadow-sm"
                                                    style="border-color: #38bdf8; background-color: #f0f9ff; color: #075985;"
                                                    title="{{ $task->open_subtasks_count }} open of {{ $task->subtasks_count }} total subtask{{ $task->subtasks_count === 1 ? '' : 's' }}">
                                                    <svg xmlns="http://www.w3.org/2000/svg"
                                                        class="h-3.5 w-3.5"
                                                        viewBox="0 0 20 20"
                                                        fill="currentColor"
                                                        aria-hidden="true">
                                                        <path fill-rule="evenodd"
                                                            d="M3.75 3.5A1.25 1.25 0 0 1 5 2.25h9A1.25 1.25 0 0 1 15.25 3.5v4A1.25 1.25 0 0 1 14 8.75H9v1.5h5.25A1.75 1.75 0 0 1 16 12v3.25A1.75 1.75 0 0 1 14.25 17h-2.5A1.75 1.75 0 0 1 10 15.25V12A1.75 1.75 0 0 1 11.75 10.25H7.5V8.75H5A1.25 1.25 0 0 1 3.75 7.5v-4ZM5 3.75a.25.25 0 0 0-.25.25v3a.25.25 0 0 0 .25.25h9a.25.25 0 0 0 .25-.25V4a.25.25 0 0 0-.25-.25H5Zm6.75 7.75a.25.25 0 0 0-.25.25v3.5c0 .138.112.25.25.25h2.5a.25.25 0 0 0 .25-.25v-3.5a.25.25 0 0 0-.25-.25h-2.5Z"
                                                            clip-rule="evenodd" />
                                                    </svg>

                                                    {{ $task->open_subtasks_count }} of {{ $task->subtasks_count }}
                                                    open subtask{{ $task->subtasks_count === 1 ? '' : 's' }}
                                                </span>
                                            @endif
                                        </div>
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
                                        {{-- View button with return URL --}}
                                        <a href="{{ route('tasks.show', [
                                                'task'   => $task,
                                                'from'   => 'alltasks',
                                                'return_url' => request()->fullUrl(),
                                            ]) }}"
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