<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskStatus extends Model
{
    protected $table = 'task_statuses';

    // Table has no timestamp columns
    public $timestamps = false;

    protected $fillable = [
        'projectid', 'statuslabel', 'statuscode',
        'colourhex', 'iscompletedstatus', 'sortorder', 'isactive',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class, 'projectid');
    }

    public function tasks()
    {
        return $this->hasMany(Task::class, 'statusid');
    }

    public function scopeGlobalDefaults($query)
    {
        return $query->whereNull('projectid');
    }

    public function scopeForProject($query, $projectId)
    {
        return $query->where('projectid', $projectId);
    }
}