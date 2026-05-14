<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;



class KnowledgeItemCategory extends Model
{
    use HasFactory;

    protected $table = 'knowledgeitemcategories';

    protected $fillable = [
        'knowledgeitemid',
        'knowledgecategoryid',
        'isprimary',
        'sortorder',
    ];

    protected $casts = [
        'knowledgeitemid' => 'integer',
        'knowledgecategoryid' => 'integer',
        'isprimary' => 'boolean',
        'sortorder' => 'integer',
    ];

    const CREATED_AT = 'createdat';
    const UPDATED_AT = 'updatedat';

    public function item(): BelongsTo
    {
        return $this->belongsTo(KnowledgeItem::class, 'knowledgeitemid');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(KnowledgeCategory::class, 'knowledgecategoryid');
    }
}
