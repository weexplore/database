<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KnowledgeReviewLog extends Model
{
    use HasFactory;

    protected $table = 'knowledgereviewlog';
    protected $primaryKey = 'id';

    public $incrementing = true;

    protected $keyType = 'int';

    protected $fillable = [
        'knowledgeitemid',
        'reviewdate',
        'reviewtype',
        'outcome',
        'summary',
        'nextreviewdate',
        'isactive',
    ];

    protected $attributes = [
        'isactive' => true,
    ];

    protected $casts = [
        'knowledgeitemid' => 'integer',
        'reviewdate' => 'date',
        'nextreviewdate' => 'date',
        'isactive' => 'boolean',
    ];

    public const CREATED_AT = 'createdat';
    public const UPDATED_AT = 'updatedat';

    public const TYPE_OPTIONS = [
        'routine' => 'Routine',
        'action' => 'Action',
        'questions' => 'Questions',
        'feedback' => 'Feedback',
        'deep-dive' => 'Deep Dive',
        'expiry-check' => 'Expiry Check',
        'quality-check' => 'Quality Check',
        'fact-check' => 'Fact Check',
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
     * Limit the query to active review-log entries.
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