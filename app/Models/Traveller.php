<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Traveller extends Model
{
    protected $table = 'travellers';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    const CREATED_AT = 'createdat';
    const UPDATED_AT = 'updatedat';

    protected $fillable = [
        'firstname',
        'lastname',
        'displayname',
        'isprimarytraveller',
        'isactive',
    ];

    protected $casts = [
        'isprimarytraveller' => 'boolean',
        'isactive' => 'boolean',
    ];

    public function tripTravellerLinks(): HasMany
    {
        return $this->hasMany(TripTraveller::class, 'travellerid');
    }

    public function trips(): BelongsToMany
    {
        return $this->belongsToMany(
            Trip::class,
            'triptravellers',
            'travellerid',
            'tripid'
        )->withPivot(['id', 'rolename', 'createdat', 'updatedat']);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class, 'travellerid');
    }
}