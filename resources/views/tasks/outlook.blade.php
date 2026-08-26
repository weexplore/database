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

            <section class="overflow-hidden border border-fuchsia-200 bg-white shadow-sm sm:rounded-lg">
                <div class="border-b border-fuchsia-200 bg-fuchsia-50 px-4 py-3">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h3 class="text-sm font-semibold text-fuchsia-800">
                                Recurring Tasks to Create
                            </h3>

                            <p class="mt-1 text-xs text-fuchsia-700">
                                Recurrence templates scheduled for generation between today and {{ $weekEnd->format('d M Y') }}.
                            </p>
                        </div>

                        <span class="text-xs font-medium text-fuchsia-800">
                            {{ $recurringTasksToGenerate->count() }}
                            task{{ $recurringTasksToGenerate->count() === 1 ? '' : 's' }}
                        </span>
                    </div>
                </div>

                <div class="p-4">
                    @forelse($recurringTasksToGenerate as $scheduled)
                        @php
                            $template = $scheduled['template'];
                            $generationDate = $scheduled['generationDate'];
                            $nextDue = $scheduled['nextDue'];
                            $leadDays = $scheduled['leadDays'];

                            $intervalCount = max(
                                1,
                                (int) $scheduled['recurrence']->intervalcount
                            );

                            $frequencyLabel = match ($scheduled['recurrence']->frequency) {
                                'daily' => 'day',
                                'weekly' => 'week',
                                'monthly' => 'month',
                                'yearly' => 'year',
                                default => 'period',
                            };
                        @endphp

                        <div class="border-b border-gray-200 py-3 last:border-b-0">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div class="min-w-[260px] flex-1">
                                    <a href="{{ route('tasks.show', [
                                            'task' => $template,
                                            'from' => 'outlook',
                                        ]) }}"
                                    class="text-sm font-semibold text-indigo-700 hover:underline">
                                        {{ $template->tasktitle }}
                                    </a>

                                    <p class="mt-1 text-xs text-gray-500">
                                        {{ $template->project?->projectname ?? 'Project not available' }}
                                        · Every {{ $intervalCount }}
                                        {{ \Illuminate\Support\Str::plural($frequencyLabel, $intervalCount) }}
                                        · Due {{ $nextDue->format('d M Y') }}

                                        @if($leadDays > 0)
                                            · Created {{ $leadDays }}
                                            day{{ $leadDays === 1 ? '' : 's' }} early
                                        @endif
                                    </p>
                                </div>

                                <div class="shrink-0 text-right">
                                    <div class="text-xs font-medium text-gray-800">
                                        Generate {{ $generationDate->format('d M Y') }}
                                    </div>

                                    @if($generationDate->isSameDay($today))
                                        <div class="mt-1 text-[11px] font-medium text-amber-700">
                                            Due today
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">
                            No recurring task occurrences are scheduled for generation in the next seven days.
                        </p>
                    @endforelse
                </div>
            </section>

            <section class="overflow-hidden border border-red-200 bg-white shadow-sm sm:rounded-lg">
                <div class="border-b border-red-200 bg-red-50 px-4 py-3">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h3 class="text-sm font-semibold text-red-800">
                                Recurring Task Generation Overdue
                            </h3>

                            <p class="mt-1 text-xs text-red-700">
                                These occurrences should already have been generated. Check the scheduler if this list is unexpected.
                            </p>
                        </div>

                        <span class="text-xs font-medium text-red-700">
                            {{ $overdueRecurringTaskGenerations->count() }}
                            occurrence{{ $overdueRecurringTaskGenerations->count() === 1 ? '' : 's' }}
                        </span>
                    </div>
                </div>

                <div class="p-4">
                    @forelse($overdueRecurringTaskGenerations as $scheduled)
                        @php
                            $template = $scheduled['template'];
                        @endphp

                        <div class="border-b border-gray-200 py-3 last:border-b-0">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div class="min-w-[260px] flex-1">
                                    <a href="{{ route('tasks.show', [
                                            'task' => $template,
                                            'from' => 'outlook',
                                        ]) }}"
                                    class="text-sm font-semibold text-indigo-700 hover:underline">
                                        {{ $template->tasktitle }}
                                    </a>

                                    <p class="mt-1 text-xs text-gray-500">
                                        {{ $template->project?->projectname ?? 'Project not available' }}
                                        · Due {{ $scheduled['nextDue']->format('d M Y') }}
                                        · Expected generation {{ $scheduled['generationDate']->format('d M Y') }}
                                    </p>
                                </div>

                                <span class="inline-flex items-center rounded-full bg-red-100 px-2 py-0.5 text-[10px] font-semibold text-red-800">
                                    Generation overdue
                                </span>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">
                            No overdue recurring task generations.
                        </p>
                    @endforelse
                </div>
            </section>
            {{-- Knowledge Reminders --}}
            @php
                $overdueKnowledgeReminders = collect()
                    ->concat($overdueKnowledgeItemReviews->map(fn ($item) => [
                        'type' => 'item_review',
                        'dueDate' => $item->nextreviewdate,
                        'knowledgeItem' => $item,
                        'title' => 'Item review',
                        'detail' => $item->reviewnotes
                            ? \Illuminate\Support\Str::limit(strip_tags($item->reviewnotes), 120)
                            : null,
                        'tab' => 'details',
                    ]))
                    ->concat($overdueKnowledgeNoteReviews->map(fn ($note) => [
                        'type' => 'note_review',
                        'dueDate' => $note->reviewdate,
                        'knowledgeItem' => $note->knowledgeItem,
                        'title' => 'Note review',
                        'detail' => $note->title
                            ?: ($note->notetype ? ucfirst($note->notetype) . ' note' : 'Knowledge note'),
                        'tab' => 'notes',
                    ]))
                    ->concat($overdueKnowledgeReviewFollowUps->map(fn ($reviewLog) => [
                        'type' => 'review_followup',
                        'dueDate' => $reviewLog->nextreviewdate,
                        'knowledgeItem' => $reviewLog->knowledgeItem,
                        'title' => 'Review follow-up',
                        'detail' => $reviewLog->reviewtype
                            ? ucfirst(str_replace('_', ' ', $reviewLog->reviewtype))
                            : null,
                        'tab' => 'details',
                    ]))
                    ->sortBy('dueDate')
                    ->values();

                $knowledgeRemindersDueToday = collect()
                    ->concat($knowledgeItemReviewsDueToday->map(fn ($item) => [
                        'type' => 'item_review',
                        'dueDate' => $item->nextreviewdate,
                        'knowledgeItem' => $item,
                        'title' => 'Item review',
                        'detail' => $item->reviewnotes
                            ? \Illuminate\Support\Str::limit(strip_tags($item->reviewnotes), 120)
                            : null,
                        'tab' => 'details',
                    ]))
                    ->concat($knowledgeNoteReviewsDueToday->map(fn ($note) => [
                        'type' => 'note_review',
                        'dueDate' => $note->reviewdate,
                        'knowledgeItem' => $note->knowledgeItem,
                        'title' => 'Note review',
                        'detail' => $note->title
                            ?: ($note->notetype ? ucfirst($note->notetype) . ' note' : 'Knowledge note'),
                        'tab' => 'notes',
                    ]))
                    ->concat($knowledgeReviewFollowUpsDueToday->map(fn ($reviewLog) => [
                        'type' => 'review_followup',
                        'dueDate' => $reviewLog->nextreviewdate,
                        'knowledgeItem' => $reviewLog->knowledgeItem,
                        'title' => 'Review follow-up',
                        'detail' => $reviewLog->reviewtype
                            ? ucfirst(str_replace('_', ' ', $reviewLog->reviewtype))
                            : null,
                        'tab' => 'details',
                    ]))
                    ->sortBy('dueDate')
                    ->values();

                $upcomingKnowledgeReminders = collect()
                    ->concat($upcomingKnowledgeItemReviews->map(fn ($item) => [
                        'type' => 'item_review',
                        'dueDate' => $item->nextreviewdate,
                        'knowledgeItem' => $item,
                        'title' => 'Item review',
                        'detail' => $item->reviewnotes
                            ? \Illuminate\Support\Str::limit(strip_tags($item->reviewnotes), 120)
                            : null,
                        'tab' => 'details',
                    ]))
                    ->concat($upcomingKnowledgeNoteReviews->map(fn ($note) => [
                        'type' => 'note_review',
                        'dueDate' => $note->reviewdate,
                        'knowledgeItem' => $note->knowledgeItem,
                        'title' => 'Note review',
                        'detail' => $note->title
                            ?: ($note->notetype ? ucfirst($note->notetype) . ' note' : 'Knowledge note'),
                        'tab' => 'notes',
                    ]))
                    ->concat($upcomingKnowledgeReviewFollowUps->map(fn ($reviewLog) => [
                        'type' => 'review_followup',
                        'dueDate' => $reviewLog->nextreviewdate,
                        'knowledgeItem' => $reviewLog->knowledgeItem,
                        'title' => 'Review follow-up',
                        'detail' => $reviewLog->reviewtype
                            ? ucfirst(str_replace('_', ' ', $reviewLog->reviewtype))
                            : null,
                        'tab' => 'details',
                    ]))
                    ->sortBy('dueDate')
                    ->values();
            @endphp

            {{-- Knowledge reminders --}}
            <section class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-rose-200">
                <div class="px-4 py-3 border-b border-rose-200 bg-rose-50">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h3 class="text-sm font-semibold text-rose-800">
                                Overdue Knowledge Reviews
                            </h3>

                            <p class="mt-1 text-xs text-rose-700">
                                Knowledge items, notes, and review follow-ups due before today.
                            </p>
                        </div>

                        <span class="text-xs font-medium text-rose-700">
                            {{ $overdueKnowledgeReminders->count() }}
                            reminder{{ $overdueKnowledgeReminders->count() === 1 ? '' : 's' }}
                        </span>
                    </div>
                </div>

                <div class="p-4">
                    @forelse($overdueKnowledgeReminders as $reminder)
                        @include('tasks.partials.outlook-knowledge-reminder-row', [
                            'reminder' => $reminder,
                            'today' => $today,
                        ])
                    @empty
                        <p class="text-sm text-gray-500">
                            No overdue Knowledge reviews.
                        </p>
                    @endforelse
                </div>
            </section>

            <section class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-amber-200">
                <div class="px-4 py-3 border-b border-amber-200 bg-amber-50">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h3 class="text-sm font-semibold text-amber-800">
                                Knowledge Reviews Due Today
                            </h3>

                            <p class="mt-1 text-xs text-amber-700">
                                {{ $today->format('l, j F Y') }}
                            </p>
                        </div>

                        <span class="text-xs font-medium text-amber-800">
                            {{ $knowledgeRemindersDueToday->count() }}
                            reminder{{ $knowledgeRemindersDueToday->count() === 1 ? '' : 's' }}
                        </span>
                    </div>
                </div>

                <div class="p-4">
                    @forelse($knowledgeRemindersDueToday as $reminder)
                        @include('tasks.partials.outlook-knowledge-reminder-row', [
                            'reminder' => $reminder,
                            'today' => $today,
                        ])
                    @empty
                        <p class="text-sm text-gray-500">
                            No Knowledge reviews are due today.
                        </p>
                    @endforelse
                </div>
            </section>

            <section class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-sky-200">
                <div class="px-4 py-3 border-b border-sky-200 bg-sky-50">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h3 class="text-sm font-semibold text-sky-800">
                                Upcoming Knowledge Reviews
                            </h3>

                            <p class="mt-1 text-xs text-sky-700">
                                Due from {{ $today->copy()->addDay()->format('l, j F Y') }}
                                to {{ $weekEnd->format('l, j F Y') }}.
                            </p>
                        </div>

                        <span class="text-xs font-medium text-sky-800">
                            {{ $upcomingKnowledgeReminders->count() }}
                            reminder{{ $upcomingKnowledgeReminders->count() === 1 ? '' : 's' }}
                        </span>
                    </div>
                </div>

                <div class="p-4">
                    @forelse($upcomingKnowledgeReminders as $reminder)
                        @include('tasks.partials.outlook-knowledge-reminder-row', [
                            'reminder' => $reminder,
                            'today' => $today,
                        ])
                    @empty
                        <p class="text-sm text-gray-500">
                            No Knowledge reviews are due in the next seven days.
                        </p>
                    @endforelse
                </div>
            </section>

            <section class="overflow-hidden border border-indigo-200 bg-white shadow-sm sm:rounded-lg">
                <div class="border-b border-indigo-200 bg-indigo-50 px-4 py-3">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h3 class="text-sm font-semibold text-indigo-800">
                                Upcoming Trips
                            </h3>

                            <p class="mt-1 text-xs text-indigo-700">
                                Planned trips starting within the next seven days, plus active trips.
                            </p>
                        </div>

                        <span class="text-xs font-medium text-indigo-800">
                            {{ $upcomingTrips->count() }}
                            trip{{ $upcomingTrips->count() === 1 ? '' : 's' }}
                        </span>
                    </div>
                </div>

                <div class="p-4">
                    @forelse($upcomingTrips as $trip)
                        <div class="border-b border-gray-200 py-3 last:border-b-0">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div class="min-w-[260px] flex-1">
                                    <a href="{{ route('trips.edit', $trip) }}"
                                    class="text-sm font-semibold text-indigo-700 hover:underline">
                                        {{ $trip->tripname }}
                                    </a>

                                    <p class="mt-1 text-xs text-gray-500">
                                        Status:
                                        {{ ucfirst($trip->tripstatus) }}

                                        · Start:
                                        {{ $trip->startdate?->format('d M Y') ?? 'Not set' }}

                                        @if($trip->enddate)
                                            · End: {{ $trip->enddate->format('d M Y') }}
                                        @endif
                                    </p>
                                </div>

                                @if($trip->tripstatus === 'active')
                                    <span class="inline-flex items-center rounded-full bg-green-100 px-2 py-0.5 text-[10px] font-semibold text-green-800">
                                        Active
                                    </span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">
                            No planned or active trips need attention in the next seven days.
                        </p>
                    @endforelse
                </div>
            </section>

            <section class="overflow-hidden border border-cyan-200 bg-white shadow-sm sm:rounded-lg">
                <div class="border-b border-cyan-200 bg-cyan-50 px-4 py-3">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h3 class="text-sm font-semibold text-cyan-800">
                                Upcoming Trip Items
                            </h3>

                            <p class="mt-1 text-xs text-cyan-700">
                                Incomplete activities, tasks, bookings, and other Trip Items scheduled through {{ $weekEnd->format('d M Y') }}.
                            </p>
                        </div>

                        <span class="text-xs font-medium text-cyan-800">
                            {{ $upcomingTripItems->count() }}
                            item{{ $upcomingTripItems->count() === 1 ? '' : 's' }}
                        </span>
                    </div>
                </div>

                <div class="p-4">
                    @forelse($upcomingTripItems as $tripItem)
                        @php
                            $location = $tripItem->place?->placename
                                ?? $tripItem->destination?->destinationname
                                ?? $tripItem->destinationItem?->destination?->destinationname;
                        @endphp

                        <div class="border-b border-gray-200 py-3 last:border-b-0">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div class="min-w-[260px] flex-1">
                                    <a href="{{ route('trips.items.edit', [
                                            'trip' => $tripItem->trip,
                                            'tripItem' => $tripItem,
                                        ]) }}"
                                    class="text-sm font-semibold text-indigo-700 hover:underline">
                                        {{ $tripItem->title }}
                                    </a>

                                    <p class="mt-1 text-xs text-gray-500">
                                        {{ $tripItem->trip?->tripname ?? 'Trip' }}
                                        · {{ $tripItem->itemdate?->format('d M Y') ?? 'Date not set' }}
                                        · {{ $tripItem->itemtype ?: 'Item' }}
                                        · {{ ucfirst($tripItem->status) }}

                                        @if($location)
                                            · {{ $location }}
                                        @endif
                                    </p>
                                </div>

                                @if($tripItem->priority === 'high')
                                    <span class="inline-flex items-center rounded-full bg-red-100 px-2 py-0.5 text-[10px] font-semibold text-red-800">
                                        High priority
                                    </span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">
                            No incomplete Trip Items are scheduled in the next seven days.
                        </p>
                    @endforelse
                </div>
            </section>

            <section class="overflow-hidden border border-orange-200 bg-white shadow-sm sm:rounded-lg">
                <div class="border-b border-orange-200 bg-orange-50 px-4 py-3">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h3 class="text-sm font-semibold text-orange-800">
                                Expiring Knowledge Attachments
                            </h3>

                            <p class="mt-1 text-xs text-orange-700">
                                Attachments expiring from today through
                                {{ $today->copy()->addDays(14)->format('d M Y') }}.
                            </p>
                        </div>

                        <span class="text-xs font-medium text-orange-800">
                            {{ $expiringKnowledgeAttachments->count() }}
                            attachment{{ $expiringKnowledgeAttachments->count() === 1 ? '' : 's' }}
                        </span>
                    </div>
                </div>

                <div class="p-4">
                    @forelse($expiringKnowledgeAttachments as $expiry)
                        @php
                            $expiryDate = \Illuminate\Support\Carbon::parse(
                                $expiry->expirydate
                            )->startOfDay();

                            $daysRemaining = $today->diffInDays($expiryDate, false);
                        @endphp

                        <div class="border-b border-gray-200 py-3 last:border-b-0">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div class="min-w-[260px] flex-1">
                                    <a href="{{ route('knowledge.items.edit', [
                                            'knowledgeItem' => $expiry->knowledge_item_id,
                                            'tab' => 'attachments',
                                        ]) }}"
                                    class="text-sm font-semibold text-indigo-700 hover:underline">
                                        {{ $expiry->knowledge_item_name }}
                                    </a>

                                    <p class="mt-1 text-xs text-gray-500">
                                        {{ $expiry->category_name ?: 'Uncategorised' }}
                                        · {{ $expiry->knowledge_item_type ?: 'Knowledge item' }}
                                        · {{ $expiry->attachmenttype ?: 'Attachment' }}
                                        · {{ $expiry->originalfilename ?: 'Unnamed file' }}
                                    </p>

                                    @if(filled($expiry->link_description))
                                        <p class="mt-1 text-xs text-gray-600">
                                            {{ \Illuminate\Support\Str::limit(
                                                $expiry->link_description,
                                                180
                                            ) }}
                                        </p>
                                    @endif
                                </div>

                                <div class="shrink-0 text-right">
                                    <div class="text-xs font-medium text-gray-800">
                                        Expires {{ $expiryDate->format('d M Y') }}
                                    </div>

                                    @if($daysRemaining === 0)
                                        <div class="mt-1 text-[11px] font-medium text-red-700">
                                            Expires today
                                        </div>
                                    @elseif($daysRemaining === 1)
                                        <div class="mt-1 text-[11px] font-medium text-amber-700">
                                            Expires tomorrow
                                        </div>
                                    @else
                                        <div class="mt-1 text-[11px] font-medium text-orange-700">
                                            {{ $daysRemaining }} days remaining
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">
                            No Knowledge attachments expire in the next 14 days.
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