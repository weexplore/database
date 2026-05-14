<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;


class Exchange extends Model
{
    use HasFactory;

    protected $table = 'exchanges';

    protected $fillable = [
        'exchangecode',
        'exchangename',
        'countrycode',
        'defaultcurrencycode',
        'marketwebsite',
        'timezone',
        'isactive',
    ];

    protected $casts = [
        'isactive' => 'boolean',
    ];

    const CREATED_AT = 'createdat';
    const UPDATED_AT = 'updatedat';

    public function instruments(): HasMany
    {
        return $this->hasMany(Instrument::class, 'exchangeid');
    }
}