<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InstrumentPriceObservation extends Model
{
    use HasFactory;

    protected $table = 'instrumentpriceobservations';

    protected $fillable = [
        'instrumentid',
        'observedon',
        'priceopen',
        'pricehigh',
        'pricelow',
        'priceclose',
        'adjustedclose',
        'volume',
        'currencycode',
        'pricesource',
        'observationnotes',
    ];

    protected $casts = [
        'instrumentid' => 'integer',
        'observedon' => 'date',
        'priceopen' => 'decimal:6',
        'pricehigh' => 'decimal:6',
        'pricelow' => 'decimal:6',
        'priceclose' => 'decimal:6',
        'adjustedclose' => 'decimal:6',
        'volume' => 'integer',
    ];

    const CREATED_AT = 'createdat';
    const UPDATED_AT = 'updatedat';

    public function instrument(): BelongsTo
    {
        return $this->belongsTo(Instrument::class, 'instrumentid');
    }
}