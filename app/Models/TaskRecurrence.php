<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class TaskRecurrence extends Model
{
   protected $table = 'task_recurrence';
    public $timestamps = false;

    protected $fillable = [
        'tasktemplateid',
        'frequency',
        'intervalcount',
        'weekdaymask',
        'monthday',
        'monthlypattern',
        'monthweeknumber',
        'monthweekday',
        'startsonoccurrence',
        'endsonoccurrence',
        'maxoccurrences',
        'leaddaysbeforedue',
        'isactive',
        'lastgeneratedon',
    ];

    protected function casts(): array
    {
        return [
            'startsonoccurrence' => 'date',
            'endsonoccurrence'   => 'date',
        ];
    }

    public function taskTemplate()
    {
        return $this->belongsTo(Task::class, 'tasktemplateid', 'id');
    }

    public function nextScheduledDate(): ?Carbon
    {
        if (! $this->frequency || ! $this->startsonoccurrence) {
            return null;
        }

        $interval = max(1, (int) $this->intervalcount);

        if (! $this->lastgeneratedon) {
            return Carbon::parse($this->startsonoccurrence)->startOfDay();
        }

        $baseDate = Carbon::parse($this->lastgeneratedon)->startOfDay();

        return match ($this->frequency) {
            'daily' => $baseDate->copy()->addDays($interval),
            'weekly' => $baseDate->copy()->addWeeks($interval),
            'monthly' => $baseDate->copy()->addMonthsNoOverflow($interval),
            'yearly' => $baseDate->copy()->addYearsNoOverflow($interval),
            default => null,
        };
    }

    public function nextGenerationDate(): ?Carbon
    {
        $nextDueDate = $this->nextScheduledDate();

        if (! $nextDueDate) {
            return null;
        }

        return $nextDueDate
            ->copy()
            ->subDays(max(0, (int) ($this->leaddaysbeforedue ?? 0)));
    }
}