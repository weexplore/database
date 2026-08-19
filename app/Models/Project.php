<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $table = 'projects';

    // Tell Eloquent which columns to use
    public const CREATED_AT = 'createdat';
    public const UPDATED_AT = 'updatedat';

    protected $fillable = [
        'projectname', 'status', 'startdate', 'targetdate',
        'colourhex', 'ownerid',
    ];

    protected $casts = [
        'startdate' => 'date',
        'targetdate' => 'date',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'ownerid');
    }

    public function tasks()
    {
        return $this->hasMany(Task::class, 'projectid');
    }

    public function taskStatuses()
    {
        return $this->hasMany(TaskStatus::class, 'projectid');
    }

    public function members()
    {
        return $this->hasMany(ProjectMember::class, 'projectid');
    }

    public function cloneDefaultStatuses(): void
    {
        foreach (TaskStatus::globalDefaults()->orderBy('sortorder')->get() as $default) {
            $this->taskStatuses()->create([
                'statuslabel' => $default->statuslabel,
                'statuscode' => $default->statuscode,
                'colourhex' => $default->colourhex,
                'iscompletedstatus' => $default->iscompletedstatus,
                'sortorder' => $default->sortorder,
                'isactive' => true,
            ]);
        }
    }
}