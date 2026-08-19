<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskLabel extends Model
{
    protected $table = 'task_labels';

    // Tell Eloquent which columns to use
    public const CREATED_AT = 'createdat';
    public const UPDATED_AT = 'updatedat';

    public $timestamps = false;

    protected $fillable = ['taskid', 'labelid'];

    public function task()
    {
        return $this->belongsTo(Task::class, 'taskid');
    }

    public function label()
    {
        return $this->belongsTo(Label::class, 'labelid');
    }
}