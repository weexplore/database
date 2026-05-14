<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class BibleBook extends Model
{
    use HasFactory;

    protected $table = 'biblebooks';

    protected $fillable = [
        'bookcode',
        'bookname',
        'testament',
        'sortorder',
        'chaptercount',
    ];

    protected $casts = [
        'sortorder' => 'integer',
        'chaptercount' => 'integer',
    ];

    const CREATED_AT = 'createdat';
    const UPDATED_AT = 'updatedat';

    public function references(): HasMany
    {
        return $this->hasMany(BibleReference::class, 'bookid');
    }
}