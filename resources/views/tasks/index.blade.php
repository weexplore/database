<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $project->projectname }} — Tasks
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="w-full max-w-none mx-auto px-4 sm:px-6 lg:px-8 xl:px-10 2xl:px-12 space-y-6">

            @include('partials.admin.flash-messages')
            @include('partials.admin.validation-summary')

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-4 border-b border-gray-200 flex items-center justify-between">
                    <a
                        href="{{ route('projects.index') }}"
                        class="inline-flex items-center rounded-md border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                    >
                        ← Back to Projects
                    </a>

                    <form method="GET" action="{{ route('tasks.index', $project) }}" class="flex items-center gap-2">
                        <input type="hidden" name="hideclosed" value="0">
                        <input type="checkbox" name="hideclosed" value="1"
                            id="hideclosed"
                            @checked($hideClosed)
                            class="rounded border-gray-300 text-blue-600 shadow-sm"
                            onchange="this.form.submit()">
                        <label for="hideclosed" class="text-xs font-medium text-gray-600">
                            Hide closed tasks
                        </label>
                    </form>
                </div>

                <form id="new-task-form"
                    method="POST"
                    action="{{ route('tasks.store') }}"
                    class="border-b border-gray-200 p-4 grid grid-cols-1 md:grid-cols-6 gap-4 bg-gray-50">
                    @csrf
                    <input type="hidden" name="projectid" value="{{ $project->id }}">

                    <div>
                        <label class="block text-xs font-medium text-gray-600">Title</label>
                        <input type="text" name="title" required
                            class="mt-1 w-full border-gray-300 rounded-md shadow-sm text-sm">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600">Status</label>
                        <select name="statusid"
                                class="mt-1 w-full border-gray-300 rounded-md shadow-sm text-sm">
                            @foreach ($statuses as $status)
                                <option value="{{ $status->id }}">{{ $status->statuslabel }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600">Priority</label>
                        <select name="priority"
                                class="mt-1 w-full border-gray-300 rounded-md shadow-sm text-sm">
                            <option value="medium" selected>Medium</option>
                            <option value="low">Low</option>
                            <option value="high">High</option>
                            <option value="urgent">Urgent</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600">Start date</label>
                        <input type="date" name="startdate"
                            class="mt-1 w-full border-gray-300 rounded-md shadow-sm text-sm">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600">Due date</label>
                        <input type="date" name="duedate"
                            class="mt-1 w-full border-gray-300 rounded-md shadow-sm text-sm">
                    </div>

                    <div class="flex items-end">
                        <button type="submit"
                                class="px-4 py-2 bg-green-600 text-white text-sm rounded hover:bg-green-700">
                            Create Task
                        </button>
                    </div>
                </form>

                <div class="p-4">
                    <div class="grid grid-cols-1 md:grid-cols-{{ min($statuses->count(), 5) }} gap-4">
                        @foreach ($statuses as $status)
                            <div class="bg-gray-50 rounded-lg p-3 border border-gray-200 status-column"
                                 data-status-id="{{ $status->id }}"
                                 ondragover="event.preventDefault();"
                                 ondrop="window.handleTaskDrop(event)">
                                <h3 class="text-sm font-semibold mb-3 flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full inline-block"
                                          style="background:{{ $status->colourhex }}"></span>
                                    <span>{{ $status->statuslabel }}</span>
                                    <span class="text-gray-400 text-xs">
                                        ({{ $tasks->get($status->id, collect())->count() }})
                                    </span>
                                </h3>

                                <div class="space-y-2">
                                    @foreach ($tasks->get($status->id, collect()) as $task)
                                        <div class="task-card"
                                             draggable="true"
                                             data-task-id="{{ $task->id }}"
                                             data-status-id="{{ $status->id }}">
                                            <a href="{{ route('tasks.show', [
                                                        'task' => $task,
                                                        'from' => 'projecttasks',
                                                        'return' => url()->full(),
                                                    ]) }}"
                                                class="block bg-white rounded-md shadow-sm p-3 text-sm hover:shadow-md transition">
                                                <div class="font-medium">{{ $task->tasktitle }}</div>
                                                @if ($task->startdate)
                                                    <div class="text-xs text-gray-500 mt-1">
                                                        Start {{ $task->startdate->format('d M Y') }}
                                                    </div>
                                                @endif

                                                @if ($task->duedate)
                                                    <div class="text-xs text-gray-500 mt-1">
                                                        Due {{ $task->duedate->format('d M Y') }}
                                                    </div>
                                                @endif
                                                @if ($task->priority)
                                                    @php
                                                        $priority = strtolower($task->priority);

                                                        $priorityStyles = match ($priority) {
                                                            'urgent' => [
                                                                'label' => 'Urgent',
                                                                'border' => '#ef4444',
                                                                'background' => '#fef2f2',
                                                                'colour' => '#991b1b',
                                                                'icon' => '!',
                                                            ],
                                                            'high' => [
                                                                'label' => 'High',
                                                                'border' => '#f97316',
                                                                'background' => '#fff7ed',
                                                                'colour' => '#9a3412',
                                                                'icon' => '↑',
                                                            ],
                                                            'low' => [
                                                                'label' => 'Low',
                                                                'border' => '#94a3b8',
                                                                'background' => '#f8fafc',
                                                                'colour' => '#475569',
                                                                'icon' => '↓',
                                                            ],
                                                            default => [
                                                                'label' => 'Medium',
                                                                'border' => '#eab308',
                                                                'background' => '#fefce8',
                                                                'colour' => '#854d0e',
                                                                'icon' => '—',
                                                            ],
                                                        };
                                                    @endphp

                                                    <div class="mt-2">
                                                        <span class="inline-flex items-center gap-1 rounded-full border px-2 py-0.5 text-[11px] font-semibold shadow-sm"
                                                            style="border-color: {{ $priorityStyles['border'] }}; background-color: {{ $priorityStyles['background'] }}; color: {{ $priorityStyles['colour'] }};"
                                                            title="Priority: {{ $priorityStyles['label'] }}">
                                                            <span class="inline-flex h-3.5 w-3.5 items-center justify-center rounded-full border text-[10px] leading-none"
                                                                style="border-color: {{ $priorityStyles['border'] }};">
                                                                {{ $priorityStyles['icon'] }}
                                                            </span>
                                                            {{ $priorityStyles['label'] }}
                                                        </span>
                                                    </div>
                                                @endif
                                                @if ($task->recurrence || $task->subtasks_count > 0 || $task->parentTask)
                                                    <div class="mt-2 flex flex-wrap items-center gap-1.5">
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
                                                        @if ($task->parentTask)
                                                            <span class="inline-flex items-center gap-1 rounded-full border px-2 py-0.5 text-[11px] font-semibold shadow-sm"
                                                                style="border-color: #4ade80; background-color: #f0fdf4; color: #166534;"
                                                                title="Child of: {{ $task->parentTask->tasktitle }}">
                                                                ↳ Child task
                                                            </span>
                                                        @endif
                                                    </div>
                                                @endif

                                                @if ($task->labels->count())
                                                    <div class="flex flex-wrap gap-1 mt-2">
                                                        @foreach ($task->labels as $label)
                                                            <span class="text-xs px-2 py-0.5 rounded-full text-white"
                                                                  style="background:{{ $label->colourhex }}">
                                                                {{ $label->labelname }}
                                                            </span>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </a>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Simple HTML5 drag-and-drop handler to move tasks between statuses --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const cards = document.querySelectorAll('.task-card');

            cards.forEach(card => {
                card.addEventListener('dragstart', event => {
                    event.dataTransfer.effectAllowed = 'move';
                    event.dataTransfer.setData('text/plain', card.dataset.taskId);
                    event.dataTransfer.setData('statusId', card.dataset.statusId);
                });
            });

            window.handleTaskDrop = function (event) {
                event.preventDefault();

                const taskId = event.dataTransfer.getData('text/plain');
                const fromStatusId = event.dataTransfer.getData('statusId');
                const toStatusId = event.currentTarget.dataset.statusId;

                if (!taskId || !toStatusId || fromStatusId === toStatusId) {
                    return;
                }

                const csrfToken = document
                    .querySelector('meta[name="csrf-token"]')
                    ?.getAttribute('content');

                fetch(`/tasks/${taskId}/move-status`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ statusid: toStatusId }),
                }).then(response => {
                    if (response.ok) {
                        window.location.reload();
                    } else {
                        console.error('Failed to move task', response.status);
                    }
                }).catch(error => {
                    console.error('Error moving task', error);
                });
            };
        });
    </script>
</x-app-layout>