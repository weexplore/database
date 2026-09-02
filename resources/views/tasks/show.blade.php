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
                $backUrl = $returnUrl
                    ?: match ($from) {
                        'alltasks' => route('tasksall.all'),
                        'outlook' => route('tasks.outlook'),
                        default => route('tasks.index', $task->projectid),
                    };

                $backLabel = match ($from) {
                    'alltasks' => 'Back to All Tasks',
                    'outlook' => 'Back to Task Outlook',
                    default => 'Back to Tasks',
                };
            @endphp

            @php
                $selectedKnowledgeItemIds = old(
                    'knowledgeitemids',
                    $task->knowledgeItems->pluck('id')->all()
                );
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
                            <label class="block text-xs font-medium text-gray-600">
                                Start date
                            </label>

                            <input
                                id="main-task-startdate"
                                type="date"
                                name="startdate"
                                value="{{ old('startdate', $task->startdate?->format('Y-m-d')) }}"
                                class="mt-1 w-full border-gray-300 rounded-md shadow-sm text-sm"
                            >
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-600">
                                Due date
                            </label>

                            <input
                                id="main-task-duedate"
                                type="date"
                                name="duedate"
                                value="{{ old('duedate', $task->duedate?->format('Y-m-d')) }}"
                                min="{{ old('startdate', $task->startdate?->format('Y-m-d')) }}"
                                class="mt-1 w-full border-gray-300 rounded-md shadow-sm text-sm"
                            >

                            @error('duedate')
                                <p class="mt-1 text-xs text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror
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

                    <div>
                   {{-- Linked Knowledge Items --}}
<div class="space-y-2">
    <div class="flex items-center gap-2">
        <label class="block text-xs font-medium text-gray-600">
            Linked Knowledge Items
        </label>

        <button type="button"
                id="toggle-knowledge-items-panel"
                class="text-xs px-2 py-1 rounded border border-gray-300 bg-gray-50 text-gray-700 hover:bg-gray-100">
            {{ $task->knowledgeItems->isNotEmpty()
                ? 'Add or change knowledge items'
                : 'Add knowledge items' }}
        </button>
    </div>

    {{-- Current saved links; JavaScript updates this after checkbox changes. --}}
    <div id="selected-knowledge-items-summary"
         class="{{ $task->knowledgeItems->isNotEmpty()
             ? 'flex flex-wrap gap-2'
             : 'hidden' }}">

        @foreach ($task->knowledgeItems as $knowledgeItem)
            <a href="{{ route('knowledge.items.edit', [
                    'knowledgeItem' => $knowledgeItem,
                    'returnto' => request()->fullUrl(),
                ]) }}"
            class="inline-flex cursor-pointer items-center rounded-full bg-indigo-100 px-2.5 py-1 text-xs font-medium text-indigo-800 hover:bg-indigo-200 hover:underline"
            title="Open {{ $knowledgeItem->itemname }} in Knowledge Base">

                <span>{{ $knowledgeItem->itemname }}</span>

                @if ($knowledgeItem->primaryCategory?->categoryname)
                    <span class="ml-1 text-indigo-600">
                        — {{ $knowledgeItem->primaryCategory->categoryname }}
                    </span>
                @endif
            </a>
        @endforeach
    </div>

    {{-- AJAX search picker. This starts hidden but is always rendered. --}}
    <div id="knowledge-items-panel"
         class="hidden rounded-md border border-gray-200 bg-gray-50 p-3">

        <div class="mb-3 flex items-center justify-between gap-3">
            <p class="text-xs text-gray-600">
                Search active Knowledge Items, then tick the items to link to this task.
            </p>

            <span id="knowledge-items-selected-count"
                  class="hidden whitespace-nowrap rounded-full bg-indigo-100 px-2 py-0.5 text-xs font-medium text-indigo-800">
            </span>
        </div>

        <input type="search"
               id="knowledge-items-search"
               autocomplete="off"
               placeholder="Search Knowledge Items…"
               class="mb-3 w-full rounded-md border-gray-300 shadow-sm text-sm">

        {{-- Search results are built by JavaScript. --}}
        <div id="knowledge-items-list"
             class="max-h-64 space-y-1 overflow-y-auto">
            <p class="py-3 text-center text-xs text-gray-500">
                Start typing to search active Knowledge Items.
            </p>
        </div>

        <p id="knowledge-items-no-results"
           class="hidden py-3 text-center text-xs text-gray-500">
            No matching active Knowledge Items found.
        </p>

        {{-- Selected IDs missing from current visible search results go here. --}}
        <div id="knowledge-items-hidden-inputs"></div>
    </div>
