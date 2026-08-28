<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;


class KnowledgeItem extends Model
{
    use HasFactory;

    protected $table = 'knowledgeitems';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

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
    'itemtype' => 'integer',
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

    public function attachments()
    {
        return $this->belongsToMany(
            KnowledgeAttachment::class,
            'knowledgeitem_attachments',
            'knowledgeitemid',
            'knowledgeattachmentid'
        )
            ->withPivot([
                'description',
                'expirydate',
                'isprimary',
                'sortorder',
            ])
            ->withTimestamps('created_at', 'updated_at');
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
    public function itemType(): BelongsTo
    {
        return $this->belongsTo(KnowledgeItemType::class, 'itemtype');
    }

    public function relationships(): HasMany
    {
        return $this->hasMany(KnowledgeRelationship::class, 'fromitemid');
    }
    public function place(): BelongsTo
    {
        return $this->belongsTo(Place::class, 'placeid');
    }
    
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(
            KnowledgeTag::class,
            'knowledgeitemtags',
            'knowledgeitemid',
            'tagid'
        )->withTimestamps('createdat', 'updatedat');
    }

    public function instrument(): HasOne
    {
        return $this->hasOne(Instrument::class, 'knowledgeitemid');
    }

    public function personFacts(): HasMany
    {
        return $this->hasMany(KnowledgePersonFact::class, 'knowledgeitemid')
            ->orderBy('sortorder')
            ->orderBy('facttype')
            ->orderBy('datefrom');
    }
    public function tasks()
    {
        return $this->belongsToMany(
            Task::class,
            'taskknowledgeitems',
            'knowledgeitemid',
            'taskid'
        )->withTimestamps();
    }
}
