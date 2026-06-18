<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LegalEntity extends Model
{
    use HasFactory;

    protected $table = 'legal_entities';
    
    const CREATED_AT = 'createdat';
    const UPDATED_AT = 'updatedat';

    protected $fillable = [
        'entitycode',
        'entityname',
        'entitytype',
        'abn',
        'acn',
        'sortorder',
        'isactive',
        'notes',
    ];

    protected $casts = [
        'sortorder' => 'integer',
        'isactive' => 'boolean',
    ];

    public function cashbookAccounts(): HasMany
    {
        return $this->hasMany(CashbookAccount::class, 'legalentityid');
    }

    public function cashbookCategories(): HasMany
    {
        return $this->hasMany(CashbookCategory::class, 'legalentityid');
    }

    public function cashbookTransactions(): HasMany
    {
        return $this->hasMany(CashbookTransaction::class, 'legalentityid');
    }
}