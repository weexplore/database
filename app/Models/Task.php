<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $table = 'tasks';

    // Map timestamps to createdat/updatedat
    public const CREATED_AT = 'createdat';
    public const UPDATED_AT = 'updatedat';

    protected $fillable = [
        'projectid',
        'parenttaskid',
        'statusid',
        'tasktitle',
        'description',
        'priority',
        'assignedto',
        'startdate',
        'duedate',
        'completedat',
        'sortorder',
        'isrecurringtemplate',
        'generatedfromtemplateid',
        'estimatedefforthours',
        'actualefforthours',
        'taskexpectation',
        'statuscomment',
        'completedat',
        'recurrencerootid',
    ];

    protected $casts = [
        'startdate' => 'date',
        'duedate' => 'date',
        'completedat' => 'datetime',
        'estimatedefforthours' => 'decimal:2',
        'actualefforthours' => 'decimal:2',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class, 'projectid');
    }

    public function status()
    {
        return $this->belongsTo(TaskStatus::class, 'statusid');
    }

    public function parent()
    {
        return $this->belongsTo(Task::class, 'parenttaskid');
    }

    public function subtasks()
    {
        return $this->hasMany(Task::class, 'parenttaskid');
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigneeid');
    }

    public function labels()
    {
        return $this->belongsToMany(Label::class, 'task_labels', 'taskid', 'labelid');
    }

    public function comments()
    {
        return $this->hasMany(TaskComment::class, 'taskid')
            ->orderBy('createdat', 'desc');
    }

    public function recurrence()
    {
        // FK is tasktemplateid on task_recurrence, local key is tasks.id
        return $this->hasOne(TaskRecurrence::class, 'tasktemplateid', 'id');
    }

    public function dependsOn()
    {
        return $this->belongsToMany(Task::class, 'task_dependencies', 'taskid', 'dependsontaskid');
    }
    public function parentTask()
    {
        return $this->belongsTo(Task::class, 'parenttaskid');
    }
    public function dependencies()
    {
        return $this->hasMany(TaskDependency::class, 'taskid');
    }

    public function dependentTasks()
    {
        return $this->hasMany(TaskDependency::class, 'dependsontaskid');
    }
    public function openSubtasks()
    {
        return $this->subtasks()
            ->where(function ($query) {
                $query->whereHas('status', function ($statusQuery) {
                    $statusQuery->where('iscompletedstatus', false);
                })
                ->orWhereNull('statusid');
            });
    }
}