<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Region extends Model
{
    protected $table = 'regions';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    const CREATED_AT = 'createdat';
    const UPDATED_AT = 'updatedat';

    protected $fillable = [
        'countryid',
        'stateid',
        'parentregionid',
        'regionname',
        'regiontype',
        'notes',
        'sortorder',
        'isactive',
    ];

    protected $casts = [
        'countryid' => 'integer',
        'stateid' => 'integer',
        'parentregionid' => 'integer',
        'sortorder' => 'integer',
        'isactive' => 'boolean',
    ];

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'countryid');
    }

    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class, 'stateid');
    }

    public function parentRegion(): BelongsTo
    {
        return $this->belongsTo(Region::class, 'parentregionid');
    }

    public function childRegions(): HasMany
    {
        return $this->hasMany(Region::class, 'parentregionid');
    }

    public function places(): HasMany
    {
        return $this->hasMany(Place::class, 'regionid');
    }
}