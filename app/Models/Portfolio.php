<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Portfolio extends Model
{
    use HasFactory;

    protected $table = 'portfolios';

    protected $fillable = [
        'portfolioname',
        'portfoliotype',
        'basecurrencycode',
        'ownernotes',
        'isactive',
    ];

    protected $casts = [
        'isactive' => 'boolean',
    ];

    const CREATED_AT = 'createdat';
    const UPDATED_AT = 'updatedat';

    public function members(): HasMany
    {
        return $this->hasMany(PortfolioMember::class, 'portfolioid');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(InstrumentTransaction::class, 'portfolioid');
    }

    public function incomeEvents(): HasMany
    {
        return $this->hasMany(InstrumentIncomeEvent::class, 'portfolioid');
    }

    public function holdingSnapshots(): HasMany
    {
        return $this->hasMany(PortfolioHoldingSnapshot::class, 'portfolioid');
    }
}