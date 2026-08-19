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
                    class="border-b border-gray-200 p-4 grid grid-cols-1 md:grid-cols-5 gap-4 bg-gray-50">
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
                                            <a href="{{ route('tasks.show', $task) }}"
                                               class="block bg-white rounded-md shadow-sm p-3 text-sm hover:shadow-md transition">
                                                <div class="font-medium">{{ $task->tasktitle }}</div>

                                                @if ($task->duedate)
                                                    <div class="text-xs text-gray-500 mt-1">
                                                        Due {{ $task->duedate->format('d M Y') }}
                                                    </div>
                                                @endif
                                                @if (optional($task->recurrence)->isactive)
                                                    <div class="mt-1 text-[11px] text-emerald-700 flex items-center gap-1">
                                                        <span class="inline-block w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                        Recurring task
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