<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BibleReference extends Model
{
    use HasFactory;

    protected $table = 'biblereferences';

    protected $fillable = [
        'knowledgeitemid',
        'versionid',
        'bookid',
        'chapterfrom',
        'versefrom',
        'chapterto',
        'verseto',
        'referencelabel',
        'notes',
    ];

    protected $casts = [
        'knowledgeitemid' => 'integer',
        'versionid' => 'integer',
        'bookid' => 'integer',
        'chapterfrom' => 'integer',
        'versefrom' => 'integer',
        'chapterto' => 'integer',
        'verseto' => 'integer',
    ];

    const CREATED_AT = 'createdat';
    const UPDATED_AT = 'updatedat';

    public function item(): BelongsTo
    {
        return $this->belongsTo(KnowledgeItem::class, 'knowledgeitemid');
    }

    public function version(): BelongsTo
    {
        return $this->belongsTo(BibleVersion::class, 'versionid');
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(BibleBook::class, 'bookid');
    }
}
