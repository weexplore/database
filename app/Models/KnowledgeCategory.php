<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KnowledgeCategory extends Model
{
    use HasFactory;

    protected $table = 'knowledgecategories';

    protected $fillable = [
        'domainid',
        'parentcategoryid',
        'categoryname',
        'categorytype',
        'slug',
        'description',
        'sortorder',
        'isfeatured',
        'isactive',
    ];

    protected $casts = [
        'domainid' => 'integer',
        'parentcategoryid' => 'integer',
        'sortorder' => 'integer',
        'isfeatured' => 'boolean',
        'isactive' => 'boolean',
    ];

    const CREATED_AT = 'createdat';
    const UPDATED_AT = 'updatedat';

    public function domain(): BelongsTo
    {
        return $this->belongsTo(KnowledgeDomain::class, 'domainid');
    }

    public function parentCategory(): BelongsTo
    {
        return $this->belongsTo(KnowledgeCategory::class, 'parentcategoryid');
    }

    public function childCategories(): HasMany
    {
        return $this->hasMany(KnowledgeCategory::class, 'parentcategoryid');
    }

    public function primaryItems(): HasMany
    {
        return $this->hasMany(KnowledgeItem::class, 'primarycategoryid');
    }

    public function itemLinks(): HasMany
    {
        return $this->hasMany(KnowledgeItemCategory::class, 'knowledgecategoryid');
    }
}