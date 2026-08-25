<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Task Outlook
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    Overdue tasks, tasks due today, and scheduled work for the next seven days.
                </p>
            </div>

            <a href="{{ route('tasksall.all') }}"
               class="inline-flex items-center px-3 py-1.5 bg-gray-100 text-gray-700 text-xs font-medium rounded border border-gray-300 hover:bg-gray-200">
                All Tasks
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="w-full max-w-none mx-auto px-4 sm:px-6 lg:px-8 xl:px-10 2xl:px-12 space-y-6">

            @include('partials.admin.flash-messages')
            @include('partials.admin.validation-summary')

            {{-- Overdue --}}
            <section class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-red-200">
                <div class="px-4 py-3 border-b border-red-200 bg-red-50">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h3 class="text-sm font-semibold text-red-800">
                                Overdue
                            </h3>

                            <p class="mt-1 text-xs text-red-700">
                                Open tasks due before {{ $today->format('l, j F Y') }}.
                            </p>
                        </div>

                        <span class="text-xs font-medium text-red-700">
                            {{ $overdueTasks->count() }}
                            task{{ $overdueTasks->count() === 1 ? '' : 's' }}
                        </span>
                    </div>
                </div>

                <div class="p-4">
                    @forelse ($overdueTasks as $task)
                        @include('tasks.partials.outlook-task-row', [
                            'task' => $task,
                            'statuses' => $statuses,
                            'today' => $today,
                        ])
                    @empty
                        <p class="text-sm text-gray-500">
                            No overdue open tasks.
                        </p>
                    @endforelse
                </div>
            </section>

            {{-- Due today --}}
            <section class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-amber-200">
                <div class="px-4 py-3 border-b border-amber-200 bg-amber-50">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h3 class="text-sm font-semibold text-amber-800">
                                Due today
                            </h3>

                            <p class="mt-1 text-xs text-amber-700">
                                {{ $today->format('l, j F Y') }}
                            </p>
                        </div>

                        @php
                            $estimatedTotal = $dueTodayTasks->sum(
                                fn ($task) => (float) ($task->estimatedefforthours ?? 0)
                            );

                            $actualTotal = $dueTodayTasks->sum(
                                fn ($task) => (float) ($task->actualefforthours ?? 0)
                            );
                        @endphp

                        <p class="text-xs text-amber-800">
                            Estimated:
                            {{ rtrim(rtrim(number_format($estimatedTotal, 2), '0'), '.') }}h
                            · Actual:
                            {{ rtrim(rtrim(number_format($actualTotal, 2), '0'), '.') }}h
                        </p>
                    </div>
                </div>

                <div class="p-4">
                    @forelse ($dueTodayTasks as $task)
                        @include('tasks.partials.outlook-task-row', [
                            'task' => $task,
                            'statuses' => $statuses,
                            'today' => $today,
                        ])
                    @empty
                        <p class="text-sm text-gray-500">
                            No open tasks are due today.
                        </p>
                    @endforelse
                </div>
            </section>

            {{-- Work in progress --}}
            <section class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-violet-200">
                <div class="px-4 py-3 border-b border-violet-200 bg-violet-50">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h3 class="text-sm font-semibold text-violet-800">
                                Work in progress
                            </h3>

                            <p class="mt-1 text-xs text-violet-700">
                                Open tasks that have started and are due after today.
                            </p>
                        </div>

                        @php
                            $estimatedTotal = $inProgressTasks->sum(
                                fn ($task) => (float) ($task->estimatedefforthours ?? 0)
                            );

                            $actualTotal = $inProgressTasks->sum(
                                fn ($task) => (float) ($task->actualefforthours ?? 0)
                            );
                        @endphp

                        <p class="text-xs text-violet-800">
                            {{ $inProgressTasks->count() }}
                            task{{ $inProgressTasks->count() === 1 ? '' : 's' }}
                            · Estimated:
                            {{ rtrim(rtrim(number_format($estimatedTotal, 2), '0'), '.') }}h
                            · Actual:
                            {{ rtrim(rtrim(number_format($actualTotal, 2), '0'), '.') }}h
                        </p>
                    </div>
                </div>

                <div class="p-4">
                    @forelse ($inProgressTasks as $task)
                        @include('tasks.partials.outlook-task-row', [
                            'task' => $task,
                            'statuses' => $statuses,
                            'today' => $today,
                        ])
                    @empty
                        <p class="text-sm text-gray-500">
                            No open tasks are currently in progress.
                        </p>
                    @endforelse
                </div>
            </section>

            {{-- Upcoming starts and deadlines --}}
            <section class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-sky-200">
                <div class="px-4 py-3 border-b border-sky-200 bg-sky-50">
                    <h3 class="text-sm font-semibold text-sky-800">
                        Upcoming Starts and Deadlines
                    </h3>

                    <p class="mt-1 text-xs text-sky-700">
                        Tasks beginning or due between
                        {{ $today->copy()->addDay()->format('l, j F Y') }}
                        and
                        {{ $weekEnd->format('l, j F Y') }}.
                    </p>
                </div>

                <div class="p-4 space-y-5">
                    @forelse ($upcomingTasks as $date => $tasksForDay)
                        @php
                            $day = \Carbon\Carbon::parse($date);

                            $estimatedTotal = $tasksForDay->sum(
                                fn ($task) => (float) ($task->estimatedefforthours ?? 0)
                            );

                            $actualTotal = $tasksForDay->sum(
                                fn ($task) => (float) ($task->actualefforthours ?? 0)
                            );
                        @endphp

                        <div class="rounded-lg border border-gray-200">
                            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-200 bg-gray-50 px-4 py-3">
                                <div>
                                    <h4 class="text-sm font-semibold text-gray-800">
                                        {{ $day->format('l, j F Y') }}
                                    </h4>

                                    <p class="mt-1 text-xs text-gray-500">
                                        {{ $tasksForDay->count() }}
                                        task{{ $tasksForDay->count() === 1 ? '' : 's' }}
                                    </p>
                                </div>

                                <p class="text-xs text-gray-600">
                                    Estimated:
                                    {{ rtrim(rtrim(number_format($estimatedTotal, 2), '0'), '.') }}h
                                    · Actual:
                                    {{ rtrim(rtrim(number_format($actualTotal, 2), '0'), '.') }}h
                                </p>
                            </div>

                            <div class="p-4">
                                @foreach ($tasksForDay as $task)
                                    @include('tasks.partials.outlook-task-row', [
                                        'task' => $task,
                                        'statuses' => $statuses,
                                        'today' => $today,
                                    ])
                                @endforeach
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">
                            No open tasks are scheduled for the next seven days.
                        </p>
                    @endforelse
                </div>
            </section>
            {{-- Today's activity --}}
            <section class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-emerald-200">
                <div class="px-4 py-3 border-b border-emerald-200 bg-emerald-50">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h3 class="text-sm font-semibold text-emerald-800">
                                Today’s Activity
                            </h3>

                            <p class="mt-1 text-xs text-emerald-700">
                                Tasks updated or completed today, plus comments posted today.
                            </p>
                        </div>

                        <span class="text-xs font-medium text-emerald-800">
                            {{ $todaysActivity->count() }}
                            task{{ $todaysActivity->count() === 1 ? '' : 's' }}
                        </span>
                    </div>
                </div>

                <div class="p-4">
                    @forelse ($todaysActivity as $activity)
                        @php
                            $activityTask = $activity['task'];
                            $comments = $activity['comments'];
                        @endphp

                        <div class="border-b border-gray-200 py-3 last:border-b-0">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div class="min-w-[260px] flex-1">
                                    <a href="{{ route('tasks.show', [
                                            'task' => $activityTask,
                                            'from' => 'outlook',
                                            'return' => url()->full(),
                                        ]) }}"
                                    class="text-sm font-semibold text-indigo-700 hover:underline">
                                        {{ $activityTask->tasktitle }}
                                    </a>

                                    <p class="mt-0.5 text-[11px] text-gray-500">
                                        {{ $activityTask->project->projectname }}

                                        @if ($activityTask->parentTask)
                                            · Subtask of
                                            <a href="{{ route('tasks.show', [
                                                    'task' => $activityTask->parentTask,
                                                    'from' => 'outlook',
                                                    'return' => url()->full(),
                                                ]) }}"
                                            class="text-indigo-700 hover:underline">
                                                {{ $activityTask->parentTask->tasktitle }}
                                            </a>
                                        @endif
                                    </p>
                                </div>

                                <div class="flex flex-wrap items-center gap-1">
                                    @if ($activity['completed'])
                                        <span class="inline-flex items-center rounded-full bg-green-100 px-2 py-0.5 text-[10px] font-semibold text-green-800">
                                            Completed today
                                        </span>
                                    @endif

                                    @if ($activity['updated'])
                                        <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-semibold text-slate-700">
                                            Updated today
                                        </span>
                                    @endif

                                    @if ($comments->isNotEmpty())
                                        <span class="inline-flex items-center rounded-full bg-blue-100 px-2 py-0.5 text-[10px] font-semibold text-blue-800">
                                            {{ $comments->count() }}
                                            comment{{ $comments->count() === 1 ? '' : 's' }}
                                            today
                                        </span>
                                    @endif
                                </div>
                            </div>

                            @if ($comments->isNotEmpty())
                                <div class="mt-2 space-y-1">
                                    @foreach ($comments as $comment)
                                        <div class="rounded border border-blue-100 bg-blue-50 px-2 py-1.5 text-xs text-blue-900">
                                            <span class="font-semibold">
                                                {{ $comment->user->name ?? 'Ian Seaman' }}
                                            </span>

                                            <span class="ml-1 text-blue-700">
                                                {{ $comment->createdat
                                                    ?->shiftTimezone('Australia/Sydney')
                                                    ->format('g:i A') }}
                                            </span>

                                            <span class="ml-1">
                                                {{ $comment->commenttext }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">
                            No task activity has been recorded today.
                        </p>
                    @endforelse
                </div>
            </section>
        </div>
    </div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-reschedule-task]').forEach(function (button) {
            button.addEventListener('click', function () {
                const taskId = button.dataset.rescheduleTask;
                const date = button.dataset.date;

                const dueDateInput = document.getElementById(
                    'outlook-due-date-' + taskId
                );

                if (dueDateInput) {
                    dueDateInput.value = date;
                }
            });
        });
    });
</script>
</x-app-layout>