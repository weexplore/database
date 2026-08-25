<?php

use App\Models\Task;
use App\Models\TaskRecurrence;
use Carbon\Carbon;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('tasks:generate-recurring', function () {
    $today = now()->startOfDay();

    $recurrences = TaskRecurrence::query()
        ->where('isactive', 1)
        ->whereDate('startsonoccurrence', '<=', $today->toDateString())
        ->where(function ($query) use ($today) {
            $query->whereNull('endsonoccurrence')
                ->orWhereDate('endsonoccurrence', '>=', $today->toDateString());
        })
        ->get();

    $createdCount = 0;
    $skippedCount = 0;

    /*
     * Copies a child task's date while retaining its offset from the
     * corresponding parent template task.
     */
    $cloneRecurringTaskDate = function (
        ?string $templateParentDate,
        ?string $templateChildDate,
        ?string $newParentDate
    ): ?string {
        if (! $templateChildDate || ! $newParentDate) {
            return null;
        }

        if (! $templateParentDate) {
            return Carbon::parse($newParentDate)
                ->startOfDay()
                ->toDateString();
        }

        $templateParent = Carbon::parse($templateParentDate)->startOfDay();
        $templateChild = Carbon::parse($templateChildDate)->startOfDay();
        $newParent = Carbon::parse($newParentDate)->startOfDay();

        return $newParent
            ->copy()
            ->addDays(
                $templateParent->diffInDays($templateChild, false)
            )
            ->toDateString();
    };

    foreach ($recurrences as $recurrence) {
        $template = Task::query()
            ->with([
                'project',
                'labels',
                'subtasks.labels',
                'subtasks.subtasks.labels',
                'subtasks.subtasks.subtasks.labels',
            ])
            ->find($recurrence->tasktemplateid);

        if (! $template) {
            $this->warn(
                "Skipped recurrence {$recurrence->id}: "
                . "task template {$recurrence->tasktemplateid} was not found."
            );

            $skippedCount++;

            continue;
        }

        /*
         * Generated work must start in an open status, even if the
         * recurrence template was closed/completed after prior use.
         */
        $generatedTaskStatusId = $template->project
            ->taskStatuses()
            ->where('iscompletedstatus', false)
            ->orderBy('sortorder')
            ->value('id');

        if (! $generatedTaskStatusId) {
            $this->warn(
                "Skipped recurrence {$recurrence->id}: "
                . "project {$template->projectid} has no open task status."
            );

            $skippedCount++;

            continue;
        }

        $recurrenceRootId = $template->recurrencerootid ?? $template->id;


        if ($recurrence->maxoccurrences) {
            $generatedCount = Task::query()
                ->where('recurrencerootid', $recurrenceRootId)
                ->whereNull('parenttaskid')
                ->count();

            if ($generatedCount >= $recurrence->maxoccurrences) {
                $this->line(
                    "Skipped recurrence {$recurrence->id}: "
                    . "maximum of {$recurrence->maxoccurrences} occurrences reached."
                );

                $skippedCount++;

                continue;
            }
        }

        $interval = max(1, (int) $recurrence->intervalcount);

        /*
         * On its first run, startsonoccurrence is itself the first due date.
         * Thereafter, calculate from the date of the previously generated
         * occurrence.
         */
        if ($recurrence->lastgeneratedon) {
            $baseDate = Carbon::parse(
                $recurrence->lastgeneratedon
            )->startOfDay();

            $nextDue = match ($recurrence->frequency) {
                'daily' => $baseDate->copy()->addDays($interval),
                'weekly' => $baseDate->copy()->addWeeks($interval),
                'monthly' => $baseDate->copy()->addMonthsNoOverflow($interval),
                'yearly' => $baseDate->copy()->addYearsNoOverflow($interval),
                default => null,
            };
        } else {
            $nextDue = Carbon::parse(
                $recurrence->startsonoccurrence
            )->startOfDay();
        }

        if (! $nextDue) {
            $this->warn(
                "Skipped recurrence {$recurrence->id}: "
                . "unknown frequency '{$recurrence->frequency}'."
            );

            $skippedCount++;

            continue;
        }

        $leadDays = max(
            0,
            (int) ($recurrence->leaddaysbeforedue ?? 0)
        );

        /*
         * A task is generated at its due date minus lead days.
         * The task itself keeps the calculated due date.
         */
        $nextGenerationDate = $nextDue
            ->copy()
            ->subDays($leadDays);

        if ($nextGenerationDate->greaterThan($today)) {
            continue;
        }

        DB::transaction(function () use (
            $template,
            $recurrence,
            $nextDue,
            $leadDays,
            $generatedTaskStatusId,
            $cloneRecurringTaskDate,
            &$createdCount
        ) {
            $newTask = Task::create([
                'projectid' => $template->projectid,
                'parenttaskid' => null,
                'statusid' => $generatedTaskStatusId,
                'tasktitle' => $template->tasktitle,
                'description' => $template->description,
                'priority' => $template->priority,
                'assignedto' => $template->assignedto,
                'startdate' => $nextDue
                    ->copy()
                    ->subDays($leadDays)
                    ->toDateString(),
                'duedate' => $nextDue->toDateString(),
                'sortorder' => $template->sortorder ?? 0,
                'isrecurringtemplate' => 0,
                'generatedfromtemplateid' => $template->id,
                'estimatedefforthours' => $template->estimatedefforthours,
                'actualefforthours' => null,
                'taskexpectation' => $template->taskexpectation,
                'statuscomment' => null,
                'completedat' => null,
                'recurrencerootid' => $recurrence->recurrencerootid ?? $template->id,
            ]);

            $newTask->labels()->sync(
                $template->labels->pluck('id')->all()
            );

            $cloneSubtasks = function (
                Task $templateParent,
                Task $newParent
            ) use (
                &$cloneSubtasks,
                $cloneRecurringTaskDate,
                $generatedTaskStatusId
            ) {
                $templateParent->loadMissing('subtasks.labels');

                foreach ($templateParent->subtasks as $templateSubtask) {
                    $newSubtask = Task::create([
                        'projectid' => $newParent->projectid,
                        'parenttaskid' => $newParent->id,
                        'statusid' => $generatedTaskStatusId,
                        'tasktitle' => $templateSubtask->tasktitle,
                        'description' => $templateSubtask->description,
                        'priority' => $templateSubtask->priority,
                        'assignedto' => $templateSubtask->assignedto,
                        'startdate' => $cloneRecurringTaskDate(
                            $templateParent->startdate,
                            $templateSubtask->startdate,
                            $newParent->startdate
                        ),
                        'duedate' => $cloneRecurringTaskDate(
                            $templateParent->duedate,
                            $templateSubtask->duedate,
                            $newParent->duedate
                        ),
                        'sortorder' => $templateSubtask->sortorder ?? 0,
                        'isrecurringtemplate' => 0,
                        'generatedfromtemplateid' => $templateSubtask->id,
                        'estimatedefforthours' => $templateSubtask->estimatedefforthours,
                        'actualefforthours' => null,
                        'taskexpectation' => $templateSubtask->taskexpectation,
                        'statuscomment' => null,
                        'completedat' => null,
                    ]);

                    $newSubtask->labels()->sync(
                        $templateSubtask->labels->pluck('id')->all()
                    );

                    $cloneSubtasks($templateSubtask, $newSubtask);
                }
            };

            $cloneSubtasks($template, $newTask);

            /*
            * Roll the recurrence forward to the newly-created task.
            *
            * The just-created open task becomes the editable template for the
            * following occurrence. The former template becomes ordinary history.
            */
            $template->isrecurringtemplate = 0;
            $template->save();

            $newTask->isrecurringtemplate = 1;
            $newTask->save();

            $recurrence->tasktemplateid = $newTask->id;
            $recurrence->lastgeneratedon = $nextDue->toDateString();
            $recurrence->save();

            $createdCount++;
        });
    }

    $this->info(
        "Recurring task generation completed. "
        . "Created: {$createdCount}. Skipped: {$skippedCount}."
    );
})->describe('Generate tasks from recurring templates');