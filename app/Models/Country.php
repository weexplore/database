<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Country extends Model
{
    protected $table = 'countries';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    const CREATED_AT = 'createdat';
    const UPDATED_AT = 'updatedat';

    protected $fillable = [
        'countrycode',
        'countryname',
        'sortorder',
        'isactive',
    ];

    protected $casts = [
        'sortorder' => 'integer',
        'isactive' => 'boolean',
    ];

    public function states(): HasMany
    {
        return $this->hasMany(State::class, 'countryid');
    }

    public function regions(): HasMany
    {
        return $this->hasMany(Region::class, 'countryid');
    }

    public function places(): HasMany
    {
        return $this->hasMany(Place::class, 'countryid');
    }
}