</div>
                    <div class="flex items-center justify-end pt-2 border-t border-gray-200">
                        <button type="submit"
                                class="px-5 py-2 bg-green-600 text-white text-sm rounded hover:bg-green-700">
                            Save Task
                        </button>
                    </div>
                </form>
            </div>

            {{-- Visual separation between the Task editor and supporting panels. --}}
            <div class="border-t border-gray-300 mt-6"></div>

            {{-- Comments control --}}
            <div class="bg-white shadow-sm rounded-lg px-6 pb-6 pt-0">
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
                        If any schedule details change, future occurrences are recalculated
                        from the recurrence start date.
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
                                    <input
                                        form="new-subtask-form"
                                        id="new-subtask-startdate"
                                        type="date"
                                        name="startdate"
                                        value="{{ old('startdate') }}"
                                        class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                                    >
                                </td>

                                <td class="px-3 py-2 min-w-[145px]">
                                    <input
                                        form="new-subtask-form"
                                        id="new-subtask-duedate"
                                        type="date"
                                        name="duedate"
                                        value="{{ old('duedate') }}"
                                        min="{{ old('startdate') }}"
                                        class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                                    >
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
                                        <input
                                            form="subtask-form-{{ $sub->id }}"
                                            id="subtask-startdate-{{ $sub->id }}"
                                            type="date"
                                            name="startdate"
                                            value="{{ $sub->startdate?->format('Y-m-d') }}"
                                            data-subtask-startdate="{{ $sub->id }}"
                                            class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                                        >
                                    </td>

                                    <td class="px-3 py-2 min-w-[145px]">
                                        <input
                                            form="subtask-form-{{ $sub->id }}"
                                            id="subtask-duedate-{{ $sub->id }}"
                                            type="date"
                                            name="duedate"
                                            value="{{ $sub->duedate?->format('Y-m-d') }}"
                                            min="{{ $sub->startdate?->format('Y-m-d') }}"
                                            data-subtask-duedate="{{ $sub->id }}"
                                            class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                                        >
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

@php
    $knowledgeItemPickerSelectedItems = $task->knowledgeItems
        ->map(function ($knowledgeItem) {
            return [
                'id' => (int) $knowledgeItem->id,
                'itemname' => $knowledgeItem->itemname,
                'categoryname' => $knowledgeItem->primaryCategory?->categoryname,
            ];
        })
        ->values()
        ->all();
@endphp

