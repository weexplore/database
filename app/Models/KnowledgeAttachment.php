<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class KnowledgeAttachment extends Model
{
    use HasFactory;

    protected $table = 'knowledgeattachments';

    protected $fillable = [
        'attachmenttype',
        'filename',
        'originalfilename',
        'mimetype',
        'filesizebytes',
        'uploadedat',
        'uploadedby',
        'isprimary',
    ];

    protected $casts = [
        'filesizebytes' => 'integer',
        'uploadedat' => 'datetime',
        'isprimary' => 'boolean',
    ];

    const CREATED_AT = 'createdat';
    const UPDATED_AT = 'updatedat';

    
    public function items(): BelongsToMany
    {
        return $this->belongsToMany(
            KnowledgeItem::class,
            'knowledgeitem_attachments',
            'knowledgeattachmentid',
            'knowledgeitemid'
        )
            ->withPivot([
                'description',
                'expirydate',
                'isprimary',
                'sortorder',
            ])
            ->withTimestamps('created_at', 'updated_at');
    }
}