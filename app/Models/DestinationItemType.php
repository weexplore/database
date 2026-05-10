<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DestinationItemType extends Model
{
    protected $table = 'destination_item_types';

    protected $fillable = [
        'typename',
        'slug',
        'sortorder',
        'isactive',
    ];

    public $timestamps = false;

    public function destinationItems()
    {
        return $this->belongsToMany(
            DestinationItem::class,
            'destinationitem_destination_item_type',
            'destination_item_type_id',
            'destinationitem_id'
        );
    }
}