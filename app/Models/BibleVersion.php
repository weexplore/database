<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BibleVersion extends Model
{
    use HasFactory;

    protected $table = 'bibleversions';

protected $fillable = [
    'versioncode',
    'apibibleid',
    'apiversionlabel',
    'versionname',
    'languagecode',
    'notes',
    'isactive',
    'apisyncedat',
];

protected $casts = [
    'isactive' => 'boolean',
    'apisyncedat' => 'datetime',
];

    const CREATED_AT = 'createdat';
    const UPDATED_AT = 'updatedat';

    public function references(): HasMany
    {
        return $this->hasMany(BibleReference::class, 'versionid');
    }
}