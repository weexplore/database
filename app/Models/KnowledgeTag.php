<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KnowledgeTag extends Model
{
    use HasFactory;

    protected $table = 'knowledgetags';

    protected $fillable = [
        'tagname',
        'tagtype',
        'description',
        'sortorder',
        'isactive',
    ];

    protected $casts = [
        'sortorder' => 'integer',
        'isactive' => 'boolean',
    ];

    const CREATED_AT = 'createdat';
    const UPDATED_AT = 'updatedat';

    public function itemLinks(): HasMany
    {
        return $this->hasMany(KnowledgeItemTag::class, 'tagid');
    }
}