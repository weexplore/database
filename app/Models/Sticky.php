<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sticky extends Model
{
    protected $table = 'stickies';

    // Tell Eloquent which columns to use
    public const CREATED_AT = 'createdat';
    public const UPDATED_AT = 'updatedat';

    protected $fillable = [
        'stickytext', 'colourhex',
        'positionx', 'positiony', 'ispinned',
    ];

}