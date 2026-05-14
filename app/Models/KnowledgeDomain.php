<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KnowledgeDomain extends Model
{
    use HasFactory;

    protected $table = 'knowledgedomains';

    protected $fillable = [
        'domaincode',
        'domainname',
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

    public function categories(): HasMany
    {
        return $this->hasMany(KnowledgeCategory::class, 'domainid');
    }
}