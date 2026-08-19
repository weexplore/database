<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Label extends Model
{
    protected $table = 'labels';

    // Table has no timestamp columns
    public $timestamps = false;

    protected $fillable = ['labelname', 'colourhex'];

    public function taskLabels()
    {
        return $this->hasMany(TaskLabel::class, 'labelid');
    }

    public function tasks()
    {
        return $this->belongsToMany(Task::class, 'task_labels', 'labelid', 'taskid');
    }
}