<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KnowledgeNote extends Model
{
    use HasFactory;

    protected $table = 'knowledgenotes';

    protected $fillable = [
        'knowledgeitemid',
        'notetype',
        'title',
        'notecontent',
        'stance',
        'convictionlevel',
        'reviewdate',
        'isprivate',
        'sortorder',
    ];

    protected $casts = [
        'knowledgeitemid' => 'integer',
        'convictionlevel' => 'integer',
        'reviewdate' => 'date',
        'isprivate' => 'boolean',
        'sortorder' => 'integer',
    ];

    const CREATED_AT = 'createdat';
    const UPDATED_AT = 'updatedat';

    public function item(): BelongsTo
    {
        return $this->belongsTo(KnowledgeItem::class, 'knowledgeitemid');
    }
}