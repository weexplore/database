<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;


class KnowledgeItemTag extends Model
{
    use HasFactory;

    protected $table = 'knowledgeitemtags';

    protected $fillable = [
        'knowledgeitemid',
        'tagid',
    ];

    protected $casts = [
        'knowledgeitemid' => 'integer',
        'tagid' => 'integer',
    ];

    const CREATED_AT = 'createdat';
    const UPDATED_AT = 'updatedat';

    public function item(): BelongsTo
    {
        return $this->belongsTo(KnowledgeItem::class, 'knowledgeitemid');
    }

    public function tag(): BelongsTo
    {
        return $this->belongsTo(KnowledgeTag::class, 'tagid');
    }
}