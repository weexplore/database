<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Relations\Relation;
use App\Models\Destination;
use App\Models\DestinationItem;
use App\Models\Booking;
use App\Models\Review;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Relation::enforceMorphMap([
            'destination' => Destination::class,
            'destination_item' => DestinationItem::class,
            'booking' => Booking::class,
            'review' => Review::class,
        ]);
    }
}
