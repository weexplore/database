<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskComment extends Model
{
    public $timestamps = false; // since you're using custom column names

    protected $fillable = [
        'taskid',
        'userid',
        'commenttext',
        'createdat',
    ];

    protected function casts(): array
    {
        return [
            'createdat' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'userid');
    }

    public function task()
    {
        return $this->belongsTo(Task::class, 'taskid');
    }
}