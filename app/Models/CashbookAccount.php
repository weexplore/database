<?php

namespace App\Models;

use App\Models\BudgetLine;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CashbookAccount extends Model
{
    use HasFactory;

    protected $table = 'cashbook_accounts';

    const CREATED_AT = 'createdat';
    const UPDATED_AT = 'updatedat';

    protected $fillable = [
        'legalentityid',
        'accounttypeid',
        'accountcode',
        'accountname',
        'institutionname',
        'accountnumbermasked',
        'currencycode',
        'openingbalance',
        'openingbalancedate',
        'includeincashreporting',
        'isreconcilable',
        'isactive',
        'notes',
        'lastqifimportedat',
        'lastqiftransactiondate',
        'defaultunallocatedreceiptcategoryid',
        'defaultunallocatedpaymentcategoryid',
    ];

    protected $casts = [
        'openingbalance' => 'decimal:2',
        'openingbalancedate' => 'date',
        'includeincashreporting' => 'boolean',
        'isreconcilable' => 'boolean',
        'isactive' => 'boolean',
        'lastqifimportedat' => 'datetime',
        'lastqiftransactiondate' => 'date',
    ];

    public function legalEntity(): BelongsTo
    {
        return $this->belongsTo(LegalEntity::class, 'legalentityid');
    }

    public function accountType(): BelongsTo
    {
        return $this->belongsTo(CashbookAccountType::class, 'accounttypeid');
    }

    public function cashbookTransactions(): HasMany
    {
        return $this->hasMany(CashbookTransaction::class, 'accountid');
    }

    public function inboundTransfers(): HasMany
    {
        return $this->hasMany(CashbookTransaction::class, 'transferaccountid');
    }

    public function defaultUnallocatedReceiptCategory(): BelongsTo
    {
        return $this->belongsTo(CashbookCategory::class, 'defaultunallocatedreceiptcategoryid');
    }

    public function defaultUnallocatedPaymentCategory(): BelongsTo
    {
        return $this->belongsTo(CashbookCategory::class, 'defaultunallocatedpaymentcategoryid');
    }

    public function budgetLines(): HasMany
    {
        return $this->hasMany(BudgetLine::class, 'accountid');
    }
}