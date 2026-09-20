<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KnowledgeItemType extends Model
{
    use HasFactory;

    protected $table = 'knowledgeitemtypes';

    protected $fillable = [
        'typename',
        'description',
        'sortorder',
        'isactive',
    ];

    protected $casts = [
        'sortorder' => 'integer',
        'isactive' => 'boolean',
    ];

    public const CREATED_AT = 'createdat';
    public const UPDATED_AT = 'updatedat';

    public function knowledgeItems(): HasMany
    {
        return $this->hasMany(
            KnowledgeItem::class,
            'itemtype',
            'id'
        );
    }
}