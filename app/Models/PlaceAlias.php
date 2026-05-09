<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlaceAlias extends Model
{
    protected $table = 'placealiases';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    const CREATED_AT = 'createdat';
    const UPDATED_AT = 'updatedat';

    protected $fillable = [
        'placeid',
        'aliasname',
        'aliastype',
    ];

    protected $casts = [
        'placeid' => 'integer',
    ];

    public function place(): BelongsTo
    {
        return $this->belongsTo(Place::class, 'placeid');
    }
}