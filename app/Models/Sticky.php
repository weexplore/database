<?php
namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Sticky extends Model
{
    protected $table = 'stickies';

    // Tell Eloquent which columns to use
    public const CREATED_AT = 'createdat';
    public const UPDATED_AT = 'updatedat';

    protected $fillable = [
        'userid',
        'stickytext',
        'colourhex',
        'positionx',
        'positiony',
        'ispinned',
    ];

    protected $casts = [
        'ispinned' => 'boolean',
        'positionx' => 'integer',
        'positiony' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'userid');
    }

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('userid', $userId);
    }

}