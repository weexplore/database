<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Attachment extends Model
{
    protected $table = 'attachments';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    const CREATED_AT = 'createdat';
    const UPDATED_AT = 'updatedat';

    protected $fillable = [
        'tripid',
        'linkedtype',
        'linkedid',
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
        'tripid' => 'integer',
        'linkedid' => 'integer',
        'filesizebytes' => 'integer',
        'uploadedat' => 'datetime',
        'isprimary' => 'boolean',
    ];

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class, 'tripid');
    }

    public function linkedRecord(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'linkedtype', 'linkedid');
    }
}