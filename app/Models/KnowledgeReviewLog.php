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

    public function item(): BelongsTo
    {
        return $this->belongsTo(KnowledgeItem::class, 'knowledgeitemid');
    }
}
