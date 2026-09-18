<form method="POST"
      action="{{ route('tasks.outlook.update', $task) }}"
      class="border-b border-gray-200 py-3 last:border-b-0">
    @csrf
    @method('PATCH')

    <div class="flex flex-col gap-2">

        {{-- Compact main line --}}
        <div class="flex flex-wrap items-start gap-x-3 gap-y-2">

            {{-- Task identity --}}
            <div class="min-w-[260px] flex-1">
                <a href="{{ route('tasks.show', [
                        'task' => $task,
                        'from' => 'outlook',
                        'return' => url()->full(),
                    ]) }}"
                class="text-sm font-semibold text-indigo-700 hover:underline">
                    {{ $task->tasktitle }}
                </a>

                {{-- Add this block --}}
                <div class="mt-1 flex flex-wrap items-center gap-1.5">
                    @if ($task->dependencies_count > 0)
                        <a href="{{ route('tasks.show', [
                                'task' => $task,
                                'from' => 'outlook',
                                'return' => url()->full(),
                            ]) }}"
                        class="inline-flex items-center gap-1 rounded-full border border-amber-300 bg-amber-50 px-1.5 py-0.5 text-[10px] font-semibold text-amber-800 hover:bg-amber-100"
                        title="This task depends on {{ $task->dependencies_count }} other task{{ $task->dependencies_count === 1 ? '' : 's' }}. Open task details to review dependencies.">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                class="h-3 w-3"
                                viewBox="0 0 20 20"
                                fill="currentColor"
                                aria-hidden="true">
                                <path fill-rule="evenodd"
                                    d="M7.5 2.75A4.75 4.75 0 0 0 3 9.025v1.225a4.75 4.75 0 0 0 8.106 3.358.75.75 0 1 1 1.06 1.061A6.25 6.25 0 0 1 1.5 10.25V9.025A6.25 6.25 0 0 1 12.166 4.61a.75.75 0 0 1-1.06 1.061A4.75 4.75 0 0 0 7.5 2.75Zm5-1A6.25 6.25 0 0 1 18.5 7.75v1.225a6.25 6.25 0 0 1-10.666 4.415.75.75 0 1 1 1.06-1.061A4.75 4.75 0 0 0 16.999 9V7.75A4.75 4.75 0 0 0 9.5 4.1a.75.75 0 1 1-1.06-1.06A6.22 6.22 0 0 1 12.5 1.75ZM6 9.25A.75.75 0 0 1 6.75 8.5h6.5a.75.75 0 0 1 0 1.5h-6.5A.75.75 0 0 1 6 9.25Z"
                                    clip-rule="evenodd" />
                            </svg>

                            Depends on {{ $task->dependencies_count }}
                        </a>
                    @endif

                    @if ($task->dependent_tasks_count > 0)
                        <a href="{{ route('tasks.show', [
                                'task' => $task,
                                'from' => 'outlook',
                                'return' => url()->full(),
                            ]) }}"
                        class="inline-flex items-center gap-1 rounded-full border border-rose-300 bg-rose-50 px-1.5 py-0.5 text-[10px] font-semibold text-rose-800 hover:bg-rose-100"
                        title="Changing this task may affect {{ $task->dependent_tasks_count }} downstream task{{ $task->dependent_tasks_count === 1 ? '' : 's' }}. Open task details to review the impact.">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                class="h-3 w-3"
                                viewBox="0 0 20 20"
                                fill="currentColor"
                                aria-hidden="true">
                                <path fill-rule="evenodd"
                                    d="M10 2.5a.75.75 0 0 1 .75.75v10.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-5 5a.75.75 0 0 1-1.06 0l-5-5a.75.75 0 0 1 1.06-1.06l3.72 3.72V3.25A.75.75 0 0 1 10 2.5Z"
                                    clip-rule="evenodd" />
                            </svg>

                            Impacts {{ $task->dependent_tasks_count }}
                        </a>
                    @endif
                </div>

                <div class="mt-0.5 flex flex-wrap items-center gap-x-2 gap-y-1 text-[11px] text-gray-500">
                    <span>{{ $task->project->projectname }}</span>

                    @if ($task->parentTask)
                        <span>·</span>

                        <span>
                            Subtask of
                            <a href="{{ route('tasks.show', [
                                    'task' => $task->parentTask,
                                    'from' => 'outlook',
                                    'return' => url()->full(),
                                ]) }}"
                               class="text-indigo-700 hover:underline">
                                {{ $task->parentTask->tasktitle }}
                            </a>
                        </span>
                    @endif

                    @if ($task->startdate || $task->duedate)
                        <span>·</span>

                        <span>
                            @if ($task->startdate)
                                Starts {{ $task->startdate->format('j M') }}
                            @endif

                            @if ($task->startdate && $task->duedate)
                                ·
                            @endif

                            @if ($task->duedate)
                                Due {{ $task->duedate->format('j M') }}
                            @endif
                        </span>

                        @if (
                            $task->startdate
                            && $task->duedate
                            && $task->startdate->lessThanOrEqualTo($today)
                            && $task->duedate->greaterThan($today)
                        )
                            <span class="font-medium text-violet-700">
                                · {{ $today->diffInDays($task->duedate) }}
                                day{{ $today->diffInDays($task->duedate) === 1 ? '' : 's' }}
                                remaining
                            </span>
                        @endif
                    @endif

                    @if ($task->updatedat)
                        <span>·</span>

                        <span>
                            Updated {{ $task->updatedat->timezone('Australia/Sydney')->format('j M Y, g:i A') }}
                        </span>
                    @endif
                </div>

                @if ($task->labels->isNotEmpty())
                    <div class="mt-1 flex flex-wrap gap-1">
                        @foreach ($task->labels as $label)
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-white text-[10px]"
                                  style="background: {{ $label->colourhex }}">
                                {{ $label->labelname }}
                            </span>
                        @endforeach
                    </div>
                @endif
            </div>



            {{-- Estimate --}}
            <div class="w-20">
                <label class="block text-[11px] font-medium text-gray-500">
                    Est.
                </label>

                <input type="number"
                    name="estimatedefforthours"
                    min="0"
                    max="9999.99"
                    step="0.25"
                    value="{{ $task->estimatedefforthours }}"
                    class="mt-0.5 w-full rounded border-gray-300 px-2 py-1.5 text-xs shadow-sm">
            </div>

            {{-- Actual effort --}}
            <div class="w-20">
                <label class="block text-[11px] font-medium text-gray-500">
                    Actual
                </label>

                <input
                    type="number"
                    name="actualefforthours"
                    min="0"
                    max="9999.99"
                    step="0.25"
                    value="{{ $task->actualefforthours }}"
                    class="mt-0.5 w-full rounded border-gray-300 px-2 py-1.5 text-xs shadow-sm"
                >
            </div>

            {{-- Priority --}}
            <div class="w-24">
                <label class="block text-[11px] font-medium text-gray-500">
                    Priority
                </label>

                <select
                    name="priority"
                    class="mt-0.5 w-full rounded border-gray-300 px-2 py-1.5 text-xs shadow-sm"
                >
                    @foreach (['low', 'medium', 'high', 'urgent'] as $priority)
                        <option
                            value="{{ $priority }}"
                            @selected($task->priority === $priority)
                        >
                            {{ ucfirst($priority) }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Status --}}
            <div class="w-36">
                <label class="block text-[11px] font-medium text-gray-500">
                    Status
                </label>

                <select
                    name="statusid"
                    required
                    class="mt-0.5 w-full rounded border-gray-300 px-2 py-1.5 text-xs shadow-sm"
                >
                    @foreach (($statuses[$task->projectid] ?? collect()) as $status)
                        <option
                            value="{{ $status->id }}"
                            @selected((int) $task->statusid === (int) $status->id)
                        >
                            {{ $status->statuslabel }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Start date --}}
            <div class="w-32 shrink-0">
                <label class="block text-[11px] font-medium text-gray-500">
                    Start date
                </label>

                <input
                    id="outlook-start-date-{{ $task->id }}"
                    type="date"
                    name="startdate"
                    value="{{ $task->startdate?->format('Y-m-d') }}"
                    data-outlook-start-date="{{ $task->id }}"
                    class="mt-0.5 w-full rounded border-gray-300 px-2 py-1.5 text-xs shadow-sm"
                >
            </div>

            {{-- Due date --}}
            <div class="w-40 shrink-0">
                <label class="block text-[11px] font-medium text-gray-500">
                    Due date
                </label>

                <input
                    id="outlook-due-date-{{ $task->id }}"
                    type="date"
                    name="duedate"
                    value="{{ $task->duedate?->format('Y-m-d') }}"
                    min="{{ $task->startdate?->format('Y-m-d') }}"
                    data-outlook-due-date="{{ $task->id }}"
                    class="mt-0.5 w-full rounded border-gray-300 px-2 py-1.5 text-xs shadow-sm"
                >

                <div class="mt-1 flex gap-1 whitespace-nowrap">
                    <button
                        type="button"
                        data-reschedule-task="{{ $task->id }}"
                        data-date="{{ $today->toDateString() }}"
                        class="text-[9px] leading-none text-indigo-700 hover:underline"
                    >
                        Today
                    </button>

                    <button
                        type="button"
                        data-reschedule-task="{{ $task->id }}"
                        data-date="{{ $today->copy()->addDay()->toDateString() }}"
                        class="text-[9px] leading-none text-indigo-700 hover:underline"
                    >
                        Tomorrow
                    </button>

                    <button
                        type="button"
                        data-reschedule-task="{{ $task->id }}"
                        data-date="{{ $today->copy()->next(\Carbon\Carbon::MONDAY)->toDateString() }}"
                        class="text-[9px] leading-none text-indigo-700 hover:underline"
                    >
                        Mon
                    </button>
                </div>
            </div>
        </div>
        @if ($task->dependent_tasks_count > 0)
            <div class="w-full rounded border border-rose-200 bg-rose-50 px-2 py-1 text-[10px] text-rose-800">
                Changing this task’s status or dates may affect
                {{ $task->dependent_tasks_count }}
                downstream task{{ $task->dependent_tasks_count === 1 ? '' : 's' }}.
                <a href="{{ route('tasks.show', [
                        'task' => $task,
                        'from' => 'outlook',
                        'return' => url()->full(),
                    ]) }}"
                class="font-semibold underline hover:text-rose-950">
                    Review dependencies
                </a>
                before saving.
            </div>
        @endif
        {{-- Status comment and save action --}}
        <div class="flex items-center gap-3">
            <label for="statuscomment-{{ $task->id }}"
                class="shrink-0 text-[11px] font-medium text-gray-500">
                Status comment
            </label>

            <input type="text"
                name="statuscomment"
                id="statuscomment-{{ $task->id }}"
                value="{{ $task->statuscomment }}"
                placeholder="Progress, blocker, or next action"
                class="min-w-0 flex-1 rounded border-gray-300 px-2 py-1.5 text-xs shadow-sm">

            <button type="submit"
                    class="shrink-0 inline-flex items-center justify-center rounded bg-green-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-green-700">
                Save
            </button>
        </div>      
    </div>
</form>