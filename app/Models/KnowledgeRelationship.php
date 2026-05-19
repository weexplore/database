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

    public const INVERSE_TYPE_MAP = [
        'related' => 'related',
        'supports' => 'supported-by',
        'contradicts' => 'contradicts',
        'depends-on' => 'required-by',
        'parent-of' => 'child-of',
        'child-of' => 'parent-of',
        'married' => 'married',
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

    public function inverseRelationshipType(): ?string
    {
        return self::INVERSE_TYPE_MAP[$this->relationshiptype] ?? $this->relationshiptype;
    }

    public function relationshipTypeLabel(): string
    {
        return self::TYPE_OPTIONS[$this->relationshiptype] ?? $this->relationshiptype;
    }

    public function inverseRelationshipTypeLabel(): string
    {
        $inverse = $this->inverseRelationshipType();

        return self::TYPE_OPTIONS[$inverse]
            ?? str($inverse)->replace('-', ' ')->title()->toString();
    }
}