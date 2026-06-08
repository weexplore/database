<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TripLegSearchProfile extends Model
{
    protected $table = 'trip_leg_search_profiles';

    const CREATED_AT = 'createdat';
    const UPDATED_AT = 'updatedat';

    protected $fillable = [
        'tripid',
        'profilename',
        'profiletype',
        'searchmode',
        'corridortype',
        'defaultmaxresults',
        'notes',
        'isdefault',
        'isactive',
        'maxdetourkm',
        'maxdistancefromroutekm',
        'maxresultcount',
        'minrouteproximityscore',
        'routemode',
        'avoidtolls',
        'avoidferries',
        'avoidunpaved',
        'requirecaravanaccess',
        'includeplacetypes',
        'excludeplacetypes',
        'searchbufferkm',
        'minstopintervalkm',
        'distancemethod',
        'includelegpointsindistance',
        'usedestinationitemsasanchors',
        'rounddistancekmto',
    ];

    protected $casts = [
        'tripid' => 'integer',
        'defaultmaxresults' => 'integer',
        'isdefault' => 'boolean',
        'isactive' => 'boolean',
        'maxdetourkm' => 'decimal:1',
        'maxdistancefromroutekm' => 'decimal:1',
        'maxresultcount' => 'integer',
        'minrouteproximityscore' => 'decimal:4',
        'avoidtolls' => 'boolean',
        'avoidferries' => 'boolean',
        'avoidunpaved' => 'boolean',
        'requirecaravanaccess' => 'boolean',
        'searchbufferkm' => 'decimal:1',
        'minstopintervalkm' => 'decimal:1',
        'includelegpointsindistance' => 'boolean',
        'usedestinationitemsasanchors' => 'boolean',
        'rounddistancekmto' => 'decimal:1',
    ];

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class, 'tripid');
    }

    public function filters(): HasMany
    {
        return $this->hasMany(TripLegSearchProfileFilter::class, 'profileid')
            ->orderBy('sortorder')
            ->orderBy('id');
    }

    public function runs(): HasMany
    {
        return $this->hasMany(TripLegSearchRun::class, 'profileid')
            ->latest('id');
    }

    public function suggestions(): HasMany
    {
        return $this->hasMany(TripLegSuggestion::class, 'profileid')
            ->latest('id');
    }

    public function tripsUsingAsDefault(): HasMany
    {
        return $this->hasMany(Trip::class, 'triplegsearchprofileid', 'id');
    }

    public function tripLegsUsingAsOverride(): HasMany
    {
        return $this->hasMany(TripLeg::class, 'triplegsearchprofileid', 'id');
    }
    public function scopeActive($query)
    {
        return $query->where('isactive', 1);
    }

    public function scopeSystem($query)
    {
        return $query->where('profiletype', 'system');
    }

    public function scopeDefault($query)
    {
        return $query->where('isdefault', 1);
    }
}