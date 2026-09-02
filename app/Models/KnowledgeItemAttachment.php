<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class KnowledgeItemAttachment extends Pivot
{
    protected $table = 'knowledgeitem_attachments';

    protected $casts = [
        'expirydate' => 'date',
        'isprimary' => 'boolean',
        'sortorder' => 'integer',
    ];
}