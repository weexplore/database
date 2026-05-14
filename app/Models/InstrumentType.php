<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InstrumentType extends Model
{
    use HasFactory;

    protected $table = 'instrumenttypes';

    protected $fillable = [
        'typecode',
        'typename',
        'hasunits',
        'hasdividends',
        'hasdistributions',
        'notes',
        'isactive',
    ];

    protected $casts = [
        'hasunits' => 'boolean',
        'hasdividends' => 'boolean',
        'hasdistributions' => 'boolean',
        'isactive' => 'boolean',
    ];

    const CREATED_AT = 'createdat';
    const UPDATED_AT = 'updatedat';

    public function instruments(): HasMany
    {
        return $this->hasMany(Instrument::class, 'instrumenttypeid');
    }
}