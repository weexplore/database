<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Models\TaskRecurrence;
use App\Models\Task;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('tasks:generate-recurring', function () {
    $today = now()->toDateString();

    $recurrences = TaskRecurrence::where('isactive', 1)
        ->whereDate('startsonoccurrence', '<=', $today)
        ->where(function ($q) use ($today) {
            $q->whereNull('endsonoccurrence')
              ->orWhereDate('endsonoccurrence', '>=', $today);
        })
        ->get();

    foreach ($recurrences as $rec) {
        $template = Task::find($rec->tasktemplateid);
        if (! $template) continue;

        // simple example: daily/weekly/monthly based on intervalcount
        // and lastgeneratedon / maxoccurrences
        // (you can expand this to use weekdaymask/monthday later)

        // Decide next due date
        $base = $rec->lastgeneratedon
            ? \Carbon\Carbon::parse($rec->lastgeneratedon)
            : \Carbon\Carbon::parse($rec->startsonoccurrence);

        $nextDue = match ($rec->frequency) {
            'daily' => $base->copy()->addDays($rec->intervalcount),
            'weekly' => $base->copy()->addWeeks($rec->intervalcount),
            'monthly' => $base->copy()->addMonths($rec->intervalcount),
            'yearly' => $base->copy()->addYears($rec->intervalcount),
            default => null,
        };

        if (! $nextDue) continue;
        if ($nextDue->toDateString() > $today) continue;

        if ($rec->maxoccurrences && Task::where('generatedfromtemplateid', $template->id)->count() >= $rec->maxoccurrences) {
            continue;
        }

        // Create new task from template
        $newTask = Task::create([
            'projectid' => $template->projectid,
            'parenttaskid' => null,
            'statusid' => $template->statusid,
            'tasktitle' => $template->tasktitle,
            'description' => $template->description,
            'priority' => $template->priority,
            'assignedto' => $template->assignedto,
            'startdate' => $nextDue->copy()->subDays($rec->leaddaysbeforedue)->toDateString(),
            'duedate' => $nextDue->toDateString(),
            'sortorder' => 0,
            'isrecurringtemplate' => 0,
            'generatedfromtemplateid' => $template->id,
        ]);

        // copy labels, etc.
        $newTask->labels()->sync($template->labels->pluck('id'));

        $rec->lastgeneratedon = $nextDue->toDateString();
        $rec->save();
    }

    $this->info('Recurring tasks generated.');
})->describe('Generate tasks from recurring templates');
