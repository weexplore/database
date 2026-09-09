<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TripExpense extends Model
{
    use HasFactory;

    protected $table = 'tripexpenses';

    public const CREATED_AT = 'createdat';

    public const UPDATED_AT = 'updatedat';

    protected $fillable = [
        'tripid',
        'triplegid',
        'tripstayid',
        'placeid',

        'expensedate',
        'expensecategory',
        'subcategory',

        'description',
        'payee',

        'amount',
        'currency',
        'paymentmethod',

        'notes',
        'receiptreference',
    ];

    protected $casts = [
        'tripid' => 'integer',
        'triplegid' => 'integer',
        'tripstayid' => 'integer',
        'placeid' => 'integer',

        'expensedate' => 'date',

        'amount' => 'decimal:2',
    ];

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class, 'tripid');
    }

    public function tripLeg(): BelongsTo
    {
        return $this->belongsTo(TripLeg::class, 'triplegid');
    }

    public function tripStay(): BelongsTo
    {
        return $this->belongsTo(TripStay::class, 'tripstayid');
    }

    public function place(): BelongsTo
    {
        return $this->belongsTo(Place::class, 'placeid');
    }

    public static function categoryOptions(): array
    {
        return [
            'food' => 'Food',
            'misc' => 'Miscellaneous',
            'toll' => 'Toll',
            'parking' => 'Parking',
            'laundry' => 'Laundry',
            'medical' => 'Medical',
            'pharmacy' => 'Pharmacy',
            'supplies' => 'Supplies',
            'maintenance' => 'Maintenance',
            'craft' => 'Craft',
            'other' => 'Other',
        ];
    }

    public static function paymentMethodOptions(): array
    {
        return [
            'cash' => 'Cash',
            'debit_card' => 'Debit card',
            'credit_card' => 'Credit card',
            'bank_transfer' => 'Bank transfer',
            'other' => 'Other',
        ];
    }

    public function getCategoryLabelAttribute(): string
    {
        return static::categoryOptions()[$this->expensecategory]
            ?? ucfirst(str_replace('_', ' ', (string) $this->expensecategory));
    }

    public function getPaymentMethodLabelAttribute(): ?string
    {
        if (blank($this->paymentmethod)) {
            return null;
        }

        return static::paymentMethodOptions()[$this->paymentmethod]
            ?? ucfirst(str_replace('_', ' ', (string) $this->paymentmethod));
    }
}