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
        'outboundsortorder',
        'inboundsortorder',
    ];

    protected $casts = [
        'fromitemid' => 'integer',
        'toitemid' => 'integer',
        'effective_date' => 'date',
        'outboundsortorder' => 'integer',
        'inboundsortorder' => 'integer',
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

    public function relationshipFacts(): HasMany
    {
        return $this->hasMany(KnowledgeRelationshipFact::class, 'knowledgerelationshipid')
            ->orderBy('sortorder')
            ->orderBy('facttype')
            ->orderBy('datefrom');
    }

    public function isOutgoingFor(KnowledgeItem|int $knowledgeItem): bool
    {
        $knowledgeItemId = $knowledgeItem instanceof KnowledgeItem
            ? (int) $knowledgeItem->id
            : (int) $knowledgeItem;

        return (int) $this->fromitemid === $knowledgeItemId;
    }

    public function isIncomingFor(KnowledgeItem|int $knowledgeItem): bool
    {
        $knowledgeItemId = $knowledgeItem instanceof KnowledgeItem
            ? (int) $knowledgeItem->id
            : (int) $knowledgeItem;

        return (int) $this->toitemid === $knowledgeItemId;
    }

    public function sortOrderFor(KnowledgeItem|int $knowledgeItem): ?int
{
    $knowledgeItemId = $knowledgeItem instanceof KnowledgeItem
        ? (int) $knowledgeItem->id
        : (int) $knowledgeItem;

    if ((int) $this->fromitemid === $knowledgeItemId) {
        return $this->outboundsortorder !== null
            ? (int) $this->outboundsortorder
            : null;
    }

    if ((int) $this->toitemid === $knowledgeItemId) {
        return $this->inboundsortorder !== null
            ? (int) $this->inboundsortorder
            : null;
    }

    return null;
}

    public function setSortOrderFor(KnowledgeItem|int $knowledgeItem, int $sortOrder): void
    {
        $knowledgeItemId = $knowledgeItem instanceof KnowledgeItem
            ? (int) $knowledgeItem->id
            : (int) $knowledgeItem;

        if ((int) $this->fromitemid === $knowledgeItemId) {
            $this->outboundsortorder = $sortOrder;
            return;
        }

        if ((int) $this->toitemid === $knowledgeItemId) {
            $this->inboundsortorder = $sortOrder;
        }
    }
}