<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KnowledgeRelationship extends Model
{
    use HasFactory;

    protected $table = 'knowledgerelationships';

    protected $fillable = [
        'fromitemid',
        'toitemid',
        'relationshiptype',
        'effective_date',
        'notes',
        'sortorder',
    ];

    protected $casts = [
        'fromitemid' => 'integer',
        'toitemid' => 'integer',
        'effective_date' => 'date',
        'sortorder' => 'integer',
    ];

    const CREATED_AT = 'createdat';
    const UPDATED_AT = 'updatedat';

    public const TYPE_OPTIONS = [
        'related' => 'Related',
        'supports' => 'Supports',
        'contradicts' => 'Contradicts',
        'depends-on' => 'Depends On',
        'parent-of' => 'Parent Of',
        'child-of' => 'Child Of',
        'married' => 'Married',
    ];

    public static function typeOptions(): array
    {
        return self::TYPE_OPTIONS;
    }

    public static function typeValues(): array
    {
        return array_keys(self::TYPE_OPTIONS);
    }
    public function fromItem(): BelongsTo
    {
        return $this->belongsTo(KnowledgeItem::class, 'fromitemid');
    }

    public function toItem(): BelongsTo
    {
        return $this->belongsTo(KnowledgeItem::class, 'toitemid');
    }
}