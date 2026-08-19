<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $task->tasktitle }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="w-full max-w-none mx-auto px-4 sm:px-6 lg:px-8 xl:px-10 2xl:px-12 space-y-6">

            @include('partials.admin.flash-messages')
            @include('partials.admin.validation-summary')

            @php
                $from = request('from');
                $returnUrl = request('return'); // may be null
            @endphp

            

            @if ($from === 'alltasks')
                <button type="button"
                        onclick="window.location.href='{{ $returnUrl ?: route('tasksall.all') }}'"
                        class="inline-flex items-center px-3 py-1.5 bg-gray-100 text-gray-700 text-xs font-medium rounded border border-gray-300 hover:bg-gray-200">
                    ← Back to All Tasks
                </button>
            @else
                <button type="button"
                        onclick="window.location.href='{{ route('tasks.index', $task->projectid) }}'"
                        class="inline-flex items-center px-3 py-1.5 bg-gray-100 text-gray-700 text-xs font-medium rounded border border-gray-300 hover:bg-gray-200">
                    ← Back to Tasks
                </button>
            @endif

            {{-- Main edit card --}}
            <div class="bg-white shadow-sm rounded-lg p-6 space-y-4">
                <form method="POST" action="{{ route('tasks.update', $task) }}" class="space-y-4">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="from" value="{{ $from }}">
                    <input type="hidden" name="return_url" value="{{ $returnUrl }}">


                    <div>
                        <label class="block text-xs font-medium text-gray-600">Title</label>
                        <input type="text" name="title" value="{{ $task->tasktitle }}" required
                               class="mt-1 w-full border-gray-300 rounded-md shadow-sm text-sm">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600">Description</label>
                        <textarea name="description" rows="4"
                                  class="mt-1 w-full border-gray-300 rounded-md shadow-sm text-sm">{{ $task->description }}</textarea>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-600">Status</label>
                            <select name="statusid" class="mt-1 w-full border-gray-300 rounded-md shadow-sm text-sm">
                                @foreach ($task->project->taskStatuses()->orderBy('sortorder')->get() as $status)
                                    <option value="{{ $status->id }}" @selected($task->statusid === $status->id)>
                                        {{ $status->statuslabel }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-600">Priority</label>
                            <select name="priority" class="mt-1 w-full border-gray-300 rounded-md shadow-sm text-sm">
                                @foreach (['low','medium','high','urgent'] as $p)
                                    <option value="{{ $p }}" @selected($task->priority === $p)>{{ ucfirst($p) }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-600">Start date</label>
                            <input type="date" name="startdate" value="{{ $task->startdate?->format('Y-m-d') }}"
                                   class="mt-1 w-full border-gray-300 rounded-md shadow-sm text-sm">
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-600">Due date</label>
                            <input type="date" name="duedate" value="{{ $task->duedate?->format('Y-m-d') }}"
                                   class="mt-1 w-full border-gray-300 rounded-md shadow-sm text-sm">
                        </div>
                    </div>

                    {{-- Labels summary + panel --}}
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="block text-xs font-medium text-gray-600">Labels</label>

                            {{-- Only show the toggle when at least one label is already selected --}}
                            @if ($task->labels->isNotEmpty())
                                <button type="button"
                                        id="toggle-task-labels-panel"
                                        class="text-xs px-2 py-1 rounded border border-gray-300 bg-gray-50 text-gray-700 hover:bg-gray-100">
                                    Add or change labels
                                </button>
                            @endif
                        </div>

                        {{-- Show selected-label chips only when labels exist --}}
                        @if ($task->labels->isNotEmpty())
                            <div id="selected-task-labels-summary" class="flex flex-wrap gap-2 mb-2">
                                @foreach ($task->labels as $label)
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-white text-xs font-medium"
                                        style="background: {{ $label->colourhex }}">
                                        {{ $label->labelname }}
                                    </span>
                                @endforeach
                            </div>
                        @endif

                        {{-- 
                            With no selected labels: panel is open.
                            With labels selected: panel starts hidden and is opened by the button.
                        --}}
                        <div id="task-labels-panel"
                            class="border border-gray-200 rounded-md p-3 bg-gray-50 {{ $task->labels->isNotEmpty() ? 'hidden' : '' }}">
                            <div class="flex flex-wrap gap-3">
                                @foreach ($labels as $label)
                                    <label class="flex items-center gap-2 text-sm">
                                        <input type="checkbox"
                                            class="task-label-checkbox"
                                            name="labelids[]"
                                            value="{{ $label->id }}"
                                            @checked($task->labels->contains($label->id))>

                                        <span class="px-2 py-0.5 rounded-full text-white text-xs"
                                            style="background: {{ $label->colourhex }}">
                                            {{ $label->labelname }}
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-2 border-t border-gray-200">
                        <button type="submit"
                                class="px-5 py-2 bg-green-600 text-white text-sm rounded hover:bg-green-700">
                            Save Task
                        </button>
                    </div>
                </form>
            </div>

            {{-- Toggle Recurring settings --}}
            <div class="flex items-center justify-end mb-2">
                <button type="button"
                        id="toggle-recurring-panel"
                        class="text-xs px-3 py-1 rounded border border-slate-300 bg-slate-50 text-slate-700 hover:bg-slate-100">
                    Recurring settings
                </button>
            </div>

            {{-- Recurring panel --}}
            <div id="recurring-panel"
                 class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-slate-200 hidden">
                <div class="px-4 py-3 border-b border-slate-200 bg-slate-50">
                    <h3 class="text-sm font-semibold text-slate-800">Recurring Task</h3>
                    <p class="mt-1 text-xs text-slate-700">
                        Treat this task as a template and generate new tasks on a schedule.
                    </p>
                </div>

                <div class="p-4">
                    <form method="POST"
                          action="{{ route('tasks.update-recurrence', $task) }}"
                          class="space-y-4">
                        @csrf
                        @method('POST')

                        <input type="hidden" name="from" value="{{ $from }}">
                        <input type="hidden" name="return_url" value="{{ $returnUrl }}">


                        {{-- Core recurrence fields in a wider grid --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-600">Frequency</label>
                                <select name="frequency"
                                        id="recurrence-frequency"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                                    <option value="">None</option>
                                    @foreach (['daily','weekly','monthly','yearly'] as $freq)
                                        <option value="{{ $freq }}"
                                            @selected(optional($task->recurrence)->frequency === $freq)>
                                            {{ ucfirst($freq) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-600">Every N units</label>
                                <input type="number" name="intervalcount" min="1"
                                       value="{{ optional($task->recurrence)->intervalcount ?? 1 }}"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-600">Lead days before due</label>
                                <input type="number" name="leaddaysbeforedue" min="0"
                                       value="{{ optional($task->recurrence)->leaddaysbeforedue ?? 0 }}"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-600">Start on</label>
                                <input type="date" name="startsonoccurrence"
                                       value="{{ optional($task->recurrence)->startsonoccurrence?->format('Y-m-d') }}"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-600">End by (optional)</label>
                                <input type="date" name="endsonoccurrence"
                                       value="{{ optional($task->recurrence)->endsonoccurrence?->format('Y-m-d') }}"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-600">Max occurrences</label>
                                <input type="number" name="maxoccurrences" min="1"
                                       value="{{ optional($task->recurrence)->maxoccurrences }}"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                            </div>

                            <div class="flex items-center mt-5">
                                <input type="hidden" name="isactive" value="0">
                                <input type="checkbox" name="isactive" value="1"
                                       class="rounded border-gray-300 text-blue-600 shadow-sm"
                                       @checked(optional($task->recurrence)->isactive ?? false)>
                                <span class="ml-2 text-xs text-gray-700">Recurrence active</span>
                            </div>
                        </div>

                        {{-- Monthly pattern block --}}
                        <div id="monthly-pattern-block"
                             class="mt-4 border-t border-gray-200 pt-4 hidden">
                            <h4 class="text-xs font-semibold text-gray-900">Monthly pattern</h4>

                            <div class="mt-3 grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-xs font-medium text-gray-600">Pattern type</label>
                                    <select name="monthlypattern"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                                        <option value="day_of_month"
                                            @selected(optional($task->recurrence)->monthlypattern === 'day_of_month')>
                                            On day of month
                                        </option>
                                        <option value="nth_weekday"
                                            @selected(optional($task->recurrence)->monthlypattern === 'nth_weekday')>
                                            On Nth weekday
                                        </option>
                                        <option value="last_day"
                                            @selected(optional($task->recurrence)->monthlypattern === 'last_day')>
                                            On last day of month
                                        </option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-xs font-medium text-gray-600">Day of month</label>
                                    <input type="number" name="monthday" min="1" max="31"
                                           value="{{ optional($task->recurrence)->monthday }}"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                                    <p class="mt-1 text-[11px] text-gray-500">
                                        Used when pattern is “On day of month”.
                                    </p>
                                </div>

                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600">Week number</label>
                                        <select name="monthweeknumber"
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                                            <option value="1"  @selected(optional($task->recurrence)->monthweeknumber === 1)>First</option>
                                            <option value="2"  @selected(optional($task->recurrence)->monthweeknumber === 2)>Second</option>
                                            <option value="3"  @selected(optional($task->recurrence)->monthweeknumber === 3)>Third</option>
                                            <option value="4"  @selected(optional($task->recurrence)->monthweeknumber === 4)>Fourth</option>
                                            <option value="-1" @selected(optional($task->recurrence)->monthweeknumber === -1)>Last</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-medium text-gray-600">Weekday</label>
                                        <select name="monthweekday"
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                                            <option value="0" @selected(optional($task->recurrence)->monthweekday === 0)>Sunday</option>
                                            <option value="1" @selected(optional($task->recurrence)->monthweekday === 1)>Monday</option>
                                            <option value="2" @selected(optional($task->recurrence)->monthweekday === 2)>Tuesday</option>
                                            <option value="3" @selected(optional($task->recurrence)->monthweekday === 3)>Wednesday</option>
                                            <option value="4" @selected(optional($task->recurrence)->monthweekday === 4)>Thursday</option>
                                            <option value="5" @selected(optional($task->recurrence)->monthweekday === 5)>Friday</option>
                                            <option value="6" @selected(optional($task->recurrence)->monthweekday === 6)>Saturday</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <p class="mt-2 text-[11px] text-gray-500">
                                “Day of month” uses the day number. “Nth weekday” uses week number and weekday.
                                “Last day” automatically adjusts for 28/30/31‑day months.
                            </p>
                        </div>

                        <div class="flex items-center justify-between pt-2 border-t border-gray-200">
                            <p class="text-xs text-gray-500">
                                Save to update this task’s recurrence pattern.
                            </p>
                            <button type="submit"
                                    class="px-4 py-2 bg-blue-600 text-white text-xs font-semibold rounded hover:bg-blue-700">
                                Save Recurrence
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Delete panel, matching Destination Items style --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-red-200">
                <div class="px-4 py-3 border-b border-red-200 bg-red-50">
                    <h3 class="text-sm font-semibold text-red-800">Delete Task</h3>
                    <p class="mt-1 text-xs text-red-700">
                        This permanently removes the task and any recurrence/template links. This cannot be undone.
                    </p>
                </div>

                <div class="p-4">
                    <form method="POST"
                          action="{{ route('tasks.destroy', $task) }}"
                          onsubmit="return confirm('Delete this task? This cannot be undone.');">
                        @csrf
                        @method('DELETE')

                        <input type="hidden" name="from" value="{{ $from }}">
                        <input type="hidden" name="return_url" value="{{ $returnUrl }}">


                        <div class="flex items-center justify-end">
                            <button type="submit"
                                    class="inline-flex items-center px-4 py-2 border border-red-300 rounded-md text-xs font-semibold text-red-700 bg-white uppercase tracking-widest hover:bg-red-50">
                                Delete Task
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Move Task to another project --}}
            {{-- Toggle for Move Task panel --}}
            <div class="flex items-center justify-end mb-2">
                <button type="button"
                        id="toggle-move-task-panel"
                        class="text-xs px-3 py-1 rounded border border-indigo-300 bg-indigo-50 text-indigo-700 hover:bg-indigo-100">
                    Move task to another project
                </button>
            </div>

            {{-- Move Task panel (hidden by default) --}}
            <div id="move-task-panel"
                 class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-indigo-200 hidden">
                <div class="px-4 py-3 border-b border-indigo-200 bg-indigo-50">
                    <h3 class="text-sm font-semibold text-indigo-800">Move Task to Another Project</h3>
                    <p class="mt-1 text-xs text-indigo-700">
                        Choose a target project. The task will be moved and its status mapped where possible.
                    </p>
                </div>

                <div class="p-4">
                    <form method="POST"
                          action="{{ route('tasks.move-project', $task) }}"
                          onsubmit="return confirm('Move this task to a different project?');">
                        @csrf

                        <input type="hidden" name="from" value="{{ $from }}">
                        <input type="hidden" name="return_url" value="{{ $returnUrl }}">


                        <div class="flex flex-wrap gap-3 items-end">
                            <div class="flex-1 min-w-[220px]">
                                <label class="block text-xs font-medium text-gray-600">Target project</label>
                                <select name="projectid"
                                        class="mt-1 w-full rounded-md border-gray-300 shadow-sm text-sm"
                                        required>
                                    @foreach ($projects as $proj)
                                        <option value="{{ $proj->id }}"
                                                @selected($proj->id === $task->projectid)>
                                            {{ $proj->projectname }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <button type="submit"
                                    class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-xs font-semibold rounded-md hover:bg-indigo-700">
                                Move Task
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Sub-tasks --}}
            <div class="bg-white shadow-sm rounded-lg p-6">
                <div class="flex items-center justify-between gap-3 mb-4">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900">Sub-tasks</h3>
                        <p class="mt-1 text-xs text-gray-500">
                            Create, review, and update tasks attached to “{{ $task->tasktitle }}”.
                        </p>
                    </div>
                </div>

                {{-- Valid external form: table fields attach using form="new-subtask-form" --}}
                <form id="new-subtask-form"
                    method="POST"
                    action="{{ route('tasks.store') }}">
                    @csrf

                    <input type="hidden" name="projectid" value="{{ $task->projectid }}">
                    <input type="hidden" name="parenttaskid" value="{{ $task->id }}">
                    <input type="hidden" name="from" value="{{ $from }}">
                    <input type="hidden" name="return_url" value="{{ $returnUrl }}">
                </form>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 border-y border-gray-200">
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600">
                                    Sub-task
                                </th>
                                <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600">
                                    Start Date
                                </th>
                                <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600">
                                    Due Date
                                </th>
                                <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600">
                                    Priority
                                </th>
                                <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600">
                                    Status
                                </th>
                                <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600">
                                    Labels
                                </th>
                                <th class="px-3 py-2 text-right text-xs font-semibold text-gray-600">
                                    Actions
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-100">
                            {{-- New sub-task row --}}
                            <tr class="bg-blue-50 align-top">
                                <td class="px-3 py-2 min-w-[260px]">
                                    <input form="new-subtask-form"
                                        type="text"
                                        name="title"
                                        required
                                        placeholder="New sub-task"
                                        class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                </td>

                                <td class="px-3 py-2 min-w-[145px]">
                                    <input form="new-subtask-form"
                                        type="date"
                                        name="startdate"
                                        class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                </td>

                                <td class="px-3 py-2 min-w-[145px]">
                                    <input form="new-subtask-form"
                                        type="date"
                                        name="duedate"
                                        class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                </td>

                                <td class="px-3 py-2 min-w-[125px]">
                                    <select form="new-subtask-form"
                                            name="priority"
                                            class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                        <option value="medium" selected>Medium</option>
                                        <option value="low">Low</option>
                                        <option value="high">High</option>
                                        <option value="urgent">Urgent</option>
                                    </select>
                                </td>

                                <td class="px-3 py-2 min-w-[170px]">
                                    <select form="new-subtask-form"
                                            name="statusid"
                                            required
                                            class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                        @foreach ($statuses as $status)
                                            <option value="{{ $status->id }}">
                                                {{ $status->statuslabel }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>

                                <td class="px-3 py-2 min-w-[180px]">
                                    <span class="text-xs text-gray-400">
                                        Add labels in View
                                    </span>
                                </td>

                                <td class="px-3 py-2 whitespace-nowrap text-right">
                                    <button form="new-subtask-form"
                                            type="submit"
                                            class="inline-flex items-center px-3 py-1.5 bg-blue-600 text-white text-xs font-semibold rounded hover:bg-blue-700">
                                        + Add
                                    </button>
                                </td>
                            </tr>

                            {{-- Existing sub-tasks --}}
                            @forelse ($task->subtasks as $sub)
                                {{-- Valid external form; fields in the table row attach to it --}}
                                <form id="subtask-form-{{ $sub->id }}"
                                    method="POST"
                                    action="{{ route('tasks.update', $sub) }}">
                                    @csrf
                                    @method('PATCH')

                                    <input type="hidden" name="from" value="{{ $from }}">
                                    <input type="hidden" name="return_url" value="{{ url()->full() }}">
                                </form>

                                <tr class="hover:bg-gray-50 align-top">
                                    <td class="px-3 py-2 min-w-[260px]">
                                        <input form="subtask-form-{{ $sub->id }}"
                                            type="text"
                                            name="title"
                                            value="{{ $sub->tasktitle }}"
                                            required
                                            class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                    </td>

                                    <td class="px-3 py-2 min-w-[145px]">
                                        <input form="subtask-form-{{ $sub->id }}"
                                            type="date"
                                            name="startdate"
                                            value="{{ $sub->startdate?->format('Y-m-d') }}"
                                            class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                    </td>

                                    <td class="px-3 py-2 min-w-[145px]">
                                        <input form="subtask-form-{{ $sub->id }}"
                                            type="date"
                                            name="duedate"
                                            value="{{ $sub->duedate?->format('Y-m-d') }}"
                                            class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                    </td>

                                    <td class="px-3 py-2 min-w-[125px]">
                                        <select form="subtask-form-{{ $sub->id }}"
                                                name="priority"
                                                class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                            @foreach (['low', 'medium', 'high', 'urgent'] as $priority)
                                                <option value="{{ $priority }}"
                                                        @selected($sub->priority === $priority)>
                                                    {{ ucfirst($priority) }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>

                                    <td class="px-3 py-2 min-w-[170px]">
                                        <select form="subtask-form-{{ $sub->id }}"
                                                name="statusid"
                                                required
                                                class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                            @foreach ($statuses as $status)
                                                <option value="{{ $status->id }}"
                                                        @selected($sub->statusid === $status->id)>
                                                    {{ $status->statuslabel }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>

                                    {{-- Display only labels assigned to this sub-task --}}
                                    <td class="px-3 py-2 min-w-[180px]">
                                        @if ($sub->labels->isNotEmpty())
                                            <div class="flex flex-wrap gap-1">
                                                @foreach ($sub->labels as $label)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-white text-[11px]"
                                                        style="background: {{ $label->colourhex }}">
                                                        {{ $label->labelname }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="text-xs text-gray-400">—</span>
                                        @endif
                                    </td>

                                    <td class="px-3 py-2 whitespace-nowrap text-right">
                                        <button form="subtask-form-{{ $sub->id }}"
                                                type="submit"
                                                class="inline-flex items-center px-3 py-1.5 bg-green-600 text-white text-xs font-semibold rounded hover:bg-green-700">
                                            Save
                                        </button>

                                        <a href="{{ route('tasks.show', [
                                                'task' => $sub,
                                                'from' => $from,
                                                'return' => $returnUrl,
                                            ]) }}"
                                        class="ml-1 inline-flex items-center px-3 py-1.5 border border-gray-300 bg-white text-gray-700 text-xs font-semibold rounded hover:bg-gray-50">
                                            View
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-3 py-5 text-center text-xs text-gray-500">
                                        No sub-tasks yet. Use the blue row above to add one.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Dependencies --}}
            <div class="bg-white shadow-sm rounded-lg border border-indigo-200">
                <div class="px-4 py-3 border-b border-indigo-200 bg-indigo-50">
                    <h3 class="text-sm font-semibold text-indigo-900">Dependencies</h3>
                    <p class="mt-1 text-xs text-indigo-700">
                        Record tasks that must occur before this task. These will be used by Gantt charts.
                    </p>
                </div>

                <div class="p-4 space-y-4">
                    {{-- Add a dependency --}}
                    <form method="POST"
                        action="{{ route('tasks.dependencies.store', $task) }}"
                        class="grid grid-cols-1 md:grid-cols-4 gap-3 items-end">
                        @csrf

                        <div class="md:col-span-2">
                            <label class="block text-xs font-medium text-gray-600">
                                This task depends on
                            </label>

                            <select name="dependsontaskid"
                                    required
                                    class="mt-1 w-full rounded-md border-gray-300 shadow-sm text-sm">
                                <option value="">Select earlier task</option>

                                @foreach ($projectTasks as $otherTask)
                                    <option value="{{ $otherTask->id }}">
                                        {{ $otherTask->tasktitle }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-600">
                                Type
                            </label>

                            <select name="dependencytype"
                                    class="mt-1 w-full rounded-md border-gray-300 shadow-sm text-sm">
                                <option value="FS">Finish to Start</option>
                                <option value="SS">Start to Start</option>
                                <option value="FF">Finish to Finish</option>
                                <option value="SF">Start to Finish</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-600">
                                Lag days
                            </label>

                            <input type="number"
                                name="lagdays"
                                value="0"
                                class="mt-1 w-full rounded-md border-gray-300 shadow-sm text-sm">
                        </div>

                        <div class="md:col-span-4 flex justify-end">
                            <button type="submit"
                                    class="inline-flex items-center justify-center px-4 py-2 bg-indigo-600 text-white text-xs font-semibold rounded hover:bg-indigo-700">
                                Add dependency
                            </button>
                        </div>
                    </form>

                    {{-- Existing dependencies --}}
                    @if ($task->dependencies->isNotEmpty())
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-sm">
                                <thead class="border-y border-gray-200 bg-gray-50">
                                    <tr>
                                        <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600">
                                            Depends on task
                                        </th>
                                        <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600">
                                            Type
                                        </th>
                                        <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600">
                                            Lag
                                        </th>
                                        <th class="px-3 py-2 text-right text-xs font-semibold text-gray-600">
                                            Action
                                        </th>
                                    </tr>
                                </thead>

                                <tbody class="divide-y divide-gray-100">
                                    @foreach ($task->dependencies as $dependency)
                                        <tr>
                                            <td class="px-3 py-2">
                                                <a href="{{ route('tasks.show', [
                                                        'task' => $dependency->dependsOnTask,
                                                        'from' => $from,
                                                        'return' => url()->full(),
                                                    ]) }}"
                                                class="hover:underline">
                                                    {{ $dependency->dependsOnTask->tasktitle }}
                                                </a>
                                            </td>

                                            <td class="px-3 py-2 text-xs text-gray-700">
                                                @switch($dependency->dependencytype)
                                                    @case('FS')
                                                        Finish to Start
                                                        @break

                                                    @case('SS')
                                                        Start to Start
                                                        @break

                                                    @case('FF')
                                                        Finish to Finish
                                                        @break

                                                    @case('SF')
                                                        Start to Finish
                                                        @break

                                                    @default
                                                        {{ $dependency->dependencytype }}
                                                @endswitch
                                            </td>

                                            <td class="px-3 py-2 text-xs text-gray-700">
                                                {{ $dependency->lagdays }} day{{ abs($dependency->lagdays) === 1 ? '' : 's' }}
                                            </td>

                                            <td class="px-3 py-2 text-right">
                                                <form method="POST"
                                                    action="{{ route('tasks.dependencies.destroy', [
                                                        'task' => $task,
                                                        'dependency' => $dependency,
                                                    ]) }}"
                                                    onsubmit="return confirm('Remove this dependency?');">
                                                    @csrf
                                                    @method('DELETE')

                                                    <button type="submit"
                                                            class="inline-flex items-center px-3 py-1.5 border border-red-300 rounded text-xs font-semibold text-red-700 bg-white hover:bg-red-50">
                                                        Remove
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-xs text-gray-500">
                            No dependencies have been added.
                        </p>
                    @endif

                    <p class="text-[11px] text-gray-500">
                        FS = Finish to Start; SS = Start to Start; FF = Finish to Finish; SF = Start to Finish.
                        Positive lag delays this task; negative lag allows overlap.
                    </p>
                </div>
            </div>

            <div class="bg-white shadow-sm rounded-lg p-6">
                <h3 class="text-sm font-semibold mb-3">Comments</h3>

                <ul class="divide-y divide-gray-100 mb-4">
                    @foreach ($task->comments as $comment)
                        <li class="py-2 text-sm">
                            <span class="font-medium">{{ $comment->user->name ?? 'Unknown' }}</span>
                            <span class="text-gray-400 text-xs ml-2">
                                {{ $comment->createdat?->diffForHumans() }}
                            </span>
                            <p class="text-gray-700 mt-1">{{ $comment->commenttext }}</p>
                        </li>
                    @endforeach
                </ul>

                <form method="POST"
                      action="{{ route('task-comments.store', $task) }}"
                      class="flex gap-2">
                    @csrf

                        <input type="hidden" name="from" value="{{ $from }}">
                        <input type="hidden" name="return_url" value="{{ $returnUrl }}">


                    <input type="text"
                           name="commenttext"
                           required
                           placeholder="Add a comment..."
                           class="flex-1 border-gray-300 rounded-md shadow-sm text-sm">
                    <button type="submit"
                            class="px-4 py-2 bg-gray-600 text-white text-sm rounded hover:bg-gray-700">
                        Post
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- JS toggles --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const toggleButton = document.getElementById('toggle-move-task-panel');
            const panel = document.getElementById('move-task-panel');

            if (!toggleButton || !panel) {
                return;
            }

            function updateLabel() {
                toggleButton.textContent = panel.classList.contains('hidden')
                    ? 'Move task to another project'
                    : 'Hide move options';
            }

            toggleButton.addEventListener('click', function () {
                panel.classList.toggle('hidden');
                updateLabel();
            });

            updateLabel();
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const toggleButton = document.getElementById('toggle-task-labels-panel');
            const panel = document.getElementById('task-labels-panel');
            const summary = document.getElementById('selected-task-labels-summary');

            if (!panel) {
                return;
            }

            const checkboxes = Array.from(
                document.querySelectorAll('.task-label-checkbox')
            );

            function getLabelText(checkbox) {
                return checkbox
                    .closest('label')
                    ?.querySelector('span')
                    ?.textContent
                    ?.trim() || '';
            }

            function updateSummary() {
                const selectedLabels = checkboxes
                    .filter(cb => cb.checked)
                    .map(getLabelText)
                    .filter(Boolean);

                summary.innerHTML = '';

                if (selectedLabels.length === 0) {
                    summary.classList.add('hidden');
                    return;
                }

                summary.classList.remove('hidden');

                selectedLabels.forEach(label => {
                    const chip = document.createElement('span');
                    chip.className =
                        'inline-flex items-center px-2.5 py-1 rounded-full bg-blue-50 text-blue-700 text-xs font-medium border border-blue-200';
                    chip.textContent = label;
                    summary.appendChild(chip);
                });
            }

            if (toggleButton) {
                function updateToggleLabel() {
                    toggleButton.textContent = panel.classList.contains('hidden')
                        ? 'Add or change labels'
                        : 'Hide labels';
                }

                toggleButton.addEventListener('click', function () {
                    panel.classList.toggle('hidden');
                    updateToggleLabel();
                });

                updateToggleLabel();
            }

            checkboxes.forEach(cb => cb.addEventListener('change', updateSummary));

            // initial state
            updateSummary();
            updateToggleLabel();
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const toggleBtn = document.getElementById('toggle-recurring-panel');
            const panel = document.getElementById('recurring-panel');
            const freqSelect = document.getElementById('recurrence-frequency');
            const monthlyBlock = document.getElementById('monthly-pattern-block');

            if (!toggleBtn || !panel || !freqSelect || !monthlyBlock) return;

            function updateRecurringLabel() {
                toggleBtn.textContent = panel.classList.contains('hidden')
                    ? 'Recurring settings'
                    : 'Hide recurring settings';
            }

            function updateMonthlyVisibility() {
                if (freqSelect.value === 'monthly') {
                    monthlyBlock.classList.remove('hidden');
                } else {
                    monthlyBlock.classList.add('hidden');
                }
            }

            toggleBtn.addEventListener('click', function () {
                panel.classList.toggle('hidden');
                updateRecurringLabel();
            });

            freqSelect.addEventListener('change', updateMonthlyVisibility);

            // initial state
            updateRecurringLabel();
            updateMonthlyVisibility();
        });
    </script>
</x-app-layout>