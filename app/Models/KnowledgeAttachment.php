<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KnowledgeAttachment extends Model
{
    use HasFactory;

    protected $table = 'knowledgeattachments';

    protected $fillable = [
        'knowledgeitemid',
        'attachmenttype',
        'filename',
        'originalfilename',
        'mimetype',
        'filesizebytes',
        'description',
        'uploadedat',
        'uploadedby',
        'isprimary',
    ];

    protected $casts = [
        'knowledgeitemid' => 'integer',
        'filesizebytes' => 'integer',
        'uploadedat' => 'datetime',
        'isprimary' => 'boolean',
    ];

    const CREATED_AT = 'createdat';
    const UPDATED_AT = 'updatedat';

    public function item(): BelongsTo
    {
        return $this->belongsTo(KnowledgeItem::class, 'knowledgeitemid');
    }
    public function items()
{
    return $this->belongsToMany(
        KnowledgeItem::class,
        'knowledgeitem_attachments',
        'knowledgeattachmentid',
        'knowledgeitemid'
    )->withPivot([
        'description',
        'isprimary',
        'sortorder',
    ]);
}
}