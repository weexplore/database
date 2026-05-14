<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;


class KnowledgeItem extends Model
{
    use HasFactory;

    protected $table = 'knowledgeitems';

    protected $fillable = [
        'primarycategoryid',
        'itemname',
        'itemtype',
        'itemstatus',
        'summary',
        'detailednotes',
        'significance',
        'reviewnotes',
        'parentitemid',
        'placeid',
        'startdate',
        'enddate',
        'nextreviewdate',
        'sortorder',
        'isfeatured',
        'isactive',
    ];

    protected $casts = [
        'primarycategoryid' => 'integer',
        'parentitemid' => 'integer',
        'placeid' => 'integer',
        'startdate' => 'date',
        'enddate' => 'date',
        'nextreviewdate' => 'date',
        'sortorder' => 'integer',
        'isfeatured' => 'boolean',
        'isactive' => 'boolean',
    ];

    const CREATED_AT = 'createdat';
    const UPDATED_AT = 'updatedat';

    public function primaryCategory(): BelongsTo
    {
        return $this->belongsTo(KnowledgeCategory::class, 'primarycategoryid');
    }

    public function parentItem(): BelongsTo
    {
        return $this->belongsTo(KnowledgeItem::class, 'parentitemid');
    }

    public function childItems(): HasMany
    {
        return $this->hasMany(KnowledgeItem::class, 'parentitemid');
    }

    public function categoryLinks(): HasMany
    {
        return $this->hasMany(KnowledgeItemCategory::class, 'knowledgeitemid');
    }

    public function outgoingRelationships(): HasMany
    {
        return $this->hasMany(KnowledgeRelationship::class, 'fromitemid');
    }

    public function incomingRelationships(): HasMany
    {
        return $this->hasMany(KnowledgeRelationship::class, 'toitemid');
    }

    public function notes(): HasMany
    {
        return $this->hasMany(KnowledgeNote::class, 'knowledgeitemid');
    }

    public function sources(): HasMany
    {
        return $this->hasMany(KnowledgeSource::class, 'knowledgeitemid');
    }

    public function tagLinks(): HasMany
    {
        return $this->hasMany(KnowledgeItemTag::class, 'knowledgeitemid');
    }

    public function properties(): HasMany
    {
        return $this->hasMany(KnowledgeProperty::class, 'knowledgeitemid');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(KnowledgeAttachment::class, 'knowledgeitemid');
    }

    public function reviewLogs(): HasMany
    {
        return $this->hasMany(KnowledgeReviewLog::class, 'knowledgeitemid');
    }

    public function bibleReferences(): HasMany
    {
        return $this->hasMany(BibleReference::class, 'knowledgeitemid');
    }

    public function instruments(): HasMany
    {
        return $this->hasMany(Instrument::class, 'knowledgeitemid');
    }
}
