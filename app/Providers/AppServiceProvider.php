<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Relations\Relation;
use App\Models\Destination;
use App\Models\DestinationItem;
use App\Models\Booking;
use App\Models\Review;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;


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
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by(
                strtolower((string) $request->input('email'))
                . '|'
                . $request->ip()
            );
        });
    }
}
