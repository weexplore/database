<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskDependency extends Model
{
    protected $table = 'task_dependencies';

    public $timestamps = false;

    protected $fillable = [
        'taskid',
        'dependsontaskid',
        'dependencytype',
        'lagdays',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'taskid');
    }

    public function dependsOnTask(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'dependsontaskid');
    }
}