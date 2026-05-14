<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KnowledgeSource extends Model
{
    use HasFactory;

    protected $table = 'knowledgesources';

    protected $fillable = [
        'knowledgeitemid',
        'sourcetype',
        'sourceurl',
        'sourcetitle',
        'sourcepublisher',
        'retrievedon',
        'importedsummary',
        'importednotes',
        'importstatus',
        'reviewedon',
        'reviewedby',
        'internalnotes',
    ];

    protected $casts = [
        'knowledgeitemid' => 'integer',
        'retrievedon' => 'date',
        'reviewedon' => 'date',
    ];

    const CREATED_AT = 'createdat';
    const UPDATED_AT = 'updatedat';

    public function item(): BelongsTo
    {
        return $this->belongsTo(KnowledgeItem::class, 'knowledgeitemid');
    }

    public function corporateActions(): HasMany
    {
        return $this->hasMany(InstrumentCorporateAction::class, 'sourceid');
    }
}