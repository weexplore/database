<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KnowledgePersonFact extends Model
{
    use HasFactory;

    protected $table = 'knowledgepersonfacts';

    protected $fillable = [
        'knowledgeitemid',
        'facttype',
        'factlabel',
        'datetext',
        'datefrom',
        'dateto',
        'datequalifier',
        'placeid',
        'valuetext',
        'notes',
        'proofstatus',
        'ispreferred',
        'sortorder',
    ];

    protected $casts = [
        'knowledgeitemid' => 'integer',
        'placeid' => 'integer',
        'datefrom' => 'date',
        'dateto' => 'date',
        'ispreferred' => 'boolean',
        'sortorder' => 'integer',
    ];

    public const FACT_TYPE_OPTIONS = [
    'birth' => 'Birth',
    'death' => 'Death',
    'burial' => 'Burial',
    'christening' => 'Christening',
    'baptism' => 'Baptism',
    'residence' => 'Residence',
    'occupation' => 'Occupation',
    'education' => 'Education',
    'immigration' => 'Immigration',
    'emigration' => 'Emigration',
    'military-service' => 'Military Service',
    'probate' => 'Probate',
    'will' => 'Will',
    'other' => 'Other',
];

public const DATE_QUALIFIER_OPTIONS = [
    'exact' => 'Exact',
    'about' => 'About',
    'before' => 'Before',
    'after' => 'After',
    'between' => 'Between',
    'estimated' => 'Estimated',
    'calculated' => 'Calculated',
    'unknown' => 'Unknown',
];

public const PROOF_STATUS_OPTIONS = [
    'proven' => 'Proven',
    'likely' => 'Likely',
    'possible' => 'Possible',
    'disputed' => 'Disputed',
    'unknown' => 'Unknown',
];

    const CREATED_AT = 'createdat';
    const UPDATED_AT = 'updatedat';

    public function knowledgeItem(): BelongsTo
    {
        return $this->belongsTo(KnowledgeItem::class, 'knowledgeitemid');
    }

    public function place(): BelongsTo
    {
        return $this->belongsTo(Place::class, 'placeid');
    }
    public static function factTypeOptions(): array
    {
        return self::FACT_TYPE_OPTIONS;
    }

    public static function dateQualifierOptions(): array
    {
        return self::DATE_QUALIFIER_OPTIONS;
    }

    public static function proofStatusOptions(): array
    {
        return self::PROOF_STATUS_OPTIONS;
    }
}