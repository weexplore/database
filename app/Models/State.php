<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class State extends Model
{
    protected $table = 'states';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    const CREATED_AT = 'createdat';
    const UPDATED_AT = 'updatedat';

    protected $fillable = [
        'countryid',
        'statecode',
        'statename',
        'sortorder',
        'isactive',
    ];

    protected $casts = [
        'countryid' => 'integer',
        'sortorder' => 'integer',
        'isactive' => 'boolean',
    ];

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'countryid');
    }

    public function regions(): HasMany
    {
        return $this->hasMany(Region::class, 'stateid');
    }

    public function places(): HasMany
    {
        return $this->hasMany(Place::class, 'stateid');
    }
}