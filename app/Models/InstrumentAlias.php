<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InstrumentAlias extends Model
{
    use HasFactory;

    protected $table = 'instrumentaliases';

    protected $fillable = [
        'instrumentid',
        'aliasvalue',
        'aliastype',
    ];

    protected $casts = [
        'instrumentid' => 'integer',
    ];

    const CREATED_AT = 'createdat';
    const UPDATED_AT = 'updatedat';

    public function instrument(): BelongsTo
    {
        return $this->belongsTo(Instrument::class, 'instrumentid');
    }
}