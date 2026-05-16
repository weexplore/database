<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KnowledgeReviewLog extends Model
{
    use HasFactory;

    protected $table = 'knowledgereviewlog';

    protected $fillable = [
        'knowledgeitemid',
        'reviewdate',
        'reviewtype',
        'outcome',
        'summary',
        'nextreviewdate',
    ];

    protected $casts = [
        'knowledgeitemid' => 'integer',
        'reviewdate' => 'date',
        'nextreviewdate' => 'date',
    ];

    const CREATED_AT = 'createdat';
    const UPDATED_AT = 'updatedat';

    public const TYPE_OPTIONS = [
        'routine' => 'Routine',
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
    public function item(): BelongsTo
    {
        return $this->belongsTo(KnowledgeItem::class, 'knowledgeitemid');
    }
}
