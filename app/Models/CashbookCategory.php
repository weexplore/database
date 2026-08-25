<?php

namespace App\Models;


use App\Models\BudgetLine;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CashbookCategory extends Model
{
    use HasFactory;

    protected $table = 'cashbook_categories';

    const CREATED_AT = 'createdat';
    const UPDATED_AT = 'updatedat';

    protected $fillable = [
        'legalentityid',
        'categorytypeid',
        'parentcategoryid',
        'categorycode',
        'categoryname',
        'allowposting',
        'issystem',
        'sortorder',
        'isactive',
        'notes',
    ];

    protected $casts = [
        'allowposting' => 'boolean',
        'issystem' => 'boolean',
        'sortorder' => 'integer',
        'isactive' => 'boolean',
    ];

    public function legalEntity(): BelongsTo
    {
        return $this->belongsTo(LegalEntity::class, 'legalentityid');
    }

    public function categoryType(): BelongsTo
    {
        return $this->belongsTo(CashbookCategoryType::class, 'categorytypeid');
    }

    public function parentCategory(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parentcategoryid');
    }

    public function childCategories(): HasMany
    {
        return $this->hasMany(self::class, 'parentcategoryid');
    }

    public function transactionLines(): HasMany
    {
        return $this->hasMany(CashbookTransactionLine::class, 'categoryid');
    }

    public function budgetLines(): HasMany
    {
        return $this->hasMany(BudgetLine::class, 'categoryid');
    }

    /**
     * Return ancestor categories from the root down to this category's parent.
     * The current category is not included.
     */
    public function ancestors(): \Illuminate\Support\Collection
    {
        $ancestors = collect();
        $category = $this->parentCategory;

        while ($category) {
            $ancestors->prepend($category);
            $category = $category->parentCategory;
        }

        return $ancestors;
    }
}