<script>
(function initialiseKnowledgeItemPicker() {
    function start() {
        const button = document.getElementById('toggle-knowledge-items-panel');
        const panel = document.getElementById('knowledge-items-panel');
        const input = document.getElementById('knowledge-items-search');
        const list = document.getElementById('knowledge-items-list');
        const noResults = document.getElementById('knowledge-items-no-results');

        if (!button || !panel || !input || !list) {
            console.error('Knowledge Item picker was not initialised.', {
                buttonFound: Boolean(button),
                panelFound: Boolean(panel),
                inputFound: Boolean(input),
                listFound: Boolean(list),
            });

            return;
        }

        const searchUrl = @json(route('tasks.knowledge-items.search'));

        let timer = null;

        function setButtonLabel() {
            button.textContent = panel.classList.contains('hidden')
                ? 'Add knowledge items'
                : 'Hide knowledge items';
        }

        function showMessage(message, className) {
            list.innerHTML = '';

            const messageElement = document.createElement('p');

            messageElement.className =
                className || 'py-3 text-center text-xs text-gray-500';

            messageElement.textContent = message;

            list.appendChild(messageElement);
        }

        function renderItems(items) {
            list.innerHTML = '';

            if (noResults) {
                noResults.classList.add('hidden');
            }

            if (!Array.isArray(items) || items.length === 0) {
                if (noResults) {
                    noResults.classList.remove('hidden');
                } else {
                    showMessage('No matching active Knowledge Items found.');
                }

                return;
            }

            items.forEach(function (item) {
                const option = document.createElement('label');

                option.className =
                    'flex items-center gap-2 rounded px-2 py-1 text-sm hover:bg-white cursor-pointer';

                const checkbox = document.createElement('input');

                checkbox.type = 'checkbox';
                checkbox.name = 'knowledgeitemids[]';
                checkbox.value = String(item.id);
                checkbox.checked = Boolean(item.selected);
                checkbox.className =
                    'rounded border-gray-300 text-indigo-600 shadow-sm';

                const text = document.createElement('span');

                text.className = 'min-w-0 truncate';

                const itemName = document.createElement('span');

                itemName.className = 'font-medium text-gray-800';
                itemName.textContent = item.itemname;

                text.appendChild(itemName);

                if (item.categoryname) {
                    const categoryName = document.createElement('span');

                    categoryName.className = 'ml-2 text-xs text-gray-500';
                    categoryName.textContent = item.categoryname;

                    text.appendChild(categoryName);
                }

                option.appendChild(checkbox);
                option.appendChild(text);

                list.appendChild(option);
            });
        }

        function searchKnowledgeItems(searchTerm) {
            showMessage('Searching…');

            if (noResults) {
                noResults.classList.add('hidden');
            }

            const url = searchUrl + '?search=' + encodeURIComponent(searchTerm);

            fetch(url, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            })
                .then(function (response) {
                    if (!response.ok) {
                        throw new Error('HTTP ' + response.status);
                    }

                    return response.json();
                })
                .then(function (data) {
                    renderItems(data.items || []);
                })
                .catch(function (error) {
                    console.error('Knowledge Item search failed:', error);

                    showMessage(
                        'Unable to load Knowledge Items: ' + error.message,
                        'py-3 text-center text-xs text-red-600'
                    );
                });
        }

        button.addEventListener('click', function () {
            panel.classList.toggle('hidden');
            setButtonLabel();

            if (!panel.classList.contains('hidden')) {
                input.focus();

                /*
                 * Do not automatically load the first 50 items. The page
                 * remains fast, and the user explicitly searches by typing.
                 */
                showMessage('Start typing to search active Knowledge Items.');
            }
        });

        input.addEventListener('input', function () {
            const searchTerm = input.value.trim();

            window.clearTimeout(timer);

            if (searchTerm.length === 0) {
                showMessage('Start typing to search active Knowledge Items.');

                if (noResults) {
                    noResults.classList.add('hidden');
                }

                return;
            }

            timer = window.setTimeout(function () {
                searchKnowledgeItems(searchTerm);
            }, 250);
        });

        setButtonLabel();

        console.info('Knowledge Item picker initialised.');
    }

    /*
     * Works whether this script occurs before or after the picker markup.
     */
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', start);
    } else {
        start();
    }
})();
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
   <script>
