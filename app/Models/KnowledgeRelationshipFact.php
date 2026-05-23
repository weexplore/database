<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KnowledgeRelationshipFact extends Model
{
    use HasFactory;

    protected $table = 'knowledgerelationshipfacts';

    protected $fillable = [
        'knowledgerelationshipid',
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
        'knowledgerelationshipid' => 'integer',
        'placeid' => 'integer',
        'datefrom' => 'date',
        'dateto' => 'date',
        'ispreferred' => 'boolean',
        'sortorder' => 'integer',
    ];

    const CREATED_AT = 'createdat';
    const UPDATED_AT = 'updatedat';

public const FACT_TYPE_OPTIONS = [
    // Partner / spouse relationship facts
    'marriage' => 'Marriage',
    'engagement' => 'Engagement',
    'separation' => 'Separation',
    'divorce' => 'Divorce',
    'annulment' => 'Annulment',
    'widowed' => 'Widowed',
    'partnership-start' => 'Partnership Start',
    'partnership-end' => 'Partnership End',

    // Parent / child / family linkage facts
    'birth-related' => 'Birth Related',
    'adoption' => 'Adoption',
    'foster-care' => 'Foster Care',
    'guardianship' => 'Guardianship',
    'step-relationship' => 'Step Relationship',
    'recognition' => 'Recognition',
    'paternity' => 'Paternity',
    'maternity' => 'Maternity',
    'dna-match' => 'DNA Match',
    'custody' => 'Custody',
    'co-residence' => 'Co Residence',
    'estrangement' => 'Estrangement',
    'reunion' => 'Reunion',

    // Generic fallback
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

    public function relationship(): BelongsTo
    {
        return $this->belongsTo(KnowledgeRelationship::class, 'knowledgerelationshipid');
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