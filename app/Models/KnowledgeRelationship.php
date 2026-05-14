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
        'notes',
        'sortorder',
    ];

    protected $casts = [
        'fromitemid' => 'integer',
        'toitemid' => 'integer',
        'sortorder' => 'integer',
    ];

    const CREATED_AT = 'createdat';
    const UPDATED_AT = 'updatedat';

    public function fromItem(): BelongsTo
    {
        return $this->belongsTo(KnowledgeItem::class, 'fromitemid');
    }

    public function toItem(): BelongsTo
    {
        return $this->belongsTo(KnowledgeItem::class, 'toitemid');
    }
}