document.addEventListener('DOMContentLoaded', function () {
    const toggleButton = document.getElementById(
        'toggle-knowledge-items-panel'
    );

    const panel = document.getElementById(
        'knowledge-items-panel'
    );

    const searchInput = document.getElementById(
        'knowledge-items-search'
    );

    if (!toggleButton || !panel) {
        return;
    }

    function updateToggleLabel() {
        toggleButton.textContent = panel.classList.contains('hidden')
            ? '{{ $task->knowledgeItems->isNotEmpty()
                ? 'Add or change knowledge items'
                : 'Add knowledge items' }}'
            : 'Hide knowledge items';
    }

    toggleButton.addEventListener('click', function () {
        panel.classList.toggle('hidden');
        updateToggleLabel();

        if (!panel.classList.contains('hidden') && searchInput) {
            searchInput.focus();
        }
    });

    updateToggleLabel();
});
</script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                function bindDateRange(startInput, dueInput) {
                    if (!startInput || !dueInput) {
                        return;
                    }

                    /*
                    * On initial page load, set the browser constraint only.
                    * Do not silently alter existing saved dates.
                    */
                    function setMinimumDueDate() {
                        dueInput.min = startInput.value || '';
                    }

                    /*
                    * When the user deliberately changes Start, keep Due valid:
                    * - blank Due becomes Start;
                    * - earlier Due moves to Start;
                    * - same/later Due remains unchanged.
                    */
                    function correctDueDateAfterStartChange() {
                        const startDate = startInput.value;
                        const dueDate = dueInput.value;

                        dueInput.min = startDate || '';

                        if (!startDate) {
                            return;
                        }

                        if (!dueDate || dueDate < startDate) {
                            dueInput.value = startDate;
                        }
                    }

                    setMinimumDueDate();

                    startInput.addEventListener(
                        'change',
                        correctDueDateAfterStartChange
                    );

                    startInput.addEventListener(
                        'input',
                        correctDueDateAfterStartChange
                    );
                }

                /*
                * Main task edit card.
                */
                bindDateRange(
                    document.getElementById('main-task-startdate'),
                    document.getElementById('main-task-duedate')
                );

                /*
                * New inline subtask row.
                */
                bindDateRange(
                    document.getElementById('new-subtask-startdate'),
                    document.getElementById('new-subtask-duedate')
                );

                /*
                * Existing inline subtask rows.
                */
                document.querySelectorAll('[data-subtask-startdate]').forEach(
                    function (startInput) {
                        const subtaskId = startInput.dataset.subtaskStartdate;

                        const dueInput = document.querySelector(
                            '[data-subtask-duedate="' + subtaskId + '"]'
                        );

                        bindDateRange(startInput, dueInput);
                    }
                );
            });
        </script>
        <script>
