<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        'cachedpassagetext',
        'cachedreferencetext',
        'apipassagekey',
        'passagefetchedat',
    ];

    protected $casts = [
        'knowledgeitemid' => 'integer',
        'versionid' => 'integer',
        'bookid' => 'integer',
        'chapterfrom' => 'integer',
        'versefrom' => 'integer',
        'chapterto' => 'integer',
        'verseto' => 'integer',
        'passagefetchedat' => 'datetime',
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

    public function buildReferenceText(): string
    {
        $bookName = $this->book?->bookname ?: 'Book';

        $from = $this->chapterfrom . ($this->versefrom ? ':' . $this->versefrom : '');
        $to = null;

        if ($this->chapterto) {
            $to = $this->chapterto . ($this->verseto ? ':' . $this->verseto : '');
        } elseif ($this->verseto && $this->versefrom) {
            $to = $this->chapterfrom . ':' . $this->verseto;
        }

        return $to ? "{$bookName} {$from}-{$to}" : "{$bookName} {$from}";
    }
}