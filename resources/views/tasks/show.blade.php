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

                // Use one consistent parameter name for inbound links and submitted forms.
                $returnUrl = request('return_url');

                // The current Task page is useful as the return target for navigating
                // between parent, child and dependency Task detail pages.
                $currentTaskUrl = request()->fullUrl();
            @endphp
            @php
                $hasRecurrence = $task->recurrence !== null;
                $hasSubtasks = $task->subtasks->isNotEmpty();
                $hasDependencies = $task->dependencies->isNotEmpty();
            @endphp

            

            @php
                $backUrl = $returnUrl ?: route('tasks.index', $task->projectid);

                $backLabel = match ($from) {
                    'alltasks' => '← Back to All Tasks',
                    'outlook' => '← Back to Task Outlook',
                    default => '← Back to Tasks',
                };
            @endphp

            <a href="{{ $backUrl }}"
            class="inline-flex items-center px-3 py-1.5 bg-gray-100 text-gray-700 text-xs font-medium rounded border border-gray-300 hover:bg-gray-200">
                {{ $backLabel }}
            </a>

            {{-- Main edit card --}}
            <div class="bg-white shadow-sm rounded-lg p-6 space-y-4">
                <form method="POST" action="{{ route('tasks.update', $task) }}" class="space-y-4">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="from" value="{{ $from }}">
                    <input type="hidden" name="return_url" value="{{ $returnUrl }}">

                    @if ($task->parentTask)
                        <div class="rounded-md border border-gray-200 bg-gray-50 px-3 py-2">
                            <span class="block text-xs font-medium text-gray-500">Parent task</span>

                            <a href="{{ route('tasks.show', [
                                    'task' => $task->parentTask,
                                    'from' => $from,
                                    'return_url' => $currentTaskUrl,
                                ]) }}"
                            class="mt-1 inline-block text-sm font-medium text-indigo-700 hover:underline">
                                {{ $task->parentTask->tasktitle }}
                            </a>
                        </div>
                    @endif

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

                    <div>
                        <label class="block text-xs font-medium text-gray-600">
                            Expected outcome or constraints
                        </label>

                        <textarea name="taskexpectation"
                                rows="3"
                                placeholder="What does done look like? Include constraints, dependencies, access notes, or acceptance criteria."
                                class="mt-1 w-full rounded-md border-gray-300 shadow-sm text-sm">{{ old('taskexpectation', $task->taskexpectation) }}</textarea>
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
                        <div>
                            <label class="block text-xs font-medium text-gray-600">
                                Estimated effort (hours)
                            </label>

                            <input type="number"
                                name="estimatedefforthours"
                                min="0"
                                max="9999.99"
                                step="0.25"
                                value="{{ old('estimatedefforthours', $task->estimatedefforthours) }}"
                                class="mt-1 w-full rounded-md border-gray-300 shadow-sm text-sm">
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-600">
                                Actual effort (hours)
                            </label>

                            <input type="number"
                                name="actualefforthours"
                                min="0"
                                max="9999.99"
                                step="0.25"
                                value="{{ old('actualefforthours', $task->actualefforthours) }}"
                                class="mt-1 w-full rounded-md border-gray-300 shadow-sm text-sm">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600">
                            Status comment
                        </label>

                        <textarea name="statuscomment"
                                rows="2"
                                placeholder="Current progress, blocker, next action, or reason for delay."
                                class="mt-1 w-full rounded-md border-gray-300 shadow-sm text-sm">{{ old('statuscomment', $task->statuscomment) }}</textarea>
                    </div>

                    {{-- Labels summary + panel --}}
                    <div>
                        <div class="flex items-center gap-2 mb-2">
                            <label class="block text-xs font-medium text-gray-600">
                                Labels
                            </label>

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
            {{-- Comments control --}}
             <div class="bg-white shadow-sm rounded-lg p-6">
                <h3 class="text-sm font-semibold mb-3">Comments</h3>

                <ul class="divide-y divide-gray-100 mb-4">
                    @foreach ($task->comments as $comment)
                        <li class="py-2 text-sm">
                            <span class="font-medium">{{ $comment->user->name ?? 'Ian Seaman' }}</span>
                            <span class="text-gray-400 text-xs ml-2"
                                title="{{ $comment->createdat
                                    ?->shiftTimezone('Australia/Sydney')
                                    ->format('d M Y, g:i A T') }}">
                                {{ $comment->createdat
                                    ?->shiftTimezone('Australia/Sydney')
                                    ->diffForHumans() }}
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
                        <input type="hidden"
                            name="return_url"
                            value="{{ $returnUrl ?: $currentTaskUrl }}">


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

            {{-- Recurring settings control --}}
            <div class="flex items-center justify-start mb-2">
                <button type="button"
                        id="toggle-recurring-panel"
                        class="text-xs px-3 py-1 rounded border border-slate-300 bg-slate-50 text-slate-700 hover:bg-slate-100">
                    {{ $hasRecurrence ? 'Hide recurring settings' : 'Recurring settings' }}
                </button>
            </div>

            {{-- Recurring panel --}}
            <div id="recurring-panel"
                class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-slate-200 {{ $hasRecurrence ? '' : 'hidden' }}">
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
                        @php
                            $nextOccurrence = $task->recurrence?->nextScheduledDate();
                        @endphp

                        @if ($task->recurrence)
                            <div class="mx-4 mt-4 rounded-md border border-slate-200 bg-slate-50 px-3 py-2 text-xs text-slate-700">
                                @if (! $task->recurrence->isactive)
                                    <span class="font-semibold text-amber-700">Paused.</span>
                                    This recurrence is inactive and will not generate tasks.
                                @elseif ($nextOccurrence)
                                    @php
                                        $nextDueDate = $task->recurrence->nextScheduledDate();
                                        $nextGenerationDate = $task->recurrence->nextGenerationDate();
                                    @endphp

                                    <span class="font-semibold text-slate-900">Next generated:</span>
                                    {{ $nextGenerationDate?->format('l, j F Y') }}

                                    @if ($nextDueDate)
                                        <span class="ml-2 text-slate-600">
                                            Due {{ $nextDueDate->format('l, j F Y') }}
                                        </span>
                                    @endif
                                @else
                                    <span class="font-semibold text-red-700">Not scheduled.</span>
                                    Set a frequency and start date.
                                @endif
                            </div>
                        @endif

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

            @php
                $directSubtaskEstimatedHours = $task->subtasks
                    ->sum(fn ($subtask) => (float) ($subtask->estimatedefforthours ?? 0));

                $directSubtaskActualHours = $task->subtasks
                    ->sum(fn ($subtask) => (float) ($subtask->actualefforthours ?? 0));
            @endphp

            @if ($task->subtasks->isNotEmpty())
                <div class="mb-4 rounded-md border border-sky-200 bg-sky-50 px-3 py-2 text-xs text-sky-900">
                    <span class="font-semibold">Direct subtask effort:</span>
                    Estimated {{ rtrim(rtrim(number_format($directSubtaskEstimatedHours, 2), '0'), '.') }}h
                    · Actual {{ rtrim(rtrim(number_format($directSubtaskActualHours, 2), '0'), '.') }}h

                    <span class="ml-2 text-sky-700">
                        Parent task effort is separate and is not included in these totals.
                    </span>
                </div>
            @endif

            
            {{-- Sub-tasks control --}}
            <div class="flex items-center justify-start mb-2">
                <button type="button"
                        id="toggle-subtasks-panel"
                        class="text-xs px-3 py-1 rounded border border-sky-300 bg-sky-50 text-sky-700 hover:bg-sky-100">
                    {{ $hasSubtasks
                        ? 'Hide sub-tasks ('.$task->subtasks->count().')'
                        : 'Show sub-tasks' }}
                </button>
            </div>

            {{-- Sub-tasks panel --}}
            <div id="subtasks-panel"
                class="bg-white shadow-sm rounded-lg p-6 {{ $hasSubtasks ? '' : 'hidden' }}">
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
                    <input type="hidden" name="return_url" value="{{ $currentTaskUrl }}">
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
                                    <input type="hidden" name="return_url" value="{{ $currentTaskUrl }}">
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
                                                'return_url' => $currentTaskUrl,
                                                    
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

            {{-- Dependencies control --}}
            <div class="flex items-center justify-start mb-2">
                <button type="button"
                        id="toggle-dependencies-panel"
                        class="text-xs px-3 py-1 rounded border border-indigo-300 bg-indigo-50 text-indigo-700 hover:bg-indigo-100">
                    {{ $hasDependencies
                        ? 'Hide dependencies ('.$task->dependencies->count().')'
                        : 'Show dependencies' }}
                </button>
            </div>

            {{-- Dependencies panel --}}
            <div id="dependencies-panel"
                class="bg-white shadow-sm rounded-lg border border-indigo-200 {{ $hasDependencies ? '' : 'hidden' }}">
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
                                                        'return_url' => $currentTaskUrl,
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

           
            {{-- Make this task a subtask --}}
            @if (
                $task->parenttaskid === null
                && $task->subtasks->isEmpty()
            )
                @php
                    $eligibleParentTasks = $projectTasks
                        ->filter(function ($candidate) use ($task) {
                            return (int) $candidate->id !== (int) $task->id
                                && $candidate->parenttaskid === null;
                        });
                @endphp

                @if ($eligibleParentTasks->isNotEmpty())
                    <div class="flex items-center justify-start mb-2">
                        <button type="button"
                                id="toggle-make-subtask-panel"
                                class="text-xs px-3 py-1 rounded border border-violet-300 bg-violet-50 text-violet-700 hover:bg-violet-100">
                            Make this task a subtask
                        </button>
                    </div>

                    <div id="make-subtask-panel"
                        class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-violet-200 hidden">
                        <div class="px-4 py-3 border-b border-violet-200 bg-violet-50">
                            <h3 class="text-sm font-semibold text-violet-800">
                                Make This Task a Subtask
                            </h3>

                            <p class="mt-1 text-xs text-violet-700">
                                Attach this task beneath another top-level task in the same project.
                                Tasks cannot be attached beneath existing subtasks.
                            </p>
                        </div>

                        <div class="p-4">
                            <form method="POST"
                                action="{{ route('tasks.make-subtask', $task) }}"
                                onsubmit="return confirm('Make this task a subtask?');">
                                @csrf

                                <input type="hidden" name="from" value="{{ $from }}">
                                <input type="hidden" name="return_url" value="{{ $returnUrl }}">

                                <div class="flex flex-wrap gap-3 items-end">
                                    <div class="flex-1 min-w-[260px]">
                                        <label class="block text-xs font-medium text-gray-600">
                                            Parent task
                                        </label>

                                        <select name="parenttaskid"
                                                required
                                                class="mt-1 w-full rounded-md border-gray-300 shadow-sm text-sm">
                                            <option value="">
                                                Select a top-level task
                                            </option>

                                            @foreach ($eligibleParentTasks as $candidate)
                                                <option value="{{ $candidate->id }}">
                                                    {{ $candidate->tasktitle }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <button type="submit"
                                            class="inline-flex items-center px-4 py-2 bg-violet-600 text-white text-xs font-semibold rounded-md hover:bg-violet-700">
                                        Make Subtask
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                @endif
            @endif
            {{-- Duplicate Task --}}
            @if ($task->parenttaskid === null)
                <div class="flex items-center justify-start mb-2">
                    <button type="button"
                            id="toggle-duplicate-task-panel"
                            class="text-xs px-3 py-1 rounded border border-emerald-300 bg-emerald-50 text-emerald-700 hover:bg-emerald-100">
                        Duplicate task
                    </button>
                </div>

                <div id="duplicate-task-panel"
                    class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-emerald-200 hidden">
                    <div class="px-4 py-3 border-b border-emerald-200 bg-emerald-50">
                        <h3 class="text-sm font-semibold text-emerald-800">Duplicate Task</h3>
                        <p class="mt-1 text-xs text-emerald-700">
                            The copy starts as an open task with no dates, completed date, actual effort, or recurrence.
                        </p>
                    </div>

                    <div class="p-4">
                        <form method="POST"
                            action="{{ route('tasks.duplicate', $task) }}"
                            class="space-y-4">
                            @csrf

                            <input type="hidden" name="from" value="{{ $from }}">
                            <input type="hidden" name="return_url" value="{{ $returnUrl }}">

                            <div>
                                <label class="block text-xs font-medium text-gray-600">
                                    New task title
                                </label>

                                <input type="text"
                                    name="tasktitle"
                                    value="Copy of {{ $task->tasktitle }}"
                                    maxlength="200"
                                    class="mt-1 w-full rounded-md border-gray-300 shadow-sm text-sm">
                            </div>

                            @if ($task->subtasks->isNotEmpty())
                                <label class="flex items-center gap-2 text-sm text-gray-700">
                                    <input type="hidden" name="copy_subtasks" value="0">

                                    <input type="checkbox"
                                        name="copy_subtasks"
                                        value="1"
                                        class="rounded border-gray-300 text-emerald-600 shadow-sm">

                                    Copy {{ $task->subtasks->count() }}
                                    subtask{{ $task->subtasks->count() === 1 ? '' : 's' }}
                                </label>
                            @endif

                            <div class="flex justify-end">
                                <button type="submit"
                                        class="inline-flex items-center px-4 py-2 bg-emerald-600 text-white text-xs font-semibold rounded-md hover:bg-emerald-700">
                                    Create Copy
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif
            {{-- Move Task to another project --}}
            {{-- Toggle for Move Task panel --}}
            <div class="flex items-center justify-start mb-2">
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

        function getLabelDetails(checkbox) {
            const labelChip = checkbox
                .closest('label')
                ?.querySelector('span');

            if (!labelChip) {
                return null;
            }

            return {
                text: labelChip.textContent.trim(),
                colour: labelChip.style.background || '#6b7280',
            };
        }

        function updateSummary() {
            if (!summary) {
                return;
            }

            const selectedLabels = checkboxes
                .filter(checkbox => checkbox.checked)
                .map(getLabelDetails)
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
                    'inline-flex items-center px-2.5 py-1 rounded-full text-white text-xs font-medium';

                chip.style.background = label.colour;
                chip.textContent = label.text;

                summary.appendChild(chip);
            });
        }

        function updateToggleLabel() {
            if (!toggleButton) {
                return;
            }

            toggleButton.textContent = panel.classList.contains('hidden')
                ? 'Add or change labels'
                : 'Hide labels';
        }

        if (toggleButton) {
            toggleButton.addEventListener('click', function () {
                panel.classList.toggle('hidden');
                updateToggleLabel();
            });
        }

        checkboxes.forEach(checkbox => {
            checkbox.addEventListener('change', updateSummary);
        });

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

            if (!toggleBtn || !panel || !freqSelect || !monthlyBlock) {
                return;
            }

            function updateRecurringLabel() {
                toggleBtn.textContent = panel.classList.contains('hidden')
                    ? 'Recurring settings'
                    : 'Hide recurring settings';
            }

            function updateMonthlyVisibility() {
                monthlyBlock.classList.toggle(
                    'hidden',
                    freqSelect.value !== 'monthly'
                );
            }

            toggleBtn.addEventListener('click', function () {
                panel.classList.toggle('hidden');
                updateRecurringLabel();
            });

            freqSelect.addEventListener('change', updateMonthlyVisibility);

            updateRecurringLabel();
            updateMonthlyVisibility();
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const toggleButton = document.getElementById('toggle-make-subtask-panel');
            const panel = document.getElementById('make-subtask-panel');

            if (!toggleButton || !panel) {
                return;
            }

            function updateLabel() {
                toggleButton.textContent = panel.classList.contains('hidden')
                    ? 'Make this task a subtask'
                    : 'Hide subtask options';
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
            const toggleButton = document.getElementById('toggle-duplicate-task-panel');
            const panel = document.getElementById('duplicate-task-panel');

            if (!toggleButton || !panel) {
                return;
            }

            function updateLabel() {
                toggleButton.textContent = panel.classList.contains('hidden')
                    ? 'Duplicate task'
                    : 'Hide duplicate options';
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
            function setupToggle(buttonId, panelId, showLabel, hideLabel) {
                const button = document.getElementById(buttonId);
                const panel = document.getElementById(panelId);

                if (!button || !panel) {
                    return;
                }

                function updateLabel() {
                    button.textContent = panel.classList.contains('hidden')
                        ? showLabel
                        : hideLabel;
                }

                button.addEventListener('click', function () {
                    panel.classList.toggle('hidden');
                    updateLabel();
                });

                updateLabel();
            }

            setupToggle(
                'toggle-subtasks-panel',
                'subtasks-panel',
                'Show sub-tasks',
                'Hide sub-tasks{{ $hasSubtasks ? ' ('.$task->subtasks->count().')' : '' }}'
            );

            setupToggle(
                'toggle-dependencies-panel',
                'dependencies-panel',
                'Show dependencies',
                'Hide dependencies{{ $hasDependencies ? ' ('.$task->dependencies->count().')' : '' }}'
            );
        });
    </script>
</x-app-layout>