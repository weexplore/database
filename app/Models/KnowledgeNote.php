<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KnowledgeNote extends Model
{
    use HasFactory;

    protected $table = 'knowledgenotes';
    protected $primaryKey = 'id';

    public $incrementing = true;

    protected $keyType = 'int';

    protected $fillable = [
        'knowledgeitemid',
        'notetype',
        'title',
        'notecontent',
        'stance',
        'convictionlevel',
        'reviewdate',
        'isprivate',
        'isactive',
        'sortorder',
    ];

    protected $attributes = [
        'isprivate' => true,
        'isactive' => true,
    ];

    protected $casts = [
        'knowledgeitemid' => 'integer',
        'convictionlevel' => 'integer',
        'reviewdate' => 'date',
        'isprivate' => 'boolean',
        'isactive' => 'boolean',
        'sortorder' => 'integer',
    ];

    public const CREATED_AT = 'createdat';
    public const UPDATED_AT = 'updatedat';

    public const TYPE_OPTIONS = [
        'research' => 'Research',
        'commentary' => 'Commentary',
        'review' => 'Review',
        'insight' => 'Insight',
        'warning' => 'Warning',
    ];

    public static function typeOptions(): array
    {
        return self::TYPE_OPTIONS;
    }

    public static function typeValues(): array
    {
        return array_keys(self::TYPE_OPTIONS);
    }

    /**
     * Limit the query to active notes.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where(
            $this->qualifyColumn('isactive'),
            true
        );
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(
            KnowledgeItem::class,
            'knowledgeitemid'
        );
    }

    public function knowledgeItem(): BelongsTo
    {
        return $this->belongsTo(
            KnowledgeItem::class,
            'knowledgeitemid'
        );
    }
}