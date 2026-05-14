<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InstrumentIncomeEvent extends Model
{
    use HasFactory;

    protected $table = 'instrumentincomeevents';

    protected $fillable = [
        'portfolioid',
        'instrumentid',
        'incometype',
        'announcementdate',
        'exdate',
        'recorddate',
        'paymentdate',
        'unitsheld',
        'amountperunit',
        'grosscashamount',
        'frankingpercent',
        'frankingcreditamount',
        'taxdeferredamount',
        'capitalgainscomponent',
        'foreignwithholdingtax',
        'netcashreceived',
        'currencycode',
        'fxrateaud',
        'isreinvested',
        'reinvestedtransactionid',
        'notes',
    ];

    protected $casts = [
        'portfolioid' => 'integer',
        'instrumentid' => 'integer',
        'announcementdate' => 'date',
        'exdate' => 'date',
        'recorddate' => 'date',
        'paymentdate' => 'date',
        'unitsheld' => 'decimal:6',
        'amountperunit' => 'decimal:6',
        'grosscashamount' => 'decimal:2',
        'frankingpercent' => 'decimal:2',
        'frankingcreditamount' => 'decimal:2',
        'taxdeferredamount' => 'decimal:2',
        'capitalgainscomponent' => 'decimal:2',
        'foreignwithholdingtax' => 'decimal:2',
        'netcashreceived' => 'decimal:2',
        'fxrateaud' => 'decimal:8',
        'isreinvested' => 'boolean',
        'reinvestedtransactionid' => 'integer',
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

    public function reinvestedTransaction(): BelongsTo
    {
        return $this->belongsTo(InstrumentTransaction::class, 'reinvestedtransactionid');
    }
}