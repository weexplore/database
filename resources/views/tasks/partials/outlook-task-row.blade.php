<form method="POST"
      action="{{ route('tasks.outlook.update', $task) }}"
      class="border-b border-gray-200 py-3 last:border-b-0">
    @csrf
    @method('PATCH')

    <div class="flex flex-col gap-2">

        {{-- Compact main line --}}
        <div class="flex flex-wrap items-end gap-x-3 gap-y-2">

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

                <input type="number"
                       name="actualefforthours"
                       min="0"
                       max="9999.99"
                       step="0.25"
                       value="{{ $task->actualefforthours }}"
                       class="mt-0.5 w-full rounded border-gray-300 px-2 py-1.5 text-xs shadow-sm">
            </div>

            {{-- Status --}}
            <div class="w-36">
                <label class="block text-[11px] font-medium text-gray-500">
                    Status
                </label>

                <select name="statusid"
                        required
                        class="mt-0.5 w-full rounded border-gray-300 px-2 py-1.5 text-xs shadow-sm">
                    @foreach (($statuses[$task->projectid] ?? collect()) as $status)
                        <option value="{{ $status->id }}"
                                @selected((int) $task->statusid === (int) $status->id)>
                            {{ $status->statuslabel }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Due date and actions --}}
            <div class="w-40 shrink-0">
                <label class="block text-[11px] font-medium text-gray-500">
                    Due date
                </label>

                <input id="outlook-due-date-{{ $task->id }}"
                    type="date"
                    name="duedate"
                    value="{{ $task->duedate?->format('Y-m-d') }}"
                    class="mt-0.5 w-full rounded border-gray-300 px-2 py-1.5 text-xs shadow-sm">

                <div class="mt-1">
                    <div class="mt-1 flex gap-1 whitespace-nowrap">
                        <button type="button"
                                data-reschedule-task="{{ $task->id }}"
                                data-date="{{ $today->toDateString() }}"
                                class="text-[9px] leading-none text-indigo-700 hover:underline">
                            Today
                        </button>

                        <button type="button"
                                data-reschedule-task="{{ $task->id }}"
                                data-date="{{ $today->copy()->addDay()->toDateString() }}"
                                class="text-[9px] leading-none text-indigo-700 hover:underline">
                            Tomorrow
                        </button>

                        <button type="button"
                                data-reschedule-task="{{ $task->id }}"
                                data-date="{{ $today->copy()->next(\Carbon\Carbon::MONDAY)->toDateString() }}"
                                class="text-[9px] leading-none text-indigo-700 hover:underline">
                            Mon
                        </button>
                    </div>

                    <div class="mt-1 flex justify-end">
                        <button type="submit"
                                class="inline-flex items-center justify-center rounded bg-green-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-green-700">
                            Save
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Compact second line: status comment --}}
        <div class="flex items-center gap-2">
            <label class="shrink-0 text-[11px] font-medium text-gray-500">
                Status comment
            </label>

            <input type="text"
                   name="statuscomment"
                   value="{{ $task->statuscomment }}"
                   placeholder="Progress, blocker, or next action"
                   class="min-w-0 flex-1 rounded border-gray-300 px-2 py-1.5 text-xs shadow-sm">
        </div>
    </div>
</form>