<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TripLegSearchProfileFilter extends Model
{
    protected $table = 'trip_leg_search_profile_filters';

    const CREATED_AT = 'createdat';
    const UPDATED_AT = 'updatedat';


    protected $fillable = [
        'profile_id',
        'filter_type',
        'filter_value',
        'is_include',
        'sort_order',
        'notes',
    ];

    protected $casts = [
        'is_include' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function profile(): BelongsTo
    {
        return $this->belongsTo(TripLegSearchProfile::class, 'profile_id');
    }
}