<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InstrumentCorporateAction extends Model
{
    use HasFactory;

    protected $table = 'instrumentcorporateactions';

    protected $fillable = [
        'instrumentid',
        'actiondate',
        'actiontype',
        'ratiofrom',
        'ratioto',
        'oldvalue',
        'newvalue',
        'notes',
        'sourceid',
    ];

    protected $casts = [
        'instrumentid' => 'integer',
        'actiondate' => 'date',
        'ratiofrom' => 'decimal:8',
        'ratioto' => 'decimal:8',
        'sourceid' => 'integer',
    ];

    const CREATED_AT = 'createdat';
    const UPDATED_AT = 'updatedat';

    public function instrument(): BelongsTo
    {
        return $this->belongsTo(Instrument::class, 'instrumentid');
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(KnowledgeSource::class, 'sourceid');
    }
}