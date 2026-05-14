<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PortfolioMember extends Model
{
    use HasFactory;

    protected $table = 'portfoliomembers';

    protected $fillable = [
        'portfolioid',
        'membername',
        'rolelabel',
    ];

    protected $casts = [
        'portfolioid' => 'integer',
    ];

    const CREATED_AT = 'createdat';
    const UPDATED_AT = 'updatedat';

    public function portfolio(): BelongsTo
    {
        return $this->belongsTo(Portfolio::class, 'portfolioid');
    }
}