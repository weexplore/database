<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CashbookTransaction extends Model
{
    use HasFactory;

    protected $table = 'cashbook_transactions';

    const CREATED_AT = 'createdat';
    const UPDATED_AT = 'updatedat';

    protected $fillable = [
        'legalentityid',
        'accountid',
        'transferaccountid',
        'transactionkind',
        'transactiondate',
        'posteddate',
        'referencenumber',
        'payeename',
        'description',
        'amounttotal',
        'isreconciled',
        'reconciledat',
        'sourcetype',
        'externalsourcekey',
        'importbatchid',
        'isduplicatecandidate',
        'duplicateoftransactionid',
        'needsallocation',
        'notes',
    ];

    protected $casts = [
        'transactiondate' => 'date',
        'posteddate' => 'date',
        'amounttotal' => 'decimal:2',
        'isreconciled' => 'boolean',
        'reconciledat' => 'datetime',
        'isduplicatecandidate' => 'boolean',
        'needsallocation' => 'boolean',
    ];

    public function legalEntity(): BelongsTo
    {
        return $this->belongsTo(LegalEntity::class, 'legalentityid');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(CashbookAccount::class, 'accountid');
    }

    public function transferAccount(): BelongsTo
    {
        return $this->belongsTo(CashbookAccount::class, 'transferaccountid');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(CashbookTransactionLine::class, 'transactionid')
            ->orderBy('linenumber');
    }

    public function duplicateOf(): BelongsTo
    {
        return $this->belongsTo(self::class, 'duplicateoftransactionid');
    }

    public function duplicateCandidates(): HasMany
    {
        return $this->hasMany(self::class, 'duplicateoftransactionid');
    }

    public function isTransfer(): bool
    {
        return $this->transactionkind === 'transfer';
    }

    public function isReceipt(): bool
    {
        return $this->transactionkind === 'receipt';
    }

    public function isPayment(): bool
    {
        return $this->transactionkind === 'payment';
    }
}