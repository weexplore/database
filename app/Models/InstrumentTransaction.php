<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InstrumentTransaction extends Model
{
    use HasFactory;

    protected $table = 'instrumenttransactions';

    protected $fillable = [
        'portfolioid',
        'instrumentid',
        'transactiondate',
        'settlementdate',
        'transactiontype',
        'quantity',
        'priceperunit',
        'grossamount',
        'brokerage',
        'taxesandfees',
        'netcashamount',
        'currencycode',
        'fxrateaud',
        'externalreference',
        'notes',
    ];

    protected $casts = [
        'portfolioid' => 'integer',
        'instrumentid' => 'integer',
        'transactiondate' => 'date',
        'settlementdate' => 'date',
        'quantity' => 'decimal:6',
        'priceperunit' => 'decimal:6',
        'grossamount' => 'decimal:2',
        'brokerage' => 'decimal:2',
        'taxesandfees' => 'decimal:2',
        'netcashamount' => 'decimal:2',
        'fxrateaud' => 'decimal:8',
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

    public function reinvestedIncomeEvents(): HasMany
    {
        return $this->hasMany(InstrumentIncomeEvent::class, 'reinvestedtransactionid');
    }
}