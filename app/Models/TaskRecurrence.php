<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
}