<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CashbookAccountType extends Model
{
    use HasFactory;

    protected $table = 'cashbook_account_types';

    const CREATED_AT = 'createdat';
    const UPDATED_AT = 'updatedat';

    protected $fillable = [
        'typecode',
        'typename',
        'sortorder',
        'isactive',
    ];

    protected $casts = [
        'sortorder' => 'integer',
        'isactive' => 'boolean',
    ];

    public function cashbookAccounts(): HasMany
    {
        return $this->hasMany(CashbookAccount::class, 'accounttypeid');
    }
}