(function () {
    function initialiseKnowledgeItemPicker() {
        const button = document.getElementById('toggle-knowledge-items-panel');
        const panel = document.getElementById('knowledge-items-panel');
        const searchInput = document.getElementById('knowledge-items-search');
        const resultList = document.getElementById('knowledge-items-list');
        const noResults = document.getElementById('knowledge-items-no-results');
        const hiddenInputs = document.getElementById('knowledge-items-hidden-inputs');
        const selectedCount = document.getElementById('knowledge-items-selected-count');
        const selectedSummary = document.getElementById('selected-knowledge-items-summary');

        /*
         * Do nothing unless every required element exists.
         * This avoids JavaScript errors on any page that does not contain
         * the Task Knowledge Item picker.
         */
        if (
            !button ||
            !panel ||
            !searchInput ||
            !resultList ||
            !hiddenInputs
        ) {
            return;
        }

        const searchUrl = @json(route('tasks.knowledge-items.search'));

        /*
         * Start with currently linked Knowledge Items.
         *
         * Do not use a multi-line Blade JSON expression around a closure. Blade parsing can
         * fail in that situation. These simple individual JSON values are
         * safe for item names containing apostrophes or quotes.
         */
        const selectedItems = new Map();

        @foreach ($task->knowledgeItems as $knowledgeItem)
            selectedItems.set(
                {{ (int) $knowledgeItem->id }},
                {
                    id: {{ (int) $knowledgeItem->id }},
                    itemname: @json($knowledgeItem->itemname),
                    categoryname: @json($knowledgeItem->primaryCategory?->categoryname)
                }
            );
        @endforeach

        let searchTimer = null;
        let firstOpen = true;
        let requestSerial = 0;

        function setButtonText() {
            if (panel.classList.contains('hidden')) {
                button.textContent = selectedItems.size > 0
                    ? 'Add or change knowledge items'
                    : 'Add knowledge items';

                return;
            }

            button.textContent = 'Hide knowledge items';
        }

        function updateSelectedCount() {
            if (!selectedCount) {
                return;
            }

            if (selectedItems.size === 0) {
                selectedCount.textContent = '';
                selectedCount.classList.add('hidden');

                return;
            }

            selectedCount.textContent = selectedItems.size === 1
                ? '1 item selected'
                : selectedItems.size + ' items selected';

            selectedCount.classList.remove('hidden');
        }

        function updateSelectedSummary() {
    if (!selectedSummary) {
        return;
    }

    selectedSummary.innerHTML = '';

    if (selectedItems.size === 0) {
        selectedSummary.className = 'hidden';
        return;
    }

    selectedSummary.className = 'flex flex-wrap gap-2 mb-2';

    selectedItems.forEach(function (item) {
        const chip = document.createElement('a');

        /*
         * This is the exact existing Knowledge Item edit route pattern.
         * The ID placeholder is replaced in JavaScript for each selected
         * Knowledge Item. returnto restores the current Task page.
         */
        const itemEditUrl = '{{ route('knowledge.items.edit', [
            'knowledgeItem' => '__KNOWLEDGE_ITEM_ID__',
            'return_to' => '__RETURN_TO__',
        ]) }}';

        chip.href = itemEditUrl
            .replace('__KNOWLEDGE_ITEM_ID__', String(item.id))
            .replace('__RETURN_TO__', encodeURIComponent(window.location.href));

        chip.title = 'Open ' + item.itemname + ' in Knowledge Base';

        chip.className =
            'inline-flex cursor-pointer items-center rounded-full bg-indigo-100 px-2.5 py-1 text-xs font-medium text-indigo-800 hover:bg-indigo-200 hover:underline';

        const itemName = document.createElement('span');
        itemName.textContent = item.itemname;

        chip.appendChild(itemName);

        if (item.categoryname) {
            const categoryName = document.createElement('span');

            categoryName.className = 'ml-1 text-indigo-600';
            categoryName.textContent = '— ' + item.categoryname;

            chip.appendChild(categoryName);
        }

        selectedSummary.appendChild(chip);
    });
}

        function getVisibleCheckboxIds() {
            const visibleIds = new Set();

            const checkboxes = resultList.querySelectorAll(
                'input[data-knowledge-item-checkbox="1"]'
            );

            checkboxes.forEach(function (checkbox) {
                visibleIds.add(Number(checkbox.value));
            });

            return visibleIds;
        }

        /*
         * Important: results are replaced after every search. A selected
         * record that is not in the current results must remain represented
         * by a hidden field, otherwise sync() would detach it on Save Task.
         */
        function updateHiddenInputs() {
            const visibleIds = getVisibleCheckboxIds();

            hiddenInputs.innerHTML = '';

            selectedItems.forEach(function (item, id) {
                if (visibleIds.has(id)) {
                    return;
                }

                const hiddenInput = document.createElement('input');

                hiddenInput.type = 'hidden';
                hiddenInput.name = 'knowledgeitemids[]';
                hiddenInput.value = String(id);

                hiddenInputs.appendChild(hiddenInput);
            });
        }

        function showMessage(message, className) {
            resultList.innerHTML = '';

            const messageNode = document.createElement('p');

            messageNode.className = className ||
                'py-3 text-center text-xs text-gray-500';

            messageNode.textContent = message;

            resultList.appendChild(messageNode);
        }

        function renderResults(items) {
            resultList.innerHTML = '';

            if (noResults) {
                noResults.classList.add('hidden');
            }

            if (!Array.isArray(items) || items.length === 0) {
                if (noResults) {
                    noResults.classList.remove('hidden');
                } else {
                    showMessage('No matching active Knowledge Items found.');
                }

                updateHiddenInputs();
                updateSelectedCount();
                updateSelectedSummary();

                return;
            }

            items.forEach(function (item) {
                const itemId = Number(item.id);

                const option = document.createElement('label');

                option.className =
                    'flex cursor-pointer items-center gap-2 rounded px-2 py-1 text-sm hover:bg-white';

                const checkbox = document.createElement('input');

                checkbox.type = 'checkbox';
                checkbox.name = 'knowledgeitemids[]';
                checkbox.value = String(itemId);
                checkbox.dataset.knowledgeItemCheckbox = '1';
                checkbox.checked = selectedItems.has(itemId);
                checkbox.className =
                    'rounded border-gray-300 text-indigo-600 shadow-sm';

                const text = document.createElement('span');

                text.className = 'min-w-0 truncate';

                const itemName = document.createElement('span');

                itemName.className = 'font-medium text-gray-800';
                itemName.textContent = item.itemname;

                text.appendChild(itemName);

                if (item.categoryname) {
                    const categoryName = document.createElement('span');

                    categoryName.className = 'ml-2 text-xs text-gray-500';
                    categoryName.textContent = item.categoryname;

                    text.appendChild(categoryName);
                }

                checkbox.addEventListener('change', function () {
                    if (checkbox.checked) {
                        selectedItems.set(itemId, {
                            id: itemId,
                            itemname: item.itemname,
                            categoryname: item.categoryname || null
                        });
                    } else {
                        selectedItems.delete(itemId);
                    }

                    updateHiddenInputs();
                    updateSelectedCount();
                    updateSelectedSummary();
                    setButtonText();
                });

                option.appendChild(checkbox);
                option.appendChild(text);

                resultList.appendChild(option);
            });

            updateHiddenInputs();
            updateSelectedCount();
            updateSelectedSummary();
        }

        function searchKnowledgeItems(searchTerm) {
            const thisRequest = ++requestSerial;

            const url = new URL(searchUrl, window.location.origin);

            if (searchTerm !== '') {
                url.searchParams.set('search', searchTerm);
            }

            selectedItems.forEach(function (item, id) {
                url.searchParams.append('selected[]', String(id));
            });

            showMessage('Searching…');

            if (noResults) {
                noResults.classList.add('hidden');
            }

            fetch(url.toString(), {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then(function (response) {
                    if (!response.ok) {
                        throw new Error('HTTP ' + response.status);
                    }

                    return response.json();
                })
                .then(function (data) {
                    /*
                     * Ignore a slower old result after a newer search has
                     * already been sent.
                     */
                    if (thisRequest !== requestSerial) {
                        return;
                    }

                    renderResults(
                        Array.isArray(data.items) ? data.items : []
                    );
                })
                .catch(function (error) {
                    if (thisRequest !== requestSerial) {
                        return;
                    }

                    showMessage(
                        'Unable to load Knowledge Items: ' + error.message,
                        'py-3 text-center text-xs text-red-600'
                    );

                    console.error('Knowledge Item search failed:', error);
                });
        }

        button.addEventListener('click', function () {
            panel.classList.toggle('hidden');
            setButtonText();

            if (!panel.classList.contains('hidden')) {
                searchInput.focus();

                /*
                 * Show a small initial list once. This verifies the route
                 * immediately and provides a usable browse list without
                 * rendering thousands of options in the page HTML.
                 */
                if (firstOpen) {
                    firstOpen = false;
                    searchKnowledgeItems('');
                }
            }
        });

        searchInput.addEventListener('input', function () {
            const searchTerm = searchInput.value.trim();

            window.clearTimeout(searchTimer);

            if (searchTerm === '') {
                searchTimer = window.setTimeout(function () {
                    searchKnowledgeItems('');
                }, 150);

                return;
            }

            searchTimer = window.setTimeout(function () {
                searchKnowledgeItems(searchTerm);
            }, 250);
        });

        updateHiddenInputs();
        updateSelectedCount();
        updateSelectedSummary();
        setButtonText();
    }

    /*
     * This works whether the script is rendered before or after the main
     * Task form and the picker HTML.
     */
    if (document.readyState === 'loading') {
        document.addEventListener(
            'DOMContentLoaded',
            initialiseKnowledgeItemPicker
        );
    } else {
        initialiseKnowledgeItemPicker();
    }
})();
</script>
</x-app-layout>