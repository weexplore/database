<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CashbookCategoryType extends Model
{
    use HasFactory;

    protected $table = 'cashbook_category_types';

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

    public function cashbookCategories(): HasMany
    {
        return $this->hasMany(CashbookCategory::class, 'categorytypeid');
    }
}
