<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PortfolioHoldingSnapshot extends Model
{
    use HasFactory;

    protected $table = 'portfolioholdingsnapshots';

    protected $fillable = [
        'portfolioid',
        'instrumentid',
        'snapshotdate',
        'quantityheld',
        'averagecostbase',
        'marketprice',
        'marketvalue',
        'unrealisedgainloss',
        'incomeyeartodate',
    ];

    protected $casts = [
        'portfolioid' => 'integer',
        'instrumentid' => 'integer',
        'snapshotdate' => 'date',
        'quantityheld' => 'decimal:6',
        'averagecostbase' => 'decimal:6',
        'marketprice' => 'decimal:6',
        'marketvalue' => 'decimal:2',
        'unrealisedgainloss' => 'decimal:2',
        'incomeyeartodate' => 'decimal:2',
    ];

    const CREATED_AT = 'createdat';
    const UPDATED_AT = 'updatedat';

    public function portfolio(): BelongsTo
    {
        return $this->belongsTo(Portfolio::class, 'portfolioid');
    }

    public function instrument(): BelongsTo
    {
        return $this->belongsTo(Instrument::class, 'instrumentid');
    }
}
