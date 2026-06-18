<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashbookTransactionLine extends Model
{
    use HasFactory;

    protected $table = 'cashbook_transaction_lines';

    const CREATED_AT = 'createdat';
    const UPDATED_AT = 'updatedat';

    protected $fillable = [
        'transactionid',
        'linenumber',
        'categoryid',
        'linedescription',
        'amount',
        'taxcode',
        'notes',
    ];

    protected $casts = [
        'linenumber' => 'integer',
        'amount' => 'decimal:2',
    ];

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(CashbookTransaction::class, 'transactionid');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(CashbookCategory::class, 'categoryid');
    }

}
