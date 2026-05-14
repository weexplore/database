<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KnowledgeProperty extends Model
{
    use HasFactory;

    protected $table = 'knowledgeproperties';

    protected $fillable = [
        'knowledgeitemid',
        'propertyname',
        'propertyvalue',
        'propertydatatype',
        'sortorder',
    ];

    protected $casts = [
        'knowledgeitemid' => 'integer',
        'sortorder' => 'integer',
    ];

    const CREATED_AT = 'createdat';
    const UPDATED_AT = 'updatedat';

    public function item(): BelongsTo
    {
        return $this->belongsTo(KnowledgeItem::class, 'knowledgeitemid');
    }
}