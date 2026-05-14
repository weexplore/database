<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Instrument extends Model
{
    use HasFactory;

    protected $table = 'instruments';

    protected $fillable = [
        'instrumenttypeid',
        'exchangeid',
        'knowledgeitemid',
        'symbol',
        'instrumentname',
        'isin',
        'apiric',
        'currencycode',
        'fundmanager',
        'sector',
        'industry',
        'domicilecountrycode',
        'status',
        'website',
        'notes',
        'isactive',
    ];

    protected $casts = [
        'instrumenttypeid' => 'integer',
        'exchangeid' => 'integer',
        'knowledgeitemid' => 'integer',
        'isactive' => 'boolean',
    ];

    const CREATED_AT = 'createdat';
    const UPDATED_AT = 'updatedat';

    public function instrumentType(): BelongsTo
    {
        return $this->belongsTo(InstrumentType::class, 'instrumenttypeid');
    }

    public function exchange(): BelongsTo
    {
        return $this->belongsTo(Exchange::class, 'exchangeid');
    }

    public function knowledgeItem(): BelongsTo
    {
        return $this->belongsTo(KnowledgeItem::class, 'knowledgeitemid');
    }

    public function aliases(): HasMany
    {
        return $this->hasMany(InstrumentAlias::class, 'instrumentid');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(InstrumentTransaction::class, 'instrumentid');
    }

    public function incomeEvents(): HasMany
    {
        return $this->hasMany(InstrumentIncomeEvent::class, 'instrumentid');
    }

    public function corporateActions(): HasMany
    {
        return $this->hasMany(InstrumentCorporateAction::class, 'instrumentid');
    }

    public function priceObservations(): HasMany
    {
        return $this->hasMany(InstrumentPriceObservation::class, 'instrumentid');
    }

    public function holdingSnapshots(): HasMany
    {
        return $this->hasMany(PortfolioHoldingSnapshot::class, 'instrumentid');
    }
}