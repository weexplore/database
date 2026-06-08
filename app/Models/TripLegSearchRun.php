<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TripLegSearchRun extends Model
{
    protected $table = 'trip_leg_search_runs';

    const CREATED_AT = 'createdat';
    const UPDATED_AT = 'updatedat';

    protected $fillable = [
        'trip_id',
        'trip_leg_id',
        'profile_id',
        'run_status',
        'corridor_type',
        'radius_km_used',
        'minimum_results_target',
        'results_found',
        'started_at',
        'completed_at',
        'summary',
        'notes',
    ];

    protected $casts = [
        'radius_km_used' => 'decimal:1',
        'minimum_results_target' => 'integer',
        'results_found' => 'integer',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class);
    }

    public function tripLeg(): BelongsTo
    {
        return $this->belongsTo(TripLeg::class, 'trip_leg_id');
    }

    public function profile(): BelongsTo
    {
        return $this->belongsTo(TripLegSearchProfile::class, 'profile_id');
    }

    public function suggestions(): HasMany
    {
        return $this->hasMany(TripLegSuggestion::class, 'run_id')
            ->orderBy('rank_score', 'desc')
            ->orderBy('distance_from_corridor_km')
            ->orderBy('id');
    }
}